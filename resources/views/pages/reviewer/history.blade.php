@extends('layouts.event')

@section('title', 'Review History - ' . $event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item active"><a href="{{ route("events") }}">Events</a></li>
    <li class="breadcrumb-item active"><a href="{{ route("dashboard.event", request()->route('event')) }}">{{$event_name}}</a></li>
    <li class="breadcrumb-item">Reviewer</li>
    <li class="breadcrumb-item active"><span>Review History</span></li>
@endsection

@section('content')
<div class="container-fluid px-4">
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">Review History</h4>
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
                                <th class="bg-body-secondary">Paper Title</th>
                                <th class="bg-body-secondary">Last Recommendation</th>
                                <th class="bg-body-secondary">Last Reviewed Date</th>
                                <th class="bg-body-secondary">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($reviewHistory as $paperId => $history)
                                @php
                                    $latestReview = $history['reviews'][0] ?? null;
                                @endphp
                                <tr class="align-middle">
                                    <td class="details-control bg-body-secondary" style="cursor:pointer;"></td>
                                    <td class="text-center"></td>
                                    <td class="text-center">{{ $paperId }}</td>
                                    <td>{{ $history['paper_title'] }}</td>
                                    <td>
                                        @if ($latestReview)
                                            @php
                                                $badgeClass = match ($latestReview['review']->recommendation) {
                                                    'Accept' => 'bg-success',
                                                    'Minor Revisions' => 'bg-warning text-dark',
                                                    'Major Revisions' => 'bg-orange text-white',
                                                    'Decline' => 'bg-danger',
                                                    default => 'bg-secondary',
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $latestReview['review']->recommendation }}</span>
                                        @else
                                            <span class="badge bg-secondary">-</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if ($latestReview && $latestReview['review']->created_at)
                                            {{ \Carbon\Carbon::parse($latestReview['review']->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        @if ($latestReview)
                                            <a href="{{ route('events.reviewer.view', [$eventObj->event_code, $latestReview['paper']->paper_sub_id]) }}" class="btn btn-primary btn-sm">Detail</a>
                                        @endif
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

{{-- Data for expandable rows --}}
<script>
    const rowData = [
        @foreach ($reviewHistory as $paperId => $history)
        {
            history: [
                @foreach ($history['reviews'] as $reviewData)
                {
                    round: @json($reviewData['round']),
                    recommendation: @json($reviewData['review']->recommendation),
                    note_for_author: @json($reviewData['review']->note_for_author),
                    note_for_editor: @json($reviewData['review']->note_for_editor),
                    attach_file: @json($reviewData['review']->attach_file),
                    created_at: @json($reviewData['review']->created_at ? \Carbon\Carbon::parse($reviewData['review']->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-'),
                    reviewer_name: @json(optional($reviewData['review']->reviewer)->given_name . ' ' . optional($reviewData['review']->reviewer)->family_name),
                    paper_id: @json($paperId),
                    paper_title: @json($history['paper_title']),
                    paper_sub_id: @json($reviewData['paper']->paper_sub_id),
                },
                @endforeach
            ]
        },
        @endforeach
    ];

    function format(data) {
        let html = `<div class="p-2">`;
        if (data.history && data.history.length > 0) {
            html += `
                <strong>Review History</strong>
                <div class="table-responsive">
                    <table class="table border mb-0">
                        <thead class="fw-semibold text-nowrap">
                            <tr>
                                <th class="bg-body-secondary">Round</th>
                                <th class="bg-body-secondary">Paper ID</th>
                                <th class="bg-body-secondary">Revision ID</th>
                                <th class="bg-body-secondary">Recommendation</th>
                                <th class="bg-body-secondary">Note for Author</th>
                                <th class="bg-body-secondary">Note for Editor</th>
                                <th class="bg-body-secondary">Review File</th>
                                <th class="bg-body-secondary">Reviewed Date</th>
                            </tr>
                        </thead>
                        <tbody>`;
            data.history.forEach(function(h) {
                html += `
                    <tr>
                        <td>${h.round ?? '-'}</td>
                        <td class="text-center">${h.paper_id ?? '-'}</td>
                        <td class="text-center">${h.paper_sub_id ?? '-'}</td>
                        <td>
                            ${
                                h.recommendation
                                    ? `<span class="badge ${
                                        h.recommendation === 'Accept' ? 'bg-success'
                                        : h.recommendation === 'Minor Revisions' ? 'bg-warning text-dark'
                                        : h.recommendation === 'Major Revisions' ? 'bg-orange text-white'
                                        : h.recommendation === 'Decline' ? 'bg-danger'
                                        : 'bg-secondary'
                                    }">${h.recommendation}</span>`
                                    : '<span class="badge bg-secondary">-</span>'
                            }
                        </td>
                        <td>${h.note_for_author ?? '-'}</td>
                        <td>${h.note_for_editor ?? '-'}</td>
                        <td>
                            ${h.attach_file ? `<a href="/storage/${h.attach_file}" class="btn btn-sm btn-primary" target="_blank"><i class="fas fa-download me-1"></i>Download Review</a>` : '-'}
                        </td>
                        <td>${h.created_at ?? '-'}</td>
                    </tr>`;
            });
            html += `</tbody></table></div>`;
        }
        html += '</div>';
        return html;
    }

    $(document).ready(function() {
        var table = $('#reviewer').DataTable({
            "columnDefs": [
                { "orderable": false, "searchable":false, "targets": [0,1,5,6] },
                { "width": "3%", "targets": 0,
                    "className": 'dt-control',
                    "orderable": false,
                    "data": null,
                    "defaultContent": ''
                },    // expand
                { 
                    "width": "5%",
                    "targets": 1,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },    // No.
                { "width": "8%", "targets": 2 },   // ID Paper
                { "width": "49%", "targets": 3 },   // Title
                { "width": "10%", "targets": 4 },   // Recommendation
                { "width": "15%", "targets": 5 },    // Last Reviewed Date
                { "width": "10%", "targets": 6 }    // Action
            ],
            "autoWidth": false,
            "order": [[1, 'asc']],
            drawCallback: function(settings) {
                var api = this.api();
                api.column(1, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });

        // Add event listener for opening and closing details
        $('#reviewer tbody').on('click', 'td.details-control', function () {
            var tr = $(this).closest('tr');
            var row = table.row( tr );
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

<style>
    .bg-orange {
        background-color: #fd7e14 !important;
    }
</style>
@endsection