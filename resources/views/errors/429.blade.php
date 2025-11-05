@extends('errors::minimal')

@section('title', __('Too Many Requests'))
@section('code', '429')
@section('message', __('Too Many Requests'))
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