@extends('layouts.event')

@section('title', 'View Paper - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route("dashboard")}}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route("events") }}">Event</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route("dashboard.event", request()->route('event')) }}">{{$event->event_name}}</a>
    </li>
    <li class="breadcrumb-item active">
        Editor
    </li>
    <li class="breadcrumb-item active"><span><a
                href="{{ route('index.editor.decision', request()->route('event')) }}">Editor Decision</a></span>
    </li>
    <li class="breadcrumb-item active"><span>Detail Paper</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Paper Information</h4>
            </div>
            <div class="card-body px-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Paper Title:</label>
                    <p>{{ $paper->title ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Abstract:</label>
                    <p>{{ $paper->abstract ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Topic:</label>
                    <p>
                        @if ($paper->topicpapers && count($paper->topicpapers) > 0)
                            {{ $paper->topicpapers[0]->topic->topic_name ?? '-' }}
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Keywords:</label>
                    <p>
                        @if (!empty($paper->keywords))
                            @php
                                $keywords = json_decode($paper->keywords, true);
                            @endphp
                            @if (is_array($keywords))
                                {{ implode(', ', array_column($keywords, 'value')) }}
                            @else
                                {{ $paper->keywords }}
                            @endif
                        @else
                            -
                        @endif
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Authors:</label>
                    <p>
                    @php
                        $authors = $paper->author; // assuming this is an array or collection
                        $names = collect($authors)->map(function ($author) {
                            return $author->given_name . ' ' . $author->family_name;
                        })->toArray();
                        $allNames = implode(', ', $names);
                        $displayNames = Str::limit($allNames, 50, '...');
                    @endphp
                    <span title="{{ $allNames }}">{{ $displayNames }}</span>
                    </p>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Note for Editor:</label>
                    <p>{{ $paper->note_for_editor ?? '-' }}</p>
                </div>
                <div class="mb-3">
                    <a href="{{ route('editor.check', [request()->route('event'), $paper->paper_sub_id]) }}"
                        class="btn btn-primary">Download Paper
                    </a>
                </div>
                <a href="{{ route('index.editor.decision', request()->route('event')) }}" class="btn btn-danger">Back</a>
            </div>
        </div>
    </div>
    
@endsection