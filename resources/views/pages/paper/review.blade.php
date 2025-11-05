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
    <li class="breadcrumb-item active"><span>Result Review Paper</span>
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
                <h4 class="mb-0">Review Details</h4>
            </div>
            <div class="card-body">
            @if($history->count())
            <div class="accordion mb-4" id="historyAccordion">
                @foreach($history as $hist)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading{{ $hist->round }}">
                            <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse"
                                data-coreui-target="#collapse{{ $hist->round }}" aria-expanded="false"
                                aria-controls="collapse{{ $hist->round }}">
                                Round {{ $hist->round }}
                            </button>
                        </h2>
                        <div id="collapse{{ $hist->round }}" class="accordion-collapse collapse"
                            aria-labelledby="heading{{ $hist->round }}" data-coreui-parent="#historyAccordion">
                            <div class="accordion-body">
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
                                                    @foreach($hist->assignment as $reviewer)
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
                                                    @if (!empty(optional($hist->decisions->first())->decision) && optional($hist->decisions->first())->decision === 'Template Revision')
                                                    @foreach($hist->assignment as $reviewer)
                                                    <td class="text-center">N/A</td>
                                                    @endforeach
                                                    @else
                                                    @foreach($hist->assignment as $reviewer)
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
                                                @if (!empty(optional($hist->decisions->first())->decision) && optional($hist->decisions->first())->decision === 'Template Revision')
                                                    @foreach($hist->assignment as $reviewer)
                                                        <td class="text-center">N/A</td>
                                                    @endforeach
                                                @else
                                                @foreach($hist->assignment as $reviewer)
                                                    @php
                                                        $totalScore = 0;
                                                        $count = 0;
                                                        foreach($review_items as $item) {
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
                                    @foreach($hist->assignment as $reviewer)
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
                                    @foreach($hist->assignment as $reviewer)
                                        <div class="mb-3">
                                            <label class="form-label">Reviewer {{ $loop->iteration }}</label>
                                            <br>
                                            @php
                                                $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                            @endphp
                                            @if ($latestReview)
                                                @if($latestReview->attach_file)
                                                    <a href="{{ route('editor.file.review', [request()->route('event'), $latestReview->review_id]) }}"
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
                                <fieldset>
                                    <legend>Final Decision</legend>
                                    @foreach($hist->decisions as $decision)
                                        <div class="mb-3">
                                            <label class="form-label">Decision:</label>
                                            <input type="text" class="form-control" value="{{ $decision->decision ?? '-' }}" disabled>
                                        </div>
                                        <div class="mb-3">
                                            <label class="form-label">Note for Author:</label>
                                            <textarea class="form-control" rows="3"
                                                disabled>{{ $decision->note_for_author ?? '-' }}</textarea>
                                        </div>
                                        @endforeach
                                        <div class="mb-3">
                                            <label class="form-label">Similarity (%):</label>
                                            <input type="text" step="0.01" min="0" max="100" class="form-control" value="{{ $hist->similarity ?? '-' }}" disabled>
                                        </div>
                                </fieldset>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            @endif
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.getElementById("myForm").addEventListener("keydown", function(e) {
                if (e.key === "Enter") {
                    e.preventDefault();
                }
            });
        });

    </script>
    
@endsection
