<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

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

        return $status === Password::ResetLinkSent
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
                    ])
                ])->setRememberToken(Str::random(60));

                $user->save();

                event(new PasswordReset($user));
            }
        );

        return $status === Password::PasswordReset
            ? redirect()->route('login')->with('status', __($status))
            : back()->withErrors(['email' => [__($status), 'captcha' => 'The captcha is invalid.']]);
    }
}
