<?php

namespace App\Http\Controllers;

use App\Models\Paper;
use App\Models\Payment;
use App\Models\PaymentSettings;
use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Country;
use App\Models\Topic;
use App\Models\TopicPaper;
use Illuminate\Support\Facades\File;
use App\Models\UserEvent;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use App\Models\Role;
use App\Models\TopicUser;
use PhpOffice\PhpWord\TemplateProcessor;
use App\Services\EmailService;
use NcJoes\OfficeConverter\OfficeConverter;
use App\Models\UserLogs; 
use Illuminate\Support\Facades\Log;

class OrganizerController extends Controller
{
    protected $emailService;

    public function __construct(EmailService $emailService)
    {
        $this->emailService = $emailService;
    }
    function event_index($event_code)
    {
        try {
            $event = Event::where('event_code', '=', $event_code)->firstOrFail();
            $countries = Country::orderBy('country_name')->get();
            $topicNames = Topic::where('event_id', $event->event_id)->pluck('topic_name')->toArray();
            $event->topics = json_encode(array_map(function ($t) {
                return ["value" => $t];
            }, $topicNames));
            return view('pages.organizer.event.index', compact('event', 'countries'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    function users_index($event_code)
    {
        try {
            $event = Event::with(['user'])
                ->where('event_code', '=', $event_code)
                ->firstOrFail();
            $join = UserEvent::where('user_id', '=', Auth::user()->user_id)
                ->where('event_id', '=', $event->event_id)
                ->exists();
            $members = User::with(['user_events.role', 'country'])
                ->whereHas('user_events', function ($query) use ($event) {
                    $query->where('event_id', $event->event_id);
                })
                ->get();
            return view('pages.organizer.users.index', compact('event', 'join', 'members'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load users: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function update_event(Request $request, $event_code)
    {
        try {
            $event = Event::where('event_code', '=', $event_code)->firstOrFail();
            $event_id = $event->event_id;
            $dataTopics = json_decode($request->input('topics'), true);
            $topics = $dataTopics;
            $request->validate(
                [
                    'event_desc' => 'required|max:255',
                    'event_logo' => 'nullable|mimes:jpeg,png,jpg,webp|dimensions:max_width=450,max_height=450',
                    'event_country' => 'required|exists:countries,country_id',
                    'event_organizer' => 'required|max:255',
                    'manager_name' => 'required|max:255',
                    'manager_contact_email' => 'required|email|max:255',
                    'manager_contact_country_code' => 'required|max:5',
                    'manager_contact_number' => 'required|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
                    'support_name' => 'nullable|max:255',
                    'support_contact_email' => 'nullable|email|max:255',
                    'support_contact_country_code' => 'required_with:support_contact_number|max:5',
                    'support_contact_number' => 'nullable|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
                    'treasure_name' => 'nullable|max:255',
                    'treasure_contact_email' => 'nullable|email|max:255',
                    'treasure_contact_country_code' => 'required_with:treasure_contact_number|max:5',
                    'treasure_contact_number' => 'nullable|max:15|regex:/^([0-9\s\-\+\(\)]*)$/',
                    'event_start' => 'required|date',
                    'event_end' => 'required|date|after_or_equal:event_start',
                    'submission_start' => 'required|date',
                    'submission_end' => 'required|date|after_or_equal:submission_start',
                    'revision_start' => 'required|date',
                    'revision_end' => 'required|date|after_or_equal:revision_start',
                    'join_np_start' => 'required|date',
                    'join_np_end' => 'required|date|after_or_equal:join_np_start',
                    'camera_ready_start' => 'required|date',
                    'camera_ready_end' => 'required|date|after_or_equal:camera_ready_start',
                    'payment_start' => 'required|date',
                    'payment_end' => 'required|date|after_or_equal:payment_start',
                ],
                [
                    'event_desc.required' => 'Event description is required.',
                    'event_desc.max' => 'Event description should not exceed 255 characters.',
                    'event_logo.required' => 'Event logo is required.',
                    'event_logo.mimes' => 'Event logo should be a jpeg, png, jpg, or webp file.',
                    'event_logo.dimensions' => 'Event logo should not exceed 450*450 pixels.',
                    'event_country.required' => 'Event country is required.',
                    'event_country.exists' => 'Event country must be a valid country.',
                    'event_organizer.required' => 'Event organizer is required.',
                    'event_organizer.max' => 'Event organizer should not exceed 255 characters.',
                    'manager_name.required' => 'Manager name is required.',
                    'manager_name.max' => 'Manager name should not exceed 255 characters.',
                    'manager_contact_email.required' => 'Manager contact email is required.',
                    'manager_contact_email.email' => 'Manager contact email should be a valid email address.',
                    'manager_contact_email.max' => 'Manager contact email should not exceed 255 characters.',
                    'manager_contact_country_code.required' => 'Manager contact country code is required.',
                    'manager_contact_number.required' => 'Manager contact number is required.',
                    'manager_contact_number.max' => 'Manager contact number should not exceed 15 characters.',
                    'manager_contact_number.regex' => 'Manager contact number should be a valid phone number.',
                    'support_name.max' => 'Support name should not exceed 255 characters.',
                    'support_contact_email.email' => 'Support contact email should be a valid email address.',
                    'support_contact_email.max' => 'Support contact email should not exceed 255 characters.',
                    'support_contact_country_code.required_with' => 'Support contact country code is required.',
                    'support_contact_number.max' => 'Support contact number should not exceed 15 characters.',
                    'support_contact_number.regex' => 'Support contact number should be a valid phone number.',
                    'treasure_name.max' => 'Treasure name should not exceed 255 characters.',
                    'treasure_contact_email.email' => 'Treasure contact email should be a valid email address.',
                    'treasure_contact_email.max' => 'Treasure contact email should not exceed 255 characters.',
                    'treasure_contact_country_code.required_with' => 'Treasure contact country code is required.',
                    'treasure_contact_number.max' => 'Treasure contact number should not exceed 15 characters.',
                    'treasure_contact_number.regex' => 'Treasure contact number should be a valid phone number.',
                    'event_start.required' => 'Event start date is required.',
                    'event_start.date' => 'Event start date must be a valid date.',
                    'event_end.required' => 'Event end date is required.',
                    'event_end.date' => 'Event end date must be a valid date.',
                    'event_end.after_or_equal' => 'Event end date must be after or equal to the event start date.',
                    'submission_start.required' => 'Submission start date is required.',
                    'submission_start.date' => 'Submission start date must be a valid date.',
                    'submission_end.required' => 'Submission end date is required.',
                    'submission_end.date' => 'Submission end date must be a valid date.',
                    'submission_end.after_or_equal' => 'Submission end date must be after or equal to the submission start date.',
                    'revision_start.required' => 'Revision start date is required.',
                    'revision_start.date' => 'Revision start date must be a valid date.',
                    'revision_end.required' => 'Revision end date is required.',
                    'revision_end.date' => 'Revision end date must be a valid date.',
                    'revision_end.after_or_equal' => 'Revision end date must be after or equal to the revision start date.',
                    'join_np_start.required' => 'Join NP start date is required.',
                    'join_np_start.date' => 'Join NP start date must be a valid date.',
                    'join_np_end.required' => 'Join NP end date is required.',
                    'join_np_end.date' => 'Join NP end date must be a valid date.',
                    'join_np_end.after_or_equal' => 'Join NP end date must be after or equal to the join NP start date.',
                    'camera_ready_start.required' => 'Camera ready start date is required.',
                    'camera_ready_start.date' => 'Camera ready start date must be a valid date.',
                    'camera_ready_end.required' => 'Camera ready end date is required.',
                    'camera_ready_end.date' => 'Camera ready end date must be a valid date.',
                    'camera_ready_end.after_or_equal' => 'Camera ready end date must be after or equal to the camera ready start date.',
                    'payment_start.required' => 'Payment start date is required.',
                    'payment_start.date' => 'Payment start date must be a valid date.',
                    'payment_end.required' => 'Payment end date is required.',
                    'payment_end.date' => 'Payment end date must be a valid date.',
                    'payment_end.after_or_equal' => 'Payment end date must be after or equal to the payment start date.',
                    'topics' => [
                        'required',
                        function ($attribute, $value, $fail) use ($topics) {
                            if (!is_array($topics)) {
                                return $fail('The keywords field must be a valid array.');
                            }
                            foreach ($topics as $topic) {
                                if (!isset($topic['value']) || !is_string($topic['value']) || trim($topic['value']) === '' || trim($topic['value']) === '.') {
                                    return $fail('Each topic must have a non-empty "value" field.');
                                }
                            }
                        },
                    ],
                ],
            );
            if ($request->hasFile('event_logo')) {
                if ($event->event_logo && File::exists(public_path('storage/' . config('path.logo_event') . $event->event_logo))) {
                    File::delete(public_path('storage/' . config('path.logo_event') . $event->event_logo));
                }
                $path = $request->file('event_logo')->storeAs(config('path.logo_event') . $event->event_id, $event->event_shortname . '.' . $request->event_logo->extension(), 'public');
                $event->event_logo = $event->event_id . '/' . $event->event_shortname . '.' . $request->event_logo->extension();
            }
            $eventEnd = \Carbon\Carbon::parse($request->event_end)->endOfDay();
            $submissionStart = \Carbon\Carbon::parse($request->submission_start);
            $now = now();

            $status = 'Upcoming';
            if ($now > $eventEnd) {
                $status = 'Finished';
            }
            if ($now < $submissionStart) {
                $status = 'Upcoming';
            }
            if ($now > $submissionStart && $now < $eventEnd) {
                $status = 'Ongoing';
            }

            $event->event_desc = $request->event_desc;
            $event->event_url = env('APP_URL') . $request->event_code;
            $event->event_organizer = $request->event_organizer;
            $event->country_id = $request->event_country;
            $event->event_start = $request->event_start;
            $event->event_end = $request->event_end;
            $event->submission_start = $request->submission_start;
            $event->submission_end = $request->submission_end;
            $event->revision_start = $request->revision_start;
            $event->revision_end = $request->revision_end;
            $event->join_np_start = $request->join_np_start;
            $event->join_np_end = $request->join_np_end;
            $event->camera_ready_start = $request->camera_ready_start;
            $event->camera_ready_end = $request->camera_ready_end;
            $event->payment_start = $request->payment_start;
            $event->payment_end = $request->payment_end;
            $event->manager_name = $request->manager_name;
            $event->manager_contact_email = $request->manager_contact_email;
            $event->manager_contact_ct = $request->manager_contact_country_code;
            $event->manager_contact_number = $request->manager_contact_number;
            $event->support_name = $request->support_name;
            $event->support_contact_email = $request->support_contact_email;
            $event->support_contact_ct = $request->support_contact_country_code;
            $event->support_contact_number = $request->support_contact_number;
            $event->treasurer_name = $request->treasure_name;
            $event->treasurer_contact_email = $request->treasure_contact_email;
            $event->treasurer_contact_ct = $request->treasure_contact_country_code;
            $event->treasurer_contact_number = $request->treasure_contact_number;
            $event->updated_by = auth()->user()->user_id;
            $event->event_status = $status;
            $event->save();

            $topicsInUse = TopicPaper::join('paper_submissions', 'topic_papers.first_paper_sub_id', '=', 'paper_submissions.paper_sub_id')
                ->where('paper_submissions.event_id', $event->event_id)
                ->pluck('topic_papers.topic_id')
                ->toArray();

            Topic::where('event_id', $event->event_id)->whereNotIn('topic_id', $topicsInUse)->delete();

            $existingTopicNames = Topic::where('event_id', $event->event_id)->whereIn('topic_id', $topicsInUse)->pluck('topic_name')->toArray();

            $newTopicNames = collect($topics)->pluck('value')->toArray();

            foreach ($existingTopicNames as $existingName) {
                $key = array_search($existingName, $newTopicNames);
                if ($key !== false) {
                    unset($newTopicNames[$key]);
                }
            }

            foreach ($newTopicNames as $topicName) {
                if (trim($topicName) !== '') {
                    Topic::create([
                        'topic_name' => $topicName,
                        'event_id' => $event->event_id,
                    ]);
                }
            }
            return back()->with('success', 'Event updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        }
    }

    public function edit_role_index(Request $request, $event_code, $username)
    {
        try {
            session(['previous_url' => url()->previous()]);
            $event = Event::where('event_code', $event_code)->firstOrFail();
            $event_id = $event->event_id;
            $user = User::where('username', $username)->firstOrFail();
            $user_id = $user->user_id;
            $user_detail = $user;
            $userEvent = UserEvent::with('user.country', 'role', 'event')->where('event_id', $event_id)->where('user_id', $user_id)->first();
            if (!$userEvent) {
                throw new \Exception('User is not a member of this event.');
            }
            $userRoles = User::with('user_events.role', 'user_events.event', 'country')->where('user_id', $user_id)->whereRelation('user_events', 'event_id', '=', $event_id)->first();
            $userEvent->roles = $userRoles->user_events->pluck('role.role_id')->toArray();
            $roles = Role::all();
            if (in_array('Paper Reviewer', $userRoles->user_events->pluck('role.role_name')->toArray())) {
                $user_expertise = $user_detail->expertise_users()->with('expertise')->get()->pluck('expertise.expertise_name')->implode(', ');
                $topics = Topic::where('event_id', '=', $event->event_id)->get();
                $selected_topics = TopicUser::where('user_id', $user_id)->join('topics', 'topic_users.topic_id', '=', 'topics.topic_id')->pluck('topics.topic_name')->implode(', ');
                $userEvent->topics = $selected_topics;
            } else {
                $user_expertise = null;
                $userEvent->topics = null;
                $topics = null;
            }
            return view('pages.organizer.users.edit', [
                'user' => $userEvent,
                'roles' => $roles,
                'user_expertise' => $user_expertise,
                'topics' => $topics
            ]);
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load user role edit page: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function update_role(Request $request, $event_code, $username)
    {
        try {
            $event = Event::where('event_code', $event_code)->firstOrFail();
            $event_id = $event->event_id;
            $user = User::where('username', $username)->firstOrFail();
            $user_id = $user->user_id;

            if ($request->has('topics')) {
                $dataTopics = json_decode($request->input('topics'), true);
                $topics = $dataTopics;

                $request->validate([
                    'topics' => [
                        'required',
                        function ($attribute, $value, $fail) use ($topics) {
                            if (!is_array($topics)) {
                                return $fail('The topics field must be a valid array.');
                            }
                            foreach ($topics as $topic) {
                                if (!isset($topic['value']) || !is_string($topic['value']) || trim($topic['value']) === '' || trim($topic['value']) === '.') {
                                    return $fail('Each topic must have a non-empty "value" field.');
                                }
                            }
                        },
                    ],
                ]);

                if (is_array($topics)) {
                    TopicUser::where('user_id', $user_id)->delete();
                    foreach ($topics as $topic) {
                        if (isset($topic['value']) && trim($topic['value']) !== '') {
                            TopicUser::create([
                                'topic_id' => $topic['value'],
                                'user_id' => $user_id,
                            ]);
                        }
                    }
                }
            }

            $request->validate(
                [
                    'roles' => 'required|array',
                    'roles.*' => 'exists:roles,role_id',
                ],
                [
                    'roles.required' => 'At least one role is required.',
                    'roles.array' => 'Roles must be an array.',
                    'roles.*.exists' => 'Selected role does not exist.',
                ],
            );

            $existingRoles = UserEvent::where('event_id', $event_id)->where('user_id', $user_id)->get();
            UserEvent::where('event_id', $event_id)->where('user_id', $user_id)->delete();

            try {
                foreach ($request->roles as $role_id) {
                    UserEvent::create([
                        'event_id' => $event_id,
                        'user_id' => $user_id,
                        'role_id' => $role_id,
                    ]);
                }
            } catch (\Exception $e) {
                foreach ($existingRoles as $role) {
                    UserEvent::create([
                        'event_id' => $role->event_id,
                        'user_id' => $role->user_id,
                        'role_id' => $role->role_id,
                    ]);
                }

                return redirect(session('previous_url', route('organizer.users', $event_code)))
                    ->withErrors('Failed to update user role : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))
                    ->withInput();
            }

            return redirect(session('previous_url', route('organizer.users', $event_code)))->with('success', 'User role updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return redirect(session('previous_url', route('organizer.users', $event_code)))
                ->withErrors($e->errors())
                ->withInput();
        } catch (\Exception $e) {
            return redirect(session('previous_url', route('organizer.users', $event_code)))
                ->withErrors('Failed to update user role : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))
                ->withInput();
        }
    }

    function payment_index($event_code)
    {
        $event = Event::where('event_code', '=', $event_code)->first();
        $event_id = $event->event_id;
        $payments = Payment::with([
            'paper',
            'user',
            'event',
            'paymentHistories' => function ($query) {
                $query->orderBy('created_at', 'desc')->limit(1);
            }
        ])
            ->where('event_id', '=', $event_id)
            ->orderBy('created_at', 'desc')
            ->get();
        // ->groupBy('paid_by')
        // ->map(function ($group) {
        // return $group->first();
        // })
        // ->values();
        // dd($payments);
        $payment_set = PaymentSettings::where('event_id', $event_id)->first();
        return view('pages.organizer.payment.index', compact('event', 'payment_set', 'payments'));
    }

    function payment_update($event_code, $payment_id)
    {
        try {
            $event = Event::where('event_code', '=', $event_code)->firstOrFail();
            $event_id = $event->event_id;
            $payment = Payment::where('payment_id', '=', $payment_id)->first();
            $payment->update(['status' => $payment->status === 'Pending' ? 'Paid' : 'Unpaid']);
            $payment->refresh();

            if ($payment->status === 'Paid' && $oldStatus === 'Pending') {
                try {
                    $organizerUser = Auth::user(); // Ini adalah organizer yang menekan tombol konfirmasi
                    if ($organizerUser) {
                        UserLogs::create([
                            'user_id' => $organizerUser->user_id,
                            'ip_address' => $request->getClientIp(),
                            'user_log_type' => 'Payment Confirmation', // <-- Nilai ENUM
                            'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                            'created_at' => now(),
                        ]);
                    }
                } catch (\Exception $e) {
                    // Catat error jika logging gagal, tapi jangan hentikan proses utama
                    Log::error('Gagal mencatat log Payment Confirmation: ' . $e->getMessage());
                }
            }

            if ($payment->first_paper_sub_id) {
                if ($payment->status === 'Paid') {
                    try {
                        $data = $this->send_loi($event_id, $payment->first_paper_sub_id);
                    } catch (\Exception $e) {
                        $payment->update(['status' => 'Pending']);
                        // dd(str_replace(["\r", "\n"], ' ', $e->getMessage()));
                        return back()->withErrors('Failed to generate LoI: ' . str_replace(["\r", "\n"], ' ', str_replace(["\r", "\n"], ' ', $e->getMessage())));
                    }

                    if ($data) {
                        try {
                            $this->emailService->sendLoI($data[0], $data[1], $data[2]);
                        } catch (\Exception $e) {
                            \Log::error(str_replace(["\r", "\n"], ' ', $e->getMessage()));
                            return back()->withErrors('Failed to sent LoI email to author : ' . str_replace(["\r", "\n"], ' ', str_replace(["\r", "\n"], ' ', $e->getMessage())));
                        }
                    }
                }
            } else {
                if ($payment->status === 'Paid') {
                    try {
                        $data = $this->loi_nonpresenter($event, $payment->paid_by);
                    } catch (\Exception $e) {
                        $payment->update(['status' => 'Pending']);
                        // dd(str_replace(["\r", "\n"], ' ', $e->getMessage()));
                        return back()->withErrors('Failed to generate LoI: ' . str_replace(["\r", "\n"], ' ', str_replace(["\r", "\n"], ' ', $e->getMessage())));
                    }

                    if ($data) {
                        try {
                            $this->emailService->sendLoINP($data[0], $data[1], $event);
                        } catch (\Exception $e) {
                            \Log::error(str_replace(["\r", "\n"], ' ', $e->getMessage()));
                            return back()->withErrors('Failed to sent LoI email to author : ' . str_replace(["\r", "\n"], ' ', str_replace(["\r", "\n"], ' ', $e->getMessage())));
                        }
                    }
                }
            }

            //opsi log

            return back()->with('success', 'Payment Status updated successfully.');
            // return redirect()->back()->with('success', 'Payment Status updated successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to change payment status : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function send_loi($event, $paper_sub_id)
    {
        // Check if template file exists
        $templatePath = public_path('assets/template/LoI_ISSAT_2025.docx');
        if (!file_exists($templatePath)) {
            return back()->withErrors('Template file not found: ' . $templatePath);
        }

        $phpWord = new TemplateProcessor($templatePath);
        $paper = Paper::with(['event:event_id,event_name,event_start,event_end', 'event.country:country_id,country_name', 'user'])->where('paper_sub_id', $paper_sub_id)->firstOrFail();
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

        $phpWord->setValue('Conference Dates', $formattedDate);
        $phpWord->setValue('Date Sent', date('d F Y'));
        $authors = json_decode($paper->authors, true);
        $authorNames = collect($authors)->pluck('name')->implode(', ');
        $phpWord->setValue('Author', $authorNames);
        $phpWord->setValue('Paper Title', $paper->title);

        $loaDir = storage_path('app/public/loi');
        if (!is_dir($loaDir)) {
            mkdir($loaDir, 0755, true);
        }

        $authorName = preg_replace('/[^A-Za-z0-9_\-]/', '_', $paper->title);
        $fileName = 'LOI_' . $authorName . '_' . $paper_sub_id . '_' . date('Y-m-d') . '.docx';
        $filePath = $loaDir . '/' . $fileName;

        $phpWord->saveAs($filePath);

        // Convert to PDF using OfficeConverter
        if (!isset($_SERVER['HOME'])) {
            $_SERVER['HOME'] = getenv('HOME') ?: (getenv('HOMEDRIVE') . getenv('HOMEPATH'));
        }

        $converter = new OfficeConverter($filePath);
        $converter->convertTo(basename($fileName, '.docx') . '.pdf'); // output to same directory

        $pdfFilePath = $loaDir . '/' . basename($fileName, '.docx') . '.pdf';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return [$paper, $authorNames, $pdfFilePath];
    }

    public function loi_nonpresenter($event, $participant_id)
    {
        $templatePath = public_path('assets/template/LoI_ISSAT_2025_non_presenter.docx');
        if (!file_exists($templatePath)) {
            return back()->withErrors('Template file not found: ' . $templatePath);
        }
        $participant = User::where('user_id', $participant_id)->firstOrFail();

        $phpWord = new TemplateProcessor($templatePath);
        $phpWord->setValue('Conference Name', $event->event_name);
        $startDate = new \DateTime($event->event_start);
        $endDate = new \DateTime($event->event_end);
        if ($startDate->format('Y-m-d') === $endDate->format('Y-m-d')) {
            $formattedDate = $startDate->format('d F Y');
        } elseif ($startDate->format('Y-m') === $endDate->format('Y-m')) {
            $formattedDate = $startDate->format('d') . ' - ' . $endDate->format('d') . ' ' . $endDate->format('F Y');
        } elseif ($startDate->format('Y') === $endDate->format('Y')) {
            $formattedDate = $startDate->format('d F') . ' - ' . $endDate->format('d F Y');
        } else {
            $formattedDate = $startDate->format('d F Y') . ' - ' . $endDate->format('d F Y');
        }

        $phpWord->setValue('Conference Dates', $formattedDate);
        $phpWord->setValue('Date Sent', date('d F Y'));
        $phpWord->setValue('Participant Name', $participant->given_name . ' ' . $participant->family_name);

        $loaDir = storage_path('app/public/loi');
        if (!is_dir($loaDir)) {
            mkdir($loaDir, 0755, true);
        }

        $fileName = 'LOI_' . $participant->given_name . '_' . $participant->family_name . '_' . date('Y-m-d') . '.docx';
        $filePath = $loaDir . '/' . $fileName;

        $phpWord->saveAs($filePath);

        // Convert to PDF using OfficeConverter
        if (!isset($_SERVER['HOME'])) {
            $_SERVER['HOME'] = getenv('HOME') ?: (getenv('HOMEDRIVE') . getenv('HOMEPATH'));
        }

        $converter = new OfficeConverter($filePath);
        $converter->convertTo(basename($fileName, '.docx') . '.pdf'); // output to same directory

        $pdfFilePath = $loaDir . '/' . basename($fileName, '.docx') . '.pdf';
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        return [$participant, $pdfFilePath];
    }

    public function payment_set_index(Request $request, $event)
    {
        try {
            $event = Event::where('event_code', '=', $event)->firstOrFail();
            $paymentSettings = PaymentSettings::where('event_id', $event->event_id)->first();
            return view('pages.organizer.payment.settings', compact('event', 'paymentSettings'));
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load payment settings: ' . str_replace(["\r", "\n"], ' ', $th->getMessage()));
        }
    }

    public function payment_set_store(Request $request, $event)
    {
        try {
            $event = Event::where('event_code', '=', $event)->firstOrFail();

            $request->validate([
                'pay_as_pstr_on_ntl_curr' => 'required|string|max:3',
                'pay_as_pstr_on_ntl_amount' => 'required|numeric|min:0',
                'pay_as_pstr_off_ntl_curr' => 'required|string|max:3',
                'pay_as_pstr_off_ntl_amount' => 'required|numeric|min:0',
                'pay_as_npstr_off_ntl_curr' => 'required|string|max:3',
                'pay_as_npstr_off_ntl_amount' => 'required|numeric|min:0',
                'pay_as_pstr_on_intl_curr' => 'required|string|max:3',
                'pay_as_pstr_on_intl_amount' => 'required|numeric|min:0',
                'pay_as_pstr_off_intl_curr' => 'required|string|max:3',
                'pay_as_pstr_off_intl_amount' => 'required|numeric|min:0',
                'acc_beneficiary_name' => 'required|string|max:100',
                'acc_bank_name' => 'required|string|max:100',
                'acc_bank_acc' => 'required|string|max:50',
                'acc_swift_code' => 'required|string|max:20',
            ]);

            $paymentSettings = PaymentSettings::updateOrCreate(
                ['event_id' => $event->event_id],
                [
                    'event_id' => $event->event_id,
                    'pay_as_pstr_on_ntl' => $request->pay_as_pstr_on_ntl,
                    'pay_as_pstr_on_ntl_curr' => $request->pay_as_pstr_on_ntl_curr,
                    'pay_as_pstr_on_ntl_amount' => $request->pay_as_pstr_on_ntl_amount,
                    'pay_as_pstr_off_ntl' => $request->pay_as_pstr_off_ntl,
                    'pay_as_pstr_off_ntl_curr' => $request->pay_as_pstr_off_ntl_curr,
                    'pay_as_pstr_off_ntl_amount' => $request->pay_as_pstr_off_ntl_amount,
                    'pay_as_npstr_off_ntl' => $request->pay_as_npstr_off_ntl,
                    'pay_as_npstr_off_ntl_curr' => $request->pay_as_npstr_off_ntl_curr,
                    'pay_as_npstr_off_ntl_amount' => $request->pay_as_npstr_off_ntl_amount,
                    'pay_as_pstr_on_intl' => $request->pay_as_pstr_on_intl,
                    'pay_as_pstr_on_intl_curr' => $request->pay_as_pstr_on_intl_curr,
                    'pay_as_pstr_on_intl_amount' => $request->pay_as_pstr_on_intl_amount,
                    'pay_as_pstr_off_intl' => $request->pay_as_pstr_off_intl,
                    'pay_as_pstr_off_intl_curr' => $request->pay_as_pstr_off_intl_curr,
                    'pay_as_pstr_off_intl_amount' => $request->pay_as_pstr_off_intl_amount,
                    'acc_beneficiary_name' => $request->acc_beneficiary_name,
                    'acc_bank_name' => $request->acc_bank_name,
                    'acc_bank_acc' => $request->acc_bank_acc,
                    'acc_swift_code' => $request->acc_swift_code,
                    'created_by' => auth()->user()->user_id,
                    'updated_by' => auth()->user()->user_id,
                ]
            );

            return redirect()->back()->with('success', 'Payment settings updated successfully.');
        } catch (\Throwable $th) {
            return redirect()->back()->withErrors('Failed to load payment settings: ' . str_replace(["\r", "\n"], ' ', $th->getMessage()));
        }
    }
}
