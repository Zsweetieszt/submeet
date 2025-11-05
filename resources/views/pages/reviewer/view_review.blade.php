@extends('layouts.event')

@section('title', 'View Review - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active"><a href="{{ route(name: "events") }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route("dashboard.event", request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item">Reviewer</li>
    <li class="breadcrumb-item"><a href="{{ route('events.reviewer', [$event->event_code]) }}">Review Paper</a></li>
    <li class="breadcrumb-item active"><span>View Review</span></li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h5 class="mb-0">Paper Information</h5>
            </div>
            <div class="card-body">
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
                    <a href="{{ route('events.reviewer.check', [request()->route('event'), $paper->paper_sub_id]) }}"
                        class="btn btn-sm btn-primary btn-rounded">
                        <i class="mdi mdi-pencil align-middle"></i> Download Paper
                    </a>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">View Review</h4>
            </div>
            <div class="card-body px-4">

                @if ($review)
                    {{-- Review Items --}}
                    <fieldset class="border rounded-3 p-3 mb-4">
                        <legend class="float-none w-auto px-3 fs-6 mb-0">Review Items</legend>
                        <div class="table-responsive">
                            <table class="table border mb-0 align-middle">
                                <thead class="fw-semibold text-nowrap">
                                    <tr class="align-middle">
                                        <th class="bg-body-secondary text-start" style="width:40px;">No</th>
                                        <th class="bg-body-secondary text-start" style="width:150px">Item</th>
                                        <th class="bg-body-secondary text-start" style="width:320px">Description</th>
                                        <th class="bg-body-secondary text-start" style="width:70px;">Weight (%)</th>
                                        <th class="bg-body-secondary text-start" style="width:250px;">Score</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $reviewItems = \App\Models\ReviewItem::with('options')
                                            ->where('event_id', $event->event_id)
                                            ->orderBy('seq')
                                            ->get();
                                    @endphp
                                    @foreach ($reviewItems as $idx => $item)
                                        @php
                                            $content = $reviewContents->firstWhere(
                                                'review_item_id',
                                                $item->review_item_id,
                                            );
                                            $option = $content
                                                ? \App\Models\ReviewOption::where(
                                                    'review_item_id',
                                                    $item->review_item_id,
                                                )
                                                    ->where('scale', $content->value)
                                                    ->first()
                                                : null;
                                        @endphp
                                        <tr>
                                            <td class="text-center">{{ $idx + 1 }}</td>
                                            <td class="text-start">{{ $item->name }}</td>
                                            <td class="text-start">{{ $item->desc }}</td>
                                            <td class="text-center">{{ $item->weight }}</td>
                                            <td class="text-start">
                                                @if ($option)
                                                    {{ $option->scale }} - {{ $option->desc }}
                                                @else
                                                    -
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </fieldset>

                    {{-- Evaluation Paper --}}
                    <fieldset class="border rounded-3 p-3 mb-4">
                        <legend class="float-none w-auto px-3 fs-6 mb-0">Evaluation Paper</legend>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Overall Evaluation</label>
                            <div class="form-control" style="height: auto; min-height: 100px;">
                                {{ $review->note_for_author ?? '-' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Recommendation</label>
                            <div class="form-control">
                                @php
                                    $badgeClass = match ($review->recommendation) {
                                        'Accept' => 'bg-success',
                                        'Minor Revisions' => 'bg-warning text-dark',
                                        'Major Revisions' => 'bg-orange text-white',
                                        'Decline' => 'bg-danger',
                                        default => 'bg-secondary',
                                    };
                                @endphp
                                <span class="badge {{ $badgeClass }} fs-6">{{ $review->recommendation }}</span>
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Note for Editor</label>
                            <div class="form-control" style="height: auto; min-height: 100px;">
                                {{ $review->note_for_editor ?? '-' }}
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Review File</label>
                            @if ($review->attach_file)
                                <div class="mt-2">
                                    <a class="btn btn-primary btn-sm" href="{{ route('events.reviewer.download', [request()->route('event'), $paper->paper_sub_id]) }}"
                                        target="_blank">
                                        <i class="fas fa-download me-1"></i> Download Review
                                    </a>
                                    <small class="text-muted d-block mt-1">
                                        File: {{ basename($review->attach_file) }}
                                    </small>
                                </div>
                            @else
                                <div class="form-control">
                                    <span class="text-muted">No Paper attached</span>
                                </div>
                            @endif
                        </div>
                    </fieldset>

                    {{-- Review Summary --}}
                    <fieldset class="border rounded-3 p-3 mb-4">
                        <legend class="float-none w-auto px-3 fs-6 mb-0">Review Summary</legend>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Review Date:</label>
                                    <span>{{ $review->created_at ? \Carbon\Carbon::parse($review->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-' }}</span>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="mb-2">
                                    <label class="form-label fw-bold">Last Updated:</label>
                                    <span>{{ $review->updated_at ? \Carbon\Carbon::parse($review->updated_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-' }}</span>
                                </div>
                            </div>
                        </div>
                    </fieldset>
                @else
                    {{-- No Review Found --}}
                    <div class="alert alert-info" role="alert">
                        <h5 class="alert-heading">No Review Found</h5>
                        <p>You haven't submitted a review for this paper yet.</p>
                        <hr>
                        <p class="mb-0">
                            <a href="{{ route('events.reviewer.review', [$event->event_code, $paper->paper_sub_id]) }}"
                                class="btn btn-primary">
                                <i class="fas fa-edit me-1"></i> Create Review
                            </a>
                        </p>
                    </div>
                @endif

                {{-- Action Buttons --}}
                <div class="d-flex justify-content-end mt-4">
                    <a href="{{ route('events.reviewer', [$event->event_code]) }}" class="btn btn-secondary">
                        <i class="fas fa-arrow-left me-1"></i> Back
                    </a>
                </div>
            </div>
        </div>
    </div>
    
@endsection

<style>
    .bg-orange {
        background-color: #fd7e14 !important;
    }
</style>
