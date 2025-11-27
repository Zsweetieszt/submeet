<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Paper;
use App\Models\Review;
use App\Models\ReviewItem;
use App\Models\ReviewOption;
use App\Models\Assignment;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use App\Services\EmailService;

class ReviewerController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function index($event)
    {
        try {
            $eventObj = Event::where('event_code', $event)->first();

            if (!$eventObj) {
                return redirect()->back()->withErrors('Event not found.');
            }

            $event_name = $eventObj->event_name;
            $event_id = $eventObj->event_id;
            $reviewer_id = auth()->user()->user_id;

            $papers = Paper::with([
                'user',
                'assignment' => function ($query) use ($reviewer_id) {
                    $query->where('reviewer_id', $reviewer_id);
                },
                'assignment.assigner',
                'decisions',
            ])->whereHas('assignment', function ($query) use ($event_id, $reviewer_id) {
                $query->where('reviewer_id', $reviewer_id)
                    ->whereHas('paper', function ($subQuery) use ($event_id) {
                        $subQuery->where('event_id', $event_id);
                    });
            })->orderBy('paper_sub_id', 'desc')
            ->get();
            // dd($papers);

            $firstPaperIds = $papers->pluck('first_paper_sub_id')->toArray();

            $allPaperVersions = Paper::whereIn('first_paper_sub_id', $firstPaperIds)
                ->with(['assignment.reviews', 'assignment.reviewer'])
                ->orderBy('round', 'desc')
                ->get();

            // dd( $allPaperVersions);

            $paperRoundHistory = $allPaperVersions->groupBy('first_paper_sub_id');

            $reviewerAssignments = Assignment::where('reviewer_id', $reviewer_id)
                ->whereHas('paper', function ($query) use ($event_id) {
                    $query->where('event_id', $event_id);
                })
                ->pluck('assign_id')
                ->toArray();

            $reviews = Review::whereIn('assign_id', $reviewerAssignments)->pluck('assign_id')->toArray();

            return view('pages.reviewer.index', compact(['papers', 'reviews', 'event_name', 'paperRoundHistory']));
        } catch (\Exception $e) {
            \Log::error("Error loading reviewer index: " . str_replace(["\r", "\n"], ' ', $e->getMessage()) . " Stack: " . $e->getTraceAsString());
            return redirect()->back()->withErrors('An error occurred while loading the reviewer dashboard.');
        }
    }

    public function check($event, $paper_id)
    {
        $paper = Paper::where('paper_sub_id', $paper_id)->first();

        if (!$paper) {
            return redirect()->back()->withErrors('Paper not found.');
        }

        try {
            $filePath = storage_path('app/public/' . config('path.paper') . $paper->attach_file);

            if (!file_exists($filePath)) {
                return redirect()->back()->withErrors('File not found on server.');
            }

            return response()->download($filePath);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('An error occurred while downloading the file: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function download_review_file($event, $paper_id)
    {
        try {
            $eventObj = Event::where('event_code', $event)->firstOrFail();
            $paper = Paper::where('paper_sub_id', $paper_id)->firstOrFail();

            if ($paper->event_id !== $eventObj->event_id) {
                return redirect()->back()->withErrors('Paper not found in this event.');
            }

            $assignment = Assignment::where('paper_sub_id', $paper_id)
                ->where('reviewer_id', auth()->user()->user_id)
                ->whereHas('paper', function ($query) use ($eventObj) {
                    $query->where('event_id', $eventObj->event_id);
                })
                ->first();

            if (!$assignment) {
                return redirect()->back()->withErrors('You are not assigned to review this paper.');
            }

            $review = Review::where('assign_id', $assignment->assign_id)->first();

            if (!$review || !$review->attach_file) {
                return redirect()->back()->withErrors('No review file attached.');
            }

            $filePath = storage_path('app/public/' . $review->attach_file);

            if (!file_exists($filePath)) {
                return redirect()->back()->withErrors('File not found on server.');
            }

            return response()->download($filePath);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->back()->withErrors('Event or paper not found.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('An error occurred while downloading the file: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function review($event, $paper_id)
    {
        try {

            $eventObj = Event::where('event_code', $event)->firstOrFail();
            $event_name = $eventObj->event_name;
            $paper = Paper::with(['topicpapers.topic', 'decisions'])->where('paper_sub_id', $paper_id)->firstOrFail();
            if (count($paper->decisions) > 0) {
                return redirect()->route('events.reviewer', [$event])
                    ->withErrors('This paper has already been decided.');
            }

            $reviewItems = ReviewItem::with('options')
                ->where('event_id', $eventObj->event_id)
                ->orderBy('seq')
                ->get();

            return view('pages.reviewer.review', compact('event_name', 'paper', 'reviewItems'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('events.reviewer', [$event])
                ->withErrors('Event or paper not found.');
        } catch (\Exception $e) {
            return redirect()->route('events.reviewer', [$event])
                ->withErrors('An error occurred while loading the review form.');
        }
    }

    public function submit_review(Request $request, $event, $paper_id)
    {
        try {
            $eventObj = Event::where('event_code', $event)->firstOrFail();
            $paper = Paper::with(['decisions'])->where('paper_sub_id', $paper_id)->firstOrFail();
            if (count($paper->decisions) > 0) {
                return redirect()->route('events.reviewer', [$event])
                    ->withErrors('This paper has already been decided.');
            }
            $assignment = Assignment::where('paper_sub_id', $paper_id)
                ->where('reviewer_id', auth()->user()->user_id)
                ->whereHas('paper', function ($query) use ($eventObj) {
                    $query->where('event_id', $eventObj->event_id);
                })
                ->first();

            if (!$assignment) {
                return back()->withErrors('Assignment not found or you are not assigned to review this paper for this event.');
            }

            // Validasi input
            $request->validate([
                'options' => 'required|array',
                'options.*' => 'required|integer',
                'note_for_author' => 'required|string',
                'recommendation' => 'required|in:Accept,Minor Revisions,Major Revisions,Decline',
                'note_for_editor' => 'max:3000',
                'paper_file' => 'nullable|file|mimes:doc,docx|max:5120', // Mengubah 'required' menjadi 'nullable' jika upload file tidak selalu wajib
            ], [
                'options.required' => 'All review items must be filled.',
                'options.*.required' => 'All review items must be filled.',
                'note_for_author.required' => 'Overall evaluation is required.',
                'recommendation.in' => 'Recommendation must be one of: Accept, Minor Revisions, Major Revisions, Decline.', // Pesan error disesuaikan
                'paper_file.mimes' => 'Allowed file type: DOC/DOCX.',
                'paper_file.max' => 'Max. file size: 5 MB.',
            ]);

            DB::beginTransaction();

            // --- Handle File Lampiran ---
            $attachFile = null;
            $attachUrl = null;

            if ($request->hasFile('paper_file')) {
                $file = $request->file('paper_file');
                $parent_paper_id = $paper->first_paper_sub_id ?? $paper->paper_sub_id;
                $round = $paper->round ?? 0;
                $folder = 'review_files/' . $eventObj->event_id . '/' . $parent_paper_id;
                $filename = $eventObj->event_id . '_' . $parent_paper_id . '_' . $assignment->reviewer_id . '_r_' . $round . '.' . $file->getClientOriginalExtension();
                $path = $file->storeAs($folder, $filename, 'public');
                $attachFile = $path;
            }

            $review = Review::create([
                'assign_id' => $assignment->assign_id,
                'note_for_author' => $request->note_for_author,
                'note_for_editor' => $request->note_for_editor,
                'recommendation' => $request->recommendation,
                'attach_file' => $attachFile,
                'attach_url' => $attachUrl,
            ]);

            foreach ($request->options as $review_item_id => $scale) {
                $option = ReviewOption::where('review_item_id', $review_item_id)
                    ->where('scale', $scale)
                    ->first();

                if ($option) {
                    DB::table('review_contents')->insert([
                        'review_id' => $review->review_id,
                        'review_item_id' => $review_item_id,
                        'value' => $scale,
                        'created_by' => auth()->user()->user_id,
                        'updated_by' => auth()->user()->user_id,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }
            }

            DB::commit();

            // Send notification email to author
            $editors = User::whereHas('user_events', function ($query) use ($eventObj) {
                $query->where('event_id', $eventObj->event_id);
            })
                ->whereHas('user_events.role', function ($query) {
                    $query->where('role_name', 'Editor');
                })->get();

            $emailService = app(EmailService::class);

            foreach ($editors as $editor) {
                try {
                    $emailService->sendEditorReviewSubmitted($editor, auth()->user(), $paper, $eventObj, $review);
                } catch (\Exception $e) {
                    \Log::error("Gagal mengirim email ke editor ID {$editor->user_id}: " . str_replace(["\r", "\n"], ' ', $e->getMessage()));
                }
            }

            return redirect()->route('events.reviewer', [$event])->with('success', 'Review submitted successfully.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Error submitting review: " . str_replace(["\r", "\n"], ' ', $e->getMessage()) . " Stack: " . $e->getTraceAsString());
            return back()->withErrors('An error occurred: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        }
    }

    public function view_review($event, $paper_id)
    {
        try {
            $eventObj = Event::where('event_code', $event)->firstOrFail();
            $paper = Paper::with(['topicpapers.topic', 'user'])->where('paper_sub_id', $paper_id)->firstOrFail();

            if ($paper->event_id !== $eventObj->event_id) {
                return redirect()->route('events.reviewer', [$event])
                    ->withErrors('Paper not found in this event.');
            }

            $assignment = Assignment::where('paper_sub_id', $paper_id)
                ->where('reviewer_id', auth()->user()->user_id)
                ->whereHas('paper', function ($query) use ($eventObj) {
                    $query->where('event_id', $eventObj->event_id);
                })
                ->first();

            if (!$assignment) {
                return redirect()->route('events.reviewer', [$event])
                    ->withErrors('You are not assigned to review this paper.');
            }

            $review = Review::where('assign_id', $assignment->assign_id)->first();

            $reviewContents = $review
                ? DB::table('review_contents')
                    ->where('review_id', $review->review_id)
                    ->get()
                : collect();

            return view('pages.reviewer.view_review', [
                'event' => $eventObj,
                'paper' => $paper,
                'review' => $review,
                'reviewContents' => $reviewContents,
            ]);

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('events.reviewer', [$event])
                ->withErrors('Event or paper not found.');
        } catch (\Exception $e) {
            \Log::error("Error viewing review: " . str_replace(["\r", "\n"], ' ', $e->getMessage()) . " Stack: " . $e->getTraceAsString());
            return redirect()->route('events.reviewer', [$event])
                ->withErrors('An error occurred while loading the review.');
        }
    }

    public function history($event)
    {
        try {
            $eventObj = Event::where('event_code', $event)->firstOrFail();
            $event_name = $eventObj->event_name;
            $reviewer_id = auth()->user()->user_id;

            $assignments = Assignment::where('reviewer_id', $reviewer_id)
                ->whereHas('paper', function ($query) use ($eventObj) {
                    $query->where('event_id', $eventObj->event_id);
                })->orderBy('created_at', 'desc')
                ->get();

            $reviewHistory = [];

            foreach ($assignments as $assignment) {
                $review = Review::where('assign_id', $assignment->assign_id)->first();

                if ($review) {
                    $paper = Paper::where('paper_sub_id', $assignment->paper_sub_id)->first();

                    if ($paper) {
                        $parentPaperId = $paper->first_paper_sub_id ?? $paper->paper_sub_id;

                        if (!isset($reviewHistory[$parentPaperId])) {
                            $reviewHistory[$parentPaperId] = [
                                'paper_title' => $paper->title,
                                'paper_id' => $parentPaperId,
                                'reviews' => []
                            ];
                        }

                        $round = $paper->round;

                        $reviewHistory[$parentPaperId]['reviews'][] = [
                            'review' => $review,
                            'paper' => $paper,
                            'round' => $round
                        ];
                    }
                }
            }

            foreach ($reviewHistory as &$history) {
                usort($history['reviews'], function ($a, $b) {
                    return $b['round'] <=> $a['round'];
                });
            }
            return view('pages.reviewer.history', compact('event_name', 'reviewHistory', 'eventObj'));

        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('events.reviewer', [$event])
                ->withErrors('Event not found.');
        } catch (\Exception $e) {
            \Log::error("Error loading review history: " . str_replace(["\r", "\n"], ' ', $e->getMessage()) . " Stack: " . $e->getTraceAsString());
            return redirect()->route('events.reviewer', [$event])
                ->withErrors('An error occurred while loading review history.');
        }
    }
}