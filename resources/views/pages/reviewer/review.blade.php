@extends('layouts.event')

@section('title', 'Review Paper - ' . $event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route(name: "events") }}">Events</a>
    </li>
    <li class="breadcrumb-item"><a
            href="{{ route("dashboard.event", request()->route('event')) }}">{{$event_name}}</a>
    </li>
    <li class="breadcrumb-item">Reviewer</li>
    <li class="breadcrumb-item"><a href="{{ route('events.reviewer', [request()->route('event')]) }}">Review Paper</a></li>
    <li class="breadcrumb-item active"><span>Review</span></li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Paper Information</h4>
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
                    <a href="{{ route('events.reviewer.check', [request()->route('event'), $paper->paper_sub_id]) }}"
                        class="btn btn-sm btn-primary btn-rounded">
                        <i class="mdi mdi-pencil align-middle"></i> Download Paper
                    </a>
                </div>
            </div>
        </div>
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Review Paper</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form id="reviewForm" class="needs-validation" novalidate
                        action="{{ route('review.submit', [request()->route('event'), $paper->paper_sub_id]) }}"
                        method="post" enctype="multipart/form-data">
                        @csrf
                        {{-- Review Items --}}
                        <fieldset class="border rounded-3 p-3 mb-4">
                            <legend class="float-none w-auto px-3 fs-6 mb-0">Review Items</legend>
                            <div class="table-responsive">
                                <table class="table border mb-0 align-middle">
                                    <thead class="fw-semibold text-nowrap">
                                        <tr class="align-middle">
                                            <th class="bg-body-secondary" style="width:40px;">No</th>
                                            <th class="bg-body-secondary">Item</th>
                                            <th class="bg-body-secondary">Description</th>
                                            <th class="bg-body-secondary" style="width:80px;">Weight (%)</th>
                                            <th class="bg-body-secondary" style="width:220px;">Option <span
                                                    class="text-danger">*</span></th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($reviewItems as $idx => $item)
                                            <tr>
                                                <td class="text-center">{{ $idx + 1 }}</td>
                                                <td>{{ $item->name }}</td>
                                                <td>{{ $item->desc }}</td>
                                                <td>{{ $item->weight }}</td>
                                                <td>
                                                    <select
                                                        class="form-select @error('options.' . $item->review_item_id) is-invalid @enderror"
                                                        name="options[{{ $item->review_item_id }}]" required>
                                                        <option value="" selected disabled>Choose...</option>
                                                        @foreach ($item->options as $option)
                                                            <option value="{{ $option->scale }}"
                                                                {{ old('options.' . $item->review_item_id) == $option->scale ? 'selected' : '' }}>
                                                                {{ $option->scale }} - {{ $option->desc }}
                                                            </option>
                                                        @endforeach
                                                    </select>
                                                    @error('options.' . $item->review_item_id)
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
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
                                <label for="note_for_author" class="form-label">Overall Evaluation <span
                                        class="text-danger">*</span></label>
                                <textarea class="form-control @error('note_for_author') is-invalid @enderror" id="note_for_author"
                                    name="note_for_author" rows="3" required placeholder="Write your overall evaluation for the author">{{ old('note_for_author') }}</textarea>
                                @error('note_for_author')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="recommendation" class="form-label">Recommendation <span
                                        class="text-danger">*</span></label>
                                <select class="form-select @error('recommendation') is-invalid @enderror"
                                    id="recommendation" name="recommendation" required>
                                    <option value="" selected disabled>Choose...</option>
                                    <option value="Accept" {{ old('recommendation') == 'Accept' ? 'selected' : '' }}>Accept
                                    </option>
                                    <option value="Minor Revisions"
                                        {{ old('recommendation') == 'Minor Revisions' ? 'selected' : '' }}>Minor Revisions
                                    </option>
                                    <option value="Major Revisions"
                                        {{ old('recommendation') == 'Major Revisions' ? 'selected' : '' }}>Major Revisions
                                    </option>
                                    <option value="Decline" {{ old('recommendation') == 'Decline' ? 'selected' : '' }}>
                                        Decline</option>
                                </select>
                                @error('recommendation')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="note_for_editor" class="form-label">Note for Editor</label>
                                <textarea class="form-control @error('note_for_editor') is-invalid @enderror" id="note_for_editor"
                                    name="note_for_editor" rows="3" required placeholder="Write your note for the editor" maxlength="3000">{{ old('note_for_editor') }}</textarea>
                                @error('note_for_editor')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="mb-3">
                                <label for="paper_file" class="form-label">Review File</label>
                                <input type="file" class="form-control @error('paper_file') is-invalid @enderror"
                                    id="paper_file" name="paper_file" onchange="previewLogo(event)"
                                    placeholder="Paper File">
                                @error('paper_file')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                                <small class="form-text">Allowed file type: DOC/DOCX. Max. file size: 5
                                    MB.</small>
                            </div>

                        </fieldset>

                        <div class="d-flex justify-content-between mt-4">
                            <a href="{{ url()->previous() }}" class="btn btn-danger">Cancel</a>
                            <button type="submit" class="btn btn-primary confirm-submit">Submit Review</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".confirm-submit").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    let form = this.closest("form");
                    Swal.fire({
                        title: "Are you sure want to Submit?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        confirmButtonText: "Yes, Submit!",
                        cancelButtonText: "No, cancel!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });
        });
    </script>
    
@endsection
