@extends('layouts.auth')

@section('title', 'Verify Email')

@section('content')
    @if (Auth::user()->activated_at != null && Auth::user()->status == true)
        <script>
            window.location = "{{ route('dashboard') }}";
        </script>
    @endif

    <div class="bg-body-tertiary min-vh-100 d-flex flex-row align-items-center">
        <!-- @if (session('resent'))
            <div class="toast-container top-0 end-0 p-3">
                <div class="toast align-items-center fade show" role="alert" aria-live="assertive" aria-atomic="true">
                    <div class="d-flex">
                        <div class="toast-body">
                            {{ __('A fresh verification link has been sent to your email address.') }}
                        </div>
                        <button type="button" class="btn-close me-2 m-auto" data-coreui-dismiss="toast"
                            aria-label="Close"></button>
                    </div>
                </div>
            </div>
        @endif -->
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-md-8">
                    <div class="d-flex justify-content-center mb-4">
                        <img class="login-brand-full" width="384" height="128"
                            src="{{ asset('assets/brand/Logo-SubMeet.png') }}" alt="SubMeet">
                    </div>
                    {{-- <h1 class="text-center my-3">{{ env('APP_NAME') }}</h1> --}}
                    <div class="card">
                        <div class="card-header">{{ __('Verify Your Email Address') }}</div>

                        <div class="card-body">

                            @if (Auth::user()->activated_at == null && Auth::user()->status == false)
                                {{ __('Thanks for signing up! Before getting started, could you verify your email address by clicking on the link we just emailed to you?') }}
                                {!! __('Before proceeding, please check your email <strong>including spam or junk folder</strong> for a verification link.') !!}
                                {{ __('If you did not receive the email, please click the button below to request another.') }}
                                <form class="d-inline" method="POST" action="{{ route('verification.send') }}">
                                    @csrf

                                    <div class="my-3">
                                        <div class="recaptcha-wrapper">
                                            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                                        </div>
                                        @error('g-recaptcha-response')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <button type="submit"
                                        class="btn btn-primary btn-block">{{ __('click here to
                                                                                                                                                                                                request another') }}</button>
                                </form>

                                <form class="mt-3" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block text-white">{{ __('Logout') }}</button>
                                </form>
                            @elseif(Auth::user()->activated_at != null && Auth::user()->status == false)
                                {{ __('Your account has been disabled. Please contact our admin.') }}

                                <form class="mt-3" method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="btn btn-danger btn-block">{{ __('Logout') }}</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
