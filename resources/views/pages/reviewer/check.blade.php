@extends('layouts.event')

@section('title', 'Review')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Event</a>
    </li>
    <li class="breadcrumb-item active"><span>Check Paper</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-body">
                {{ $fileUrl }}
            </div>
        </div>
    </div>
    
@endsection
