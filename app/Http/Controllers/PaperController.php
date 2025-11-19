<?php

namespace App\Http\Controllers;

use App\Models\Assignment;
use App\Models\Author;
use App\Models\Decision;
use App\Models\Review;
use App\Models\ReviewItem;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Paper;
use App\Models\Topic;
use App\Models\TopicPaper;
use App\Services\EmailService;
use App\Models\UserLogs; 
use Illuminate\Support\Facades\Log; 
use Auth;

class PaperController extends Controller
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
                return back()->withErrors('Event not found.');
            }
            $event_id = $eventObj->event_id;
            $papers = Paper::with(['event', 'first:paper_sub_id,status'])
                ->where('user_id', '=', auth()->user()->user_id)
                ->where('event_id', '=', $event_id)
                // ->where('status', '==', 'Accepted')
                ->orderBy('created_at', 'desc')
                ->get();
            // dd($papers);
            $firstPaperIds = $papers->pluck('first_paper_sub_id')->toArray();

            $allPaperVersions = Paper::whereIn('first_paper_sub_id', $firstPaperIds)
                ->orderBy('round', 'desc')
                ->with(['decisions'])
                ->get();

            $paperHistory = $allPaperVersions->groupBy('first_paper_sub_id');
            return view('pages.paper.index', compact('papers', 'eventObj', 'paperHistory'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading papers.');
        }
    }

    public function index_submit(Request $request)
    {
        try {
            $event = Event::where('event_code', $request->route('event'))->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $submission_end = \Carbon\Carbon::parse($event->submission_end)->endOfDay();
            if ($submission_end < now()) {
                return back()->withErrors('Submission for this event has ended.');
            }
            $submission_start = \Carbon\Carbon::parse($event->submission_start);
            if ($submission_start > now()) {
                return back()->withErrors('Submission for this event has not started.');
            }
            $event_name = $event->event_name;
            $event_id = $event->event_id;
            $user = User::where('root', 0)->get();
            $topics = Topic::where('event_id', '=', $event_id)->get();
            return view('pages.paper.submit', compact('user', 'event_name', 'topics'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading the submission form.');
        }
    }

    public function submit_paper(Request $request)
    {
        try {
            $event = Event::where('event_code', $request->event)->first();
            $submission_end = \Carbon\Carbon::parse($event->submission_end)->endOfDay();
            ;
            if ($submission_end < now()) {
                return back()->withErrors('Submission for this event has ended.');
            }
            $submission_start = \Carbon\Carbon::parse($event->submission_start);
            if ($submission_start > now()) {
                return back()->withErrors('Submission for this event has not started.');
            }
            $event = $event->event_id;
            $data = json_decode($request->input('keywords'), true);
            $keywords = $data;
            $request->validate(
                [
                    'title' => 'required|string',
                    'subtitle' => 'nullable|string',
                    'abstract' => [
                        'required',
                        'string',
                        function ($attribute, $value, $fail) {
                            $wordCount = str_word_count($value);
                            if ($wordCount > 250) {
                                $fail('The abstract may not be greater than 250 words.');
                            }
                        },
                    ],
                    'keywords' => [
                        'required',
                        function ($attribute, $value, $fail) use ($keywords) {
                            if (!is_array($keywords)) {
                                return $fail('The keywords field must be a valid array.');
                            }

                            $count = count($keywords);
                            if ($count < 3 || $count > 5) {
                                return $fail('You must provide between 3 and 5 keywords.');
                            }
                            foreach ($keywords as $keyword) {
                                if (!isset($keyword['value']) || !is_string($keyword['value']) || trim($keyword['value']) === '' || trim($keyword['value']) === '.') {
                                    return $fail('Each keyword must have a non-empty "value" field.');
                                }
                            }
                        },
                    ],
                    'topics' => 'required',
                    'paper_file' => 'required|file|mimes:doc,docx|max:5500',
                    'authors' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            $authors = json_decode($value, true);
                            if (!is_array($authors)) {
                                return $fail('The authors field must be a valid JSON array.');
                            }

                            if (count($authors) > 20) {
                                return $fail('You cannot provide more than 20 authors.');
                            }

                            foreach ($authors as $author) {
                                if (!isset($author['value']) || !is_string($author['value'])) {
                                    return $fail('Each author must have a "value" field.');
                                }

                                $parts = explode(' - ', $author['value']);
                                if (count($parts) !== 2) {
                                    return $fail('Each author must be in the format "Name - Email".');
                                }

                                $email = trim($parts[1]);

                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    return $fail('Each author must have a valid email address.');
                                }
                            }
                        },
                    ],
                    'corresponding' => [
                        'required',
                        function ($attribute, $value, $fail) use ($request) {
                            $authors = json_decode($request->authors, true);

                            if (!is_array($authors) || count($authors) === 0) {
                                return $fail('The authors field must contain at least one author.');
                            }

                            $correspondingIndex = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);

                            if ($correspondingIndex < 1 || $correspondingIndex > count($authors)) {
                                return $fail('The corresponding author must be a valid author from the authors list.');
                            }
                        },
                    ],
                    'note_for_editor' => 'max:3000',
                ],
                [
                    'title.required' => 'The title field is required.',
                    'abstract.required' => 'The abstract field is required.',
                    'keywords.required' => 'The keywords field is required.',
                    'paper_file.require' => 'Paper is required',
                    'paper_file.mimes' => 'Paper should be a doc or docx.',
                    'paper_file.size' => 'The paper may not be greater than 5 MB.',
                    'authors.required' => 'The authors field is required.',
                    'corresponding.required' => 'The corresponding field is required.',
                    'note_for_editor.max' => 'The note for editor may not be greater than 3000 characters.',
                ],
            );

            $authors = json_decode($request->authors, true);
            $transformedAuthors = array_map(function ($author) {
                $parts = explode(' - ', $author['value']);
                return [
                    'name' => trim($parts[0]),
                    'email' => trim($parts[1]),
                ];
            }, $authors);
            $correspondingIndex = (int) filter_var($request->corresponding, FILTER_SANITIZE_NUMBER_INT);
            $paper = Paper::create([
                'event_id' => $event,
                'user_id' => auth()->user()->user_id,
                'round' => 0,
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'abstract' => $request->abstract,
                'keywords' => json_encode($keywords),
                'attach_file' => '-',
                'authors' => json_encode($transformedAuthors),
                'status' => 'Submitted',
                'corresponding' => $correspondingIndex,
                'note_for_editor' => $request->note_for_editor,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
            $paper->first_paper_sub_id = $paper->paper_sub_id;
            $paper->save();
            $path = $request->file('paper_file')->storeAs(config('path.paper') . $paper->event_id . '/' . $paper->paper_sub_id, $event . '_' . $paper->paper_sub_id . '_' . $paper->round . '.' . $request->paper_file->getClientOriginalExtension(), 'public');
            $paper->attach_file = ltrim($path, config('path.paper'));
            $paper->save();

            // ==============================================================
            // START LOGGING: Merekam aktivitas "Submit Paper"
            // ==============================================================
            $user = Auth::user();
            if ($user) {
                UserLogs::create([
                    'user_id' => $user->user_id,
                    'ip_address' => $request->getClientIp(),
                    // Tipe log baru
                    'user_log_type' => 'Submit Paper', 
                    'user_agent' => json_encode(
                        [
                            'user_agent_raw' => $request->header('User-Agent'),
                            'paper_info' => [
                                'paper_sub_id' => $paper->paper_sub_id,
                                'title' => $paper->title,
                                'event_code' => $request->event,
                                'file_name' => $request->file('paper_file')->getClientOriginalName(),
                            ]
                        ], 
                        JSON_THROW_ON_ERROR
                    ),
                    'created_at' => now(),
                ]);
            }
            // ==============================================================
            // END LOGGING
            // ==============================================================

            try {
                TopicPaper::create([
                    'topic_id' => $request->topics,
                    'first_paper_sub_id' => $paper->first_paper_sub_id,
                ]);
            } catch (\Exception $e) {
                return back()->with('error', 'Error relating topic and paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
            }

            $order = 1;
            foreach ($transformedAuthors as $author) {
                $user = User::where('email', $author['email'])->first();
                $nameParts = explode(' ', $author['name'], 2);
                $givenName = $nameParts[0];
                $familyName = isset($nameParts[1]) ? $nameParts[1] : '';
                $is_corresponding = 0;
                if ($correspondingIndex == $order) {
                    $is_corresponding = 1;
                } else {
                    $is_corresponding = 0;
                }

                if ($user) {
                    Author::create([
                        'paper_sub_id' => $paper->paper_sub_id,
                        'given_name' => $user->given_name,
                        'family_name' => $user->family_name,
                        'email' => $user->email,
                        'order' => $order,
                        'is_corresponding' => $is_corresponding,
                        'user_id' => $user->user_id,
                    ]);
                } else {
                    Author::create([
                        'paper_sub_id' => $paper->paper_sub_id,
                        'given_name' => $givenName,
                        'family_name' => $familyName,
                        'email' => $author['email'],
                        'order' => $order,
                        'is_corresponding' => $is_corresponding,
                        'user_id' => null,
                    ]);
                }
                $order++;
            }

            $eventObject = Event::find($event);

            // Kirim ke author
            try {
                $this->emailService->sendAuthorSubmitPaper(auth()->user(), $paper, $eventObject);
            } catch (\Exception $e) {
                \Log::error("Gagal kirim email ke author: " . str_replace(["\r", "\n"], ' ', $e->getMessage()));
            }

            // Kirim ke semua editor
            $editors = User::whereHas('user_events', function ($query) use ($eventObject) {
                $query->where('event_id', $eventObject->event_id);
            })
                ->whereHas('user_events.role', function ($query) {
                    $query->where('role_name', 'Editor');
                })->get();

            foreach ($editors as $editor) {
                try {
                    $this->emailService->sendEditorNewPaper($editor, $paper, $eventObject);
                } catch (\Exception $e) {
                    \Log::error("Gagal kirim email ke editor {$editor->email}: " . str_replace(["\r", "\n"], ' ', $e->getMessage()));
                }
            }

            return redirect()
                ->route('index.paper', [$request->event])
                ->with('success', 'Submit paper successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return back()->withErrors(str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while submitting the paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function index_edit($event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $paper = Paper::where('paper_sub_id', $paper_id)->first();
            if (!$paper) {
                return back()->withErrors('Paper not found.');
            }
            if ($paper->status != 'Submitted') {
                return back()->withErrors('Paper not editable');
            }
            $topics = Topic::where('event_id', '=', $event->event_id)->get();
            $selected_topics = TopicPaper::where('first_paper_sub_id', $paper->first_paper_sub_id)->first();
            $paper->topics = $selected_topics;
            // dd($paper->topics);
            if ($paper->status == 'In Review') {
                return back()->withErrors('You cannot edit this paper as it is currently under review.');
            }
            $user = User::where('root', 0)->get();
            $paper->keywords = json_decode($paper->keywords, true);
            $paper->keywords = array_column($paper->keywords, 'value');
            $paper->keywords = implode(', ', $paper->keywords);
            $paper->authors = json_decode($paper->authors, true);
            $paper->authors = array_map(function ($author) {
                return $author['name'] . ' - ' . $author['email'];
            }, $paper->authors);
            $paper->authors = implode(', ', $paper->authors);
            return view('pages.paper.edit', compact('paper', 'event', 'user', 'topics'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading the edit form.');
        }
    }

    public function update(Request $request, $event, $paper)
    {
        try {
            $event = Event::where('event_code', $request->event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $event = $event->event_id;
            $paper = Paper::where('paper_sub_id', $paper)->first();
            if (!$paper) {
                return back()->withErrors('Paper not found.');
            }
            if ($paper->status != 'Submitted') {
                return back()->withErrors('Paper not editable');
            }
            $data = json_decode($request->input('keywords'), true);
            $keywords = $data;
            $request->validate(
                [
                    'title' => 'required|string',
                    'subtitle' => 'nullable|string',
                    'abstract' => [
                        'required',
                        'string',
                        function ($attribute, $value, $fail) {
                            $wordCount = str_word_count($value);
                            if ($wordCount > 250) {
                                $fail('The abstract may not be greater than 250 words.');
                            }
                        },
                    ],
                    'topics' => 'required',
                    'keywords' => [
                        'required',
                        function ($attribute, $value, $fail) use ($keywords) {
                            if (!is_array($keywords)) {
                                return $fail('The keywords field must be a valid array.');
                            }

                            $count = count($keywords);
                            if ($count < 3 || $count > 5) {
                                return $fail('You must provide between 3 and 5 keywords.');
                            }
                            foreach ($keywords as $keyword) {
                                if (!isset($keyword['value']) || !is_string($keyword['value']) || trim($keyword['value']) === '' || trim($keyword['value']) === '.') {
                                    return $fail('Each keyword must have a non-empty "value" field.');
                                }
                            }
                        },
                    ],
                    'paper_file' => 'file|mimes:doc,docx|max:5500',
                    'authors' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            $authors = json_decode($value, true);
                            if (!is_array($authors)) {
                                return $fail('The authors field must be a valid JSON array.');
                            }

                            if (count($authors) > 20) {
                                return $fail('You cannot provide more than 20 authors.');
                            }

                            foreach ($authors as $author) {
                                if (!isset($author['value']) || !is_string($author['value'])) {
                                    return $fail('Each author must have a "value" field.');
                                }

                                $parts = explode(' - ', $author['value']);
                                if (count($parts) !== 2) {
                                    return $fail('Each author must be in the format "Name - Email".');
                                }

                                $email = trim($parts[1]);

                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    return $fail('Each author must have a valid email address.');
                                }
                            }
                        },
                    ],
                    'corresponding' => [
                        'required',
                        function ($attribute, $value, $fail) use ($request) {
                            $authors = json_decode($request->authors, true);

                            if (!is_array($authors) || count($authors) === 0) {
                                return $fail('The authors field must contain at least one author.');
                            }

                            $correspondingIndex = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);

                            if ($correspondingIndex < 1 || $correspondingIndex > count($authors)) {
                                return $fail('The corresponding author must be a valid author from the authors list.');
                            }
                        },
                    ],
                    'note_for_editor' => 'max:3000',
                ],
                [
                    'title.required' => 'The title field is required.',
                    'abstract.required' => 'The abstract field is required.',
                    'keywords.required' => 'The keywords field is required.',
                    'paper_file.mimes' => 'Paper should be a doc or docx',
                    'paper_file.size' => 'The paper may not be greater than 5 MB.',
                    'authors.required' => 'The authors field is required.',
                    'corresponding.required' => 'The corresponding field is required.',
                    'note_for_editor.max' => 'The note for editor may not be greater than 3000 characters.',
                ],
            );

            $authors = json_decode($request->authors, true);
            $transformedAuthors = array_map(function ($author) {
                $parts = explode(' - ', $author['value']);
                return [
                    'name' => trim($parts[0]),
                    'email' => trim($parts[1]),
                ];
            }, $authors);
            $correspondingIndex = (int) filter_var($request->corresponding, FILTER_SANITIZE_NUMBER_INT);
            $paper->title = $request->title;
            $paper->subtitle = $request->subtitle;
            $paper->abstract = $request->abstract;
            $paper->keywords = json_encode($keywords);
            $paper->authors = json_encode($transformedAuthors);
            $paper->corresponding = $correspondingIndex;
            $paper->note_for_editor = $request->note_for_editor;
            $paper->updated_by = auth()->user()->user_id;
            $paper->save();
            if ($request->file('paper_file')) {
                $path = $request->file('paper_file')->storeAs(
                    config('path.paper') . $paper->event_id . '/' . $paper->paper_sub_id,
                    $event . '_' . $paper->paper_sub_id . '_' . $paper->round . '.' . $request->paper_file->getClientOriginalExtension(),
                    'public'
                );
                $paper->attach_file = ltrim($path, config('path.paper'));
                $paper->save();
            }

            if ($request->topics) {
                try {
                    TopicPaper::where('first_paper_sub_id', $paper->first_paper_sub_id)->delete();
                    TopicPaper::create([
                        'topic_id' => $request->topics,
                        'first_paper_sub_id' => $paper->first_paper_sub_id,
                    ]);
                } catch (\Exception $e) {
                    return back()->with('error', 'Error relating topic and paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
                }
            }

            Author::where('paper_sub_id', $paper->paper_sub_id)->delete();
            $order = 1;
            foreach ($transformedAuthors as $author) {
                $user = User::where('email', $author['email'])->first();
                $nameParts = explode(' ', $author['name'], 2);
                $givenName = $nameParts[0];
                $familyName = isset($nameParts[1]) ? $nameParts[1] : '';
                $is_corresponding = $correspondingIndex == $order ? 1 : 0;

                if ($user) {
                    Author::create([
                        'paper_sub_id' => $paper->paper_sub_id,
                        'given_name' => $user->given_name,
                        'family_name' => $user->family_name,
                        'email' => $user->email,
                        'order' => $order,
                        'is_corresponding' => $is_corresponding,
                        'user_id' => $user->user_id,
                    ]);
                } else {
                    Author::create([
                        'paper_sub_id' => $paper->paper_sub_id,
                        'given_name' => $givenName,
                        'family_name' => $familyName,
                        'email' => $author['email'],
                        'order' => $order,
                        'is_corresponding' => $is_corresponding,
                        'user_id' => null,
                    ]);
                }
                $order++;
            }

            return redirect()->route('index.paper', [$request->event])
                ->with('success', 'Edit Paper successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return back()->withErrors(str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while updating the paper: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function index_revision($event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $event_id = $event->event_id;
            $paper = Paper::with(['assignment.reviews', 'decisions'])->where('paper_sub_id', $paper_id)->first();
            if (!$paper) {
                return back()->withErrors('Paper not found.');
            }
            $topics = Topic::where('event_id', '=', $event->event_id)->get();
            $selected_topics = TopicPaper::where('first_paper_sub_id', $paper->first_paper_sub_id)->first();
            $paper->topics = $selected_topics;

            $user = User::where('root', 0)->get();
            $paper->keywords = json_decode($paper->keywords, true);
            $paper->keywords = array_column($paper->keywords, 'value');
            $paper->keywords = implode(', ', $paper->keywords);
            $paper->authors = json_decode($paper->authors, true);
            $paper->authors = array_map(function ($author) {
                return $author['name'] . ' - ' . $author['email'];
            }, $paper->authors);
            $paper->authors = implode(', ', $paper->authors);
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

            return view('pages.paper.revise', compact('paper', 'event', 'user', 'topics', 'review_items', 'history'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading revision papers.');
        }
    }

    public function revision_paper(Request $request, $event, $paper_id)
    {
        try {
            $data = json_decode($request->input('keywords'), true);
            $keywords = $data;
            $request->validate(
                [
                    'title' => 'required|string',
                    'subtitle' => 'nullable|string',
                    'abstract' => [
                        'required',
                        'string',
                        function ($attribute, $value, $fail) {
                            $wordCount = str_word_count($value);
                            if ($wordCount > 250) {
                                $fail('The abstract may not be greater than 250 words.');
                            }
                        },
                    ],
                    'keywords' => [
                        'required',
                        function ($attribute, $value, $fail) use ($keywords) {
                            if (!is_array($keywords)) {
                                return $fail('The keywords field must be a valid array.');
                            }

                            $count = count($keywords);
                            if ($count < 3 || $count > 5) {
                                return $fail('You must provide between 3 and 5 keywords.');
                            }
                            foreach ($keywords as $keyword) {
                                if (!isset($keyword['value']) || !is_string($keyword['value']) || trim($keyword['value']) === '' || trim($keyword['value']) === '.') {
                                    return $fail('Each keyword must have a non-empty "value" field.');
                                }
                            }
                        },
                    ],
                    'paper_file' => 'required|file|mimes:doc,docx|max:5500',
                    'authors' => [
                        'required',
                        function ($attribute, $value, $fail) {
                            $authors = json_decode($value, true);
                            if (!is_array($authors)) {
                                return $fail('The authors field must be a valid JSON array.');
                            }

                            if (count($authors) > 20) {
                                return $fail('You cannot provide more than 20 authors.');
                            }

                            foreach ($authors as $author) {
                                if (!isset($author['value']) || !is_string($author['value'])) {
                                    return $fail('Each author must have a "value" field.');
                                }

                                $parts = explode(' - ', $author['value']);
                                if (count($parts) !== 2) {
                                    return $fail('Each author must be in the format "Name - Email".');
                                }

                                $email = trim($parts[1]);

                                if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                                    return $fail('Each author must have a valid email address.');
                                }
                            }
                        },
                    ],
                    'corresponding' => [
                        'required',
                        function ($attribute, $value, $fail) use ($request) {
                            $authors = json_decode($request->authors, true);

                            if (!is_array($authors) || count($authors) === 0) {
                                return $fail('The authors field must contain at least one author.');
                            }

                            $correspondingIndex = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);

                            if ($correspondingIndex < 1 || $correspondingIndex > count($authors)) {
                                return $fail('The corresponding author must be a valid author from the authors list.');
                            }
                        },
                    ],
                    'note_for_editor' => 'max:3000',
                ],
                [
                    'paper_file.require' => 'Paper is required',
                    'paper_file.mimes' => 'Paper should be a doc or docx',
                    'paper_file.size' => 'The paper may not be greater than 5 MB.',
                ],
            );

            $event = Event::where('event_code', $request->event)->first();
            $eventObj = $event;
            $revision_end = \Carbon\Carbon::parse($event->revision_end)->endOfDay();
            if ($revision_end < now()) {
                return back()->withErrors('Revision for this event has ended.');
            }
            $revision_start = \Carbon\Carbon::parse($event->revision_start);
            if ($revision_start > now()) {
                return back()->withErrors('Revision for this event has not started.');
            }
            $event = $event->event_id;

            $papers = Paper::where('paper_sub_id', $paper_id)->first();
            if (!$papers) {
                return back()->withErrors('Paper not found.');
            }
            // dd($papers);
            $authors = json_decode($request->authors, true);
            $transformedAuthors = array_map(function ($author) {
                $parts = explode(' - ', $author['value']);
                return [
                    'name' => trim($parts[0]),
                    'email' => trim($parts[1]),
                ];
            }, $authors);
            $correspondingIndex = (int) filter_var($request->corresponding, FILTER_SANITIZE_NUMBER_INT);
            $first_paper = Paper::where('paper_sub_id', $papers->first_paper_sub_id)
                ->where('event_id', $event)
                ->where('user_id', auth()->user()->user_id)
                ->first();


            $prev_paper = Paper::where('first_paper_sub_id', $first_paper->paper_sub_id)
                ->latest()
                ->first();

            $paper = Paper::create([
                'first_paper_sub_id' => $first_paper->paper_sub_id,
                'event_id' => $event,
                'user_id' => auth()->user()->user_id,
                'round' => $prev_paper->round + 1,
                'title' => $request->title,
                'subtitle' => $request->subtitle,
                'abstract' => $request->abstract,
                'keywords' => json_encode($keywords),
                'authors' => json_encode($transformedAuthors),
                'corresponding' => $correspondingIndex,
                'note_for_editor' => $request->note_for_editor,
                'attach_file' => '-',
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);
            $path = $request->file('paper_file')->storeAs(config('path.paper') . $paper->event_id . '/' . $first_paper->paper_sub_id, $event . '_' . $first_paper->paper_sub_id . '_' . $paper->round . '.' . $request->paper_file->getClientOriginalExtension(), 'public');
            $paper->attach_file = ltrim($path, config('path.paper'));
            $paper->save();
            $first_paper->status = 'In Review';
            $first_paper->updated_by = auth()->user()->user_id;
            $first_paper->save();
            $order = 1;
            foreach ($transformedAuthors as $author) {
                $user = User::where('email', $author['email'])->first();
                $nameParts = explode(' ', $author['name'], 2);
                $givenName = $nameParts[0];
                $familyName = isset($nameParts[1]) ? $nameParts[1] : '';
                $is_corresponding = 0;
                if ($correspondingIndex == $order) {
                    $is_corresponding = 1;
                } else {
                    $is_corresponding = 0;
                }

                if ($user) {
                    Author::create([
                        'paper_sub_id' => $paper->paper_sub_id,
                        'given_name' => $user->given_name,
                        'family_name' => $user->family_name,
                        'email' => $user->email,
                        'order' => $order,
                        'is_corresponding' => $is_corresponding,
                        'user_id' => $user->user_id,
                    ]);
                } else {
                    Author::create([
                        'paper_sub_id' => $paper->paper_sub_id,
                        'given_name' => $givenName,
                        'family_name' => $familyName,
                        'email' => $author['email'],
                        'order' => $order,
                        'is_corresponding' => $is_corresponding,
                        'user_id' => null,
                    ]);
                }
                $order++;
            }

            $prev_assignment = Assignment::where('paper_sub_id', $prev_paper->paper_sub_id)->get();

            foreach ($prev_assignment as $assignment) {
                Assignment::create([
                    'reviewer_id' => $assignment->reviewer_id,
                    'paper_sub_id' => $paper->paper_sub_id,
                    'first_paper_sub_id' => $assignment->first_paper_sub_id,
                    'order' => $assignment->order,
                    'assigned_by' => $assignment->assigned_by,
                    'created_by' => $assignment->created_by,
                    'updated_by' => $assignment->updated_by,
                ]);
            }

            // Send email to author
            $assignments = Assignment::where('paper_sub_id', $paper->paper_sub_id)->get();

            foreach ($assignments as $assignment) {
                try {
                    $reviewer = User::find($assignment->reviewer_id);
                    if ($reviewer) {
                        $this->emailService->sendReviewerRevisionSubmitted(
                            $reviewer,
                            $paper,
                            $eventObj,
                            $paper->round
                        );
                    }
                } catch (\Exception $e) {
                    \Log::error("Gagal mengirim email revisi ke reviewer ID {$assignment->reviewer_id}: " . str_replace(["\r", "\n"], ' ', $e->getMessage()));
                }
            }

            try {
                $user = Auth::user(); // Mendapatkan pengguna yang sedang login
                if ($user) {
                    UserLogs::create([
                        'user_id' => $user->user_id,
                        'ip_address' => $request->getClientIp(),
                        'user_log_type' => 'Submit Revision', // <-- Nilai ENUM
                        'user_agent' => json_encode([
                            'user_agent_raw' => $request->header('User-Agent'),
                            'paper_info' => [
                                'new_paper_sub_id' => $paper->paper_sub_id,
                                'first_paper_sub_id' => $paper->first_paper_sub_id,
                                'title' => $paper->title,
                                'event_code' => $request->event,
                                'round' => $paper->round,
                                'file_name' => $request->file('paper_file')->getClientOriginalName(),
                            ]
                        ], JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Catat error jika logging gagal, tapi jangan hentikan proses utama
                Log::error('Gagal mencatat log Submit Revision: ' . $e->getMessage());
            }

            return redirect()->route('index.paper', [$request->event])
                ->with('success', 'Submit revision successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Http\Exceptions\PostTooLargeException $e) {
            return back()->withErrors(str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while submitting the revision: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function check($event, $paper_id)
    {
        try {
            $paper = Paper::where('paper_sub_id', $paper_id)->first();
            if (!$paper) {
                return redirect()->back()->withErrors('Paper not found.');
            }
            $filePath = storage_path('app/public/' . config('path.paper') . $paper->attach_file);
            if (!file_exists($filePath)) {
                return redirect()->back()->withErrors('File not found.');
            }
            return response()->download($filePath);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('An error occurred while downloading the file.');
        }
    }

    public function index_review($event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $event_id = $event->event_id;
            $paper = Paper::with(['assignment.reviews', 'decisions'])->where('paper_sub_id', $paper_id)->first();
            if (!$paper) {
                return back()->withErrors('Paper not found.');
            }
            $topics = Topic::where('event_id', '=', $event->event_id)->get();
            $selected_topics = TopicPaper::where('first_paper_sub_id', $paper->first_paper_sub_id)->first();
            $paper->topics = $selected_topics;

            $user = User::where('root', 0)->get();
            $paper->keywords = json_decode($paper->keywords, true);
            $paper->keywords = array_column($paper->keywords, 'value');
            $paper->keywords = implode(', ', $paper->keywords);
            $paper->authors = json_decode($paper->authors, true);
            $paper->authors = array_map(function ($author) {
                return $author['name'] . ' - ' . $author['email'];
            }, $paper->authors);
            $paper->authors = implode(', ', $paper->authors);
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
            return view('pages.paper.review', compact('paper', 'event', 'user', 'topics', 'review_items', 'history'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading review papers.');
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

    public function detail($event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return back()->withErrors('Event not found.');
            }
            $event_id = $event->event_id;
            $paper = Paper::with(['assignment.reviews', 'decisions'])->where('paper_sub_id', $paper_id)->first();
            if (!$paper) {
                return back()->withErrors('Paper not found.');
            }
            $topics = Topic::where('event_id', '=', $event->event_id)->get();
            $selected_topics = TopicPaper::where('first_paper_sub_id', $paper->first_paper_sub_id)->first();
            $paper->topics = $selected_topics;

            $user = User::where('root', 0)->get();
            $paper->keywords = json_decode($paper->keywords, true);
            $paper->keywords = array_column($paper->keywords, 'value');
            $paper->keywords = implode(', ', $paper->keywords);
            $paper->authors = json_decode($paper->authors, true);
            $paper->authors = array_map(function ($author) {
                return $author['name'] . ' - ' . $author['email'];
            }, $paper->authors);
            $paper->authors = implode(', ', $paper->authors);
            $review_items = ReviewItem::where('event_id', $event->event_id)
                ->orderBy('seq')
                ->with([
                    'options',
                    'review_contents' => function ($query) {
                        $query->orderBy('review_id');
                    }
                ])
                ->get();

            $decision = Decision::where('paper_sub_id', $paper->paper_sub_id)->first();

            if ($decision != null && $decision) {
                if ($decision->decision == 'Declined') {
                    return back()->withErrors('Paper has been declined.');
                }

                $review_last = Assignment::where('paper_sub_id', $paper->paper_sub_id)
                    ->with(['reviews.review_contents'])
                    ->orderBy('order', 'asc')
                    ->get();

                if (empty($decision) && $paper->round > 0) {
                    $prevPaper = Paper::where('first_paper_sub_id', $paper->first_paper_sub_id)
                        ->where('round', $paper->round - 1)
                        ->first();
                    if ($prevPaper) {
                        $prevDecision = Decision::where('paper_sub_id', $prevPaper->paper_sub_id)->first();
                        if ($prevDecision) {
                            $decision = $prevDecision;
                        }
                        $prevReviewLast = Assignment::where('paper_sub_id', $prevPaper->paper_sub_id)
                            ->with(['reviews.review_contents'])
                            ->orderBy('order', 'asc')
                            ->get();
                        if ($prevReviewLast->count() > 0) {
                            $review_last = $prevReviewLast;
                        }
                    }
                }
            } else {
                $decision = null;
                $review_last = null;
            }

            return view('pages.paper.detail', compact('paper', 'event', 'user', 'topics', 'review_items', 'review_last', 'decision'));
        } catch (\Exception $e) {
            return back()->withErrors('An error occurred while loading paper details.' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}
