@extends('layouts.app')

@section('title', 'Events')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><span>Events</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Events</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <div class="d-flex justify-content-end">
                        @if (Auth::user()->root)
                            <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
                                <a class="btn btn-primary" href="{{ route('events.create') }}">
                                    <i class="cil-pencil"></i> Create
                                </a>
                            </div>
                        @endif
                    </div>
                    @if (Auth::user()->root)
                        <div class="table-responsive">
                            <table id="event" class="table border mb-0">
                                <thead class="fw-semibold text-nowrap">
                                    <tr class="align-middle">
                                        <th class="bg-body-secondary">No.</th>
                                        <th class="bg-body-secondary">Event Name</th>
                                        <th class="bg-body-secondary">Event Code</th>
                                        <th class="bg-body-secondary">Manager Name</th>
                                        <th class="bg-body-secondary">Manager Email</th>
                                        <th class="bg-body-secondary">Manager Phone</th>
                                        <th class="bg-body-secondary">Event Status</th>
                                        <th class="bg-body-secondary">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($events as $event)
                                        <tr>
                                            <td class="text-center"></td>
                                            <td>{{ $event->event_name }}</td>
                                            <td>{{ $event->event_code }}</td>
                                            <td>{{ $event->manager_name }}</td>
                                            <td>{{ $event->manager_contact_email }}</td>
                                            <td>{{ $event->manager_contact_ct . '-' . $event->manager_contact_number }}</td>
                                            <td>{{ $event->event_status }}</td>
                                            <td>
                                                <div class="d-flex gap-2">
                                                    <a href="{{ route('dashboard.event', $event->event_code) }}"
                                                        class="btn btn-primary btn-sm btn-rounded text-white"> View</a>
                                                    <a href="{{ route('events.show', $event->event_code) }}"
                                                        class="btn btn-sm btn-success btn-rounded text-white"><i
                                                            class="mdi mdi-eye align-middle"></i> Detail</a>
                                                    <a href="{{ route('events.edit', $event->event_code) }}"
                                                        class="btn btn-sm btn-warning btn-rounded text-white"><i
                                                            class="mdi mdi-pencil align-middle"></i> Edit</a>
                                                    <form action="{{ route('events.destroy', $event->event_code) }}" method="POST"
                                                        class="d-inline">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="button"
                                                            class="btn btn-sm btn-danger confirm-delete text-white"><i
                                                                class="mdi mdi-delete align-middle"></i> Delete</button>
                                                    </form>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="row d-flex justify-content-star">
                            @if(collect($events)->isEmpty())
                                <div class="col-12">
                                    <div class="alert alert-info" role="alert">
                                        No events available.
                                    </div>
                                </div>
                            @else
                                @foreach ($events as $event)
                                    <div class="col-md-3 mb-4" style="width: 18rem;">
                                        <div class="card h-100 d-flex flex-column">
                                            @if (!empty($event->event_logo) && file_exists(public_path('storage/' . config('path.logo_event') . $event->event_logo)))
                                                <img src="{{ asset('storage/' . config('path.logo_event') . $event->event_logo) }}"
                                                    class="card-img-top" alt="..." style="height: 250px;">
                                            @else
                                                <div class="card-img-top"
                                                    style="height: 250px; background: #f0f0f0; display: flex; align-items: center; justify-content: center;">
                                                    <span class="text-muted">No Image</span>
                                                </div>
                                            @endif
                                            <div class="card-body px-4 d-flex flex-column">
                                                <h5 class="card-title text-truncate" title="{{ $event->event_name }}">
                                                    {{ $event->event_name }}
                                                </h5>
                                                <p class="card-text text-truncate" style="max-width: 100%;"
                                                    title="{{ $event->event_desc }}">{{ $event->event_desc }}</p>
                                                <div class="row align-items-center mt-auto">
                                                    <div class="col">
                                                        @php
                                                            $user_event_ids = collect($user_events)->pluck('event_id')->toArray();
                                                        @endphp
                                                        @if (in_array($event->event_id, $user_event_ids))
                                                            <div>
                                                                @if ((collect($user_events)->firstWhere('event_id', $event->event_id)['role'] ?? '') === 'Presenter')
                                                                    @if((collect($user_events)->firstWhere('event_id', $event->event_id)['is_offline'] ?? false))
                                                                        <span class="badge bg-primary mb-2 px-2" role="button"
                                                                            data-coreui-toggle="modal" data-coreui-target="#exampleModal"
                                                                            data-event_id="{{ $event->event_id }}"
                                                                            data-role="Presenter (Offline)">Presenter (Offline)</span>
                                                                    @else
                                                                        <span class="badge bg-primary mb-2 px-2" role="button"
                                                                            data-coreui-toggle="modal" data-coreui-target="#exampleModal"
                                                                            data-event_id="{{ $event->event_id }}"
                                                                            data-role="Presenter (Online)">Presenter (Online)</span>
                                                                    @endif
                                                                @elseif ((collect($user_events)->firstWhere('event_id', $event->event_id)['role'] ?? '') === 'Non-Presenter')
                                                                    <span class="badge bg-success mb-2 px-2" role="button"
                                                                        data-coreui-toggle="modal" data-coreui-target="#exampleModal"
                                                                        data-event_id="{{ $event->event_id }}"
                                                                        data-role="Non Presenter">Non-Presenter</span>
                                                                @endif
                                                            </div>
                                                            <a href="{{ route('dashboard.event', $event->event_code) }}"
                                                                class="btn btn-primary">View</a>
                                                        @else
                                                            @if ($event->event_status == 'Ongoing')
                                                                <a href="{{ route('events.show', $event->event_code) }}"
                                                                    class="btn btn-success text-white">Join</a>
                                                            @endif
                                                        @endif
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            @endif
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    @if (!$user_has_payment)
        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="exampleModalLabel">Change Role</h5>
                        <button type="button" class="btn-close" data-coreui-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <form id="changeRoleForm" action="#" method="post">
                        @csrf
                        @method('POST')
                        <input type="hidden" name="event_id" id="modal_event_id" value="">
                        <input type="hidden" name="old_role" id="modal_old_role" value="">
                        <div class="modal-body">
                            <label for="modal_role">Role<span class="text-danger">*</span></label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="1">Presenter</option>
                                <option value="2">Non-Presenter</option>
                            </select>
                            <label class="mt-3 mb-3" for="modal_is_offline">Attendance<span class="text-danger">*</span></label>
                            <select name="is_offline" id="modal_is_offline" required class="form-select"
                                aria-label="Default select example">
                                <option value="0">Online</option>
                                <option value="1">Offline</option>
                            </select>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-coreui-dismiss="modal">Cancel</button>
                            <button type="submit" class="btn btn-primary confirm-submit">Save</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif

    <script>
        $('#event').DataTable({
            columnDefs: [{
                targets: '_all',
                className: 'dt-left',
            },
            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: '4%',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { targets: 7, orderable: false, searchable: false}
            ],
            drawCallback: function(settings) {
                var api = this.api();
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });
        document.addEventListener("DOMContentLoaded", function () {
            document.querySelectorAll(".confirm-delete").forEach(button => {
                button.addEventListener("click", function (e) {
                    e.preventDefault();
                    let form = this.closest("form");

                    Swal.fire({
                        title: "Are you sure want to delete?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        cancelButtonText: "No, cancel!",
                        confirmButtonText: "Yes, delete!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            document.querySelectorAll('.confirm-submit').forEach(button => {
                button.addEventListener('click', function (e) {
                    e.preventDefault();
                    let form = this.closest('form');

                    Swal.fire({
                        title: 'Are you sure?',
                        text: 'You are about to submit this form.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonColor: '#3085d6',
                        cancelButtonColor: '#d33',
                        cancelButtonText: 'No, cancel!',
                        confirmButtonText: 'Yes, submit!'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });


            document.querySelectorAll('[data-coreui-target="#exampleModal"]').forEach(function (badge) {
                badge.addEventListener('click', function () {
                    var eventId = this.getAttribute('data-event_id');
                    var role = this.getAttribute('data-role');
                    var eventCode = this.closest('.card').querySelector('a.btn-primary').getAttribute('href').split('/').pop();
                    document.getElementById('modal_event_id').value = eventId;
                    var roleSelect = document.getElementById('role');
                    var old_role = document.getElementById('modal_old_role');
                    if (role && role.toLowerCase().includes('non')) {
                        old_role.value = '2'
                        roleSelect.value = '2';
                    } else {
                        old_role.value = '1'
                        roleSelect.value = '1';
                    }
                    var isOfflineSelect = document.getElementById('modal_is_offline');
                    if (role && role.toLowerCase().includes('offline')) {
                        isOfflineSelect.value = '1';
                    } else {
                        isOfflineSelect.value = '0';
                    }
                    var form = document.getElementById('changeRoleForm');
                    form.action = '/dashboard/event/' + eventCode + '/change-role';
                });
            });
        });
    </script>

@endsection