@extends('errors::minimal')

@section('title', __('Method Not Allowed'))
@section('code', '405')
@section('message', __('Method Not Allowed'))
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