@extends('layouts.app')

@section('title', 'Users')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><span>Users</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <!-- /.row-->
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Users</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <div class="d-flex justify-content-end">
                        <div class="btn-toolbar d-none d-md-block" role="toolbar" aria-label="Toolbar with buttons">
                            <a class="btn btn-primary" href="{{ route('users.create') }}">
                                <i class="cil-pencil"></i> Create User
                            </a>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table id="event" class="table border mb-0" style="min-width:1200px;">
                            <thead class="fw-semibold text-nowrap">
                                <tr class="align-middle">
                                    <th class="bg-body-secondary">No.</th>
                                    <th class="bg-body-secondary">User ID</th>
                                    <th class="bg-body-secondary">Name</th>
                                    <th class="bg-body-secondary">Email</th>
                                    <th class="bg-body-secondary">Username</th>
                                    <th class="bg-body-secondary">Phone Number 1</th>
                                    <th class="bg-body-secondary">Country</th>
                                    <th class="bg-body-secondary">Status</th>
                                    <th class="bg-body-secondary">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($users as $user)
                                    <tr>
                                        <td class="text-center"></td>
                                        <td class="text-center">{{ $user->user_id }}</td>
                                        <td>{{ $user->given_name . ' ' . $user->family_name }}</td>
                                        <td>{{ $user->email }}</td>
                                        <td>{{ $user->username }}</td>
                                        @if ($user->ct_phone_number_1)
                                            <td>{{ $user->ct_phone_number_1 . '-' . $user->phone_number_1 }}</td>
                                        @else
                                            <td>{{ $user->phone_number_1 }}</td>
                                        @endif
                                        <td>{{ $user->country->country_name }}</td>
                                        <td>
                                            <form action="{{ route('users.change_status', $user->username) }}"
                                                method="POST">
                                                @csrf
                                                <div class="form-check form-switch">
                                                    <input class="form-check-input" type="checkbox" role="switch"
                                                        name="status" onchange="this.form.submit()"
                                                        {{ $user->status ? 'checked' : '' }}>
                                                </div>
                                            </form>
                                        </td>
                                        <td>
                                            <div class="d-flex gap-2">
                                                <a href="{{ route('users.show', $user->username) }}"
                                                    class="btn btn-sm btn-success btn-rounded text-white"><i
                                                        class="mdi mdi-eye align-middle"></i> Detail</a>
                                                <a href="{{ route('users.edit', $user->username) }}"
                                                    class="btn btn-sm btn-warning btn-rounded text-white"><i
                                                        class="mdi mdi-pencil align-middle"></i> Edit</a>
                                                <form action="{{ route('users.destroy', $user->username) }}" method="POST"
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
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#event').DataTable({
            scrollX: true,
            columnDefs: [
            {
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
            { targets: 1, width: '8%' }, 
            { targets: 2, width: '15%' },  
            { targets: 3, width: '18%' },  
            { targets: 4, width: '10%' },  
            { targets: 5, width: '12%' },  
            { targets: 6, width: '10%' },  
            { targets: 7, width: '7%' },   
            { targets: 8, width: '16%', orderable: false, searchable: false},  
            ],
            drawCallback: function(settings) {
                var api = this.api();
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });
        document.addEventListener("DOMContentLoaded", function() {
            document.querySelectorAll(".confirm-delete").forEach(button => {
                button.addEventListener("click", function(e) {
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
        });
    </script>
    
@endsection
