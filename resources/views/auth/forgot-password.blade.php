@extends('layouts.auth')

@section('title', 'Forgot Password')

@section('content')

    <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
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
                    <div class="d-flex justify-content-center mb-4">
                        <img class="login-brand-full" width="384" height="128"
                            src="{{ asset('assets/brand/Logo-SubMeet.png') }}" alt="SubMeet">
                    </div>
                    <div class="card-group d-block d-md-flex row">
                        <div class="card col-md-12 p-4 mb-0">
                            <div class="card-body">
                                <h1>Forgot Password?</h1>
                                <p class="text-body-secondary">The reset password link will be sent to your email.</p>
                                <form class="needs-validation" novalidate method="POST"
                                    action="{{ route('forgot_password.send') }}">
                                    @csrf
                                    <div class="input-group mb-3"><span class="input-group-text">
                                            <i class="cil-envelope-open"></i></span>
                                        <input class="form-control  @error('email') is-invalid @enderror" required
                                            type="text" name="email" maxlength="255" placeholder="Email" value="{{ old('email') }}">
                                        @error('email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-4">
                                        <div class="recaptcha-wrapper">
                                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                        </div>
                                        @error('g-recaptcha-response')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="row">
                                        <div class="col-6">
                                            <button class="btn btn-primary px-4" type="submit">Submit</button>
                                        </div>
                                        <div class="col-6 text-end">
                                            <a class="btn btn-link px-0" href="login">Back to login?</a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        {{-- <div class="card col-md-5 text-white bg-primary py-5">
                        <div class="card-body text-center">
                            <div>
                                <h2>Sign up</h2>
                                <p>Lorem ipsum dolor sit amet, consectetur adipisicing elit, sed do eiusmod tempor
                                    incididunt ut labore et dolore magna aliqua.</p>
                                <button class="btn btn-lg btn-outline-light mt-3" type="button"
                                    onclick="window.location.href='{{ route('register') }}'">Register Now!</button>
                            </div>
                        </div>
                    </div> --}}
                    </div>
                </div>
            </div>
        </div>
    </div>
    
@endsection
