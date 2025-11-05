@extends('layouts.auth')

@section('title', 'Register')

@section('content')
    <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
        <!-- @if ($errors->has('error'))
            <div class="toast-container top-0 end-0 p-3">
                <div class="toast align-items-center fade show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ $errors->first('error') }}
                        </div>
                        <button type="button" class="btn-close me-2 m-auto" data-coreui-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif -->
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="d-flex justify-content-center mb-4 mt-5">
                        <img class="login-brand-full" width="384" height="128"
                            src="{{ asset('assets/brand/Logo-SubMeet.png') }}" alt="SubMeet">
                    </div>
                    <div class="card my-4 w-100">
                        <div class="card-body p-4">
                            <h1>Register</h1>
                            <p class="text-body-secondary">Create your account</p>
                            <form class="needs-validation" novalidate action="{{ route('create_user') }}" method="post">
                                @csrf
                                <fieldset class="a">
                                    <legend class="fs-6 a">Account</legend>
                                    {{-- Email Field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-envelope-open"></i>
                                        </span>
                                        <input class="form-control @error('email') is-invalid @enderror" name="email"
                                            type="text" placeholder="Email" maxlength="255" value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- Username Field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-user"></i>
                                        </span>
                                        <input class="form-control @error('username') is-invalid @enderror" name="username"
                                            type="text" maxlength="15" placeholder="Username" value="{{ old('username') }}">
                                        @error('username')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- Password field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-lock-locked"></i>
                                        </span>
                                        <input id="password" class="form-control @error('password') is-invalid @enderror" type="password"
                                            name="password" maxlength="128" placeholder="Password">
                                        <span class="input-group-text" id="toggle-password" style="cursor: pointer;">
                                            <i class="fa-regular fa-eye"></i>
                                        </span>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                const passwordInput = document.getElementById('password');
                                                const toggle = document.getElementById('toggle-password');
                                                toggle.addEventListener('mousedown', function () {
                                                    passwordInput.type = 'text';
                                                });
                                                toggle.addEventListener('mouseup', function () {
                                                    passwordInput.type = 'password';
                                                });
                                                toggle.addEventListener('mouseleave', function () {
                                                    passwordInput.type = 'password';
                                                });
                                            });
                                        </script>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- Repeat Password field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-lock-locked"></i>
                                        </span>
                                        <input class="form-control @error('repeat_password') is-invalid @enderror"
                                            type="password" maxlength="128" name="repeat_password" placeholder="Repeat password">
                                        <span class="input-group-text" id="toggle-repeat-password" style="cursor: pointer;">
                                            <i class="fa-regular fa-eye"></i>
                                        </span>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                const repeatPasswordInput = document.querySelector('input[name="repeat_password"]');
                                                const toggleRepeat = document.getElementById('toggle-repeat-password');
                                                toggleRepeat.addEventListener('mousedown', function () {
                                                    repeatPasswordInput.type = 'text';
                                                });
                                                toggleRepeat.addEventListener('mouseup', function () {
                                                    repeatPasswordInput.type = 'password';
                                                });
                                                toggleRepeat.addEventListener('mouseleave', function () {
                                                    repeatPasswordInput.type = 'password';
                                                });
                                            });
                                        </script>
                                        @error('repeat_password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend class="fs-6">Identity</legend>
                                    {{-- Given Name field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-user"></i>
                                        </span>
                                        <input class="form-control @error('given_name') is-invalid @enderror" type="text"
                                            name="given_name" maxlength="100" placeholder="Given Name" value="{{ old('given_name') }}">
                                        @error('given_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- Family Name field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-user"></i>
                                        </span>
                                        <input class="form-control @error('family_name') is-invalid @enderror"
                                            type="text" maxlength="100" name="family_name" placeholder="Family Name"
                                            value="{{ old('family_name') }}">
                                        @error('family_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- honorif field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-user"></i>
                                        </span>
                                        <select class="form-control form-select @error('honorif') is-invalid @enderror"
                                            name="honorif">
                                            <option value="" disabled selected>Honorific</option>
                                            <option value="Mr." {{ old('honorif') == "Mr."? 'selected' : '' }}>Mr.</option>
                                            <option value="Mrs." {{ old('honorif') == "Mrs."? 'selected' : '' }}>Mrs.</option>
                                            <option value="Ms." {{ old('honorif') == "Ms."? 'selected' : '' }}>Ms.</option>
                                            <option value="Miss" {{ old('honorif') == "Miss"? 'selected' : '' }}>Miss</option>
                                        </select>
                                        @error('honorif')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                                <fieldset>
                                    <legend class="fs-6">Institution</legend>
                                    {{-- Institution Name field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-institution"></i>
                                        </span>
                                        <input class="form-control @error('institution_name') is-invalid @enderror"
                                            type="text" maxlength="255" name="institution_name" placeholder="Institution Name"
                                            value="{{ old('institution_name') }}">
                                        @error('institution_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    {{-- Country field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-globe-alt"></i>
                                        </span>
                                        <select class="form-control form-select @error('country') is-invalid @enderror" name="country"
                                            value="{{ old('country') }}">
                                            <option value="" disabled selected>Country</option>
                                            @foreach ($countries as $country)
                                                <option value="{{ $country->country_id }}"
                                                    {{ old('country') == $country->country_id ? 'selected' : '' }}>
                                                    {{ $country->country_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('country')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                                <fieldset class="mb-4">
                                    <legend class="fs-6">Contact</legend>

                                    {{-- Phone Number 1 field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-phone"></i>
                                        </span>
                                        <select class="form-select" name="phone_1_country_code" id="phone_1_country_code"
                                            style="max-width: 110px;">
                                            <option value="" selected disabled>Select Country Code</option>
                                            @foreach ($countries as $country)
                                                <option
                                                    data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                    value="{{ $country->phonecode }}"
                                                    {{ old('phone_1_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                    {{ $country->country_name . ' ' . '+' . $country->phonecode }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input class="form-control @error('phone_1') is-invalid @enderror" name="phone_1"
                                            id="phone_1" type="text" maxlength="15" placeholder="Phone Number 1"
                                            value="{{ old('phone_1') }}">
                                        @error('phone_1')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('phone_1_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('phone_1_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Phone Number 2 field --}}
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-phone"></i>
                                        </span>
                                        <select class="form-select" name="phone_2_country_code" id="phone_2_country_code"
                                            style="max-width: 110px;">
                                            <option value="" selected disabled>Select Country Code</option>
                                            @foreach ($countries as $country)
                                                <option
                                                    data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                    value="{{ $country->phonecode }}"
                                                    {{ old('phone_2_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                    {{ $country->country_name . ' ' . '+' . $country->phonecode }}
                                                </option>
                                            @endforeach
                                        </select>
                                        <input class="form-control @error('phone_2') is-invalid @enderror" type="text"
                                            id="phone_2" name="phone_2" maxlength="15" placeholder="Phone Number 2 (Optional)"
                                            value="{{ old('phone_2') }}">
                                        @error('phone_2')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('phone_2_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('phone_2_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>

                                <div class="mb-4">
                                    <div class="recaptcha-wrapper">
                                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                    </div>
                                    @error('g-recaptcha-response')
                                        <div class="invalid-feedback d-block">{{ $message }}</div>
                                    @enderror
                                </div>

                                <button class="btn btn-block btn-primary" type="submit">Create Account</button>
                            </form>
                            <a class="btn btn-link px-0 mt-2" href="{{ route('login') }}">Back to
                                Login</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection

@section('script')
    function handleCountrySelect(selectId) {
    const select = document.getElementById(selectId);
    if (!select) return;

    const options = select.options;

    // Restore full label for all options
    for (let option of options) {
    const fullLabel = option.getAttribute('data-label');
    if (fullLabel) {
    option.textContent = fullLabel;
    }
    }

    // Shorten only the selected one
    const selectedOption = select.options[select.selectedIndex];
    selectedOption.textContent = '+' + selectedOption.value;

    // When dropdown is opened, restore all labels for full list
    select.addEventListener('mousedown', () => {
    for (let option of options) {
    const fullLabel = option.getAttribute('data-label');
    if (fullLabel) {
    option.textContent = fullLabel;
    }
    }
    });

    // When changed, reapply short label to selected only
    select.addEventListener('change', () => {
    handleCountrySelect(selectId);
    });
    }

    // Initialize on page load
    handleCountrySelect('phone_1_country_code');
    handleCountrySelect('phone_2_country_code');
@endsection
