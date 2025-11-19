<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Support\Facades\Auth;
use App\Models\UserLogs;
use Illuminate\Support\Facades\Log;

class VerificationController extends Controller
{
    public function send(Request $request)
    {
        try {
            $request->validate([
            'g-recaptcha-response' => 'required',
            ], [
            'g-recaptcha-response.required' => 'The captcha field is required.'
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->validator)->withInput();
        }

        $request->user()->sendEmailVerificationNotification();
        return back()->with('resent', true);
    }

    public function verify(EmailVerificationRequest $request)
    {
        $request->fulfill();
        $user  = Auth::user();
        $user = User::find($user->user_id);
        if($user->first_login_at == null) {
            $user->status = true;
        }
        $user->first_login_at = now();
        $user->save();

        Auth::login($user);

        try {
            // $user sudah didefinisikan di atas
            if ($user) {
                UserLogs::create([
                    'user_id' => $user->user_id,
                    'ip_address' => $request->getClientIp(),
                    'user_log_type' => 'Account Activation', // <-- Nilai ENUM baru
                    'user_agent' => json_encode($request->header('User-Agent'), JSON_THROW_ON_ERROR),
                    'created_at' => now(),
                ]);
            }
        } catch (\Exception $e) {
            // Catat error jika logging gagal, tapi jangan hentikan proses utama
            Log::error('Gagal mencatat log Account Activation: ' . $e->getMessage());
        }

        return redirect('/dashboard');
    }

    public function show()
    {
        return view('auth.verify-email');
    }
}
