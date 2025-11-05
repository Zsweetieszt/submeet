@extends('errors::minimal')

@section('title', __('Unauthorized'))
@section('code', '401')
@section('message', __('Unauthorized'))
@section('sub_message')
    {!! 'Please contact <a href="mailto:submeet.cms@gmail.com">submeet.cms@gmail.com</a> if you are having any difficulties.' !!}
@endsection
@section('button')
    <a href="{{ url()->previous() }}" class="btn btn-primary me-3">
        {{ __('Go Back') }}
    </a>
    <a href="{{ url('/') }}" class="btn btn-secondary">
        {{ __('Home') }}
    </a>
@endsection