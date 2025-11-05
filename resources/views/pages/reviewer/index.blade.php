@extends('layouts.event')

@section('title', 'Review - ' . $event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event_name }}</a>
    </li>
    <li class="breadcrumb-item">Reviewer</li>
    <li class="breadcrumb-item active"><span>Review Paper</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">Review Paper</h4>
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
                                        <th class="bg-body-secondary">Status</th>
                                        <th class="bg-body-secondary">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @php
                                        $papers = $papers->unique('first_paper_sub_id');
                                    @endphp
                                    @foreach ($papers as $paper)
                                        @php
                                            $paper_detail = $paper->assignment->first();
                                        @endphp
                                        <tr>
                                            <td class="details-control bg-body-secondary" style="cursor:pointer;"></td>
                                            <td class="text-center"></td>
                                            <td class="text-center">
                                                <span title="Current Paper ID: {{ $paper->paper_sub_id }}">
                                                    {{ $paper->first_paper_sub_id }}
                                                </span>
                                            </td>
                                            <td>{{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') }}
                                            </td>
                                            <td>{{ $paper->title }}</td>
                                            <td>
                                                @php
                                                    $my_assignment = $paper->assignment
                                                        ->where('reviewer_id', auth()->user()->user_id)
                                                        ->first();
                                                @endphp
                                                @if ($my_assignment && in_array($my_assignment->assign_id, $reviews))
                                                    <span class="badge rounded-pill text-bg-success text-white">Reviewed</span>
                                                @elseif (count($paper->decisions) > 0)
                                                    <span class="badge rounded-pill text-bg-secondary text-white">Decided</span>
                                                @else
                                                    <span class="badge rounded-pill text-bg-danger text-white">Needs Review</span>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="d-flex flex-column flex-md-row gap-2">
                                                    <a href="{{ route('events.reviewer.check', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                        class="btn btn-sm btn-primary btn-rounded">
                                                        <i class="fas fa-download"></i> Download Paper
                                                    </a>
                                                    @php
                                                        $my_assignment = $paper->assignment
                                                            ->where('reviewer_id', auth()->user()->user_id)
                                                            ->first();
                                                    @endphp
                                                    @if ($my_assignment && in_array($my_assignment->assign_id, $reviews))
                                                        <a href="{{ route('events.reviewer.view', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                            class="btn btn-sm btn-info btn-rounded">
                                                            <i class="fas fa-eye"></i> View
                                                        </a>
                                                    @else
                                                        @if(count($paper->decisions) == 0)
                                                            <a href="{{ route('events.reviewer.review', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                                class="btn btn-sm btn-warning btn-rounded">
                                                                <i class="fas fa-edit"></i> Review
                                                            </a>
                                                        @endif
                                                    @endif
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                            {{-- <a href="{{ route('index.revision.paper', request()->route('event')) }}"
                                class="btn btn-sm btn-primary btn-rounded "><i class="mdi mdi-eye align-middle"></i>
                                Revision Paper</a> --}}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        const rowData = [
            @foreach ($papers as $paper){
                papers: [
                @foreach (($paperRoundHistory[$paper->first_paper_sub_id] ?? []) as $paper)
                    @if($paper->assignment->first()?->reviews->first())
                        {
                        round: @json($paper->round ?? '-'),
                        @php
                            $current_assignment = $paper->assignment
                                ->where('reviewer_id', auth()->user()->user_id)
                                ->first();
                        @endphp
                        reviewed_date: @json($current_assignment?->reviews?->first() ? \Carbon\Carbon::parse($current_assignment->reviews->first()->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-'),
                        reviewed_by: @json($current_assignment?->reviewer ? $current_assignment->reviewer->given_name . ' ' . $current_assignment->reviewer->family_name : '-'),
                        reviews_id: @json($current_assignment?->reviews->first() ? $current_assignment->reviews->first()->review_id : '-'),
                        paper_id: @json($paper->paper_sub_id),
                        revision_id: @json($paper->paper_sub_id), 
                        first_paper_id: @json($paper->first_paper_sub_id),
                        title: @json($paper->title),
                        submission_date: @json(\Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP')),
                        attach_file: @json($paper->paper_file ?? null),
                        @php
                            $current_my_assignment = $paper->assignment
                                ->where('reviewer_id', auth()->user()->user_id)
                                ->first();
                        @endphp
                        @if ($current_my_assignment && in_array($current_my_assignment->assign_id, $reviews))
                            status: 'Reviewed'
                        @elseif (count($paper->decisions) > 0)
                            status: 'Decided'
                        @else
                            status: 'Needs Review'
                        @endif
                        }@if (!$loop->last),@endif
                    @endif
                @endforeach
                ]
            }@if (!$loop->last),@endif
            @endforeach
        ];

        function format(data) {
            let html = `<div class="p-2">`;
            if (data.papers && data.papers.length > 0) {
                html += `
                    <strong>Review Round:</strong>
                    <div class="table-responsive">
                        <table class="table border mb-0">
                            <thead class="fw-semibold text-nowrap">
                                <tr>
                                    <th class="bg-body-secondary">Round</th>
                                    <th class="bg-body-secondary">Paper ID</th>
                                    <th class="bg-body-secondary">Revision ID</th>
                                    <th class="bg-body-secondary">Review ID</th>
                                    <th class="bg-body-secondary">Submission Date</th>
                                    <th class="bg-body-secondary">Reviewed Date</th>
                                    <th class="bg-body-secondary">Reviewed By</th>
                                    <th class="bg-body-secondary">Status</th>
                                    <th class="bg-body-secondary">Action</th>
                                </tr>
                            </thead>
                            <tbody>`;
                data.papers.forEach(function (h) {
                    html += `
                        <tr>
                            <td>${h.round ?? '-'}</td>
                            <td>${h.first_paper_id ?? '-'}</td>
                            <td>${h.revision_id ?? '-'}</td>
                            <td>${h.reviews_id ?? '-'}</td>
                            <td>${h.submission_date ?? '-'}</td>
                            <td>${h.reviewed_date ?? '-'}</td>
                            <td>${h.reviewed_by ?? '-'}</td>
                            <td>
                                ${h.status === 'Reviewed' ? '<span class="badge rounded-pill text-bg-success text-white">Reviewed</span>' : 
                                h.status === 'Decided' ? '<span class="badge rounded-pill text-bg-secondary text-white">Decided</span>' : 
                                '<span class="badge rounded-pill text-bg-danger text-white">Needs Review</span>'}
                            </td>
                            <td>
                                <div class="d-flex flex-column flex-md-row gap-2">
                                    ${`<a href="/{{ request()->route('event') }}/reviewer/check/${h.paper_id}" class="btn btn-sm btn-primary btn-rounded"><i class="fas fa-download"></i> Download File</a>`}
                                    ${`<a href="/{{ request()->route('event') }}/reviewer/view/${h.paper_id}" class="btn btn-sm btn-info btn-rounded"><i class="fas fa-eye"></i> View</a>`}
                                </div>
                            </td>
                        </tr>`;
                });
                html += `</tbody></table></div>`;
            }
            html += '</div>';
            return html;
        }
    </script>

    <script>
        $(document).ready(function () {
            var table = $('#reviewer').DataTable({
                columnDefs: [
                    { targets: [0,1,5,6], orderable: false, searchable: false },
                    {
                        "width": "3%", "targets": 0,
                        "className": 'dt-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": ''
                    },
                    { 
                        width: "5%",
                        targets: 1, 
                        orderable: false, 
                        searchable: false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { width: "7%", targets: 2 },
                    { width: "15%", targets: 3 },
                    { width: "40%", targets: 4 },
                    { width: "10%", targets: 5 },
                    { width: "25%", targets: 6 }
                ],
                autoWidth: false,
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column(1, {page: 'current'}).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }
            });

            $('#reviewer tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var idx = row.index();

                if (row.child.isShown()) {
                    $('div.dt-row-child', row.child()).slideUp(200, function () {
                        row.child.hide();
                        tr.removeClass('shown');
                    });
                } else {
                    var childData = rowData[idx];
                    var childContent = childData && childData.papers && childData.papers.length > 0 
                        ? format(childData) 
                        : '<div class="p-2 d-flex justify-content-center">No reviewed paper yet</div>';
                    
                    row.child('<div class="dt-row-child">' + childContent + '</div>', 'no-padding').show();
                    $('div.dt-row-child', row.child()).hide().slideDown(200);
                    tr.addClass('shown');
                }
            });
        });
    </script>
@endsection