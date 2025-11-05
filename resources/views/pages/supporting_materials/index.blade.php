@extends('layouts.event')

@section('title', 'Papers - ' . $event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><span>Supporting Materials</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="">
            <!-- /.row-->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">Supporting Materials</h4>
                </div>
                <div class="">
                    <div class="card-body px-4">
                        <div class="table-responsive">
                            <table class="table border mb-0" id="reviewer">
                                <thead class="fw-semibold text-nowrap">
                                    <tr class="align-middle">
                                        <th class="bg-body-secondary text-start">No.</th>
                                        <th class="bg-body-secondary text-start">Paper ID</th>
                                        <th class="bg-body-secondary text-start">Paper Title</th>
                                        <th class="bg-body-secondary text-start">Status</th>
                                        <th class="bg-body-secondary text-start">Last Updated</th>
                                        <th class="bg-body-secondary text-start">Action</th>
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
                                            <td class="text-start">
                                                {{ $paper->title }}@if(!empty($paper->subtitle)) : {{ $paper->subtitle }}@endif
                                            </td>
                                            <td
                                                class="text-start {{ $paper->supportingMaterials->isNotEmpty() ? 'text-success' : 'text-danger' }}">
                                                {{ $paper->supportingMaterials->isNotEmpty() ? 'Completed' : 'Incompleted' }}
                                            </td>
                                            <td class="text-start">
                                                <span
                                                    title="{{ \Carbon\Carbon::parse($paper->updated_at)->setTimezone('Asia/Jakarta')->format('M d, Y H:i:s \G\M\TP') }}">
                                                    {{ \Carbon\Carbon::parse($paper->updated_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:sP') }}
                                                </span>
                                            </td>
                                            <td class="text-start">
                                                <a href="{{ route('page_upload.supporting-materials', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                    class="btn btn-sm {{ $paper->supportingMaterials->isNotEmpty() ? 'btn-primary' : 'btn-warning' }} btn-rounded">{{ $paper->supportingMaterials->isNotEmpty() ? 'Edit' : 'Upload' }}</a>
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
    <script>
        $('#reviewer').DataTable({
            order: [[1, 'asc']],
            columnDefs: [
            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: '4%',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { targets: 1, width: '8%' },
            { targets: 2, width: '53%' },
            { targets: 3, width: '8%' },
            { targets: 4, width: '15%' },
            { targets: 5, width: '8%', orderable: false, searchable: false }
            ],
            drawCallback: function(settings) {
                var api = this.api();
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });
    </script>
    
@endsection
