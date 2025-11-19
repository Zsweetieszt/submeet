<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\Paper;
use App\Models\UserEvent;
use Auth;
use Illuminate\Http\Request;
use App\Models\UserLogs; // Pastikan UserLogs di-import

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
            
            // ==============================================================
            // START LOGGING: Merekam aktivitas "View Event"
            // ==============================================================
            $user = Auth::user();
            
            // Pastikan user sedang login
            if ($user) {
                UserLogs::create([
                    'user_id' => $user->user_id,
                    'ip_address' => $request->getClientIp(),
                    // Tipe log: View Event
                    'user_log_type' => 'View Event',
                    // Menyimpan detail event yang diakses dan raw user agent (dalam format JSON)
                    'user_agent' => json_encode(
                        [
                            'user_agent_raw' => $request->header('User-Agent'),
                            'event_info' => [
                                'event_id' => $event_specific->event_id ?? null,
                                'event_name' => $event_specific->name ?? 'N/A',
                                'event_code' => $event_specific->event_code
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
                'old_role' => 'required|in:1,2' // Pastikan old_role juga divalidasi/ada
            ]);

            $event_id = $request->input('event_id');
            $event = Event::findOrFail($event_id);
            $user = Auth::user();
            
            // Mengambil dan mendeklarasikan variabel yang diperlukan untuk logging
            $old_role_id = (int) $request->input('old_role');
            $new_role_id = (int) $request->input('role');
            $is_offline_status = (bool) $request->input('is_offline');
            
            $user_event = UserEvent::where('event_id', $event->event_id)
                ->where('user_id', $user->user_id)
                ->where('role_id', $old_role_id) // Menggunakan variabel yang sudah dideklarasikan
                ->first();
            
            if (!$user_event) {
                return redirect()->back()->withErrors('User event not found for the specified role.');
            }
            
            $paper = Paper::where('event_id', $event->event_id)
                ->where('user_id', $user->user_id)
                ->first();
                
            if ($paper && $new_role_id != $user_event->role_id) { // Menggunakan variabel yang sudah dideklarasikan
                return redirect()->back()->withErrors('You cannot change role for an event with an existing paper submission. You can change attendance status only.');
            }

            // Lakukan update data
            UserEvent::where('user_id', $user_event->user_id)
                ->where('event_id', $user_event->event_id)
                ->where('role_id', $old_role_id)
                ->update([
                    'role_id' => $new_role_id,
                    'is_offline' => $is_offline_status,
                ]);

            // ==============================================================
            // START LOGGING: Merekam aktivitas "Change Role"
            // ==============================================================
            // Variabel $old_role_id, $new_role_id, dan $is_offline_status sudah terdefinisi di atas.
            $log_data = [
                'user_id' => $user->user_id,
                'ip_address' => $request->getClientIp(),
                'user_log_type' => 'Change Role', // Tipe log baru
                'user_agent' => json_encode(
                    [
                        'user_agent_raw' => $request->header('User-Agent'),
                        'event_info' => [
                            'event_id' => $event->event_id ?? null,
                            'event_name' => $event->name ?? 'N/A',
                            'event_code' => $event->event_code ?? 'N/A',
                        ],
                        'role_change' => [
                            'from_role_id' => $old_role_id,
                            'to_role_id' => $new_role_id,
                            'is_offline_status' => $is_offline_status ? 'Offline' : 'Online',
                        ]
                    ], 
                    JSON_THROW_ON_ERROR
                ),
                'created_at' => now(),
            ];
            
            UserLogs::create($log_data);
            // ==============================================================
            // END LOGGING
            // ==============================================================

            return redirect()->route('events')
                ->with('success', 'Role changed successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to change role: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}