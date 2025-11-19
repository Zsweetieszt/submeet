<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Payment;
use App\Models\Role;
use App\Models\UserEvent;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;
use App\Models\Topic;
use App\Models\TopicPaper;
use App\Models\User;
use App\Models\UserLogs;
use Illuminate\Support\Facades\Log;

class EventController extends Controller
{
    public function index()
    {
        try {
            $polban_only = env('POLBAN_ONLY', false);
            $user = Auth::user();
            $email = $user->email;
            $root = $user->root;
            if ($polban_only === true) {
                if (!str_contains($email, "polban.ac.id") && !$root) {
                    $events = [];
                    $user_events = [];
                    return view('pages.events.index', compact('events', 'user_events'));
                }
            }
            $events = Event::all();
            $user_events = Auth::user()->user_events
                ->map(function ($userEvent) {
                    return [
                        'event_id' => $userEvent->event_id,
                        'role' => $userEvent->role ? $userEvent->role->role_name : null,
                        'is_offline' => $userEvent->is_offline,
                    ];
                })->toArray();
            $user_has_payment = Payment::whereNot('status', 'Unpaid')->pluck('paid_by')->toArray();
            $user_has_payment = in_array(Auth::user()->user_id, $user_has_payment);
            return view('pages.events.index', compact('events', 'user_events', 'user_has_payment'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load events: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function create()
    {
        try {
            $countries = Country::orderBy('country_name')->get();
            return view('pages.events.create', compact('countries'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load countries: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function store(Request $request)
    {
        try {
            $dataTopics = json_decode($request->input('topics'), true);
            $topics = $dataTopics;
            $request->validate(
                [
                    'event_name' => 'required|max:100|unique:events,event_name',
                    'event_desc' => 'required|max:255',
                    'event_code' => 'required|max:35|unique:events,event_code|regex:/^[a-z0-9\-_]+$/',
                    'event_logo' => 'required|mimes:jpeg,png,jpg,webp|dimensions:max_width=450,max_height=450',
                    'event_country' => 'required|exists:countries,country_id',
                    'event_organizer' => 'required|max:255',
                    'event_shortname' => 'required|max:15',
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
                    'submission_start' => 'required|date|before:event_end',
                    'submission_end' => 'required|date|after_or_equal:submission_start',
                    'revision_start' => 'required|date|before:event_end|after_or_equal:submission_start',
                    'revision_end' => 'required|date|after_or_equal:revision_start',
                    'join_np_start' => 'required|date|before:event_end',
                    'join_np_end' => 'required|date|after_or_equal:join_np_start',
                    'camera_ready_start' => 'required|date|before:event_end',
                    'camera_ready_end' => 'required|date|after_or_equal:camera_ready_start',
                    'payment_start' => 'required|date|before:event_end',
                    'payment_end' => 'required|date|after_or_equal:payment_start',
                    'topics' => [
                        'required',
                        function ($attribute, $value, $fail) use ($topics) {
                            $count = count($topics);
                            if ($count < 3 || $count > 30) {
                                return $fail('You must provide between 3 and 30 topics.');
                            }
                            if (!is_array($topics)) {
                                return $fail('The keywords field must be a valid array.');
                            }
                            foreach ($topics as $topic) {
                                if (!isset($topic['value']) || !is_string($topic['value']) || trim($topic['value']) === '' || trim($topic['value']) === '.') {
                                    return $fail('Each topic must have a non-empty "value" field.');
                                }
                                if (strlen(trim($topic['value'])) > 100) {
                                    return $fail('Each topic must not exceed 100 characters.');
                                }
                            }
                        },
                    ],
                ],
                [
                    'event_name.required' => 'Event name is required.',
                    'event_name.max' => 'Event name should not exceed 100 characters.',
                    'event_name.unique' => 'Event name already exists.',
                    'event_desc.required' => 'Event description is required.',
                    'event_desc.max' => 'Event description should not exceed 255 characters.',
                    'event_code.required' => 'Event code is required.',
                    'event_code.max' => 'Event code should not exceed 35 characters.',
                    'event_code.unique' => 'Event code already exists.',
                    'event_code.regex' => 'Event code can only contain lowercase letters, numbers, hyphens, and underscores.',
                    'event_logo.required' => 'Event logo is required.',
                    'event_logo.mimes' => 'Event logo should be a jpeg, png, jpg, or webp file.',
                    'event_logo.dimensions' => 'Event logo should not exceed 450*450 pixels.',
                    'event_country.required' => 'Event country is required.',
                    'event_country.exists' => 'Event country must be a valid country.',
                    'event_organizer.required' => 'Event organizer is required.',
                    'event_organizer.max' => 'Event organizer should not exceed 255 characters.',
                    'event_shortname.required' => 'Event short name is required.',
                    'event_shortname.max' => 'Event short name should not exceed 15 characters.',
                    'manager_name.required' => 'Manager name is required.',
                    'manager_name.max' => 'Manager name should not exceed 255 characters.',
                    'manager_contact_email.required' => 'Manager contact email is required.',
                    'manager_contact_email.email' => 'Manager contact email should be a valid email address.',
                    'manager_contact_email.max' => 'Manager contact email should not exceed 255 characters.',
                    'manager_contact_number.required' => 'Manager contact number is required.',
                    'manager_contact_country_code.required' => 'Manager contact country code is required.',
                    'manager_contact_number.max' => 'Manager contact number should not exceed 15 characters.',
                    'manager_contact_number.regex' => 'Manager contact number should be a valid phone number.',
                    'support_name.max' => 'Support name should not exceed 255 characters.',
                    'support_contact_email.email' => 'Support contact email should be a valid email address.',
                    'support_contact_email.max' => 'Support contact email should not exceed 255 characters.',
                    'support_contact_country_code.required' => 'Manager contact country code is required.',
                    'support_contact_number.max' => 'Support contact number should not exceed 15 characters.',
                    'support_contact_number.regex' => 'Support contact number should be a valid phone number.',
                    'treasure_name.max' => 'Treasure name should not exceed 255 characters.',
                    'treasure_contact_email.email' => 'Treasure contact email should be a valid email address.',
                    'treasure_contact_email.max' => 'Treasure contact email should not exceed 255 characters.',
                    'treasure_contact_country_code.required' => 'Manager contact country code is required.',
                    'treasure_contact_number.max' => 'Treasure contact number should not exceed 15 characters.',
                    'treasure_contact_number.regex' => 'Treasure contact number should be a valid phone number.',
                    'event_start.required' => 'Event start date is required.',
                    'event_start.date' => 'Event start date must be a valid date.',
                    'event_end.required' => 'Event end date is required.',
                    'event_end.date' => 'Event end date must be a valid date.',
                    'event_end.after_or_equal' => 'Event end date must be after or equal to the event start date.',
                    'submission_start.required' => 'Submission start date is required.',
                    'submission_start.date' => 'Submission start date must be a valid date.',
                    'submission_start.before' => 'Submission start date must be before to the event end date.',
                    'submission_end.required' => 'Submission end date is required.',
                    'submission_end.date' => 'Submission end date must be a valid date.',
                    'submission_end.after_or_equal' => 'Submission end date must be after or equal to the submission start date.',
                    'revision_start.required' => 'Revision start date is required.',
                    'revision_start.date' => 'Revision start date must be a valid date.',
                    'revision_start.before' => 'Revision start date must be before to the event end date.',
                    'revision_start.after_or_equal' => 'Revision start date must be after or equal to the submission start date.',
                    'revision_end.required' => 'Revision end date is required.',
                    'revision_end.date' => 'Revision end date must be a valid date.',
                    'revision_end.after_or_equal' => 'Revision end date must be after or equal to the revision start date.',
                    'join_np_start.required' => 'Join NP start date is required.',
                    'join_np_start.date' => 'Join NP start date must be a valid date.',
                    'join_np_start.before' => 'Join NP start date must be before to the event end date.',
                    'join_np_end.required' => 'Join NP end date is required.',
                    'join_np_end.date' => 'Join NP end date must be a valid date.',
                    'join_np_end.after_or_equal' => 'Join NP end date must be after or equal to the join NP start date.',
                    'camera_ready_start.required' => 'Camera ready start date is required.',
                    'camera_ready_start.date' => 'Camera ready start date must be a valid date.',
                    'camera_ready_start.before' => 'Camera ready start date must be before to the event end date.',
                    'camera_ready_end.required' => 'Camera ready end date is required.',
                    'camera_ready_end.date' => 'Camera ready end date must be a valid date.',
                    'camera_ready_end.after_or_equal' => 'Camera ready end date must be after or equal to the camera ready start date.',
                    'payment_start.required' => 'Payment start date is required.',
                    'payment_start.date' => 'Payment start date must be a valid date.',
                    'payment_start.before' => 'Payment start date must be before to the event end date.',
                    'payment_end.required' => 'Payment end date is required.',
                    'payment_end.date' => 'Payment end date must be a valid date.',
                    'payment_end.after_or_equal' => 'Payment end date must be after or equal to the payment start date.',
                ],
            );

            $country = Country::where('country_id', $request->event_country)->first();
            $countryCode = $country->iso;
            $timezoneList = \DateTimeZone::listIdentifiers(\DateTimeZone::PER_COUNTRY, $countryCode);
            $dateTimeZone = new \DateTimeZone($timezoneList[0]);
            $dateTime = new \DateTime('now', $dateTimeZone);
            $offsetSeconds = $dateTimeZone->getOffset($dateTime);
            $sign = $offsetSeconds >= 0 ? '+' : '-';
            $absOffset = abs($offsetSeconds);
            $hours = floor($absOffset / 3600);
            $minutes = floor(($absOffset % 3600) / 60);
            $timezone = sprintf('%s%02d:%02d', $sign, $hours, $minutes);
            $url = env('APP_URL') . $request->event_code;
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
            $event = Event::create([
                'event_name' => $request->event_name,
                'event_shortname' => $request->event_shortname,
                'event_desc' => $request->event_desc,
                'event_code' => $request->event_code,
                'event_logo' => '-',
                'event_url' => $url,
                'event_organizer' => $request->event_organizer,
                'country_id' => $request->event_country,
                'event_start' => $request->event_start,
                'event_end' => $request->event_end,
                'submission_start' => $request->submission_start,
                'submission_end' => $request->submission_end,
                'revision_start' => $request->revision_start,
                'revision_end' => $request->revision_end,
                'join_np_start' => $request->join_np_start,
                'join_np_end' => $request->join_np_end,
                'camera_ready_start' => $request->camera_ready_start,
                'camera_ready_end' => $request->camera_ready_end,
                'payment_start' => $request->payment_start,
                'payment_end' => $request->payment_end,
                'manager_name' => $request->manager_name,
                'manager_contact_email' => $request->manager_contact_email,
                'manager_contact_ct' => $request->manager_contact_country_code,
                'manager_contact_number' => $request->manager_contact_number,
                'support_name' => $request->support_name,
                'support_contact_email' => $request->support_contact_email,
                'support_contact_ct' => $request->support_contact_country_code,
                'support_contact_number' => $request->support_contact_number,
                'treasurer_name' => $request->treasure_name,
                'treasurer_contact_email' => $request->treasure_contact_email,
                'treasurer_contact_ct' => $request->treasure_contact_country_code,
                'treasurer_contact_number' => $request->treasure_contact_number,
                'event_status' => $status,
                'event_tz' => $timezone,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ]);

            if (is_array($topics)) {
                foreach ($topics as $topic) {
                    if (isset($topic['value']) && trim($topic['value']) !== '') {
                        Topic::create([
                            'topic_name' => $topic['value'],
                            'event_id' => $event->event_id,
                        ]);
                    }
                }
            }

            $path = $request->file('event_logo')->storeAs(config('path.logo_event') . $event->event_id, $event->event_shortname . '.' . $request->event_logo->extension(), 'public');
            $event->event_logo = $event->event_id . '/' . $event->event_shortname . '.' . $request->event_logo->extension();
            $event->save();

            return redirect()->route('events')->with('success', 'Event created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('Failed to create event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        }
    }

    public function show(Request $request, $event_code)
    {
        try {
            $event = Event::with(['user'])
                ->where('event_code', '=', $event_code)
                ->firstOrFail();
            $join = UserEvent::where('user_id', '=', Auth::user()->user_id)
                ->where('event_id', '=', $event->event_id)
                ->exists();
            if (Auth::user()->root) {
                $members = User::with(['user_events.role', 'country'])
                    ->whereHas('user_events', function ($query) use ($event) {
                        $query->where('event_id', $event->event_id);
                    })
                    ->get();
                return view('pages.events.show', compact('event', 'join', 'members'));
            }
            return view('pages.events.show', compact('event', 'join'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function edit(Request $request, $event_code)
    {
        try {
            $event = Event::where('event_code', '=', $event_code)->firstOrFail();
            $countries = Country::orderBy('country_name')->get();
            $topics = Topic::where('event_id', $event->event_id)->pluck('topic_name')->toArray();
            $event->topics = json_encode(array_map(function($t) { return ["value" => $t]; }, $topics));
            return view('pages.events.edit', compact('event', 'countries'));
        } catch (\Exception $e) {
            return back()->withErrors('Failed to load event for editing: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function update(Request $request, $event_code)
    {
        try {
            $event = Event::where('event_code', '=', $event_code)->firstOrFail();
            $event_id = $event->event_id;
            $dataTopics = json_decode($request->input('topics'), true);
            $topics = $dataTopics;
            $request->validate(
                [
                    'event_name' => 'required|max:100|unique:events,event_name,' . $event_id . ',event_id',
                    'event_desc' => 'required|max:255',
                    'event_code' => 'required|max:35|unique:events,event_code,' . $event_id . ',event_id|regex:/^[a-z0-9\-_]+$/',
                    'event_logo' => 'nullable|mimes:jpeg,png,jpg,webp|dimensions:max_width=450,max_height=450',
                    'event_country' => 'required|exists:countries,country_id',
                    'event_organizer' => 'required|max:255',
                    'event_shortname' => 'required|max:15',
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
                    'submission_start' => 'required|date|before:event_end',
                    'submission_end' => 'required|date|after_or_equal:submission_start',
                    'revision_start' => 'required|date|before:event_end|after_or_equal:submission_start',
                    'revision_end' => 'required|date|after_or_equal:revision_start',
                    'join_np_start' => 'required|date|before:event_end',
                    'join_np_end' => 'required|date|after_or_equal:join_np_start',
                    'camera_ready_start' => 'required|date|before:event_end',
                    'camera_ready_end' => 'required|date|after_or_equal:camera_ready_start',
                    'payment_start' => 'required|date|before:event_end',
                    'payment_end' => 'required|date|after_or_equal:payment_start',
                    'topics' => [
                        'required',
                        function ($attribute, $value, $fail) use ($topics) {
                            $count = count($topics);
                            if ($count < 3 || $count > 30) {
                                return $fail('You must provide between 3 and 30 topics.');
                            }
                            if (!is_array($topics)) {
                                return $fail('The keywords field must be a valid array.');
                            }
                            foreach ($topics as $topic) {
                                if (!isset($topic['value']) || !is_string($topic['value']) || trim($topic['value']) === '' || trim($topic['value']) === '.') {
                                    return $fail('Each topic must have a non-empty "value" field.');
                                }
                                if (strlen(trim($topic['value'])) > 100) {
                                    return $fail('Each topic must not exceed 100 characters.');
                                }
                            }
                        },
                    ],
                ],
                [
                    'event_name.required' => 'Event name is required.',
                    'event_name.max' => 'Event name should not exceed 100 characters.',
                    'event_name.unique' => 'Event name already exists.',
                    'event_desc.required' => 'Event description is required.',
                    'event_desc.max' => 'Event description should not exceed 255 characters.',
                    'event_code.required' => 'Event code is required.',
                    'event_code.max' => 'Event code should not exceed 35 characters.',
                    'event_code.unique' => 'Event code already exists.',
                    'event_code.regex' => 'Event code can only contain lowercase letters, numbers, hyphens, and underscores.',
                    'event_logo.required' => 'Event logo is required.',
                    'event_logo.mimes' => 'Event logo should be a jpeg, png, jpg, or webp file.',
                    'event_logo.dimensions' => 'Event logo should not exceed 450*450 pixels.',
                    'event_country.required' => 'Event country is required.',
                    'event_country.exists' => 'Event country must be a valid country.',
                    'event_organizer.required' => 'Event organizer is required.',
                    'event_organizer.max' => 'Event organizer should not exceed 255 characters.',
                    'event_shortname.required' => 'Event short name is required.',
                    'event_shortname.max' => 'Event short name should not exceed 15 characters.',
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
                    'submission_start.before' => 'Submission start date must be before to the event end date.',
                    'submission_end.required' => 'Submission end date is required.',
                    'submission_end.date' => 'Submission end date must be a valid date.',
                    'submission_end.after_or_equal' => 'Submission end date must be after or equal to the submission start date.',
                    'revision_start.required' => 'Revision start date is required.',
                    'revision_start.date' => 'Revision start date must be a valid date.',
                    'revision_start.before' => 'Revision start date must be before to the event end date.',
                    'revision_start.after_or_equal' => 'Revision start date must be after or equal to the submission start date.',
                    'revision_end.required' => 'Revision end date is required.',
                    'revision_end.date' => 'Revision end date must be a valid date.',
                    'revision_end.after_or_equal' => 'Revision end date must be after or equal to the revision start date.',
                    'join_np_start.required' => 'Join NP start date is required.',
                    'join_np_start.date' => 'Join NP start date must be a valid date.',
                    'join_np_start.before' => 'Join NP start date must be before to the event end date.',
                    'join_np_end.required' => 'Join NP end date is required.',
                    'join_np_end.date' => 'Join NP end date must be a valid date.',
                    'join_np_end.after_or_equal' => 'Join NP end date must be after or equal to the join NP start date.',
                    'camera_ready_start.required' => 'Camera ready start date is required.',
                    'camera_ready_start.date' => 'Camera ready start date must be a valid date.',
                    'camera_ready_start.before' => 'Camera ready start date must be before to the event end date.',
                    'camera_ready_end.required' => 'Camera ready end date is required.',
                    'camera_ready_end.date' => 'Camera ready end date must be a valid date.',
                    'camera_ready_end.after_or_equal' => 'Camera ready end date must be after or equal to the camera ready start date.',
                    'payment_start.required' => 'Payment start date is required.',
                    'payment_start.date' => 'Payment start date must be a valid date.',
                    'payment_start.before' => 'Payment start date must be before to the event end date.',
                    'payment_end.required' => 'Payment end date is required.',
                    'payment_end.date' => 'Payment end date must be a valid date.',
                    'payment_end.after_or_equal' => 'Payment end date must be after or equal to the payment start date.',
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
            $event->event_name = $request->event_name;
            $event->event_shortname = $request->event_shortname;
            $event->event_desc = $request->event_desc;
            $event->event_code = $request->event_code;
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
            
            // Get all existing topics for this event
            $existingTopics = Topic::where('event_id', $event->event_id)->get();
            $existingTopicNames = $existingTopics->pluck('topic_name')->toArray();
            $newTopicNames = collect($topics)->pluck('value')->toArray();

            // Check if user is trying to delete topics (if new topics count is less than existing)
            if (count($newTopicNames) < count($existingTopicNames)) {
                return redirect()->back()->with('error', 'Topics cannot be deleted. You can only add new topics or rename existing ones.');
            }

            // Check if any existing topic is missing in the new list (deletion attempt)
            foreach ($existingTopicNames as $existingName) {
                if (!in_array($existingName, $newTopicNames)) {
                    // Check if it's a rename by comparing counts
                    $matchFound = false;
                    foreach ($newTopicNames as $newName) {
                        if (!in_array($newName, $existingTopicNames)) {
                            $matchFound = true;
                            break;
                        }
                    }
                    if (!$matchFound) {
                        return back()->withErrors('Topics cannot be deleted. You can only add new topics or rename existing ones.')->withInput();
                    }
                }
            }

            // Handle renames and additions
            foreach ($existingTopics as $index => $existingTopic) {
                if (isset($newTopicNames[$index]) && $existingTopic->topic_name !== $newTopicNames[$index]) {
                    // Rename existing topic
                    $existingTopic->topic_name = $newTopicNames[$index];
                    $existingTopic->save();
                }
            }

            // Add new topics (only if there are more new topics than existing ones)
            if (count($newTopicNames) > count($existingTopicNames)) {
                $topicsToAdd = array_slice($newTopicNames, count($existingTopicNames));
                foreach ($topicsToAdd as $topicName) {
                    if (trim($topicName) !== '') {
                        Topic::create([
                            'topic_name' => $topicName,
                            'event_id' => $event->event_id,
                        ]);
                    }
                }
            }

            return redirect()->route('events')->with('success', 'Event updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('Failed to update event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        }
    }

    public function destroy(Request $request, $event)
    {
        try {
            $event = Event::where('event_code', '=', $event)->first();
            $event->delete();
            return redirect()->route('events')->with('success', 'Event deleted successfully.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() === '23503') {
                return back()
                    ->withErrors('Cannot delete the event because it has participants.')
                    ->withInput();
            }

            return back()
                ->withErrors('An error occurred while deleting the event: ')
                ->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('Failed to delete event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))->withInput();
        }
    }

    public function join(Request $request, $event_code)
    {
        $user = Auth::user();
        $email = $user->email;
        $polban_only = env('POLBAN_ONLY', false);
        if ($polban_only === true) {
            if (!str_contains($email, "polban.ac.id")) {
                return back()->withErrors('Failed to join the event.');
            }
        }
        $request->validate(
            [
                'role' => 'required',
                'is_offline' => 'required',
            ],
            [
                'role.required' => 'You need to choose participate as.',
                'is_offline.required' => 'You need to choose attending event online or offline.',
            ]
        );

        try {
            $user = Auth::user();
            $event = Event::where('event_code', '=', $event_code)->first();

            if (UserEvent::where('user_id', '=', $user->user_id)->where('event_id', '=', $event->event_id)->exists()) {
                return redirect()->route('dashboard.event', ['event' => $event_code]);
            }

            if ($request->has('role')) {
                $role_id = Role::where('role_name', '=', $request->role)->first()->role_id;
            } else {
                $role_id = 1;
                $is_offline = 1;
            }

            UserEvent::create([
                'user_id' => $user->user_id,
                'event_id' => $event->event_id,
                'role_id' => $role_id,
                'is_offline' => $request->is_offline ?? $is_offline,
            ]);

            try {
                UserLogs::create([
                    'user_id' => $user->user_id,
                    'ip_address' => $request->getClientIp(),
                    'user_log_type' => 'Join Event', // <-- Nilai ENUM baru
                    'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Catat error jika logging gagal, tapi jangan hentikan proses utama
                Log::error('Gagal mencatat log Join Event: ' . $e->getMessage());
            }

            return redirect()->route('events', $event_code)->with('success', 'Successfully joined the event!');
        } catch (\Exception $e) {
            return back()->withErrors('Failed to join the event');
            // return back()->withErrors( 'Failed to join the event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}
