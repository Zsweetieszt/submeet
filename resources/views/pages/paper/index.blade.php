@extends('layouts.event')

@section('title', 'Papers - ' . $eventObj->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route("dashboard")}}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: "events") }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route("dashboard.event", request()->route('event')) }}">{{$eventObj->event_name}}</a>
    </li>
    <li class="breadcrumb-item active"><span>My Papers</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="">
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">My Papers</h4>
                </div>
                <div class="">
                    <div class="card-body px-4">
                        <div class="d-flex justify-content-end">
                            <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
                                @if ($eventObj->event_status == 'Ongoing')
                                    <a href="{{ route('index.submit.paper', request()->route('event')) }}"
                                        class="btn btn-md btn-primary btn-rounded"><i class="mdi mdi-eye align-middle"></i>
                                        Submit Paper</a>
                                @endif
                            </div>
                        </div>
                        <div class="table-responsive">
                            <table class="table border mb-0" id="papers-table">
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
                                        <tr class="align-middle">
                                            <td class="details-control bg-body-secondary" style="cursor:pointer;"></td>
                                            <td class="text-center"></td>
                                            <td class="text-center">
                                                <span title="Current Paper ID: {{ $paper->paper_sub_id }}">
                                                    {{ $paper->first_paper_sub_id }}
                                                </span>
                                            </td>
                                            <td>
                                                <span
                                                    title="{{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->format('M d, Y H:i:s \G\M\TP') }}">
                                                    {{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:sP') }}
                                                </span>
                                            </td>
                                            @if ($paper->subtitle)
                                                <td >{{ $paper->title . ': ' . $paper->subtitle }}</td>
                                            @else   
                                                <td>{{ $paper->title }}</td>
                                            @endif
                                            <td>
                                                @if($paper->first->status == 'Submitted')
                                                    <span class="badge bg-secondary">{{ $paper->first->status }}</span>
                                                @elseif($paper->first->status == 'In Review')
                                                    <span class="badge bg-primary">{{ $paper->first->status }}</span>
                                                @elseif($paper->first->status == 'Revision')
                                                    <span class="badge bg-warning">{{ $paper->first->status }}</span>
                                                @elseif($paper->first->status == 'Accepted')
                                                    <span class="badge bg-success">{{ $paper->first->status }}</span>
                                                @elseif($paper->first->status == 'Declined')
                                                    <span class="badge bg-danger">{{ $paper->first->status }}</span>
                                                @else
                                                    <span class="badge bg-light text-dark">{{ $paper->first->status }}</span>
                                                @endif
                                            </td>
                                            <td>
                                                @if ($paper->first->status !== 'Revision')
                                                    <a href="{{ route('check.paper', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                        class="btn btn-sm btn-primary btn-rounded "><i
                                                            class="mdi mdi-pencil align-middle"></i> Check </a>
                                                    @if ($paper->first->status != 'Declined')
                                                        <a href="{{ route('paper.detail', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                            class="btn btn-sm btn-primary btn-rounded "><i
                                                                class="mdi mdi-pencil align-middle"></i> Detail </a>
                                                    @endif
                                                    @if ($paper->first->status == 'Submitted')
                                                        <a href="{{ route('edit.paper', [request()->route('event', $paper->paper_sub_id), $paper->paper_sub_id]) }}"
                                                            class="btn btn-sm btn-warning btn-rounded "><i
                                                                class="mdi mdi-pencil align-middle"></i> Edit </a>
                                                    @endif
                                                @endif
                                                @if ($paper->first->status == 'Revision' && \Carbon\Carbon::parse($eventObj->revision_start)->lte(now()) && \Carbon\Carbon::parse($eventObj->revision_end)->endOfDay()->gte(now()))
                                                    <a href="{{ route('index.revise.paper', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                        class="btn btn-sm btn-primary btn-warning btn-rounded"><i
                                                            class="mdi mdi-eye align-middle"></i>
                                                        Revise</a>
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
    </div>
    <!-- <script>
            $('#reviewer').DataTable({});
        </script>
         -->
    <script>
        // This data is used to populate the child rows.
        // It requires the $paperHistory variable from your controller.
        const rowData = [
            @foreach ($papers as $paper)
                    {
                    papers: [
                        @foreach (($paperHistory[$paper->first_paper_sub_id] ?? []) as $paperVersion)
                                {
                                round: @json($paperVersion->round),
                                parent_id: @json($paperVersion->first_paper_sub_id),
                                id: @json($paperVersion->paper_sub_id),
                                title: @json($paperVersion->title),
                                subtitle: @json($paperVersion->subtitle),
                                decision: @json($paperVersion->decisions->last()?->decision ?? '-'),
                                submission_date: @json(\Carbon\Carbon::parse($paperVersion->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:sP')),
                            },
                        @endforeach
                        ]
                },
            @endforeach
        ];

        // This function formats the child row content to show paper history.
        function format(data) {
            let html = `<div class="p-2">`;
            if (data.papers && data.papers.length > 0) {
            html += `
                    <h6 class="mb-2"><strong>Paper Submission History</strong></h6>
                    <div class="table-responsive">
                    <table class="table table-sm table-bordered mb-0">
                        <thead class="fw-semibold text-nowrap">
                        <tr>
                            <th class="bg-body-secondary text-center">Round</th>
                            <th class="bg-body-secondary text-center">Paper ID</th>
                            <th class="bg-body-secondary text-center">Revision ID</th>
                            <th class="bg-body-secondary text-center">Submission Date</th>
                            <th class="bg-body-secondary text-center">Paper Title</th>
                            <th class="bg-body-secondary text-center">Decision</th>
                            <th class="bg-body-secondary text-center">Action</th>
                        </tr>
                        </thead>
                        <tbody>`;
            data.papers.forEach(function (p) {
                // Build the detail URL using Blade's route helper for correct event and paper id
                let detailUrl = "{{ route('paper.detail', ['__EVENT__', '__ID__']) }}"
                .replace('__EVENT__', encodeURIComponent('{{ request()->route('event') }}'))
                .replace('__ID__', encodeURIComponent(p.id));
                html += `
                        <tr>
                            <td class="text-center">${p.round ?? '-'}</td>
                            <td class="text-center">${p.parent_id ?? '-'}</td>
                            <td class="text-center">${p.id ?? '-'}</td>
                            <td>${p.submission_date ?? '-'}</td>
                            <td>${p.title ?? '-'}${p.subtitle ? ': ' + p.subtitle : ''}</td>
                            <td class="text-center">
                                ${
                                    (p.decision === 'Accept')
                                        ? '<span class="badge bg-success">Accepted</span>'
                                        : (p.decision === 'Decline')
                                            ? '<span class="badge bg-danger">Declined</span>'
                                            : (p.decision === 'Minor Revision')
                                                ? '<span class="badge bg-warning text-dark">Minor Revision</span>'
                                                : (p.decision === 'Major Revision')
                                                    ? '<span class="badge bg-warning text-dark">Major Revision</span>'
                                                    : (p.decision === 'Template Revision')
                                                        ? '<span class="badge bg-info text-dark">Template Revision</span>'
                                                        : `<span class="badge bg-body-secondary">${p.decision ?? '-'}</span>`
                                }
                            </td>
                            <td class="text-center">
                            ${
                                (p.decision === 'Decline')
                                    ? ''
                                    : `<a href="${detailUrl}"
                                        class="btn btn-sm btn-primary btn-rounded">
                                        <i class="mdi mdi-eye align-middle"></i> Detail
                                    </a>`
                            }
                            </td>
                        </tr>`;
            });
            html += `</tbody></table></div>`;
            } else {
            html += '<strong>No paper history found.</strong>';
            }
            html += '</div>';
            return html;
        }

        $(document).ready(function () {
            var table = $('#papers-table').DataTable({
                "columnDefs": [
                    {
                        "targets": 0,
                        "className": 'dt-control',
                        "orderable": false,
                        "data": null,
                        "defaultContent": '',
                        "width": "3%"
                    },
                    { "orderable": false, "targets": 6 },
                    { 
                        "width": "4%", 
                        "targets": 1,  
                        "orderable": false,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    },
                    { "width": "8%", "targets": 2 },
                    { "width": "15%", "targets": 3 },
                    { "width": "42%", "targets": 4 },
                    { "width": "8%", "targets": 5 },
                    { "width": "15%", "targets": 6, "searchable": false }
                ],
                "autoWidth": false,
                "order": [[1, 'asc']],
                "drawCallback": function(settings) {
                    var api = this.api();
                    api.column(1, {page: 'current'}).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }
            });

            $('#papers-table tbody').on('click', 'td.details-control', function () {
                var tr = $(this).closest('tr');
                var row = table.row(tr);
                var idx = row.index();

                // Tutup semua child row yang sedang terbuka, kecuali yang diklik
                $('#papers-table tbody tr.shown').each(function () {
                    var openTr = $(this);
                    if (!openTr.is(tr)) {
                        var openRow = table.row(openTr);
                        $('div.dt-row-child', openRow.child()).slideUp(200, function() {
                            openRow.child.hide();
                            openTr.removeClass('shown');
                        });
                    }
                });
            
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