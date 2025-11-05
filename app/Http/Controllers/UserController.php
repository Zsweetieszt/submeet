<?php

namespace App\Http\Controllers;

use App\Models\Country;
use App\Models\User;
use App\Models\UserEvent;
use Hash;
use Illuminate\Http\Request;
use App\Models\Expertise;
use App\Models\ExpertiseUser;

class UserController extends Controller
{
    public function index()
    {
        try {
            $users = User::with('country')->get();
            return view('pages.users.index', compact('users'));
        } catch (\Exception $e) {
            return back()->withErrors( 'Failed to load users: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function show($username)
    {
        try {
            $user = User::where('username', $username)->with('country')->firstOrFail();
            $members = UserEvent::with(['event.country', 'role'])
                ->where('user_id', '=', $user->user_id)
                ->get();
            return view('pages.users.show', compact('user', 'members'));
        } catch (\Exception $e) {
            return back()->withErrors( 'Failed to load user details: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function create()
    {
        try {
            $countries = Country::orderBy('country_name')->get();
            return view('pages.users.create', compact('countries'));
        } catch (\Exception $e) {
            return back()->withErrors( 'Failed to load create user form: ' . str_replace(["\r", "\n"], ' ', str_replace(["\r", "\n"], ' ', $e->getMessage())));
        }
    }

    public function edit($username)
    {
        try {
            $user = User::where('username', $username)->with('country')->firstOrFail();
            $countries = Country::orderBy('country_name')->get();
            return view('pages.users.edit', compact('user', 'countries'));
        } catch (\Exception $e) {
            return back()->withErrors( 'Failed to load edit user form: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function destroy($username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
            $user->delete();
            return redirect()->route('users.index')->with('success', 'User deleted successfully.');
        } catch (\Exception $e) {
            if ($e->getCode() === '23503') {
                return back()->withErrors( 'Failed to delete user: This user is associated with other records.');
            }
            return back()->withErrors( 'Failed to delete user');
        }
    }

    public function change_status($username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
            $user->update(['status' => !$user->status]);

            return redirect()->route('users.index')->with('success', 'User status changed successfully.');
        } catch (\Exception $e) {
            return back()->withErrors('error', 'Failed to change user status: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function store(Request $request)
    {
        try {
            $request->validate(
                [
                    'username' => 'required|string|max:15|min:5|unique:users|regex:/^[a-z0-9_]+$/',
                    'email' => 'required|string|email|max:255|unique:users',
                    'given_name' => 'required|string|max:125',
                    'family_name' => 'required|string|max:125',
                    'honorif' => 'required',
                    'institution_name' => 'required|string|max:255|min:3',
                    'country' => 'required|exists:countries,country_id',
                    'phone_1' => 'required|numeric|max_digits:15',
                    'phone_2' => 'nullable|numeric|max_digits:15|different:phone_1',
                    'password' => 'required|string|min:8||max:50',
                    'repeat_password' => 'required|string|same:password',
                    'phone_1_country_code' => 'required',
                    'phone_2_country_code' => 'required_with:phone_2',
                ],
                [
                    'username.required' => 'The username field is required.',
                    'username.string' => 'The username must be a string.',
                    'username.max' => 'The username may not be greater than 15 characters.',
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
                    'phone_2_country_code.required_with' => 'The secondary phone country code is required.',
                    'phone_2.numeric' => 'The secondary phone number must be numeric.',
                    'phone_2.max_digits' => 'The secondary phone number maximum length is 15 digits.',
                    'phone_2.different' => 'The secondary phone number must be different from the primary phone number.',
                    'password.required' => 'The password field is required.',
                    'password.string' => 'The password must be a string.',
                    'password.min' => 'The password must be at least 8 characters.',
                    'password.max' => 'The password may not be greater than 50 characters.',
                    'repeat_password.required' => 'The repeat password field is required.',
                    'repeat_password.string' => 'The repeat password must be a string.',
                    'repeat_password.same' => 'The repeat password and password must match.',
                    // 'g-recaptcha-response.required' => 'The captcha field is required.'
                ],
            );

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
                'status' => true,
                'activated_at' => now(),
            ]);

            return redirect()->route('users.index')->with('success', 'User created successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()
                ->withErrors(['error', 'Failed to create user: ' . str_replace(["\r", "\n"], ' ', $e->getMessage())])
                ->withInput();
        }
    }

    public function update(Request $request, $username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();
            $request->validate(
                [
                    // 'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
                    'given_name' => 'required|string|max:125',
                    'family_name' => 'required|string|max:125',
                    'honorif' => 'required',
                    'institution_name' => 'required|string|max:255|min:3',
                    'country' => 'required|exists:countries,country_id',
                    'phone_1' => 'required|numeric|max_digits:15',
                    'phone_2' => 'nullable|numeric|max_digits:15|different:phone_1',
                    'phone_1_country_code' => 'required',
                    'phone_2_country_code' => 'required_with:phone_2',
                ],
                [
                    // 'email.required' => 'The email field is required.',
                    // 'email.string' => 'The email must be a string.',
                    // 'email.email' => 'The email must be a valid email address.',
                    // 'email.max' => 'The email may not be greater than 255 characters.',
                    // 'email.unique' => 'The email has already been taken.',
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
                    'phone_2.numeric' => 'The secondary phone number must be numeric.',
                    'phone_2.max_digits' => 'The secondary phone number maximum length is 15 digits.',
                    'phone_2.different' => 'The secondary phone number must be different from the primary phone number.',
                    'phone_1_country_code.required' => 'The primary phone country code is required.',
                    'phone_2_country_code.required_with' => 'The secondary phone country code is required.',
                ],
            );

            $user->update([
                'given_name' => $request->given_name,
                'family_name' => $request->family_name,
                'honorif' => $request->honorif,
                'institution_name' => $request->institution_name,
                'country_id' => $request->country,
                'ct_phone_number_1' => $request->phone_1_country_code,
                'phone_number_1' => $request->phone_1,
                'ct_phone_number_2' => $request->phone_2_country_code,
                'phone_number_2' => $request->phone_2,
            ]);

            return redirect()->route('users.index')->with('success', 'User updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('error', 'Failed to update user: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    public function update_profile(Request $request, $username)
    {
        try {
            $user = User::where('username', $username)->firstOrFail();

            if ($request->has('expertise')) {
                $dataExpertise = json_decode($request->input('expertise'), true);
                $expertises = $dataExpertise;

                $request->validate([
                    'expertise' => [
                        'required',
                        function ($attribute, $value, $fail) use ($expertises) {
                            if (!is_array($expertises)) {
                                return $fail('The expertise field must be a valid array.');
                            }

                            foreach ($expertises as $expertise) {
                                if (!isset($expertise['value']) || !is_string($expertise['value']) || trim($expertise['value']) === '' || trim($expertise['value']) === '.') {
                                    return $fail('Each expertise must have a non-empty "value" field.');
                                }
                            }
                        },
                    ],
                ]);

                if ($request->expertise) {
                    ExpertiseUser::where('user_id', $user->user_id)->delete();
                    foreach ($expertises as $expertise) {
                        if (isset($expertise['value']) && trim($expertise['value']) !== '') {
                            $existingExpertise = Expertise::where('expertise_name', trim($expertise['value']))->first();
                            // dd($expertise);

                            $newExpertise = null;
                            if (!$existingExpertise) {
                                // dd($expertise['value']);
                                $newExpertise = Expertise::create([
                                    'expertise_name' => trim($expertise['value']),
                                ]);
                            }

                            ExpertiseUser::create([
                                'expertise_id' => $newExpertise->expertise_id ?? $existingExpertise->expertise_id,
                                'user_id' => $user->user_id,
                            ]);
                        }
                    }
                }
            }

            $request->validate(
                [
                    // 'email' => 'required|string|email|max:255|unique:users,email,' . $user->user_id . ',user_id',
                    'given_name' => 'required|string|max:125',
                    'family_name' => 'required|string|max:125',
                    'honorif' => 'required',
                    'institution_name' => 'required|string|max:255|min:3',
                    'country' => 'required|exists:countries,country_id',
                    'phone_1' => 'required|numeric|max_digits:15',
                    'phone_2' => 'nullable|numeric|max_digits:15|different:phone_1',
                    'phone_1_country_code' => 'required',
                    'phone_2_country_code' => 'required_with:phone_2',
                ],
                [
                    // 'email.required' => 'The email field is required.',
                    // 'email.string' => 'The email must be a string.',
                    // 'email.email' => 'The email must be a valid email address.',
                    // 'email.max' => 'The email may not be greater than 255 characters.',
                    // 'email.unique' => 'The email has already been taken.',
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
                    'phone_2.numeric' => 'The secondary phone number must be numeric.',
                    'phone_2.max_digits' => 'The secondary phone number maximum length is 15 digits.',
                    'phone_2.different' => 'The secondary phone number must be different from the primary phone number.',
                    'phone_1_country_code.required' => 'The primary phone country code is required.',
                    'phone_2_country_code.required_with' => 'The secondary phone country code is required.',
                ],
            );

            $user->update([
                'given_name' => $request->given_name,
                'family_name' => $request->family_name,
                'honorif' => $request->honorif,
                'institution_name' => $request->institution_name,
                'country_id' => $request->country,
                'ct_phone_number_1' => $request->phone_1_country_code,
                'phone_number_1' => $request->phone_1,
                'ct_phone_number_2' => $request->phone_2_country_code,
                'phone_number_2' => $request->phone_2,
            ]);

            return back()->with('success', 'Profile updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            // Return validation errors
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            return back()->withErrors('error', 'Failed to update profile: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }

    function edit_profile(Request $request)
    {
        try {
            $user = auth()->user();
            $countries = Country::orderBy('country_name')->get();

            if ($request->event) {
                $event = $request->event;
                $expertises = Expertise::all();
                $user_expertise = $user->expertise_users()->with('expertise')->get()->pluck('expertise.expertise_name')->implode(', ');
            } else {
                $event = null;
                $expertises = null;
                $user_expertise = null;
            }

            return view('pages.users.profile', compact('user', 'countries', 'event', 'expertises', 'user_expertise'));
        } catch (\Exception $e) {
            return back()->withErrors( 'Failed to load profile: ' . str_replace(["\r", "\n"], ' ', $e->getMessage()));
        }
    }
}
