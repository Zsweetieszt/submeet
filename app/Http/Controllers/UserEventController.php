<?php

namespace App\Http\Controllers;

use App\Models\Role;
use App\Models\Topic;
use App\Models\TopicUser;
use App\Models\User;
use App\Models\UserEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Auth;
use App\Models\Event;

class UserEventController extends Controller
{
    public function edit(Request $request, $event_code, $username){
        try {
            session(['previous_url' => url()->previous()]);
            $event = Event::where('event_code', $event_code)->first();
            $event_id = $event->event_id;
            $user = User::where('username', $username)->first();
            $user_detail = $user;
            $user_id = $user->user_id;
            $user = UserEvent::with('user.country', 'role','event')->where('event_id', $event_id)->where('user_id', $user_id)->first();
            $roles = Role::all();
            $userRoles = User::with('user_events.role', 'user_events.event', 'country')->where('user_id', $user_id)->whereRelation('user_events', 'event_id', '=', $event_id)->first();
            $user->roles = $userRoles->user_events->pluck('role.role_id')->toArray();

            if (in_array('Paper Reviewer', $userRoles->user_events->pluck('role.role_name')->toArray())) {
                $user_expertise = $user_detail->expertise_users()->with('expertise')->get()->pluck('expertise.expertise_name')->implode(', ');
                $topics = Topic::where('event_id', '=', $event->event_id)->get();
                $selected_topics = TopicUser::where('user_id', $user_id)->join('topics', 'topic_users.topic_id', '=', 'topics.topic_id')->pluck('topics.topic_name')->implode(', ');
                $user->topics = $selected_topics;
            } else {
                $user_expertise = null;
                $user->topics = null;
                $topics = null;
            }
            return view('pages.usersevents.edit', compact('user', 'roles', 'user_expertise', 'topics'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors( 'Failed to load user event edit page: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function update(Request $request, $event_code, $username){
        try {
            $event = Event::where('event_code', $event_code)->first();
            $event_id = $event->event_id;
            $user = User::where('username', $username)->first();
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

            // Store existing roles for rollback if needed
            $existingRoles = UserEvent::where('event_id', $event_id)->where('user_id', $user_id)->get();

            // Delete existing roles
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
                return redirect(session('previous_url', route('events.show', $event_code)))
                    ->withErrors( 'Failed to update user role : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))
                    ->withInput();
            }

            return redirect(session('previous_url', route('events.show', $event_code)))
                ->with('success', 'User role updated successfully.');

        } catch (\Exception $e) {
            return redirect(session('previous_url', route('events.show', $event_code)))
                ->withErrors( 'Failed to update user role : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()))
                ->withInput();
        }
    }

}
