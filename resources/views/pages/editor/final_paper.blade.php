@extends('layouts.event')

@section('title', 'Final Paper - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route("dashboard")}}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route("events") }}">Event</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route("dashboard.event", request()->route('event')) }}">{{$event->event_name}}</a>
    </li>
    <li class="breadcrumb-item">Editor</li>
    <li class="breadcrumb-item active"><span>Final Paper</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Final Paper</h4>
            </div>
            <div class="">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table border mb-0" id="reviewer">
                            <thead class="fw-semibold text-nowrap">
                                <tr class="align-middle">
                                    <th class="bg-body-secondary"></th>
                                    <th class="bg-body-secondary">No.</th>
                                    <th class="bg-body-secondary">Paper ID</th>
                                    <th class="bg-body-secondary">Paper Title</th>
                                    <th class="bg-body-secondary">Author(s)</th>
                                    <th class="bg-body-secondary">Final Decision Date</th>
                                    <th class="bg-body-secondary">Status</th>
                                    <th class="bg-body-secondary">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($decisions as $decision)
                                    <tr class="align-middle">
                                        <td class="details-control bg-body-secondary" style="cursor:pointer;"></td>
                                        <td class="text-center"></td>
                                        <td class="text-center">
                                            <span 
                                                title="Current Paper ID: {{ $decision->paper->paper_sub_id }}"
                                            >
                                                {{ $decision->paper->first_paper_sub_id }}
                                            </span>
                                        </td>
                                        <td>{{ $decision->paper->title }}</td>
                                        <td>
                                            @php
                                                $authors = $decision->paper->author; // assuming this is an array or collection
                                                $names = collect($authors)->map(function ($author) {
                                                    return $author->given_name . ' ' . $author->family_name;
                                                })->toArray();
                                                $allNames = implode(', ', $names);
                                                $displayNames = Str::limit($allNames, 50, '...');
                                            @endphp
                                            <span title="{{ $allNames }}">{{ $displayNames }}</span>
                                        </td>
                                        <td>
                                            @if ($decision->created_at)
                                                {{ \Carbon\Carbon::parse($decision->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') }}
                                            @else
                                                -
                                            @endif
                                        </td>
                                        <td>
                                            @if ($decision->firstPaper->status == 'Accepted')
                                                <span class="badge bg-success">Accepted</span>
                                            @elseif ($decision->firstPaper->status == 'Declined')
                                                <span class="badge bg-danger">Declined</span>
                                            @endif
                                        </td>
                                        <td>
                                            <a href="{{ route('detail.final', ['event' => $event->event_code, 'paper' => $decision->paper->paper_sub_id]) }}" class="btn btn-primary btn-sm">Detail</a>
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
        const rowData = [
            @foreach ($decisions as $decision)
            {
                history: [
                    @foreach (($history[$decision->first_paper_sub_id] ?? []) as $hist)
                    {
                        decision: @json($hist->decision),
                        note_for_author: @json($hist->note_for_author),
                        editor_name: @json(optional($hist->editor)->given_name . ' ' . optional($hist->editor)->family_name),
                        created_at: @json($hist->created_at ? \Carbon\Carbon::parse($hist->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-'),
                        round: @json(optional($hist->paper)->round),
                        title: @json(optional($hist->paper)->title),
                        paper_id: @json($decision->first_paper_sub_id),
                        paper_sub_id: @json(optional($hist->paper)->paper_sub_id),
                        paper: {
                            similarity: @json($hist->paper->similarity ?? '-'),
                        }
                    },
                    @endforeach
                ]
            },
            @endforeach
        ];

        function format (data) {
            let html = `<div class=\"p-2\">`;
            if (data.history && data.history.length > 0) {
                html += `
                    <strong>Decision History</strong>
                    <div class=\"table-responsive\">
                        <table class=\"table border mb-0\">
                            <thead class=\"fw-semibold text-nowrap\">
                                <tr>
                                    <th class=\"bg-body-secondary\">Round</th>
                                    <th class="bg-body-secondary">Paper ID</th>
                                    <th class="bg-body-secondary">Revision ID</th>
                                    <th class=\"bg-body-secondary\">Paper Title</th>
                                    <th class=\"bg-body-secondary\">Decision</th>
                                    <th class=\"bg-body-secondary\">Note for Editor</th>
                                    <th class=\"bg-body-secondary\">Similarity (%)</th>
                                    <th class=\"bg-body-secondary\">Editor</th>
                                    <th class=\"bg-body-secondary\">Created At</th>
                                </tr>
                            </thead>
                            <tbody>`;
                data.history.forEach(function(h) {
                    html += `
                        <tr>
                            <td>${h.round ?? '-'}</td>
                            <td class="text-center">${h.paper_id ?? '-'}</td>
                            <td class="text-center">${h.paper_sub_id ?? '-'}</td>
                            <td>${h.title ?? '-'}</td>
                            <td>
                                ${
                                    h.decision
                                        ? `<span class="badge ${
                                            h.decision === 'Accept' ? 'bg-success'
                                            : h.decision === 'Minor Revisions' ? 'bg-warning text-dark'
                                            : h.decision === 'Major Revisions' ? 'bg-orange text-white'
                                            : h.decision === 'Decline' ? 'bg-danger'
                                            : 'bg-secondary'
                                        }">${h.decision}</span>`
                                        : '<span class="badge bg-secondary">-</span>'
                                }
                            </td>
                            <td>${h.note_for_author ?? '-'}</td>
                            <td>${h.paper.similarity ?? '-'}</td>
                            <td>${h.editor_name ?? '-'}</td>
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
                    { "orderable": false, "searchable": false, "targets": [0,1,7] },
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
                    },    
                    { "width": "10%", "targets": 2 },   
                    { "width": "55%", "targets": 3 },   
                    { "width": "10%", "targets": 4 },   
                    { "width": "10%", "targets": 5 }    
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