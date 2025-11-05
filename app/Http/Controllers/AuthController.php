<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\Event;
use App\Models\UserEvent;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function authenticate(Request $request): RedirectResponse
    {
        try {
            $credentials = $request->validate([
                'login' => 'required',
                'password' => 'required',
                'g-recaptcha-response' => 'required',
            ], [
                'login.required' => 'The username or email field is required.',
                'password.required' => 'The password field is required.',
                'g-recaptcha-response.required' => 'The captcha field is required.'
            ]);

            $login_type = filter_var($credentials['login'], FILTER_VALIDATE_EMAIL) ? 'email' : 'username';

            if (Auth::attempt([$login_type => $credentials['login'], 'password' => $credentials['password']])) {
                $request->session()->regenerate();
                
                if ($request->filled('event_code')) {
                    $user = Auth::user();
                    $event = Event::where('event_code', '=', $request->event_code)->first();
                    try {
                        if($event->event_status == 'Finished') {
                            return back()->withErrors('error', 'Event is not ongoing.');
                        }
                        $role = 1;
                        if(\Carbon\Carbon::parse($event->submission_end)->endOfDay() > now()) {
                            $role = 2;
                        }
                        UserEvent::create([
                            'user_id' => $user->user_id,
                            'event_id' => $event->event_id,
                            'role_id' => $role,
                            'is_offline' => 1,
                        ]);
                    } catch (\Exception $e) {
                        return back()->withErrors('error', 'Failed to join the event: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
                    }

                    return redirect()->route('dashboard.event', ['event' => $request->input('event_code')]);
                }

                return redirect()->intended('dashboard');
            }

            return back()->withErrors([
                'login' => 'The provided credentials do not match our records.'
            ])->withInput();

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'An unexpected error occurred. Please contact support.',
            ])->withInput();
        }
    }

    public function login(Request $request)
    {
        try {
            $event_code = $request->query('e');
            return view('auth.login', compact('event_code'));
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'An unexpected error occurred. Please contact support.',
            ]);
        }
    }

    public function register()
    {
        try {
            $countries = Country::orderBy('country_name')->get();
            return view('auth.register', compact('countries'));
        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'An unexpected error occurred. Please contact support.',
            ]);
        }
    }

    public function create_user(Request $request)
    {
        try {
            $request->validate([
                'username' => 'required|string|max:50|min:5|unique:users|regex:/^[a-z0-9_]+$/',
                'email' => 'required|string|email|max:255|unique:users',
                'given_name' => 'required|string|max:125',
                'family_name' => 'required|string|max:125',
                'honorif' => 'required',
                'institution_name' => 'required|string|max:255|min:3',
                'country' => 'required|exists:countries,country_id',
                'phone_1' => 'required|numeric|max_digits:15',
                'phone_2' => 'nullable|numeric|max_digits:15|different:phone_1',
                'phone_1_country_code' => 'required',
                'phone_2_country_code' => 'required_with:phone_2',
                'password' => 'required|string|min:8||max:50',
                'repeat_password' => 'required|string|same:password',
                'g-recaptcha-response' => 'required',
                'phone_1_country_code' => 'required',
                'phone_2_country_code' => 'required_with:phone_2',
            ], [
                'username.required' => 'The username field is required.',
                'username.string' => 'The username must be a string.',
                'username.max' => 'The username may not be greater than 50 characters.',
                'username.min' => 'The username must be at least 5 characters.',
                'username.unique' => 'The username has already been taken.',
                'username.regex' => 'The username may only contain lowercase letters, numbers, and underscores.',
                'email.required' => 'The email field is required.',
                'email.string' => 'The email must be a string.',
                'email.email' => 'The email must be a valid email address.',
                'email.max' => 'The email may not be greater than 255 characters.',
                'email.unique' => 'The email has already been taken.',
                'given_name.required' => 'The given name field is required.',
                'given_name.string' => 'The given name must be a string.',
                'given_name.max' => 'The given name may not be greater than 125 characters.',
                'family_name.required' => 'The family name field is required.',
                'family_name.string' => 'The family name must be a string.',
                'family_name.max' => 'The family name may not be greater than 125 characters.',
                'honorif.required' => 'The honorif field is required.',
                'institution_name.required' => 'The institution name field is required.',
                'institution_name.string' => 'The institution name must be a string.',
                'institution_name.max' => 'The institution name may not be greater than 255 characters.',
                'institution_name.min' => 'The institution name must be at least 3 characters.',
                'country.required' => 'The country field is required.',
                'country.exists' => 'The selected country is invalid.',
                'phone_1.required' => 'The primary phone number is required.',
                'phone_1.numeric' => 'The primary phone number must be numeric.',
                'phone_1.max_digits' => 'The primary phone number maximum length is 15 digits.',
                'phone_1_country_code.required' => 'The primary phone country code is required.',
                'phone_2.numeric' => 'The secondary phone number must be numeric.',
                'phone_2.max_digits' => 'The secondary phone number maximum length is 15 digits.',
                'phone_2.different' => 'The secondary phone number must be different from the primary phone number.',
                'phone_2_country_code.required_with' => 'The secondary phone country code is required.',
                'password.required' => 'The password field is required.',
                'password.string' => 'The password must be a string.',
                'password.min' => 'The password must be at least 8 characters.',
                'password.max' => 'The password may not be greater than 50 characters.',
                'repeat_password.required' => 'The repeat password field is required.',
                'repeat_password.string' => 'The repeat password must be a string.',
                'repeat_password.same' => 'The repeat password and password must match.',
                'g-recaptcha-response.required' => 'The captcha field is required.',
                'phone_1_country_code.required' => 'The primary phone country code is required.',
                'phone_2_country_code.required_with' => 'The secondary phone country code is required.',
            ]);

            $user = User::create([
                'username' => $request->username,
                'email' => strtolower($request->email),
                'given_name' => $request->given_name,
                'family_name' => $request->family_name,
                'honorif' => $request->honorif,
                'institution_name' => $request->institution_name,
                'country_id' => $request->country,
                'ct_phone_number_1' => $request->phone_1_country_code,
                'phone_number_1' => $request->phone_1,
                'ct_phone_number_2' => $request->phone_2_country_code,
                'phone_number_2' => $request->phone_2,
                'password' => Hash::make($request->password, [
                    'memory' => 1024,
                    'time' => 2,
                    'threads' => 2,
                ]),
            ]);

            Auth::login($user);

            event(new Registered($user));

            response()->json([
                'code' => 201,
                'message' => 'User created successfully',
                'data' => $user,
            ]);

            return back()->with('success', 'Your account created successfully, please email for activation.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Illuminate\Database\QueryException $e) {
            $user->delete();
            return back()->withErrors([
                'error' => "Failed to registering : " . str_replace(["\r", "\n"], ' ', $e->getMessage()),
            ])->withInput();

        } catch (\Exception $e) {
            $user->delete();

            return back()->withErrors([
                'error' => "Failed to registering : " . str_replace(["\r", "\n"], ' ', $e->getMessage()),
                'captcha' => 'The captcha is invalid.',
            ])->withInput();
        }
    }


    public function logout(Request $request): RedirectResponse
    {
        try {
            $user = Auth::user();
            if ($user) {
                $user = User::find($user->user_id);
                $user->last_login_at = now();
                $user->save();
            }
        
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();
        
            return redirect('/login');
        } catch (\Exception $e) {
            return redirect('/login')->with('message', 'An unexpected error occurred during logout.');
        }
    }
}
