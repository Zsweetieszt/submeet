<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Paper;
use App\Models\Author;
use App\Models\CameraReadyPaper;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLogs;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class CameraReadyController extends Controller
{
    public function index($event)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return redirect()->back()->withErrors('Event not found.');
            }
            $event_name = $event->event_name;
            $event_id = $event->event_id;
            $firstPaperIds = Paper::where('event_id', $event_id)
                ->where('status', 'Accepted')
                ->pluck('paper_sub_id');
            $papers = Paper::with(['cameraReady'])
                ->whereIn('first_paper_sub_id', $firstPaperIds)
                ->where('user_id', auth()->user()->user_id)
                ->where('event_id', $event_id)
                ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY first_paper_sub_id ORDER BY round DESC) as rn')
                ->get()
                ->filter(function ($paper) {
                    return $paper->rn == 1;
                })
                ->values();
            return view('pages.camera_ready.index', compact('papers', 'event_name'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load camera ready papers: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function page_upload($event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->first();
            if (!$event) {
                return redirect()->back()->withErrors('Event not found.');
            }

            $papers = Paper::where('paper_sub_id', $paper_id)
                ->with(['cameraReady'])
                ->first();

            if (!$papers) {
                return redirect()->back()->withErrors('Paper not found.');
            }

            $camera_ready = CameraReadyPaper::where('first_paper_sub_id', $paper_id)
                ->where('event_id', $event->event_id)
                ->first();

            return view('pages.camera_ready.upload', compact('papers', 'event', 'camera_ready'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to load upload page: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
                'first_paper_sub_id' => 'required',
                'event_id' => 'required',
                'user_id' => 'required',
                'copyright_tf_file' => 'mimes:pdf|max:5120',
                'cr_paper_file' => 'mimes:doc,docx|max:5120',
            ]);

            if ($validator->fails()) {
                return redirect()->back()->withErrors($validator);
            }
            $event = Event::where('event_id', $request->event_id)->first();
            if (!$event) {
                return redirect()->back()->withErrors('Event not found.');
            }
            $camera_end = \Carbon\Carbon::parse($event->camera_ready_end)->endOfDay();
            if (now()->greaterThan($camera_end)) {
                return redirect()->back()->withErrors('Camera Ready submission period has ended.');
            }
            $camera_start = \Carbon\Carbon::parse($event->camera_ready_start);
            if (now()->lessThan($camera_start)) {
                return redirect()->back()->withErrors('Camera Ready submission period has not started yet.');
            }
            // dd($request->all());
            $copyright = CameraReadyPaper::where('first_paper_sub_id', $request->first_paper_sub_id)
                ->where('event_id', $request->event_id)
                ->first();

            // Versioning
            $copyrightFileVersion = 1;
            $cameraReadyFileVersion = 1;
            if ($copyright) {
                if ($copyright->copyright_trf_file) {
                    preg_match('/_(\d+)\.(pdf|PDF|docx|doc|DOCX|DOC)$/', $copyright->copyright_trf_file, $matches);
                    $copyrightFileVersion = isset($matches[1]) ? $matches[1] + 1 : 1;
                }
                if ($copyright->cr_paper_file) {
                    preg_match('/_(\d+)\.(pdf|PDF|docx|doc|DOCX|DOC)$/', $copyright->cr_paper_file, $matches);
                    $cameraReadyFileVersion = isset($matches[1]) ? $matches[1] + 1 : 1;
                }
            }

            // Handle file upload
            $filename1 = null;
            $filename2 = null;
            if ($request->hasFile('copyright_tf_file')) {
                $extension1 = $request->file('copyright_tf_file')->getClientOriginalExtension();
                $filename1 = "{$request->event_id}_{$request->first_paper_sub_id}_ct_{$copyrightFileVersion}.{$extension1}";
                $request->file('copyright_tf_file')->storeAs(
                    "copyright/{$request->event_id}/{$request->first_paper_sub_id}",
                    $filename1,
                    'public'
                );
            }
            if ($request->hasFile('cr_paper_file')) {
                $extension2 = $request->file('cr_paper_file')->getClientOriginalExtension();
                $filename2 = "{$request->event_id}_{$request->first_paper_sub_id}_cr_{$cameraReadyFileVersion}.{$extension2}";
                $request->file('cr_paper_file')->storeAs(
                    "paper/{$request->event_id}/{$request->first_paper_sub_id}",
                    $filename2,
                    'public'
                );
            }

            // Update or create CameraReadyPaper
            if ($copyright) {
                $updateData = ['updated_by' => auth()->user()->user_id];
                if ($filename1) $updateData['copyright_trf_file'] = $filename1;
                if ($filename2) $updateData['cr_paper_file'] = $filename2;
                $copyright->update($updateData);
            } else {
                CameraReadyPaper::create([
                    'camera_ready_id' => null,
                    'cr_paper_file' => $filename2,
                    'copyright_trf_file' => $filename1,
                    'first_paper_sub_id' => $request->first_paper_sub_id,
                    'event_id' => $request->event_id,
                    'created_by' => auth()->user()->user_id,
                    'updated_by' => auth()->user()->user_id,
                ]);
            }
            try {
                $user = Auth::user(); // Mendapatkan pengguna yang sedang login
                if ($user) {
                    UserLogs::create([
                        'user_id' => $user->user_id,
                        'ip_address' => $request->getClientIp(),
                        'user_log_type' => 'Submit Camera-ready', // <-- Nilai ENUM
                        'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Catat error jika logging gagal, tapi jangan hentikan proses utama
                Log::error('Gagal mencatat log Submit Camera-ready: ' . $e->getMessage());
            }

            return redirect()->route('index.camera-ready', $event->event_code)->with('success', 'Camera Ready Paper uploaded successfully.');
        } catch (\Exception $e) {
            // dd($e);
            return redirect()->back()->withErrors('Upload failed: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}
