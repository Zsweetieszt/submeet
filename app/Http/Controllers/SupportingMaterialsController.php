<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Event;
use App\Models\Paper;
use App\Models\Author;
use App\Models\SupportingMaterial;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use App\Models\UserLogs;             
use Illuminate\Support\Facades\Auth; 
use Illuminate\Support\Facades\Log;  


class SupportingMaterialsController extends Controller
{
    public function index($event)
    {
        try {
            $event = Event::where('event_code', $event)->firstOrFail();
            $event_name = $event->event_name;
            $event_id = $event->event_id;
            $firstPaperIds = Paper::where('event_id', $event_id)
                ->where('status', 'Accepted')
                ->pluck('paper_sub_id');
            $papers = Paper::with(['supportingMaterials'])
                ->whereIn('first_paper_sub_id', $firstPaperIds)
                ->where('user_id', auth()->user()->user_id)
                ->where('event_id', $event_id)
                ->selectRaw('*, ROW_NUMBER() OVER (PARTITION BY first_paper_sub_id ORDER BY round DESC) as rn')
                ->get()
                ->filter(function ($paper) {
                    return $paper->rn == 1;
                })
                ->values();

            return view('pages.supporting_materials.index', compact('papers', 'event_name'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Action Failed : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function page_upload($event, $paper_id)
    {
        try {
            $event = Event::where('event_code', $event)->firstOrFail();

            $papers = Paper::with(['event'])
                ->where('user_id', auth()->user()->user_id)
                ->where('event_id', $event->event_id)
                ->where('paper_sub_id', $paper_id)
                ->first();

            $supporting = SupportingMaterial::where('first_paper_sub_id', $paper_id)
                ->where('event_id', $event->event_id)
                ->first();

            return view('pages.supporting_materials.upload', compact('papers', 'event', 'supporting'));
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Action failed : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function upload(Request $request)
    {
        try {
            $validator = Validator::make($request->all(), [
            'first_paper_sub_id' => 'required',
            'event_id' => 'required',
            'user_id' => 'required',
            'presentation_tf_file' => 'mimes:pdf,ppt,pptx|max:10240',
            'video_link' => 'nullable|url',
            ]);

            if ($validator->fails()) {
            return redirect()->back()
                ->withErrors($validator)
                ->withInput();
            }

            $supporting = SupportingMaterial::where('first_paper_sub_id', $request->first_paper_sub_id)
            ->where('event_id', $request->event_id)
            ->first();

            if ($supporting != null && $supporting->slide_file != null) {
            preg_match('/_(\d+)\.(pdf|pptx?|PDF|PPTX?)$/', $supporting->slide_file, $matches);
            $slideFileVersion = isset($matches[1]) ? $matches[1] + 1 : null;
            } else {
            $slideFileVersion = 1;
            }

            if ($supporting != null && $supporting->poster_file != null) {
            preg_match('/_(\d+)\.pdf$/', $supporting->poster_file, $matches);
            $posterFileVersion = isset($matches[1]) ? $matches[1] + 1 : null;
            } else {
            $posterFileVersion = 1;
            }

            if ($request->hasFile('presentation_tf_file')) {
            $presentationFile = $request->file('presentation_tf_file');
            $extension = $presentationFile->getClientOriginalExtension();
            $filename1 = "{$request->event_id}_{$request->first_paper_sub_id}_sl_{$slideFileVersion}.{$extension}";
            $presentationFile->storeAs(
                "slide/{$request->event_id}/{$request->first_paper_sub_id}",
                $filename1,
                'public'
            );
            } else {
            $filename1 = null;
            }

            $videoLink = $request->input('video_link');

            if ($supporting) {
            $updateData = [
                'updated_by' => auth()->user()->user_id,
            ];

            if ($filename1 !== null) {
                $updateData['slide_file'] = $filename1;
            }

            if ($videoLink !== null) {
                $updateData['video_url'] = $videoLink;
            }

            $supporting->update($updateData);
            } else {
            SupportingMaterial::create([
                'supp_material_id' => null,
                'slide_file' => $filename1,
                'video_url' => $videoLink,
                'first_paper_sub_id' => $request->first_paper_sub_id,
                'event_id' => $request->event_id,
                'created_by' => auth()->user()->user_id,
                'updated_by' => auth()->user()->user_id,
            ])->save();
            }

            $event = Event::where('event_id', $request->event_id)->first();

            $papers = Paper::with(['event'])
            ->where('user_id', auth()->user()->user_id)
            ->where('event_id', $request->event_id)
            ->where('paper_sub_id', $request->first_paper_sub_id)
            ->first();

            try {
                $user = Auth::user(); // Mendapatkan pengguna yang sedang login
                if ($user) {
                    UserLogs::create([
                        'user_id' => $user->user_id,
                        'ip_address' => $request->getClientIp(),
                        'user_log_type' => 'Submit Supporting Materials', // <-- Nilai ENUM baru
                        'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                        'created_at' => now(),
                    ]);
                }
            } catch (\Exception $e) {
                // Catat error jika logging gagal, tapi jangan hentikan proses utama
                Log::error('Gagal mencatat log Submit Supporting Materials: ' . $e->getMessage());
            }

            return redirect()->route('index.supporting-materials', $event->event_code)
            ->with('success', 'Suppporting materials uploaded successfully.');
        } catch (\Exception $e) {
            return redirect()->back()->withErrors('Failed to Submit Supporting Materialss : ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}
