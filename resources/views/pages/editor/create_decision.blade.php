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
    <li class="breadcrumb-item active"><span>Decide</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        @if(isset($hasCompletedReviews) && $reviewCount < count($paper->assignment))
            <div class="alert alert-info mb-4" role="alert">
                <h6 class="alert-heading">
                    <i class="cil-info me-2"></i>Review Status Information
                </h6>
                <p class="mb-0">
                    Currently {{ $reviewCount }} out of {{ count($paper->assignment) }} assigned reviewers have completed their reviews. 
                    You can make an editorial decision based on the available reviews.
                </p>
            </div>
        @endif
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
            </div>
        </div>

        @if($history->count())
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="mb-0">Decision History</h4>
                </div>
                <div class="card-body">
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
                                                            @foreach($hist->assignment as $reviewer)
                                                                @php
                                                                    $content = $item->review_contents
                                                                        ->where('review_id', $reviewer->reviews->first()->review_id ?? null)
                                                                        ->first();
                                                                    $option = $item->options->where('scale', $content->value ?? null)->first();
                                                                @endphp
                                                            <td class="text-center">
                                                                {{ $content ? $content->value : 'Not yet reviewed' }}
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
                                                                {{ $averageScore !== null ? $averageScore : 'Not yet reviewed' }}
                                                            </td>
                                                        @endforeach
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
                                                        disabled>{{ $latestReview ? $latestReview->note_for_author : 'Not yet reviewed' }}</textarea>
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
                                                    {{ $latestReview ? $latestReview->recommendation : 'Not yet reviewed' }}
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
                                                        disabled>{{ $latestReview ? $latestReview->note_for_editor : 'Not yet reviewed' }}</textarea>
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
                                                        <span class="text-muted">Not yet reviewed</span>
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
                </div>
            </div>
        @endif
        
        <div class="card mb-4">
            <div class="">
                <div class="card-header d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0">Decide for {{ $paper->title }}</h4>
                    </div>
                </div>
                <div class="card-body px-4">
                    <form id="myForm" class="needs-validation" novalidate
                        action="{{ route('create.decision', ['event' => $event->event_code, 'paper' => $paper->paper_sub_id]) }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        @method('POST')
                        <div class="row">
                            <fieldset>
                                <legend>Review Option</legend>
                                <table class="table border mb-0">
                                    <thead class="fw-semibold text-nowrap">
                                        <tr class="align-middle">
                                            <th class="bg-body-secondary">No.</th>
                                            <th class="bg-body-secondary">Item</th>
                                            <th class="bg-body-secondary">Description</th>
                                            <th class="bg-body-secondary">Weight (%)</th>
                                            @foreach($paper->assignment as $reviewer)
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
                                                @foreach($paper->assignment as $reviewer)
                                                    @php
                                                        $content = $item->review_contents
                                                            ->where('review_id', $reviewer->reviews->first()->review_id ?? null)
                                                            ->first();
                                                        $option = $item->options->where('scale', $content->value ?? null)->first();
                                                    @endphp
                                                    <td class="text-center">
                                                        {{ $content ? $content->value : 'Not yet reviewed' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                        @endforeach
                                            <tr>
                                                <td colspan="4" class="text-center fw-semibold">Average Score</td>
                                                @foreach($paper->assignment as $reviewer)
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
                                                        {{ $averageScore !== null ? $averageScore : 'Not yet reviewed' }}
                                                    </td>
                                                @endforeach
                                            </tr>
                                    </tbody>
                                </table>
                            </fieldset>
                            <fieldset>
                                <legend>Overall Evaluation</legend>
                                @foreach($paper->assignment as $reviewer)
                                    <div class="mb-3">
                                        <label for="reviewer{{ $loop->iteration }}" class="form-label">Reviewer
                                            {{ $loop->iteration }}</label>
                                        @php
                                            $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                        @endphp
                                        <textarea class="form-control" rows="3"
                                            disabled>{{ $latestReview ? $latestReview->note_for_author : 'Not yet reviewed' }}</textarea>
                                    </div>
                                @endforeach
                            </fieldset>
                            <fieldset>
                                <legend>Recommendation</legend>
                                @foreach($paper->assignment as $reviewer)
                                    <div class="mb-3">
                                        <label for="reviewer{{ $loop->iteration }}" class="form-label">Reviewer
                                            {{ $loop->iteration }} : </label>
                                        @php
                                            $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                        @endphp
                                        {{ $latestReview ? $latestReview->recommendation : 'Not yet reviewed' }}
                                    </div>
                                @endforeach
                            </fieldset>
                            <fieldset>
                                <legend>Note for Editor</legend>
                                @foreach($paper->assignment as $reviewer)
                                    <div class="mb-3">
                                        <label for="reviewer{{ $loop->iteration }}" class="form-label">Reviewer
                                            {{ $loop->iteration }}</label>
                                        @php
                                            $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                        @endphp
                                        <textarea class="form-control" rows="3"
                                            disabled>{{ $latestReview ? $latestReview->note_for_editor : 'Not yet reviewed' }}</textarea>
                                    </div>
                                @endforeach
                            </fieldset>
                            <fieldset>
                                <legend>File Review</legend>
                                @foreach($paper->assignment as $reviewer)
                                <div class="mb-3">
                                    <label for="reviewer{{ $loop->iteration }}" class="form-label">Reviewer
                                        {{ $loop->iteration }}</label>
                                        <br>
                                        @php
                                            $latestReview = $reviewer->reviews->sortByDesc('created_at')->first();
                                        @endphp
                                        @if ($latestReview)
                                            @if($latestReview->attach_file)
                                                <a href="{{ route('editor.file.review', [request()->route('event'), $latestReview->review_id]) }}"
                                                   class="btn btn-sm btn-primary btn-rounded">
                                                    <i class="mdi mdi-pencil align-middle"></i> Download Paper
                                                </a>
                                            @else
                                                <span class="text-muted">No Paper uploaded</span>
                                            @endif
                                        @else
                                            <span class="text-muted">Not yet reviewed</span>
                                        @endif
                                    </div>
                                @endforeach
                            </fieldset>
                            <fieldset>
                                <legend>Decision</legend>
                                <div class="mb-3">
                                    <label for="recommendation" class="form-label">Editor Decision<span
                                            class="text-danger">*</span></label>
                                    <select class="form-select" id="recommendation" name="recommendation" required>
                                        <option value="">-- Select Decision --</option>
                                        <option value="Template Revision">0 = Template Revision</option>
                                        <option value="Decline">1 = Decline</option>
                                        <option value="Major Revision">2 = Major Revision</option>
                                        <option value="Minor Revision">3 = Minor Revision</option>
                                        <option value="Accept">4 = Accept</option>
                                    </select>
                                </div>
                                <div class="mb-3">
                                    <label for="note_for_author" class="form-label">Note for Author<span class="text-danger">*</span></label>
                                    <textarea class="form-control" id="note_for_author" name="note_for_author" rows="4"
                                        placeholder="Write a note for the author..."></textarea>
                                </div>
                                <div class="mb-3">
                                    <label for="similarity" class="form-label">Similarity (%)</label>
                                    <input type="number" step="0.01" min="0" max="100" class="form-control" id="similarity" name="similarity" placeholder="e.g. 12.34">
                                </div>
                            </fieldset>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ route('index.editor.decision', request()->route('event')) }}" class="btn btn-danger">Back</a>
                            <button type="submit" class="btn btn-primary confirm-submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.querySelectorAll(".confirm-submit").forEach(button => {
            button.addEventListener("click", function (e) {
                e.preventDefault();
                const form = this.closest("form");

                Swal.fire({
                    title: "Are you sure you want to submit?",
                    text: "You won't be able to revert this!",
                    icon: "warning",
                    showCancelButton: true,
                    confirmButtonColor: "#3085d6",
                    cancelButtonColor: "#d33",
                    confirmButtonText: "Yes, Submit!",
                    cancelButtonText: "Cancel",
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    </script>
    
@endsection