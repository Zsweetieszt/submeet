<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use App\Models\UserLogs;

class ForgotPasswordController extends Controller
{
    public function forgot_password()
    {
        return view('auth.forgot-password');
    }

    public function send_reset_link(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'g-recaptcha-response' => 'required',
        ],[
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'g-recaptcha-response.required' => 'The captcha field is required.'
        ]);
        
        $user = User::whereRaw('LOWER(email) = ?', [strtolower($request->email)])->first();

        if (!$user) {
            return back()->withErrors(['email' => 'There is no user registered with that email address.']);
        }

        $status = Password::sendResetLink([
            'email' => $user->email
        ]);

        // ==========================================================
        // >>> LOGIKA LOGGING BARU UNTUK FORGOT PASSWORD REQUEST 05/11/2025 <<<
        // ==========================================================
        if ($status === Password::RESET_LINK_SENT) {
            try {
                UserLogs::create([
                    'user_id' => $user->user_id,
                    'ip_address' => $request->getClientIp(),
                    'user_log_type' => 'Forgot Password Request',
                    'user_agent' => json_encode($request->header('User-Agent')), 
                    'created_at' => now(),
                ]);
            } catch (\Exception $e) {
                // Log the database error without disrupting the user flow
                \Log::error("Failed to log Forgot Password event for user ID {$user->user_id}: " . $e->getMessage());
            }
        }
        // ==========================================================
        // >>> AKHIR LOGIKA LOGGING BARU <<<
        // ==========================================================

        return $status === Password::RESET_LINK_SENT
            ? back()->with(['status' => __($status)])
            : back()->withErrors([
                'email' => __($status),
                'captcha' => 'The captcha is invalid.'
            ]);
    }

    public function reset_password($token)
    {
        return view('auth.reset-password', ['token' => $token]);
    }

    public function update_password(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
            'g-recaptcha-response' => 'required',
        ],[
            'token.required' => 'The token field is required.',
            'email.required' => 'The email field is required.',
            'email.email' => 'The email must be a valid email address.',
            'password.required' => 'The password field is required.',
            'password.min' => 'The password must be at least 8 characters.',
            'password.confirmed' => 'The password confirmation does not match.',
            'g-recaptcha-response.required' => 'The captcha field is required.'
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function (User $user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password, [
                        'memory' => 1024,
                        'time' => 2,
                        'threads' => 2,
                        'rounds' => 10,
                    ])
                ])->setRememberToken(Str::random(60));

                $user->save();
                
                // ==========================================================
                // >>> LOGIKA LOGGING BARU UNTUK PASSWORD RESET SUKSES 05/11/2025 <<<
                // ==========================================================
                try {
                    UserLogs::create([
                        'user_id' => $user->user_id,
                        'ip_address' => request()->getClientIp(),
                        'user_log_type' => 'Password Reset Success', 
                        'user_agent' => json_encode(request()->header('User-Agent')),
                        'created_at' => now(),
                    ]);
                } catch (\Exception $e) {
                    \Log::error("Failed to log Password Reset event for user ID {$user->user_id}: " . $e->getMessage());
                }
                // ==========================================================
                // >>> AKHIR LOGIKA LOGGING BARU <<<
                // ==========================================================
                
                event(new PasswordReset($user));
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status), 'captcha' => 'The captcha is invalid.']]);
    }
}