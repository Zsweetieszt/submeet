<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Paper;
use App\Models\UserEvent;
use Auth;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        try {
            $events = Event::all();
            return view('dashboard.index', compact('events'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load events.');
        }
    }

    public function event(Request $request, $event)
    {
        try {
            $event_specific = Event::where('event_code', $event)->firstOrFail();
            return view('dashboard.event', compact('event_specific'));
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return redirect()->route('events')->withErrors('Event not found.');
        }
    }

    public function change_role(Request $request)
    {
        try {
            $request->validate([
                'role' => 'required|in:1,2',
                'is_offline' => 'required|boolean',
            ]);

            $event_id = $request->input('event_id');
            $event = Event::findOrFail($event_id);
            $user = Auth::user();
            $user_event = UserEvent::where('event_id', $event->event_id)
                ->where('user_id', $user->user_id)
                ->where('role_id', $request->input('old_role'))
                ->first();
            // dd($user_event);
            if (!$user_event) {
                return redirect()->back()->withErrors('User event not found for the specified role.');
            }
            $paper = Paper::where('event_id', $event->event_id)
                ->where('user_id', $user->user_id)
                ->first();
            if ($paper && $request->input('role') != $user_event->role_id) {
                return redirect()->back()->withErrors('You cannot change role for an event with an existing paper submission. You can change attendance status only.');
            }

            UserEvent::where('user_id', $user_event->user_id)
                ->where('event_id', $user_event->event_id)
                ->where('role_id', $request->input('old_role'))
                ->update([
                    'role_id' => (int) $request->input('role'),
                    'is_offline' => (bool) $request->input('is_offline'),
                ]);

            return redirect()->route('events')
                ->with('success', 'Role changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to change role: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}
