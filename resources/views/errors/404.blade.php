@extends('errors::minimal')

@section('title', __('Not Found'))
@section('code', '404')
@section('message', __('Not Found'))
{{-- @section('sub_message', __('Oops! you seem to be lost. The page you are looking for does not exist.')) --}}
@section('sub_message')
    Oops! you seem to be lost. The page you are looking for does not exist. Please go back to the previous page or return to the home page.
@endsection
@section('button')
    <a href="{{ url()->previous() }}" class="btn btn-primary me-3">
        {{ __('Go Back') }}
    </a>
    <a href="{{ url('/') }}" class="btn btn-secondary">
        {{ __('Home') }}
    </a>
@endsection