@extends('layouts.event')

@section('title', 'Detail Final Paper')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('events') }}">Event</a></li>
    <li class="breadcrumb-item"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name ?? '-' }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('index.final.paper', request()->route('event')) }}">Final Paper</a></li>
    <li class="breadcrumb-item active"><span>Final Paper Detail</span></li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Final Paper Information</h4>
            </div>
            <div class="card-body">
                @if($history->count())
                    <div class="accordion mb-4" id="historyAccordion">
                        @foreach($history as $hist)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $hist->round }}">
                                    <button class="accordion-button {{ $loop->first ? '' : 'collapsed' }}" type="button" data-coreui-toggle="collapse"
                                        data-coreui-target="#collapse{{ $hist->round }}" aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
                                        aria-controls="collapse{{ $hist->round }}">
                                        Round {{ $hist->round }}
                                        @if($hist->decisions->count())
                                            <span class="ms-3 text-secondary"
                                                style="font-size:0.95em;">({{ $hist->decisions->first()->created_at ? $hist->decisions->first()->created_at->format('d M Y H:i') : '-' }})</span>
                                        @endif
                                    </button>
                                </h2>
                                <div id="collapse{{ $hist->round }}" class="accordion-collapse collapse {{ $loop->first ? 'show' : '' }}"
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
                                                        @foreach($hist->assignment as $reviewer)
                                                            <th class="bg-body-secondary">Score Reviewer {{ $loop->iteration }}</th>
                                                        @endforeach
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    @foreach($review_items as $item)
                                                        <tr>
                                                            <td class="text-center">{{ $loop->iteration }}</td>
                                                            <td>{{ $item->name }}</td>
                                                            <td>{{ $item->desc }}</td>
                                                            <td class="text-center">{{ $item->weight }}</td>
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
                                                        </tr>
                                                    @endforeach
                                                    <tr>
                                                        <td colspan="4" class="text-center fw-semibold">Average Score</td>
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
                                                    </tr>
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
                                            <legend>Recommendation</legend>
                                            @foreach($hist->assignment as $reviewer)
                                                <div class="mb-3">
                                                    <label class="form-label">Reviewer {{ $loop->iteration }} :</label>
                                                    @php
                                                        $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                                    @endphp
                                                    {{ $latestReview ? $latestReview->recommendation : 'Not reviewed' }}
                                                </div>
                                            @endforeach
                                        </fieldset>
                                        <fieldset>
                                            <legend>Note for Editor</legend>
                                            @foreach($hist->assignment as $reviewer)
                                                <div class="mb-3">
                                                    <label class="form-label">Reviewer {{ $loop->iteration }}</label>
                                                    @php
                                                        $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                                    @endphp
                                                    <textarea class="form-control" rows="3"
                                                        disabled>{{ $latestReview ? $latestReview->note_for_editor : 'Not reviewed' }}</textarea>
                                                </div>
                                            @endforeach
                                        </fieldset>
                                        <fieldset>
                                            <legend>Editor Decision</legend>
                                            @foreach($hist->decisions as $decision)
                                                <div class="mb-3">
                                                    <label class="form-label">Decision:</label>
                                                    <input type="text" class="form-control" value="{{ $decision->decision ?? '-' }}"
                                                        disabled>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label">Note for Author:</label>
                                                    <textarea class="form-control" rows="3"
                                                        disabled>{{ $decision->note_for_author ?? '-' }}</textarea>
                                                </div>
                                            @endforeach
                                            <div class="mb-3">
                                                <label class="form-label">Similarity (%):</label>
                                                <input type="text" step="0.01" min="0" max="100" class="form-control"
                                                    value="{{ $hist->similarity ?? '-' }}" disabled>
                                            </div>
                                        </fieldset>
                                        <div class="mb-3">
                                                <a href="{{ route('editor.check', [request()->route('event'), $hist->paper_sub_id]) }}" class="btn btn-primary">
                                                    Download Paper
                                                </a>
                                            </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">No decision history available.</div>
                @endif
            </div>
            <div class="card-footer d-flex justify-content-start">
                <a href="{{ route('index.final.paper', request()->route('event')) }}" class="btn btn-danger me-auto">
                    Back
                </a>
            </div>
        </div>
    </div>
    
@endsection