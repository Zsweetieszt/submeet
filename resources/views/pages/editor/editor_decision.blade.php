@extends('layouts.event')

@section('title', 'Editor Decision - ' . $event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route("dashboard")}}">Home</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route("events") }}">Events</a>
    </li>
    <li class="breadcrumb-item"><a
        href="{{ route("dashboard.event", request()->route('event')) }}">{{$event_name}}</a>
    </li>
    <li class="breadcrumb-item">Editor</li>
    <li class="breadcrumb-item active"><span>Editor Decision</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Editor Decision</h4>
            </div>
            <div class="">
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
                                    <th class="bg-body-secondary">Status</th>
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
                                        <td><span class="badge bg-primary">Round {{ $paper->round }}</span></td>
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
                                            @if ($paper->empty_reviews_count == $paper->assignment_count)
                                                <span>Review Not Started ({{ $paper->assignment_count - $paper->empty_reviews_count }}/{{ $paper->assignment_count }})</span>
                                            @else
                                                <span>Reviewed ({{ $paper->assignment_count - $paper->empty_reviews_count }}/{{ $paper->assignment_count }})</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if ($paper->assignment_count - $paper->empty_reviews_count > 0)
                                                <a href="{{ route('index.create.decision', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                    class="btn btn-sm btn-primary btn-rounded">
                                                    <i class="mdi mdi-pencil align-middle"></i> Decide
                                                </a>
                                            @endif
                                            <a href="{{ route('detail.paper', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                    class="btn btn-sm btn-primary btn-rounded">
                                                    <i class="mdi mdi-pencil align-middle"></i> Detail
                                                </a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script>
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

        $(document).ready(function() {
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
                    { "width": "30%", "targets": 4 },
                    { "width": "15%", "targets": 5 },
                    { "width": "12%", "targets": 6 },
                    { "width": "10%", "targets": 7 }
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
    </script>
    
@endsection