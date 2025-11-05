@extends('layouts.event')
@section('title', 'Revision Paper')
@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
    <li class="breadcrumb-item"><a href="{{ route('events') }}">Events</a></li>
    <li class="breadcrumb-item"><a href="{{ route('dashboard.event', request()->route('event')) }}">{{$event->event_name}}</a></li>
    <li class="breadcrumb-item active"><span>Revision Paper</span></li>
@endsection

@section('content')
<div class="container-fluid px-4" style="margin-bottom: 2rem;">
    <div class="card mb-4">
        <div class="card-header">
            <h4 class="card-title mb-0">Revision Paper</h4>
        </div>
        <div class="">
            <div class="card-body px-4">
                <div class="table-responsive">
                    <table class="table border mb-0" id="revisionTable">
                        <thead class="fw-semibold text-nowrap">
                            <tr class="align-middle">
                                <th class="bg-body-secondary">No.</th>
                                <th class="bg-body-secondary">Paper ID</th>
                                <th class="bg-body-secondary">Revision ID</th>
                                <th class="bg-body-secondary">Paper Title</th>
                                <th class="bg-body-secondary">Author(s)</th>
                                <th class="bg-body-secondary">Email</th>
                                <th class="bg-body-secondary">Current Round</th>
                                <th class="bg-body-secondary">Decision Type</th>
                                <th class="bg-body-secondary">Decision Date</th>
                                <th class="bg-body-secondary">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($papers as $index => $paper)
                                <tr class="align-middle">
                                    <td></td>
                                    <td>
                                        <span title="Current Paper ID: {{ $paper->paper_sub_id }}">
                                            {{ $paper->first_paper_sub_id }}
                                        </span>
                                    </td>
                                    <td>{{ $paper->paper_sub_id }}</td>
                                    <td>{{ Str::limit($paper->title, 50) }}</td>
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
                                        {{ $paper->user->email }}
                                    </td>
                                    <td>
                                        <span class="badge bg-info">Round {{ $paper->round }}</span>
                                    </td>
                                    <td>
                                        @if($paper->latest_decision)
                                            <span class="badge bg-warning">
                                                {{ $paper->latest_decision->decision }}
                                            </span>
                                        @else
                                            <span class="badge bg-secondary">No Decision</span>
                                        @endif
                                    </td>
                                    <td>
                                        @if($paper->latest_decision)
                                            {{ $paper->latest_decision->created_at ? \Carbon\Carbon::parse($paper->latest_decision->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') : '-' }}
                                        @else
                                            -
                                        @endif
                                    </td>
                                    <td>
                                        <div class="btn-group" role="group">
                                            <a href="{{ route('view.revision.paper', [request()->route('event'), $paper->paper_sub_id]) }}" 
                                                class="btn btn-sm btn-primary btn-rounded ">
                                                <i class="mdi mdi-pencil align-middle"></i> Detail 
                                            </a>
                                            @if($paper->has_new_revision)
                                                <a href="{{ route('index.create.decision', [request()->route('event'), $paper->paper_sub_id]) }}" 
                                                class="btn btn-sm btn-success">
                                                    <i class="cil-task"></i>Make New Decision
                                                </a>
                                            @endif
                                        </div>
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
        $(document).ready(function() {
            $('#revisionTable').DataTable({
                responsive: true,
                order: [[7, 'desc']],
                columnDefs: [
                    { orderable: false, searchable: false, targets: [0,9] },
                    { 
                        targets: 0,
                        render: function (data, type, row, meta) {
                            return meta.row + 1;
                        }
                    }
                ],
                drawCallback: function(settings) {
                    var api = this.api();
                    api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                        cell.innerHTML = i + 1;
                    });
                }
            });
        });
    </script>
@endsection