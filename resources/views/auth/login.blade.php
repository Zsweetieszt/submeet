@extends('layouts.auth')

@section('title', 'Login')

@section('content')

    <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
        {{-- @if ($errors->any())
    @foreach ($errors->all() as $error)
    <div class="toast-container top-0 end-0 p-3">
        <div class="toast align-items-center fade show" role="alert" aria-live="assertive" aria-atomic="true">
            <div class="d-flex">
                <div class="toast-body">
                    {{ $error }}
                </div>
                <button type="button" class="btn-close me-2 m-auto" data-coreui-dismiss="toast"
                    aria-label="Close"></button>
            </div>
        </div>
    </div>
    @endforeach
    @endif --}}
        <!-- @if (session('status'))
            <div class="toast-container top-0 end-0 p-3">
                <div class="toast align-items-center fade show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ session('status') }}
                        </div>
                        <button type="button" class="btn-close me-2 m-auto" data-coreui-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif -->
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="d-flex justify-content-center mt-3 mb-md-2 mb-4">
                        <img class="login-brand-full" style="width: 325px; height: auto;" src="{{ asset('assets/brand/Logo-SubMeet.png') }}" alt="SubMeet">
                    </div>
                    <div class="card-group d-block d-md-flex row">
                        <div class="card col-md-7 p-4 mb-0">
                            <div class="card-body">
                                <h1>Login</h1>
                                <p class="text-body-secondary">Sign In to your account</p>
                                <form class="needs-validation" novalidate method="POST"
                                    action="{{ route('authenticate') }}">
                                    @csrf
                                    @if (isset($event_code))
                                        <input type="hidden" name="event_code" value="{{ $event_code }}">
                                    @endif
                                    <div class="input-group mb-3">
                                        <span class="input-group-text">
                                            <i class="cil-user"></i>
                                        </span>
                                        <input class="form-control  @error('login') is-invalid @enderror" required
                                            type="text" name="login" maxlength="255" placeholder="Username or Email"
                                            value="{{ old('login') }}">
                                        @error('login')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="input-group mb-4">
                                        <span class="input-group-text">
                                            <i class="cil-lock-locked"></i>
                                        </span>
                                        <input id="password" class="form-control @error('password') is-invalid @enderror" required
                                            name="password" type="password" maxlength="128" placeholder="Password">
                                        <span class="input-group-text" id="toggle-password" style="cursor: pointer;">
                                            <i class="fa-regular fa-eye"></i>
                                        </span>
                                        <script>
                                            document.addEventListener('DOMContentLoaded', function () {
                                                const passwordInput = document.getElementById('password');
                                                const toggle = document.getElementById('toggle-password');
                                                const icon = toggle.querySelector('i');
                                                
                                                toggle.addEventListener('mousedown', function () {
                                                    passwordInput.type = 'text';
                                                    icon.classList.remove('fa-eye');
                                                    icon.classList.add('fa-eye-slash');
                                                    toggle.style.transform = 'scale(0.95)';
                                                });
                                                
                                                toggle.addEventListener('mouseup', function () {
                                                    passwordInput.type = 'password';
                                                    icon.classList.remove('fa-eye-slash');
                                                    icon.classList.add('fa-eye');
                                                    toggle.style.transform = 'scale(1)';
                                                });
                                                
                                                toggle.addEventListener('mouseleave', function () {
                                                    passwordInput.type = 'password';
                                                    icon.classList.remove('fa-eye-slash');
                                                    icon.classList.add('fa-eye');
                                                    toggle.style.transform = 'scale(1)';
                                                });
                                                
                                                // Add CSS transition for smooth animation
                                                toggle.style.transition = 'transform 0.1s ease';
                                            });
                                        </script>
                                        @error('password')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="mb-4">
                                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                        @error('g-recaptcha-response')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- <div class="mb-4">
                                    <img src="{{ captcha_src() }}" alt="captcha">
                                    <div class="mt-2"></div>
                                    <div class="col-md-7">
                                        <input 
                                            type="text" name="captcha" class="form-control @error('captcha') is-invalid @enderror" placeholder="Please Insert Captcha" required>
                                    </div>
                                    @error('captcha') 
                                        <div class="invalid-feedback d-block">{{ $message }}</div> 
                                    @enderror 
                               </div> --}}



                                    <div class="row">
                                        <div class="col-6">
                                            <button class="btn btn-primary px-4" type="submit">Login</button>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-link px-0" href="{{ route('forgot_password.get') }}">Forgot
                                                password?</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        <div
                            class="card col-md-5 text-white bg-primary py-5 d-flex align-items-center justify-content-center">
                            <div
                                class="card-body text-center d-flex flex-column align-items-center justify-content-center h-100">
                                <div>
                                    <h2 class="w-100 text-center">Sign up</h2>
                                    <p class="w-100 text-center">SubMeet is a Conference Management System developed by JTK
                                        POLBAN.</p>
                                    <button class="btn btn-lg btn-outline-light mt-3" type="button"
                                        onclick="window.location.href='{{ route('register') }}'">Register Now!</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
