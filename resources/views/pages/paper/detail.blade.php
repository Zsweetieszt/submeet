@extends('layouts.event')

@section('title', 'Revise Paper - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route('index.paper', request()->route('event')) }}">Papers</a>
    </li>
    <li class="breadcrumb-item active"><span>Detail Paper</span>
    </li>
@endsection
<style>
    .btn-help:hover {
        color: #0d6efd !important;
    }

    .gif-author {
        width: 150%;
        height: auto;
    }

    @media (max-width: 768px) {
        .gif-author {
            width: 100%;
        }
    }
</style>
@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Review Detail</h4>
            </div>
            <div class="card-body">
                @if(isset($decision) && $decision)
                    @if($review_last->isNotEmpty())
                        <fieldset>
                            <legend>Review Option</legend>
                            <table class="table border mb-0">
                                <thead class="fw-semibold text-nowrap">
                                    <tr class="align-middle">
                                        <th class="bg-body-secondary">No.</th>
                                        <th class="bg-body-secondary">Item</th>
                                        <th class="bg-body-secondary">Description</th>
                                        <th class="bg-body-secondary">Weight (%)</th>
                                        @foreach($review_items as $item)
                                            @foreach($review_last as $reviewer)
                                                <th class="bg-body-secondary">Score Reviewer {{ $loop->parent->iteration }}</th>
                                            @endforeach
                                            @break
                                        @endforeach
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($review_items as $item)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>{{ $item->name }}</td>
                                            <td>{{ $item->desc }}</td>
                                            <td>{{ $item->weight }}</td>
                                            @if (empty($decision?->decision) || ($decision?->decision === 'Template Revision'))
                                                @foreach($review_last as $reviewer)
                                                    <td class="text-center">N/A</td>
                                                @endforeach
                                            @else
                                                @foreach($review_last as $reviewer)
                                                    @php
                                                        $content = $item->review_contents
                                                            ->where('review_id', $reviewer->reviews->first()->review_id ?? null)
                                                            ->first();
                                                        $option = $item->options->where('scale', $content->value ?? null)->first();
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $content ? $content->value : 'Not reviewed' }}
                                                    </td>
                                                @endforeach
                                            @endif
                                        </tr>
                                    @endforeach
                                    <tr>
                                        <td colspan="4" class="text-center fw-semibold">Average Score</td>
                                        @if (empty($decision?->decision) || ($decision?->decision === 'Template Revision'))
                                            @foreach($review_last as $reviewer)
                                                <td class="text-center">N/A</td>
                                            @endforeach
                                        @else
                                            @foreach($review_last as $reviewer)
                                                @php
                                                    $totalScore = 0;
                                                    $count = 0;
                                                    foreach ($review_items as $item) {
                                                        $content = $item->review_contents
                                                            ->where('review_id', $reviewer->reviews->first()->review_id ?? null)
                                                            ->first();
                                                        if ($content) {
                                                            $totalScore += $content->value;
                                                            $count++;
                                                        }
                                                    }
                                                    $averageScore = $count > 0 ? round($totalScore / $count, 2) : null;
                                                @endphp
                                                <td class="text-center">
                                                    {{ $averageScore !== null ? $averageScore : 'Not reviewed' }}
                                                </td>
                                            @endforeach
                                        @endif
                                </tbody>
                            </table>
                        </fieldset>
                        <fieldset>
                            <legend>Overall Evaluation</legend>
                            @foreach($review_last as $reviewer)
                                <div class="mb-3">
                                    <label class="form-label">Reviewer {{ $loop->iteration }}</label>
                                    @php
                                        $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                    @endphp
                                    <textarea class="form-control" rows="3"
                                        disabled>{{ $latestReview ? $latestReview->note_for_author : 'Not reviewed' }}</textarea>
                                </div>
                            @endforeach
                        </fieldset>
                        <fieldset>
                            <legend>File Review</legend>
                            @foreach($review_last as $reviewer)
                                <div class="mb-3">
                                    <label class="form-label">Reviewer {{ $loop->iteration }}</label>
                                    <br>
                                    @php
                                        $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                    @endphp
                                    @if ($latestReview)
                                        @if($latestReview->attach_file)
                                            <a href="{{ route('paper.file.review', [request()->route('event'), $latestReview->review_id]) }}"
                                                class="btn btn-primary">Download Paper</a>
                                        @else
                                            <span class="text-muted">No Paper Available</span>
                                        @endif
                                    @else
                                        <span class="text-muted">Not reviewed</span>
                                    @endif
                                </div>
                            @endforeach
                        </fieldset>
                    @else
                        <div class="alert alert-info" role="alert">
                            Paper was directly decided by the editor. No reviewer feedback available.
                        </div>
                    @endif
                    <fieldset>
                        <legend>Final Decision</legend>
                            <div class="mb-3">
                                <label class="form-label">Decision:</label>
                                <input type="text" class="form-control" value="{{ $decision->decision ?? '-' }}" disabled>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Note for Author:</label>
                                <textarea class="form-control" rows="3" disabled>{{ $decision->note_for_author ?? '-' }}</textarea>
                            </div>
                        <div class="mb-3">
                            <label class="form-label">Similarity (%):</label>
                            <input type="text" step="0.01" min="0" max="100" class="form-control"
                                value="{{ $paper->similarity ?? '-' }}" disabled>
                        </div>
                    </fieldset>
                @else
                    <div class="alert alert-info" role="alert">
                        Paper is still under review. Please check back later for the review results.
                    </div>
                @endif
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                Revise Paper
            </div>
            <div class="card-body">
                <fieldset>
                    <legend>Paper</legend>
                    <input type="text" name="event" value=" {{ request()->route('event') }} " hidden>
                    <div class="row align-items-start">
                        <div class="mb-3">
                            <label for="title" class="form-label">Title</label>
                            <input type="text" class="form-control @error('title') is-invalid @enderror"
                                id="title" maxlength="255" name="title" required
                                value="{{ old('title', $paper->title) }}" placeholder="Title" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="subtitle" class="form-label">Subtitle</label>
                            <input type="text" class="form-control @error('subtitle') is-invalid @enderror"
                                id="subtitle" maxlength="255" name="subtitle"
                                value="{{ old('subtitle', $paper->subtitle) }}"
                                placeholder="Subtitle (Optional)" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="abstract" class="form-label">Abstract</label>
                            <textarea class="form-control @error('abstract') is-invalid @enderror" id="abstract" name="abstract" required
                                placeholder="Abstract max 250 words" style="height: 255px;" disabled>{{ old('abstract', $paper->abstract) }}</textarea>
                        </div>
                        <div class="mb-3">
                            <label for="topics" class="form-label">Topics</label>
                           <select class="form-select @error('topics') is-invalid @enderror"
                                id="topics" name="topics" required disabled>
                                <option value="" selected>Select Topic</option>
                                @foreach ($topics as $topic)
                                    <option value="{{ $topic->topic_id }}"
                                        {{ old('topics', $paper->topics->topic_id) == $topic->topic_id ? 'selected' : '' }}> {{ $topic->topic_name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="keywords" class="form-label">Keywords</label>
                            <input type="text" class="form-control @error('keywords') is-invalid @enderror"
                                id="keywords" name="keywords" required
                                value="{{ old('keywords', $paper->keywords) }}" placeholder="Keywords" disabled>
                            @error('keywords')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="mb-3">
                            <label for="paper_file" class="form-label">Paper File</label>
                            @if ($paper->attach_file)
                                <div class="mt-2">
                                    <a href="{{ route('check.paper', [request()->route('event'), $paper->paper_sub_id]) }}"
                                        target="_blank" class="btn btn-primary">Download Paper</a>
                                </div>
                            @endif
                        </div>
                        <div class="mb-3">
                            <label for="authors" class="form-label">Authors
                            </label>
                            <input type="text"
                                class="form-control place @error('authors') is-invalid @enderror"
                                id="authors" name="authors" required
                                value="{{ old('authors', $paper->authors) }}"
                                placeholder="Add authors by typing: Authors Name - Authors Email" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="corresponding" class="form-label">Corresponding Author</label>
                            <select class="form-select @error('corresponding') is-invalid @enderror"
                                id="corresponding" name="corresponding" required disabled>
                                @php
                                    $authors = [];
                                    if (!empty($paper->authors)) {
                                        $authors = array_map('trim', explode(',', $paper->authors));
                                    }
                                @endphp
                                <option value="">No Author Chosen</option>
                                @foreach($authors as $index => $author)
                                    <option value="{{ $index + 1 }}" {{ ($paper->corresponding == ($index + 1)) ? 'selected' : '' }}>
                                        {{ $author }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="note_for_editor" class="form-label">Note for Editor</label>
                            <textarea class="form-control @error('note_for_editor') is-invalid @enderror" id="note_for_editor"
                                name="note_for_editor" required placeholder="Note for Editor max 3000 characters" style="height: 255px;"
                                maxlength="3000" disabled>{{ old('note_for_editor', $paper->note_for_editor) }}</textarea>
                        </div>
                    </div>
                </fieldset>
                <a href="{{ route('index.paper', request()->route('event')) }}"
                                class="btn btn-danger">Back</a>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function () {
            document.getElementById("myForm").addEventListener("keydown", function (e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });
        });

    </script>

@endsection