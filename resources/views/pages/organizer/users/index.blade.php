@extends('layouts.event')

@section('title', 'Participants - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><span>Participants</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <!-- /.row-->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Participants</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form action="{{ route('editor.send-loa-bulk', request()->route('event')) }}" method="post">
                        @csrf
                        <button class="btn btn-danger" type="submit">Send Bulk LOA</button>
                    </form>
                    <div class="table-responsive">
                        <table class="table border mb-0" id="member">
                            <thead class="fw-semibold text-nowrap">
                                <tr class="align-middle">
                                    <th class="bg-body-secondary text-start">No.</th>
                                    <th class="bg-body-secondary">Name</th>
                                    <th class="bg-body-secondary">Email</th>
                                    <th class="bg-body-secondary text-start">Phone Number</th>
                                    <th class="bg-body-secondary">Institution</th>
                                    <th class="bg-body-secondary">Country</th>
                                    <th class="bg-body-secondary">Role</th>
                                    <th class="bg-body-secondary">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $member)
                                    @php
                                        $userEvents = $member->user_events;
                                        $roleNames = $userEvents->pluck('role.role_name')->toArray();
                                    @endphp
                                    <tr class="align-middle">
                                        <td class="text-start"></td>
                                        <td>{{ $member->given_name . ' ' . $member->family_name }}</td>
                                        <td>{{ $member->email }}</td>
                                        <td class="text-start">{{ '+' . $member->ct_phone_number_1.$member->phone_number_1 }}</td>
                                        <td>{{ $member->institution_name }}</td>
                                        <td>{{ $member->country->country_name }}</td>
                                        <td>
                                            <div class="d-flex flex-wrap gap-1">
                                                @foreach ($roleNames as $roleName)
                                                    @php
                                                        // Assign badge color based on role name
                                                        $badgeColors = [
                                                            'Presenter' => 'primary',
                                                            'Non-Presenter' => 'secondary',
                                                            'Abstract Reviewer' => 'secondary',
                                                            'Paper Reviewer' => 'success',
                                                            'Editor' => 'warning',
                                                            'Organizer' => 'info',
                                                            'Admin' => 'danger',
                                                        ];
                                                        $color = $badgeColors[$roleName] ?? 'secondary';
                                                    @endphp
                                                    <span class="badge bg-{{ $color }}">{{ $roleName }}</span>
                                                @endforeach
                                            </div>
                                        </td>
                                        <td>
                                            <a href="{{ route('organizer.users.edit', [$event->event_code, $member->username]) }}"
                                                class="btn btn-sm btn-warning btn-rounded "><i
                                                    class="mdi mdi-pencil align-middle"></i> Edit</a>
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
        $('#member').DataTable({
            columnDefs: [
                { width: "50px", targets: 0 },
                { 
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                {
                    targets: 7,
                    orderable: false,
                    searchable: false,
                }
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
