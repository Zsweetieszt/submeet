@extends('layouts.event')

@section('title', 'Desk Evaluation - ' . $event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route("dashboard")}}">Home</a>
    </li>
    <li class="breadcrumb-item"><a href="{{ route("events") }}">Events</a>
    </li>
    <li class="breadcrumb-item"><a
            href="{{ route("dashboard.event", request()->route('event')) }}">{{$event_name}}</a>
    </li>
    <li class="breadcrumb-item">Editor</li>
    <li class="breadcrumb-item active"><span>Desk Evaluation</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <div class="d-flex justify-content-between">
                    <div>
                        <h4 class="card-title mb-0">Desk Evaluation</h4>
                    </div>
                </div>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table border mb-0" id="reviewer">
                            <thead class="fw-semibold text-nowrap">
                                <tr class="align-middle">
                                    <th class="bg-body-secondary">No.</th>
                                    <th class="bg-body-secondary">Paper ID</th>
                                    <th class="bg-body-secondary">Submission Date</th>
                                    <th class="bg-body-secondary">Paper Title</th>
                                    <th class="bg-body-secondary">Author(s)</th>
                                    <th class="bg-body-secondary">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($papers as $paper)
                                    <tr>
                                        <td class="text-center"></td>
                                        <td class="text-center">
                                            <span title="Current Paper ID: {{ $paper->paper_sub_id }}">
                                                {{ $paper->first_paper_sub_id }}
                                            </span>
                                        </td>
                                        <td>{{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') }}</td>
                                        <td>{{ $paper->title }}</td>
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
                                            <a href="{{ route('view.paper', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                class="btn btn-sm btn-primary btn-rounded ">Evaluate</a>
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
        $('#reviewer').DataTable({
            "columnDefs": [
                {
                    "targets": [0, 5],
                    "orderable": false,
                    "searchable": false
                },
                { 
                    "width": "5%",
                    "targets": 0,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },    
                { "width": "10%", "targets": 1 },   
                { "width": "15%", "targets": 2 },   
                { "width": "40%", "targets": 3 },   
                { "width": "20%", "targets": 4 },   
                { "width": "10%", "targets": 5 }    
            ],
            "autoWidth": false,
            drawCallback: function(settings) {
                var api = this.api();
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });
    </script>
    
@endsection