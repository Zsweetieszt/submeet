<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Decision;
use App\Models\Event;
use App\Models\ExpertiseUser;
use App\Models\Paper;
use App\Models\Payment;
use App\Models\Review;
use App\Models\ReviewItem;
use App\Models\User;
use App\Models\UserEvent;
use DB;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\TopicUser;
use App\Services\EmailService;
use NcJoes\OfficeConverter\OfficeConverter;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Models\UserLogs;

class EditorController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }

    public function assign_reviewer(Request $request, $event, $paperId)
    {
        try {
            $request->validate([
                'reviewer1' => 'required|exists:users,user_id',
                'reviewer2' => 'nullable|exists:users,user_id',
                'reviewer3' => 'nullable|exists:users,user_id',
            ]);

            return DB::transaction(function () use ($request, $paperId) {
                $paper = Paper::where('paper_sub_id', $paperId)->firstOrFail();
                $firstPaper = Paper::where('paper_sub_id', $paper->first_paper_sub_id)->firstOrFail();

                if ($firstPaper->status !== 'In Review') {
                    $firstPaper->update(['status' => 'In Review']);
                }

                // Ambil assignment lama dengan info review
                $oldAssignments = Assignment::where('paper_sub_id', $paper->paper_sub_id)
                    ->with('reviews')
                    ->get();

                // Reviewer baru dari request
                $newReviewerIds = array_values(array_filter([
                    $request->reviewer1,
                    $request->reviewer2,
                    $request->reviewer3,
                ]));

                // Pisahkan reviewer yang sudah review dan yang belum
                $reviewersWithReviews = [];
                $reviewersWithoutReviews = [];
                $oldReviewerIds = [];

                foreach ($oldAssignments as $assignment) {
                    $oldReviewerIds[] = $assignment->reviewer_id;

                    if ($assignment->reviews->count() > 0) {
                        $reviewersWithReviews[] = $assignment->reviewer_id;
                    } else {
                        $reviewersWithoutReviews[] = $assignment->reviewer_id;
                    }
                }

                // Cek apakah ada reviewer yang sudah review tapi tidak ada di list baru
                $removedReviewersWithReviews = array_diff($reviewersWithReviews, $newReviewerIds);
                if (!empty($removedReviewersWithReviews)) {
                    return redirect()->back()->withErrors('Cannot remove reviewers who have already submitted reviews.');
                }

                // Hapus hanya assignment yang tidak memiliki review
                Assignment::where('paper_sub_id', $paper->paper_sub_id)
                    ->whereIn('reviewer_id', $reviewersWithoutReviews)
                    ->delete();

                // Update assignment yang sudah ada review (jika masih ada di list baru)
                foreach ($reviewersWithReviews as $reviewerId) {
                    if (in_array($reviewerId, $newReviewerIds)) {
                        // Update order jika perlu
                        $newOrder = array_search($reviewerId, $newReviewerIds) + 1;
                        Assignment::where('paper_sub_id', $paper->paper_sub_id)
                            ->where('reviewer_id', $reviewerId)
                            ->update(['order' => $newOrder]);
                    }
                }

                // Tambah assignment baru untuk reviewer yang belum ada
                $existingReviewerIds = Assignment::where('paper_sub_id', $paper->paper_sub_id)
                    ->pluck('reviewer_id')
                    ->toArray();

                foreach ($newReviewerIds as $index => $reviewerId) {
                    if (!in_array($reviewerId, $existingReviewerIds)) {
                        Assignment::create([
                            'reviewer_id' => $reviewerId,
                            'paper_sub_id' => $paper->paper_sub_id,
                            'first_paper_sub_id' => $paper->first_paper_sub_id,
                            'assigned_by' => auth()->id(),
                            'order' => $index + 1,
                        ]);
                    }
                }

                $event = Event::find($paper->event_id);

                // Kirim email hanya ke reviewer yang benar-benar baru
                $reviewersToNotify = array_diff($newReviewerIds, $oldReviewerIds);

                foreach ($reviewersToNotify as $reviewerId) {
                    try {
                        $reviewer = User::find($reviewerId);
                        if ($reviewer && $event) {
                            $this->emailService->sendReviewerAssignment($reviewer, $paper, $event);
                        }
                    } catch (\Exception $e) {
                        \Log::error("Gagal kirim email ke reviewer ID {$reviewerId}: " . $e->getMessage());
                    }
                }

                    try {
                    $user = Auth::user(); // Mendapatkan editor yang sedang login
                    if ($user) {
                        UserLogs::create([
                            'user_id' => $user->user_id,
                            'ip_address' => $request->getClientIp(),
                            'user_log_type' => 'Assign Reviewer',
                            'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                            'created_at' => now(), // Sesuai dengan middleware LogUserAgent
                        ]);
                    }
                } catch (\Exception $e) {
                    // Catat error jika logging gagal, tapi jangan hentikan proses utama
                    Log::error('Gagal mencatat log Assign Reviewer: ' . $e->getMessage());
                }

                return redirect()
                    ->route('index.assign.reviewer', $event->event_code)
                    ->with('success', 'Reviewer assigned successfully.');
            });
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to assign reviewer: ' . $th->getMessage());
        }
    }

    public function index_desk_evaluation($event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return redirect()->back()->withErrors('Event not found.');
            }
            $event_name = $event->event_name;
            $papers = Paper::with(['author'])
                ->where('event_id', $event->event_id)
                ->where('status', 'Submitted')
                ->orderBy('created_at', 'desc')
                ->get();
            return view('pages.editor.desk_evaluation', compact('papers', 'event_name'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load desk evaluation: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load desk evaluation: ' . $th->getMessage());
        }
    }

    public function view_paper($event, $paper)
    {
        try {
            $event = Event::where('event_code', $event)->get(['event_id', 'event_name', 'event_code'])->first();
            $user = UserEvent::with(['user', 'role'])
                // ->whereNot('user_id', auth()->id())
                ->where('event_id', $event->event_id)
                ->whereHas('role', function ($query) {
                    $query->where('role_name', 'Paper Reviewer');
                })
                ->get();

            $user = $user->map(function ($userEvent) {
                $assignments = Assignment::where('reviewer_id', $userEvent->user_id)->get();
                $filtered = $assignments->filter(function ($assignment) {
                    return $assignment->paper_sub_id == $assignment->first_paper_sub_id;
                });
                $userEvent->jmlAssignment = $filtered->count();

                return $userEvent;
            });

            $expertises = ExpertiseUser::with('expertise')->get();

            $paper = Paper::with(['user', 'author', 'topicpapers.topic'])->where('paper_sub_id', $paper)->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }

            // dd($user->pluck('given_name')->toArray());
            $reviewer_ids = $user->pluck('user_id')->toArray();
            $author_ids = $paper->author->pluck('user_id')->toArray();

            // dd($reviewer_ids, $author_ids);

            // Exclude reviewers who are authors of this paper
            $conflicted_reviewer_ids = array_intersect($reviewer_ids, $author_ids);
            // dd($conflicted_reviewer_ids);
            if (!empty($conflicted_reviewer_ids)) {
                $user = $user->whereNotIn('user_id', $conflicted_reviewer_ids);
            }

            $paper->keywords = json_decode($paper->keywords, true);
            $paper->keywords = array_column($paper->keywords, 'value');
            $paper->keywords = implode(', ', $paper->keywords);
            $paper->authors = json_decode($paper->authors, true);
            $paper->authors = array_map(function ($author) {
                return $author['name'] . ' - ' . $author['email'];
            }, $paper->authors);
            $paper->authors = implode(', ', $paper->authors);

            return view('pages.editor.view_paper', compact('paper', 'event', 'user', 'expertises'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load paper: ' . $th->getMessage());
        }
    }

    public function decline_paper(Request $request, $event, $paper)
    {
        try {
            $request->validate([
                'note' => 'required|string|max:3000',
            ]);
            $paper = Paper::where('paper_sub_id', $paper)->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }
            $first_paper = Paper::where('paper_sub_id', $paper->first_paper_sub_id)->first();
            if (!$first_paper) {
                return redirect()->back()->withErrors('First paper not found.');
            }
            $first_paper->status = 'Declined';
            $first_paper->save();
            $decision = new Decision();
            $decision->first_paper_sub_id = $first_paper->paper_sub_id;
            $decision->last_paper_sub_id = $paper->paper_sub_id;
            $decision->paper_sub_id = $paper->paper_sub_id;
            $decision->decision = 'Decline';
            $decision->editor_id = auth()->id();
            $decision->note_for_author = $request->note;
            $decision->created_by = auth()->id();
            $decision->updated_by = auth()->id();
            $decision->save();

            try {
                // Ambil data yang diperlukan
                $eventObject = Event::where('event_code', $event)->first();
                $author = User::find($paper->user_id);

                if ($author && $eventObject) {
                    // Kirim email ke author
                    $this->emailService->sendAuthorPaperDeclined($author, $paper, $eventObject, $decision);

                    \Log::error('Paper declined email sent successfully', [
                        'paper_id' => $paper->paper_sub_id,
                        'author_email' => $author->email,
                        'editor_id' => auth()->id()
                    ]);
                }
            } catch (\Exception $emailError) {
                // Log error tapi jangan stop proses decline
                \Log::error('Failed to send decline email', [
                    'error' => $emailError->getMessage(),
                    'paper_id' => $paper->paper_sub_id,
                    'author_email' => $author->email ?? 'unknown'
                ]);
            }

            return redirect()->route('index.desk.evaluation', $event)->with('success', 'Paper declined successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to decline paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to decline paper: ' . $th->getMessage());
        }
    }

    public function index_assign_reviewer($event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $firstPaperIds = Paper::where('event_id', $event->event_id)
                ->where('status', 'In Review')
                ->pluck('paper_sub_id');
            $papers = Paper::with(['assignment.reviewer'])
                // ->whereNot('assignment.reviewer_id', auth()->id())
                ->whereIn('first_paper_sub_id', $firstPaperIds)
                ->where('event_id', $event->event_id)
                ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY first_paper_sub_id ORDER BY round DESC) as rn')
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($paper) {
                    return $paper->rn == 1;
                })
                ->values();
            $user = UserEvent::with(['user', 'role'])
                // ->whereNot('user_id', auth()->id())
                ->where('event_id', $event->event_id)
                ->whereHas('role', function ($query) {
                    $query->where('role_name', 'Paper Reviewer');
                })
                ->get();

            $user = $user->map(function ($userEvent) {
                $assignments = Assignment::where('reviewer_id', $userEvent->user_id)->get();
                $filtered = $assignments->filter(function ($assignment) {
                    return $assignment->paper_sub_id == $assignment->first_paper_sub_id;
                });
                $userEvent->jmlAssignment = $filtered->count();

                return $userEvent;
            });

            $expertises = ExpertiseUser::with('expertise')->get();

            return view('pages.editor.assign_reviewer', compact('papers', 'event', 'user', 'expertises'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load assign reviewer: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load assign reviewer: ' . $th->getMessage());
        }
    }

    public function index_editor_decision($event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $firstPaperIds = Paper::where('event_id', $event->event_id)
                ->where('status', 'In Review')
                ->pluck('paper_sub_id');
            $papers = Paper::with(['assignment.reviews'])
                ->whereIn('first_paper_sub_id', $firstPaperIds)
                ->where('event_id', $event->event_id)
                ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY first_paper_sub_id ORDER BY round DESC) as rn')
                ->orderBy('created_at', 'desc')
                ->get()
                ->filter(function ($paper) {
                    return $paper->rn == 1;
                })
                ->map(function ($paper) {
                    $emptyReviews = 0;
                    foreach ($paper->assignment as $assignment) {
                        if ($assignment->reviews->isEmpty()) {
                            $emptyReviews++;
                        }
                    }
                    $paper->empty_reviews_count = $emptyReviews;
                    $paper->assignment_count = $paper->assignment->count();
                    return $paper;
                })
                ->values();
            $event_name = $event->event_name;
            return view('pages.editor.editor_decision', compact('papers', 'event_name'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load editor decision: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load editor decision: ' . $th->getMessage());
        }
    }

    public function index_create_decision($event, $paper)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $paper = Paper::with(['assignment.reviews', 'topicpapers.topic'])->where('paper_sub_id', $paper)->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }
            $hasCompletedReviews = false;
            $reviewCount = 0;

            foreach ($paper->assignment as $assignment) {
                if ($assignment->reviews->count() > 0) {
                    $hasCompletedReviews = true;
                    $reviewCount++;
                }
            }

            if (!$hasCompletedReviews) {
                return redirect()->route('index.editor.decision', $event->event_code)
                    ->withErrors('Cannot make editorial decision. No reviews have been submitted yet. Please wait for at least one reviewer to complete their review.');
            }
            $review_items = ReviewItem::where('event_id', $event->event_id)
                ->orderBy('seq')
                ->with([
                    'options',
                    'review_contents' => function ($query) {
                        $query->orderBy('review_id');
                    }
                ])
                ->get();
            $history = Paper::where('first_paper_sub_id', $paper->first_paper_sub_id)
                ->where('paper_sub_id', '!=', $paper->paper_sub_id)
                ->with(['assignment.reviews.review_contents', 'decisions'])
                ->get(['round', 'paper_sub_id', 'first_paper_sub_id', 'similarity'])
                ->sortByDesc('round');
            return view('pages.editor.create_decision', compact('event', 'history', 'paper', 'review_items'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load create decision: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load create decision: ' . $th->getMessage());
        }
    }

    public function create_decision(Request $request, $event, $paper)
    {
        try {
            $request->validate([
                'recommendation' => 'required|string',
                'note_for_author' => 'required|string|max:3000',
                'similarity' => 'nullable|numeric|min:0|max:100',
            ]);
            $paper = Paper::with('user')->where('paper_sub_id', $paper)->first();
            $first_paper = Paper::where('paper_sub_id', $paper->first_paper_sub_id)->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }

            $decision = new Decision();
            $decision->first_paper_sub_id = $first_paper->paper_sub_id;
            if ($request->recommendation == 'Decline' || $request->recommendation == 'Accept') {
                $decision->last_paper_sub_id = $paper->paper_sub_id;
            }
            $decision->paper_sub_id = $paper->paper_sub_id;
            $decision->decision = $request->recommendation;
            $decision->editor_id = auth()->id();
            $decision->note_for_author = $request->note_for_author;
            $decision->created_by = auth()->id();
            $decision->updated_by = auth()->id();
            $decision->save();

            $first_paper->status = match ($request->recommendation) {
                'Accept' => 'Accepted',
                'Decline' => 'Declined',
                default => 'Revision',
            };
            $first_paper->save();
            $data = [];

            if ($first_paper->status == 'Accepted') {
                try {
                    $data = $this->send_loa($event, $paper->first_paper_sub_id);
                } catch (\Throwable $th) {
                    return redirect()->back()->withErrors('Failed to create payment: ' . $th->getMessage());
                }
            }

            if ($request->similarity) {
                $paper->similarity = $request->similarity;
                $paper->save();
            }

            // Send email to author
            try {
                $eventObject = Event::where('event_code', $event)->first();
                $author = User::find($paper->user_id);

                if ($author && $eventObject) {
                    $combinedFeedback = null;
                    if (in_array($request->recommendation, ['Major Revision', 'Minor Revision', 'Template Revision'])) {
                        $combinedFeedback = $this->emailService->getCombinedFeedback($paper, $request->note_for_author);
                    }
                    if($data[1])
                    {
                        $author = $data[1];
                    }
                    $this->emailService->sendAuthorEditorialDecision($author, $paper, $eventObject, $decision, $combinedFeedback, $data[2] ?? null);
                }
            } catch (\Exception $emailError) {
                \Log::error('Failed to send editorial decision email', [
                    'error' => $emailError->getMessage(),
                    'paper_id' => $paper->paper_sub_id,
                    'author_email' => $author->email ?? 'unknown',
                    'editor_id' => auth()->id()
                ]);
            }

            return redirect()->route('index.editor.decision', ['event' => $event])->with('success', 'Decision created successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to create decision: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to create decision: ' . $th->getMessage());
        }
    }

    public function index_final_paper($event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $decisions = Decision::whereNotNull('last_paper_sub_id')
                ->whereHas('paper', function ($query) use ($event) {
                    $query->where('event_id', $event->event_id);
                })
                ->with(['paper:paper_sub_id,first_paper_sub_id,title', 'firstPaper:paper_sub_id,status'])
                ->orderBy('created_at', 'desc')
                ->get();
            $history = Decision::whereIn('first_paper_sub_id', $decisions->pluck('first_paper_sub_id'))
                ->with(['paper:paper_sub_id,round,title,similarity'])
                ->get()
                ->groupBy('first_paper_sub_id')
                ->map(function ($group) {
                    return $group->sortByDesc(function ($decision) {
                        return optional($decision->paper)->round;
                    })->values();
                });

            return view('pages.editor.final_paper', compact('decisions', 'history', 'event'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load final papers: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load final papers: ' . $th->getMessage());
        }
    }

    public function detail_final($event, $paper)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $paper = Paper::with(['assignment.reviews.review_contents', 'decisions'])
                ->where('paper_sub_id', $paper)
                ->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }
            $review_items = ReviewItem::where('event_id', $event->event_id)
                ->orderBy('seq')
                ->with([
                    'options',
                    'review_contents' => function ($query) {
                        $query->orderBy('review_id');
                    }
                ])
                ->get();
            $history = Paper::where('first_paper_sub_id', $paper->first_paper_sub_id)
                ->with(['assignment.reviews.review_contents', 'decisions'])
                ->get(['round', 'paper_sub_id', 'first_paper_sub_id', 'similarity'])
                ->sortByDesc('round');
            return view('pages.editor.detail_final_paper', compact('paper', 'event', 'review_items', 'history'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load final paper details: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load final paper details: ' . $th->getMessage());
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

    public function file_review($event, $review_id)
    {
        try {
            $review = Review::where('review_id', $review_id)
                ->first();

            if (!$review) {
                return redirect()->back()->withErrors('Review not found.');
            }

            $filePath = storage_path('app/public/' . $review->attach_file);

            if (!file_exists($filePath)) {
                return redirect()->back()->withErrors('File not found on server.');
            }

            return response()->download($filePath);

        } catch (\Exception $e) {
            return redirect()->back()->withErrors('An error occurred while downloading the review file: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function index_revision_paper($event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return redirect()->back()->withErrors('Event not found.');
            }

            $firstPaperIds = Paper::where('event_id', $event->event_id)
                ->where('status', 'Revision')
                ->pluck('paper_sub_id');

            $papers = Paper::with([
                'assignment.reviews',
                'user',
                'decisions' => function ($query) {
                    $query->orderBy('created_at', 'desc');
                }
            ])
                ->whereIn('first_paper_sub_id', $firstPaperIds)
                ->where('event_id', $event->event_id)
                ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY first_paper_sub_id ORDER BY round DESC) as rn')
                ->get()
                ->filter(function ($paper) {
                    return $paper->rn == 1;
                })
                ->map(function ($paper) {
                    $latestDecision = Decision::where('first_paper_sub_id', $paper->first_paper_sub_id)
                        ->orderBy('created_at', 'desc')
                        ->first();

                    $paper->latest_decision = $latestDecision;

                    $hasNewRevision = Paper::where('first_paper_sub_id', $paper->first_paper_sub_id)
                        ->where('round', '>', optional($latestDecision->paper)->round ?? 0)
                        ->exists();

                    $paper->has_new_revision = $hasNewRevision;

                    return $paper;
                })
                ->values();

            $event_name = $event->event_name;
            return view('pages.editor.revision_paper', compact('papers', 'event_name', 'event'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load revision papers: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load revision papers: ' . $th->getMessage());
        }
    }

    public function view_revision_paper($event, $paper)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $paper = Paper::with(['assignment.reviews.review_contents', 'decisions', 'user'])
                ->where('paper_sub_id', $paper)
                ->first();

            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }

            $review_items = ReviewItem::where('event_id', $event->event_id)
                ->orderBy('seq')
                ->with([
                    'options',
                    'review_contents' => function ($query) {
                        $query->orderBy('review_id');
                    }
                ])
                ->get();

            $history = Paper::where('first_paper_sub_id', $paper->first_paper_sub_id)
                ->with(['assignment.reviews.review_contents', 'decisions'])
                ->get(['round', 'paper_sub_id', 'first_paper_sub_id', 'similarity', 'title', 'created_at'])
                ->sortByDesc('round');

            return view('pages.editor.view_revision_paper', compact('paper', 'event', 'review_items', 'history'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load revision paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load revision paper: ' . $th->getMessage());
        }
    }

    public function detail_paper($event, $paper)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            $paper = Paper::with(['assignment.reviews', 'topicpapers.topic'])->where('paper_sub_id', $paper)->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }
            return view('pages.editor.detail_paper', compact('event', 'paper'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load create decision: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load create decision: ' . $th->getMessage());
        }
    }

    public function send_loa($event, $paper_sub_id)
    {
        // Check if template file exists
        $templatePath = public_path('assets/template/LoA_ISSAT_2025.docx');
        if (!file_exists($templatePath)) {
            return back()->withErrors('Template file not found: ' . $templatePath);
        }

        $phpWord = new TemplateProcessor($templatePath);
        $paper = Paper::with(['decisions', 'event:event_id,event_name,country_id,event_logo,manager_name,manager_contact_email,event_start,event_end,payment_end,camera_ready_end', 'event.country:country_id,country_name', 'user'])->where('paper_sub_id', $paper_sub_id)->firstOrFail();
        $decision = Decision::where('first_paper_sub_id', $paper->first_paper_sub_id)->orderBy('created_at', 'desc')->first();
        $lastpaper = Paper::where('paper_sub_id', $decision->last_paper_sub_id ?? $paper->paper_sub_id)->first();
        $phpWord->setValue('Conference Name', $paper->event->event_name);
        $startDate = new \DateTime($paper->event->event_start);
        $endDate = new \DateTime($paper->event->event_end);
        if ($startDate->format('Y-m') === $endDate->format('Y-m')) {
            $formattedDate = $startDate->format('d') . ' - ' . $endDate->format('d') . ' ' . $endDate->format('F Y');
        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
            $formattedDate = $startDate->format('d F') . ' - ' . $endDate->format('d F Y');
        } else {
            $formattedDate = $startDate->format('d F Y') . ' - ' . $endDate->format('d F Y');
        }

        $phpWord->setValue('Conference Event Date', $formattedDate);
        $phpWord->setValue('Conference Location', $paper->event->country->country_name);
        $phpWord->setValue('Date Sent', date('d F Y'));
        $authors = json_decode($lastpaper->authors, true);
        $authorNames = collect($authors)->pluck('name')->implode(', ');
        $phpWord->setValue('List of Authors', $authorNames);
        $paperTitle = $lastpaper->title;
        $subtitle = $lastpaper->subtitle ?? null;
        if ($subtitle && trim($subtitle) !== '') {
            $paperTitle .= ': ' . $subtitle;
        }
        $phpWord->setValue('Paper Title', $paperTitle);
        $phpWord->setValue('Supporting Materials Deadline', date('d F Y', strtotime($paper->event->camera_ready_end)));
        $phpWord->setValue('Payment Deadline', date('d F Y', strtotime($paper->event->payment_end)));

        // dd($phpWord);

        $loaDir = storage_path('app/public/loa/' . $paper->event->event_id);
        if (!is_dir($loaDir)) {
            mkdir($loaDir, 0755, true);
        }

        $authorName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $lastpaper->title);
        $fileName = 'LOA_' . $authorName . '_' . $paper_sub_id . '_' . date('Y-m-d') . '.docx';
        $filePath = $loaDir . '/' . $fileName;

        $phpWord->saveAs($filePath);

        // Convert to PDF using OfficeConverter
        if (!isset($_SERVER['HOME'])) {
            $_SERVER['HOME'] = getenv('HOME') ?: (getenv('HOMEDRIVE') . getenv('HOMEPATH'));
        }

        $converter = new OfficeConverter($filePath);
        $converter->convertTo(basename($fileName, '.docx') . '.pdf'); // output to same directory

        $pdfFilePath = $loaDir . '/' . basename($fileName, '.docx') . '.pdf';

        return [$paper, $authorNames, $pdfFilePath];
    }

    public function sendLoABulk($event)
    {
        try {
            $event = Event::where('event_code', $event)->firstOrFail();
            $acceptedPapers = Paper::with(['user', 'event'])
                ->where('event_id', $event->event_id)
                ->where('status', 'Accepted')
                ->get();

            if ($acceptedPapers->isEmpty()) {
                return redirect()->back()->withErrors('No accepted papers found for this event.');
            }

            $successCount = 0;
            $failedPapers = [];

            foreach ($acceptedPapers as $paper) {
                try {
                    // Generate LoA
                    $loaData = $this->send_loa($event->event_code, $paper->paper_sub_id);

                    // Ambil data author dari LoA
                    $author = User::find($paper->user_id);
                    if ($loaData[1]) {
                        $author = $loaData[1];
                    }

                    // Kirim email ke penulis
                    $this->emailService->sendLoA($loaData[0], $author, $loaData[2]);

                    \Log::info('LoA email sent successfully', [
                        'paper_id' => $paper->paper_sub_id,
                        'email' => $author->email,
                        'attachmentPath' => $loaData[2],
                    ]);

                    $successCount++;
                } catch (\Exception $e) {
                    \Log::error('Failed to send LoA email', [
                        'error' => $e->getMessage(),
                        'paper_id' => $paper->paper_sub_id,
                        'email' => $paper->user->email ?? 'unknown',
                    ]);

                    // Simpan paper yang gagal dikirim
                    $failedPapers[] = $paper->title;
                }
            }

            // Buat pesan sukses dan error
            $message = "LoA emails sent successfully to {$successCount} papers.";
            if (!empty($failedPapers)) {
                $message .= " Failed to send LoA for the following papers: " . implode(', ', $failedPapers) . ".";
            }

            return redirect()->back()->with('success', $message);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to send LoA emails: ' . $e->getMessage());
        }
    }
}
