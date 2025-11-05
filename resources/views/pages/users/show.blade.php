@extends('layouts.app')

@section('title', 'Detail User')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'users.index') }}">Users</a>
    </li>
    <li class="breadcrumb-item active"><span>Detail User</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Detail User</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    @if (!empty($user->user_id))
                        <p>User ID : {{ $user->user_id }}</p>
                    @endif
                    @if (!empty($user->given_name) || !empty($user->family_name))
                        <p>Name : {{ $user->given_name ?? '' }} {{ $user->family_name ?? '' }}</p>
                    @endif
                    @if (!empty($user->email))
                        <p>Email : {{ $user->email }}</p>
                    @endif
                    @if (!empty($user->phone_number_1))
                        @if ($user->ct_phone_number_1)
                            <p>Phone Number 1 : {{ $user->ct_phone_number_1 . '-' . $user->phone_number_1 }}</p>
                        @else
                            <p>Phone Number 1 : {{ $user->phone_number_1 }}</p>
                        @endif
                    @endif
                    @if (!empty($user->phone_number_2))
                        @if ($user->ct_phone_number_2)
                            <p>Phone Number 1 : {{ $user->ct_phone_number_2 . '-' . $user->phone_number_2 }}</p>
                        @else
                            <p>Phone Number 1 : {{ $user->phone_number_2 }}</p>
                        @endif
                    @endif
                    @if (!empty($user->institution_name))
                        <p>Institution : {{ $user->institution_name }}</p>
                    @endif
                    @if (!empty($user->country?->country_name))
                        <p>Country : {{ $user->country->country_name }}</p>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Events Joined</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table border mb-0" id="member">
                            <thead class="fw-semibold text-nowrap">
                                <tr class="align-middle">
                                    <th class="bg-body-secondary">No.</th>
                                    <th class="bg-body-secondary">Event Name</th>
                                    <th class="bg-body-secondary">Event Country</th>
                                    <th class="bg-body-secondary">Role</th>
                                    <th class="bg-body-secondary">Joined Date</th>
                                    <th class="bg-body-secondary">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($members as $member)
                                    <tr>
                                        <td class="text-center">{{ $loop->iteration }}</td>
                                        <td>{{ $member->event->event_name }}</td>
                                        <td>{{ $member->event->country->country_name }}</td>
                                        <td>{{ $member->role->role_name }}</td>
                                        <td>
                                            {{ \Carbon\Carbon::parse($member->created_at)->setTimezone('Asia/Jakarta')->translatedFormat('Y-m-d H:i:sP') }}
                                        </td>
                                        <td>
                                            <a href="{{ route('users.events.edit', [$member->event->event_code, $user->username]) }}"
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
        $('#member').DataTable({});
    </script>
    
@endsection
