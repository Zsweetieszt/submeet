@extends('layouts.event')

@section('title', 'Evaluate - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route('events') }}">Events</a>
    </li>
    <li class="breadcrumb-item"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item">Editor</li>
    <li class="breadcrumb-item"><a href="{{ route('index.desk.evaluation', request()->route('event')) }}"><span>Desk
                Evaluation</span></a>
    </li>
    <li class="breadcrumb-item active"><span>Evaluate</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Evaluate</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form id="myForm" class="needs-validation" novalidate action="" method="post"
                        enctype="multipart/form-data">
                        @csrf
                        @method('PUT')
                        <div class="row">
                            <fieldset>
                                <legend>Paper</legend>
                                <div class="row align-items-start">
                                    <div class="mb-3">
                                        <label for="title" class="form-label">Title<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('title') is-invalid @enderror"
                                            id="title" maxlength="255" name="title" disabled
                                            value="{{ $paper->title }}" placeholder="Title">
                                    </div>
                                    <div class="mb-3">
                                        <label for="subtitle" class="form-label">Subtitle</label>
                                        <input type="text" class="form-control @error('subtitle') is-invalid @enderror"
                                            id="subtitle" maxlength="255" name="subtitle" value="{{ $paper->subtitle }}"
                                            disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="abstract" class="form-label">Abstract<span
                                                class="text-danger">*</span></label>
                                        <textarea class="form-control @error('abstract') is-invalid @enderror" id="abstract" name="abstract" disabled
                                            placeholder="Abstract max 250 words" style="height: 255px;">{{ $paper->abstract }}</textarea>
                                    </div>
                                    <div class="mb-3">
                                        <label for="topic" class="form-label">Topic</label>
                                        <input type="text" class="form-control @error('topic') is-invalid @enderror"
                                            id="topic" name="topic" disabled
                                            value="{{ old('topic', $paper->topicpapers[0]->topic->topic_name ?? '') }}"
                                            placeholder="Topic">
                                    </div>
                                    <div class="mb-3">
                                        <label for="keywords" class="form-label">Keywords<span
                                                class="text-danger">*</span></label>
                                        <input type="text" class="form-control @error('keywords') is-invalid @enderror"
                                            id="keywords" name="keywords" disabled
                                            value="{{ old('keywords', $paper->keywords) }}" placeholder="Keywords">
                                        @error('keywords')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="paper_file" class="form-label">Paper File</label>
                                        @if ($paper->attach_file)
                                            <div class="mt-2">
                                                <a href="{{ route('editor.check', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                    class="btn btn-primary">Download
                                                </a>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="mb-3">
                                        <label for="authors" class="form-label">Authors<span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control place @error('authors') is-invalid @enderror" id="authors"
                                            name="authors" disabled value="{{ old('authors', $paper->authors) }}"
                                            placeholder="Add authors by typing: Authors Name - Authors Email">
                                    </div>
                                    <div class="mb-3">
                                        <label for="corresponding" class="form-label">Corresponding Author<span
                                                class="text-danger">*</span></label>
                                        <input type="text" name="" id=""
                                            value="{{ $paper->author[$paper->corresponding - 1]->given_name }} {{ $paper->author[$paper->corresponding - 1]->family_name }} - {{ $paper->author[$paper->corresponding - 1]->email }}"
                                            class="form-control @error('corresponding') is-invalid @enderror"
                                            placeholder="Corresponding Author" disabled>
                                    </div>
                                    <div class="mb-3">
                                        <label for="note_for_editor" class="form-label">Note for Editor</label>
                                        <textarea class="form-control @error('note_for_editor') is-invalid @enderror" id="note_for_editor"
                                            name="note_for_editor" disabled placeholder="Note for Editor max 3000 characters" style="height: 255px;"
                                            maxlength="3000">{{ $paper->note_for_editor }}</textarea>
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-danger" data-coreui-toggle="modal"
                                data-coreui-target="#declineModal">Decline</button>
                            <button type="button" class="btn btn-primary" data-coreui-toggle="modal"
                                data-coreui-target="#exampleModal">Assign Reviewer</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="declineModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Decline Paper</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>
                <form action="{{ route('decline.paper', [$event->event_code, $paper->paper_sub_id]) }}" method="post">
                    @csrf
                    @method('PUT')
                    <div class="modal-body">
                        <label for="note">Note for Author<span class="text-danger">*</span></label>
                        <textarea class="form-control @error('note') is-invalid @enderror" id="note" name="note"
                            placeholder="Decline Note max 3000 characters" style="height: 255px;" maxlength="3000">{{ old('note') }}</textarea>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary confirm-submit">Submit</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Assign Reviewer</h5>
                    <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                </div>

                <form action="{{ route('assign.reviewer', [$event->event_code, $paper->paper_sub_id]) }}" method="post">
                    @csrf
                    @method('POST')
                    <div class="modal-body">

                        <label for="reviewer1">Reviewer 1<span class="text-danger">*</span></label>
                        <select id="reviewerSelect1" name="reviewer1" class="form-select mb-2" aria-label="Default select example" required>
                            <option value="" selected>Choose Reviewer 1</option>
                            @foreach ($user as $u)
                                <option value="{{ $u->user_id }}">
                                    {{ $u->user->given_name . ' ' . $u->user->family_name . ' (' . $u->jmlAssignment . ')' . ' - ' . $u->user->email}}
                                </option>
                            @endforeach
                        </select>
                        <div id="badgeContainer1" class="badge-container d-flex align-items-center justify-content-start mb-3 gap-1 flex-wrap">
                        </div>

                        <label for="reviewer2">Reviewer 2</label>
                        <select id="reviewerSelect2" name="reviewer2" class="form-select mb-2" aria-label="Default select example">
                            <option value="" selected>Choose Reviewer 2</option>
                            @foreach ($user as $u)
                                <option value="{{ $u->user_id }}">
                                    {{ $u->user->given_name . ' ' . $u->user->family_name . ' (' . $u->jmlAssignment . ')' . ' - ' . $u->user->email}}
                                </option>
                            @endforeach
                        </select>
                        <div id="badgeContainer2" class="badge-container d-flex align-items-center justify-content-start mb-3 gap-1 flex-wrap">
                        </div>

                        <label for="reviewer3">Reviewer 3</label>
                        <select id="reviewerSelect3" name="reviewer3" class="form-select mb-2" aria-label="Default select example">
                            <option value="" selected>Choose Reviewer 3</option>
                            @foreach ($user as $u)
                                <option value="{{ $u->user_id }}">
                                    {{ $u->user->given_name . ' ' . $u->user->family_name . ' (' . $u->jmlAssignment . ')' . ' - ' . $u->user->email}}
                                </option>
                            @endforeach
                        </select>
                        <div id="badgeContainer3" class="badge-container d-flex align-items-center justify-content-start mb-3 gap-1 flex-wrap">
                        </div>

                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary confirm-submit">Submit</button>
                    </div>
                </form>

            </div>
        </div>
    </div>

    <script>
        const userExpertises = @json($expertises->groupBy('user_id')->map(function($items) {
            return $items->map(function($item) {
                return $item->expertise->expertise_name;
            });
        }));

        document.addEventListener('DOMContentLoaded', function() {
            const select1 = document.getElementById('reviewerSelect1');
            const select2 = document.getElementById('reviewerSelect2');
            const select3 = document.getElementById('reviewerSelect3');
            const badgeContainer1 = document.getElementById('badgeContainer1');
            const badgeContainer2 = document.getElementById('badgeContainer2');
            const badgeContainer3 = document.getElementById('badgeContainer3');

            select1.addEventListener('change', function() {
                const userId = this.value;

                badgeContainer1.innerHTML = '';

                if (userId && userExpertises[userId]) {
                    userExpertises[userId].forEach(expertiseName => {
                        const span = document.createElement('span');
                        span.className = 'badge text-bg-success text-white';
                        span.textContent = expertiseName;
                        badgeContainer1.appendChild(span);
                    });
                }
            });

            select2.addEventListener('change', function() {
                const userId = this.value;

                badgeContainer2.innerHTML = '';

                if (userId && userExpertises[userId]) {
                    userExpertises[userId].forEach(expertiseName => {
                        const span = document.createElement('span');
                        span.className = 'badge text-bg-success text-white';
                        span.textContent = expertiseName;
                        badgeContainer2.appendChild(span);
                    });
                }
            });

            select3.addEventListener('change', function() {
                const userId = this.value;

                badgeContainer3.innerHTML = '';

                if (userId && userExpertises[userId]) {
                    userExpertises[userId].forEach(expertiseName => {
                        const span = document.createElement('span');
                        span.className = 'badge text-bg-success text-white';
                        span.textContent = expertiseName;
                        badgeContainer3.appendChild(span);
                    });
                }
            });
        });
    </script>

    <script>
        var input = document.querySelector('input[name=keywords]');
        new Tagify(input)

        var authors = document.querySelector('input[name=authors]');
        new Tagify(authors, {
            dropdown: {
                enabled: 0,
            }
        })

        document.addEventListener("DOMContentLoaded", function() {

            const modal = document.getElementById('exampleModal');
            const reviewer1Select = modal.querySelector('select[name="reviewer1"]');
            const reviewer2Select = modal.querySelector('select[name="reviewer2"]');
            const reviewer3Select = modal.querySelector('select[name="reviewer3"]');
            const allSelects = [reviewer1Select, reviewer2Select, reviewer3Select];

            const assignedReviewers = @json($paper->assignment->pluck('reviewer_id')->toArray());

            function updateReviewerOptions() {
                const selectedValues = allSelects
                    .map(select => select.value)
                    .filter(value => value !== '');

                allSelects.forEach(currentSelect => {
                    const otherSelectedValues = selectedValues.filter(value => value !== currentSelect.value);

                    currentSelect.querySelectorAll('option').forEach(option => {
                        if (!option.value) return;
                        if (otherSelectedValues.includes(option.value)) {
                            option.style.display = 'none';
                        } else {
                            option.style.display = '';
                        }
                    });
                });
            }

            if (assignedReviewers[0]) {
                reviewer1Select.value = assignedReviewers[0];
            }
            if (assignedReviewers[1]) {
                reviewer2Select.value = assignedReviewers[1];
            }
            if (assignedReviewers[2]) {
                reviewer3Select.value = assignedReviewers[2];
            }

            reviewer1Select.dispatchEvent(new Event('change'));
            reviewer2Select.dispatchEvent(new Event('change'));
            reviewer3Select.dispatchEvent(new Event('change'));

            updateReviewerOptions();

            allSelects.forEach(select => {
                select.addEventListener('change', updateReviewerOptions);
            });

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
