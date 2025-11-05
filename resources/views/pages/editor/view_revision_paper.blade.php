@extends('layouts.event')
@section('title', 'View Revision Paper')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('events') }}">Events</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard.event', request()->route('event')) }}">{{ request()->route('event') }}</a></li>
    <li class="breadcrumb-item"><a href="{{ route('index.revision.paper', request()->route('event')) }}">Revision Paper</a></li>
    <li class="breadcrumb-item active"><span>View</span></li>
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
                <p>
                    {{ $paper->title ?? '-' }}
                    @if($paper->subtitle)
                        - {{ $paper->subtitle }}
                    @endif
                </p>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Abstract:</label>
                <p>{{ $paper->abstract ?? '-' }}</p>
            </div>
            <div class="mb-3">
                <label class="form-label fw-bold">Topic:</label>
                <p>
                    @php
                        $firstPaperId = $paper->first_paper_sub_id ?? $paper->paper_sub_id;
                        $topics = DB::table('topic_papers')
                            ->join('topics', 'topic_papers.topic_id', '=', 'topics.topic_id')
                            ->where('topic_papers.first_paper_sub_id', $firstPaperId)
                            ->pluck('topics.topic_name');
                    @endphp
                    
                    @if($topics && count($topics) > 0)
                        @foreach($topics as $topicName)
                            {{ $topicName }}
                        @endforeach
                    @else
                        {{ $paper->topicpapers[0]->topic->topic_name ?? '-' }}
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
                    $authors = $paper->author;
                    $names = collect($authors)->map(function ($author) {
                        return $author->given_name . ' ' . $author->family_name;
                    })->toArray();
                    $allNames = implode(', ', $names);
                @endphp
                <span title="{{ $allNames }}">{{ $allNames }}</span>
                </p>
            </div>
            <p><strong>Current Round:</strong> {{ $paper->round }}</p>
            <p><strong>Status:</strong> Revision Required</p>
            <div class="mb-3">
                <div class="d-flex gap-2">
                    <a href="{{ route('editor.check', [request()->route('event'), $paper->paper_sub_id]) }}"
                        class="btn btn-primary">
                        <i class="mdi mdi-pencil align-middle"></i> Download Paper
                    </a>
                    @if($history->where('round', '>', $paper->round)->count() > 0)
                        <a href="{{ route('index.create.decision', [request()->route('event'), $paper->paper_sub_id]) }}" 
                           class="btn btn-success">
                            <i class="cil-task"></i> Make New Decision
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="card mb-4">
        <div class="card-header">
            <h4 class="mb-0">Revision History</h4>
        </div>
        <div class="card-body px-4">
            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Round</th>
                            <th>Paper ID</th>
                            <th>Submission Date</th>
                            <th>Decision</th>
                            <th>Similarity</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($history as $historyItem)
                            <tr>
                                <td>
                                    <span class="badge bg-info">Round {{ $historyItem->round }}</span>
                                    @if($historyItem->paper_sub_id == $paper->paper_sub_id)
                                        <span class="badge bg-primary ms-1">Current</span>
                                    @endif
                                </td>
                                <td>{{ $historyItem->paper_sub_id }}</td>
                                <td>{{ $historyItem->created_at ? \Carbon\Carbon::parse($historyItem->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-' }}</td>
                                <td>
                                    @if($historyItem->decisions->count() > 0)
                                        @foreach($historyItem->decisions as $decision)
                                            <span class="badge bg-warning">{{ $decision->decision }}</span>
                                        @endforeach
                                    @else
                                        <span class="badge bg-secondary">No Decision</span>
                                    @endif
                                </td>
                                <td>
                                    @if($historyItem->similarity)
                                        {{ $historyItem->similarity }}%
                                    @else
                                        -
                                    @endif
                                </td>
                                <td>
                                    <a href="{{ route('editor.check', [request()->route('event'), $historyItem->paper_sub_id]) }}" 
                                       class="btn btn-sm btn-primary">
                                        Download Paper
                                    </a>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    @if($paper->assignment->count() > 0)
        <div class="card">
            <div class="card-header">
                <h4 class="mb-0">Previous Reviews</h4>
            </div>
                            @if($history->count())
                    <div class="accordion mb-4" id="historyAccordion">
                        @foreach($history as $hist)
                            <div class="accordion-item">
                                <h2 class="accordion-header" id="heading{{ $hist->round }}">
                                    <button class="accordion-button collapsed" type="button" data-coreui-toggle="collapse"
                                        data-coreui-target="#collapse{{ $hist->round }}" aria-expanded="false"
                                        aria-controls="collapse{{ $hist->round }}">
                                        Round {{ $hist->round }}
                                        @if($hist->decisions->count())
                                            <span class="ms-3 text-secondary"
                                                style="font-size:0.95em;">({{ $hist->decisions->first()->created_at ? $hist->decisions->first()->created_at->format('d M Y H:i') : '-' }})</span>
                                        @endif
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
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <div class="alert alert-info">No review available.</div>
                @endif
        </div>
    @endif
</div>
@endsection