@extends('layouts.event')

@section('title', 'Desk Evaluation - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('events') }}">Events</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a></li>
    <li class="breadcrumb-item">Editor</li>
    <li class="breadcrumb-item active"><span>Reviewer Assignment</span></li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Assign Reviewer</h4>
            </div>
            <div class="card-body px-4">
                <div class="table-responsive">
                    <table class="table border mb-0" id="reviewer">
                        <thead class="fw-semibold text-nowrap">
                            <tr class="align-middle">
                                <th class="bg-body-secondary"></th>
                                <th class="bg-body-secondary">No.</th>
                                <th class="bg-body-secondary">Paper ID</th>
                                <th class="bg-body-secondary">Submission Date</th>
                                <th class="bg-body-secondary">Paper Title</th>
                                <th class="bg-body-secondary">Round</th>
                                <th class="bg-body-secondary">Author(s)</th>
                                <th class="bg-body-secondary">Reviewer(s)</th>
                                <th class="bg-body-secondary">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($papers as $paper)
                               <tr>
                                    <td class="details-control bg-body-secondary" style="cursor:pointer;"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">
                                        <span title="Current Paper ID: {{ $paper->paper_sub_id }}">
                                            {{ $paper->first_paper_sub_id }}
                                        </span>
                                    </td>
                                    <td>{{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') }}</td>
                                    <td>{{ $paper->title }}</td>
                                    <td class="text-center">{{ $paper->round }}</td>
                                    <td>
                                        @php
                                            $authors = $paper->author; // assuming this is an array or collection
                                            $names = collect($authors)->map(function ($author) {
                                                return $author->given_name . ' ' . $author->family_name;
                                            })->toArray();
                                            $allNames = implode(', ', $names);
                                            $displayNames = Str::limit($allNames, 50, '...');
                                        @endphp
                                        <span title="{{ $allNames }}">{{ $displayNames }}</span>
                                    </td>
                                    <td>
                                        @if(count($paper->assignment) > 0)
                                            @php
                                                $reviewerNames = [];
                                                foreach ($paper->assignment as $assignment) {
                                                    $reviewerNames[] = $assignment->reviewer->given_name . ' ' . $assignment->reviewer->family_name;
                                                }
                                            @endphp
                                            {{ implode(', ', $reviewerNames) }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <button type="button" class="btn btn-primary btn-sm assign-reviewer-btn"
                                            data-coreui-toggle="modal"
                                            data-coreui-target="#assignReviewerModal"
                                            data-paper-id="{{ $paper->paper_sub_id }}"
                                            {{-- START: MODIFICATION - Add data attribute with assigned reviewer IDs --}}
                                            data-assigned-reviewers="{{ json_encode($paper->assignment->pluck('reviewer_id')) }}">
                                            {{-- END: MODIFICATION --}}
                                            Assign Reviewer
                                        </button>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="assignReviewerModal" tabindex="-1" aria-labelledby="assignReviewerModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="assignReviewerForm" method="post">
                    @csrf
                    @method('POST')
                    @php
                        $reviewer_ids = $user->pluck('user_id')->toArray();
                        $author_ids = [];
                        if (isset($paper) && $paper->author) {
                            $author_ids = $paper->author->pluck('user_id')->toArray();
                        }
                        // Exclude reviewers who are authors of this paper
                        $conflicted_reviewer_ids = array_intersect($reviewer_ids, $author_ids);
                        // dd($conflicted_reviewer_ids);
                        if (!empty($conflicted_reviewer_ids)) {
                            $user = $user->whereNotIn('user_id', $conflicted_reviewer_ids);
                        }
                    @endphp
                    <input type="hidden" name="paper_id" id="modal_paper_id" value="">
                    <div class="modal-header">
                        <h5 class="modal-title" id="assignReviewerModalLabel">Assign Reviewer</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <label for="reviewer1">Reviewer 1<span class="text-danger">*</span></label>
                        <select id="reviewerSelect1" name="reviewer1" class="form-select mb-2" aria-label="Default select example" required>
                            <option value="" selected>Choose Reviewer 1</option>
                            @foreach ($user as $u)
                            <option value="{{ $u->user_id }}">{{ $u->user->given_name . ' ' . $u->user->family_name . ' (' . $u->jmlAssignment . ')' . ' - ' . $u->user->email}}</option>
                            @endforeach
                        </select>
                        <div id="badgeContainer1" class="badge-container d-flex align-items-center justify-content-start mb-3 gap-1 flex-wrap">
                        </div>

                        <label for="reviewer2">Reviewer 2</label>
                        <select id="reviewerSelect2" name="reviewer2" class="form-select mb-2" aria-label="Default select example">
                            <option value="" selected>Choose Reviewer 2</option>
                            @foreach ($user as $u)
                            <option value="{{ $u->user_id }}">{{ $u->user->given_name . ' ' . $u->user->family_name . ' (' . $u->jmlAssignment . ')' . ' - ' . $u->user->email}}</option>
                            @endforeach
                        </select>
                        <div id="badgeContainer2" class="badge-container d-flex align-items-center justify-content-start mb-3 gap-1 flex-wrap">
                        </div>

                        <label for="reviewer3">Reviewer 3</label>
                        <select id="reviewerSelect3" name="reviewer3" class="form-select mb-2" aria-label="Default select example">
                            <option value="" selected>Choose Reviewer 3</option>
                            @foreach ($user as $u)
                            <option value="{{ $u->user_id }}">{{ $u->user->given_name . ' ' . $u->user->family_name . ' (' . $u->jmlAssignment . ')' . ' - ' . $u->user->email}}</option>
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
        document.addEventListener("DOMContentLoaded", function () {
            const form = document.getElementById('assignReviewerForm');
            const paperIdInputElement = document.getElementById('modal_paper_id');
            const reviewer1Select = form.querySelector('select[name="reviewer1"]');
            const reviewer2Select = form.querySelector('select[name="reviewer2"]');
            const reviewer3Select = form.querySelector('select[name="reviewer3"]');
            const allSelects = [reviewer1Select, reviewer2Select, reviewer3Select];

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
            allSelects.forEach(select => {
                select.addEventListener('change', updateReviewerOptions);
            });

            document.querySelectorAll('.assign-reviewer-btn').forEach(function (btn) {
                btn.addEventListener('click', function () {
                    const paperId = this.getAttribute('data-paper-id');
                    const assignedReviewers = JSON.parse(this.getAttribute('data-assigned-reviewers') || '[]');
                    
                    reviewer1Select.value = "";
                    reviewer2Select.value = "";
                    reviewer3Select.value = "";
                    
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

                    paperIdInputElement.value = paperId;
                    form.action = "{{ route('assign.reviewer', [$event->event_code, 'PAPER_ID_PLACEHOLDER']) }}".replace('PAPER_ID_PLACEHOLDER', paperId);
                });
            });

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

            var table = $('#reviewer').DataTable({
                "columnDefs": [
                    {
                        "targets": 0,
                        "className": 'dt-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": '',
                        "width": "3%"
                    },
                    { "orderable": false, "searchable": false, "targets": [0,1,8] },
                    { 
                        "width": "5%", 
                        "targets": 1,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        } 
                    },
                    { "width": "10%", "targets": 2 },
                    { "width": "15%", "targets": 3 },
                    { "width": "25%", "targets": 4 },
                    { "width": "5%", "targets": 5 },
                    { "width": "15%", "targets": 6 },
                    { "width": "10%", "targets": 7 },
                    { "width": "12%", "targets": 8 }
                ],
                autoWidth: false,
                "order": [[1, 'asc']],
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }
            });

            $('#reviewer tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var idx = row.index();

                if (row.child.isShown()) {
                    $('div.dt-row-child', row.child()).slideUp(200, function() {
                        row.child.hide();
                        tr.removeClass('shown');
                    });
                } else {
                    row.child('<div class="dt-row-child">' + format(rowData[idx]) + '</div>', 'no-padding').show();
                    $('div.dt-row-child', row.child()).hide().slideDown(200);
                    tr.addClass('shown');
                }
            });
        });

        function formatKeywords(keywords) {
            if (!keywords) return '';
            
            try {
                if (typeof keywords === 'string' && keywords.startsWith('[') && keywords.endsWith(']')) {
                    const keywordArray = JSON.parse(keywords);
                    return keywordArray.map(k => k.value || k).join(', ');
                }
                
                if (typeof keywords === 'string') {
                    return keywords;
                }
                
                if (Array.isArray(keywords)) {
                    return keywords.map(k => k.value || k).join(', ');
                }
                
                return keywords.toString();
            } catch (e) {
                console.log('Error parsing keywords:', e);
                return keywords;
            }
        }

        const rowData = [
            @foreach ($papers as $paper)
            {
                paper: {
                    id: @json($paper->paper_sub_id),
                    first_id: @json($paper->first_paper_sub_id),
                    title: @json($paper->title),
                    subtitle: @json($paper->subtitle ?? ''),
                    abstract: @json($paper->abstract ?? ''),
                    keywords: @json($paper->keywords ?? ''),
                    similarity: @json($paper->similarity ?? '-'),
                    round: @json($paper->round ?? '-'),
                    note_for_editor: @json($paper->note_for_editor ?? ''),
                    submission_date: @json(\Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP')),
                    authors: [
                        @foreach($paper->author as $author)
                        {
                            given_name: @json($author->given_name),
                            family_name: @json($author->family_name),
                            email: @json($author->email ?? ''),
                            affiliation: @json($author->affiliation ?? ''),
                            country: @json($author->country->country_name ?? ''),
                            is_corresponding: @json($author->is_corresponding ?? 0),
                            order: @json($author->order ?? 1),
                        },
                        @endforeach
                    ]
                },
                reviews: [
                    @foreach($paper->assignment as $assignment)
                    @php
                        $latestReview = $assignment->reviews->sortByDesc('created_at')->first();
                    @endphp
                    {
                        reviewer_order: @json($assignment->order),
                        reviewer_name: @json(optional($assignment->reviewer)->given_name . ' ' . optional($assignment->reviewer)->family_name),
                        recommendation: @json($latestReview->recommendation ?? 'Not yet reviewed'),
                        note_for_author: @json($latestReview->note_for_author ?? 'Not yet reviewed'),
                        note_for_editor: @json($latestReview->note_for_editor ?? 'Not yet reviewed'),
                        review_date: @json($latestReview ? \Carbon\Carbon::parse($latestReview->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : 'Not yet reviewed'),
                        criteria: [
                            @if($latestReview && $latestReview->criteria)
                            @foreach($latestReview->criteria as $criteria)
                            {
                                name: @json($criteria->criteria_name),
                                score: @json($criteria->pivot->score ?? '-'),
                                comment: @json($criteria->pivot->comment ?? '-')
                            },
                            @endforeach
                            @endif
                        ]
                    },
                    @endforeach
                ]
            },
            @endforeach
        ];

        function format(data) {
            let html = `<div class="p-2">`;
            
            html += `
                <div class="mb-3">
                    <strong>Paper Details:</strong>
                    <div class="table-responsive">
                        <table class="table border mb-0">
                            <thead class="fw-semibold text-nowrap">
                                <tr>
                                    <th class="bg-body-secondary">Paper ID</th>
                                    <th class="bg-body-secondary">Author Name</th>
                                    <th class="bg-body-secondary">Email</th>
                                    <th class="bg-body-secondary">Paper Title</th>
                                    <th class="bg-body-secondary">Round</th>
                                    <th class="bg-body-secondary">Subtitle</th>
                                    <th class="bg-body-secondary">Keywords</th>
                                    <th class="bg-body-secondary">Submission Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>${data.paper.first_id} <small class="text-muted">(Current: ${data.paper.id})</small></td>
                                    <td>
                                        ${data.paper.authors.map(author => author.given_name + ' ' + author.family_name).join(', ')}
                                    </td>
                                    <td>
                                        ${data.paper.authors.map(author => author.email || '-').join(', ')}
                                    </td>
                                    <td style="max-width: 270px;">
                                        <div title="${data.paper.title}" style="max-height: 100px; overflow-y: auto;">
                                            ${data.paper.title}
                                        </div>
                                    </td>
                                    <td class="text-center">${data.paper.round}</td>
                                    <td style="max-width: 270px;">
                                        <div title="${data.paper.subtitle || '-'}" style="max-height: 100px; overflow-y: auto;">
                                            ${data.paper.subtitle || '-'}
                                        </div>
                                    </td>
                                    <td style="max-width: 270px;">
                                        <div style="max-height: 100px; overflow-y: auto;">
                                            ${formatKeywords(data.paper.keywords) || '-'}
                                        </div>
                                    </td>
                                    <td>${data.paper.submission_date}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>`;

            // Abstract
            if (data.paper.abstract) {
                html += `
                    <div class="mb-3">
                        <strong>Abstract:</strong>
                        <div class="bg-body-secondary p-2 rounded border" style="max-height: 180px; overflow-y: auto;">
                            ${data.paper.abstract}
                        </div>
                    </div>`;
            }

            // Note for Editor
            if (data.paper.note_for_editor) {
                html += `
                    <div class="mb-3">
                        <strong>Note for Editor from Author:</strong>
                        <div class="bg-body-secondary p-2 rounded border" style="max-height: 180px; overflow-y: auto;">
                            ${data.paper.note_for_editor}
                        </div>
                    </div>`;
            }

            // Reviews Information
            if (data.reviews && data.reviews.length > 0) {
                html += `
                    <div class="mb-3">
                        <strong>Reviews & Note for Editor:</strong>
                        <div class="table-responsive">
                            <table class="table border mb-0">
                                <thead class="fw-semibold text-nowrap">
                                    <tr>
                                        <th class="bg-body-secondary">Reviewer</th>
                                        <th class="bg-body-secondary">Recommendation</th>
                                        <th class="bg-body-secondary">Note for Author</th>
                                        <th class="bg-body-secondary">Note for Editor</th>
                                        <th class="bg-body-secondary">Review Date</th>
                                    </tr>
                                </thead>
                                <tbody>`;
                
                data.reviews.forEach(function(review) {
                    html += `
                        <tr style="height: 120px;">
                            <td class="align-top">Reviewer ${review.reviewer_order}<br><small>${review.reviewer_name}</small></td>
                            <td class="align-top"><span class="badge bg-primary">${review.recommendation}</span></td>
                            <td class="align-top"><div style="max-height: 100px; overflow-y: auto; font-size: 0.9em; min-height: 80px;">${review.note_for_author}</div></td>
                            <td class="align-top"><div class="bg-body-secondary p-2 rounded border" style="max-height: 100px; overflow-y: auto; font-size: 0.9em; min-height: 80px;"><strong>${review.note_for_editor}</strong></div></td>
                            <td class="align-top"><small>${review.review_date}</small></td>
                        </tr>`;
                });
                html += `</tbody></table></div></div>`;
            }
            
            html += '</div>';
            return html;
        }
    </script>

@endsection