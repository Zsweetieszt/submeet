@extends('layouts.app')

@section('title', 'Detail Event')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><span>Detail Event</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="">
            <div class="">
                <div class="card shadow-sm">
                    <div class="card-header py-3">
                        <div class="d-flex align-items-center">
                            @if (!empty($event->event_logo))
                                <img class="me-3" width="80"
                                    src="{{ asset('storage/' . config('path.logo_event') . $event->event_logo) }}"
                                    alt="Event logo">
                            @endif
                            <div>
                                <div class="d-flex align-items-center mb-1">
                                    <h4 class="fw-bold mb-0 me-2">{{ $event->event_name }}</h4>
                                    @if (!empty($event->event_status))
                                        <span
                                            class="badge rounded-pill
                                            {{ match ($event->event_status) {
                                                'Ongoing' => 'bg-success',
                                                'Upcoming' => 'bg-primary',
                                                'Finished' => 'bg-secondary',
                                                'Canceled' => 'bg-danger',
                                                default => 'bg-secondary',
                                            } }}">
                                            {{ $event->event_status }}
                                        </span>
                                    @endif
                                </div>
                                @if (!empty($event->event_shortname))
                                    <p class="text-muted mb-0">{{ $event->event_shortname }}</p>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="card-body px-4">
                        <div class="row">
                            <div class="col-md-6 mb-4">
                                <h5 class="border-bottom pb-2 mb-3">Event Details</h5>

                                @if (!empty($event->event_desc))
                                    <div class="mb-3">
                                        <h6 class="fw-bold mb-1">Description</h6>
                                        <p>{{ $event->event_desc }}</p>
                                    </div>
                                @endif

                                @if (!empty($event->country?->country_name) || !empty($event->event_code) || !empty($event->event_organizer))
                                    <div class="col mb-3">
                                        @if (!empty($event->country?->country_name))
                                            <div class="col-md-6 mb-2">
                                                <span class="fw-bold">Country:</span>
                                                {{ $event->country->country_name }}
                                            </div>
                                        @endif
                                        @if (!empty($event->event_code))
                                            <div class="col-md-6 mb-2">
                                                <span class="fw-bold">Event Code:</span> {{ $event->event_code }}
                                            </div>
                                        @endif
                                        @if (!empty($event->event_organizer))
                                            <div class="col-md-6 mb-2">
                                                <span class="fw-bold">Organizer:</span>
                                                {{ $event->event_organizer }}
                                            </div>
                                        @endif
                                    </div>
                                @endif
                            </div>

                            <div class="col-md-6 mb-4">
                                <h5 class="border-bottom pb-2 mb-3">Important Dates</h5>

                                @if (!empty($event->event_start) && !empty($event->event_end))
                                    <div class="mb-2">
                                        <span class="fw-bold">Event:</span>
                                        {{ \Carbon\Carbon::parse($event->event_start)->translatedFormat('j F Y') }}
                                        to
                                        {{ \Carbon\Carbon::parse($event->event_end)->translatedFormat('j F Y') }}
                                        <span
                                            class="text-muted">(GMT{{ \Carbon\Carbon::parse($event->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                                    </div>
                                @endif

                                @if (!empty($event->submission_start) && !empty($event->submission_end))
                                    <div class="mb-2">
                                        <span class="fw-bold">Submission:</span>
                                        {{ \Carbon\Carbon::parse($event->submission_start)->translatedFormat('j F Y') }}
                                        to
                                        {{ \Carbon\Carbon::parse($event->submission_end)->translatedFormat('j F Y') }}
                                        <span
                                            class="text-muted">(GMT{{ \Carbon\Carbon::parse($event->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                                    </div>
                                @endif

                                @if (!empty($event->revision_start) && !empty($event->revision_end))
                                    <div class="mb-2">
                                        <span class="fw-bold">Revision:</span>
                                        {{ \Carbon\Carbon::parse($event->revision_start)->translatedFormat('j F Y') }}
                                        to
                                        {{ \Carbon\Carbon::parse($event->revision_end)->translatedFormat('j F Y') }}
                                        <span
                                            class="text-muted">(GMT{{ \Carbon\Carbon::parse($event->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                                    </div>
                                @endif

                                @if (!empty($event->join_np_start) && !empty($event->join_np_end))
                                    <div class="mb-2">
                                        <span class="fw-bold">Non-Presenter Registration:</span>
                                        {{ \Carbon\Carbon::parse($event->join_np_start)->translatedFormat('j F Y') }}
                                        to
                                        {{ \Carbon\Carbon::parse($event->join_np_end)->translatedFormat('j F Y') }}
                                        <span
                                            class="text-muted">(GMT{{ \Carbon\Carbon::parse($event->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                                    </div>
                                @endif

                                @if (!empty($event->camera_ready_start) && !empty($event->camera_ready_end))
                                    <div class="mb-2">
                                        <span class="fw-bold">Camera Ready:</span>
                                        {{ \Carbon\Carbon::parse($event->camera_ready_start)->translatedFormat('j F Y') }}
                                        to
                                        {{ \Carbon\Carbon::parse($event->camera_ready_end)->translatedFormat('j F Y') }}
                                        <span
                                            class="text-muted">(GMT{{ \Carbon\Carbon::parse($event->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                                    </div>
                                @endif

                                @if (!empty($event->payment_start) && !empty($event->payment_end))
                                    <div class="mb-2">
                                        <span class="fw-bold">Payment:</span>
                                        {{ \Carbon\Carbon::parse($event->payment_start)->translatedFormat('j F Y') }}
                                        to
                                        {{ \Carbon\Carbon::parse($event->payment_end)->translatedFormat('j F Y') }}
                                        <span
                                            class="text-muted">(GMT{{ \Carbon\Carbon::parse($event->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <div class="row mt-2">
                            <div class="col-md-12 mb-4">
                                <h5 class="border-bottom pb-2 mb-3">Contact Information</h5>
                                <div class="row">
                                    @if (!empty($event->manager_name) || !empty($event->manager_contact_email) || !empty($event->manager_contact_number))
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-0">
                                                <div class="card-body px-4">
                                                    <h6 class="card-title fw-bold">Manager</h6>
                                                    @if (!empty($event->manager_name))
                                                        <p class="mb-1">{{ $event->manager_name }}</p>
                                                    @endif
                                                    @if (!empty($event->manager_contact_email))
                                                        <p class="mb-1"><i
                                                                class="fas fa-envelope"></i> {{ $event->manager_contact_email }}
                                                        </p>
                                                    @endif
                                                    @if (!empty($event->manager_contact_number))
                                                        <p class="mb-1"><i
                                                                class="fas fa-phone"></i> {{ $event->manager_contact_ct . $event->manager_contact_number }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (!empty($event->support_name) || !empty($event->support_contact_email) || !empty($event->support_contact_number))
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-0">
                                                <div class="card-body px-4">
                                                    <h6 class="card-title fw-bold">Support</h6>
                                                    @if (!empty($event->support_name))
                                                        <p class="mb-1">{{ $event->support_name }}</p>
                                                    @endif
                                                    @if (!empty($event->support_contact_email))
                                                        <p class="mb-1"><i
                                                                class="fas fa-envelope"></i> {{ $event->support_contact_email }}
                                                        </p>
                                                    @endif
                                                    @if (!empty($event->support_contact_number))
                                                        <p class="mb-1"><i
                                                                class="fas fa-phone"></i> {{ $event->support_contact_ct . $event->support_contact_number }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    @if (
                                        !empty($event->treasurer_name) ||
                                            !empty($event->treasurer_contact_email) ||
                                            !empty($event->treasurer_contact_number))
                                        <div class="col-md-4 mb-3">
                                            <div class="card h-100 border-0">
                                                <div class="card-body px-4">
                                                    <h6 class="card-title fw-bold">Treasurer</h6>
                                                    @if (!empty($event->treasurer_name))
                                                        <p class="mb-1">{{ $event->treasurer_name }}</p>
                                                    @endif
                                                    @if (!empty($event->treasurer_contact_email))
                                                        <p class="mb-1"><i
                                                                class="fas fa-envelope"></i> {{ $event->treasurer_contact_email }}
                                                        </p>
                                                    @endif
                                                    @if (!empty($event->treasurer_contact_number))
                                                        <p class="mb-1"><i
                                                                class="fas fa-phone"></i> {{ $event->treasurer_contact_ct . $event->treasurer_contact_number }}
                                                        </p>
                                                    @endif
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="row mb-3">
                            <div class="col-md-12">
                                <div class="d-flex justify-content-between align-items-center border-top pt-3">
                                    <div class="text-muted small">
                                        @if (!empty($event->user?->given_name) || !empty($event->user?->family_name))
                                            Created by: {{ $event->user->given_name ?? '' }}
                                            {{ $event->user->family_name ?? '' }}
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>

                        @if (!$join && !Auth::user()->root && $event->event_status != 'Finished')
                            @if ($event->event_status == 'Ongoing')
                                <button type="button" class="btn btn-primary" data-coreui-toggle="modal"
                                    data-coreui-target="#exampleModal">
                                    Join Event
                                </button>
                            @else
                                <p class="text-danger">You cannot join this event because the time limit has passed or not started yet.</p>    
                            @endif
                            <button type="button" class="btn btn-danger cancel-join">Cancel</button>
                        @endif

                        <!-- Modal -->
                        <div class="modal fade" id="exampleModal" tabindex="-1" aria-labelledby="exampleModalLabel"
                            aria-hidden="true">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title" id="exampleModalLabel">Join Event</h5>
                                        <button type="button" class="btn-close" data-coreui-dismiss="modal"
                                            aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('events.join', $event->event_code) }}" method="post">
                                        @csrf
                                        <div class="modal-body">
                                            @php
                                                $now = now();
                                                $submissionStart = \Carbon\Carbon::parse($event->submission_start);
                                                $submissionEnd = \Carbon\Carbon::parse($event->submission_end)->endOfDay();
                                                $npStart = \Carbon\Carbon::parse($event->join_np_start);
                                                $npEnd = \Carbon\Carbon::parse($event->join_np_end)->endOfDay();

                                                $submissionOpen = $submissionStart->lte($now) && $submissionEnd->gte($now) && $event->event_status == 'Ongoing';
                                                $submissionNotStarted = $now->lt($submissionStart);
                                                $submissionEnded = $now->gt($submissionEnd);

                                                $nonPresenterOpen = $npStart->lte($now) && $npEnd->gte($now) && $event->event_status == 'Ongoing';
                                                $nonPresenterNotStarted = $now->lt($npStart);
                                                $nonPresenterEnded = $now->gt($npEnd);
                                            @endphp

                                            @if ($submissionNotStarted && $nonPresenterNotStarted)
                                                <p class="text-danger">You cannot participate as Presenter or Non-Presenter because the registration period has not started yet.</p>
                                            @elseif ($submissionEnded && $nonPresenterEnded)
                                                <p class="text-danger">You cannot participate as Presenter or Non-Presenter because the registration period has ended.</p>
                                            @else
                                                @if ($submissionNotStarted)
                                                    <p class="text-danger">You cannot participate as Presenter because the registration period has not started yet.</p>
                                                @elseif ($submissionEnded)
                                                    <p class="text-danger">You cannot participate as Presenter because the registration period has ended.</p>
                                                @endif

                                                @if ($nonPresenterNotStarted)
                                                    <p class="text-danger">You cannot participate as Non-Presenter because the registration period has not started yet.</p>
                                                @elseif ($nonPresenterEnded)
                                                    <p class="text-danger">You cannot participate as Non-Presenter because the registration period has ended.</p>
                                                @endif
                                            @endif
                                            <label for="role">Participate as</label>
                                            <div style="font-size: small" class="text-body-secondary my-3">
                                                <div class="row">
                                                    <span>Presenter: Submit Paper</span>
                                                    <span>Non-Presenter: Only as Attendance</span>
                                                </div>
                                            </div>
                                            <select name="role" class="form-select" 
                                                aria-label="Default select example">
                                                <option value="" selected>Choose Participate as</option>
                                            @if ($submissionOpen)
                                                <option value="Presenter">
                                                    Presenter
                                                </option>
                                               @endif
                                                @if ($nonPresenterOpen)
                                                    <option value="Non-Presenter">
                                                        Non-Presenter
                                                </option>
                                                @endif
                                            </select>

                                            <label class="mt-3 mb-3" for="is_offline">Attendance</label>
                                            <select name="is_offline" class="form-select"
                                                aria-label="Default select example" >
                                                <option value="" selected>Choose Attendance</option>
                                                <option value="0">Online</option>
                                                <option value="1">Offline</option>
                                            </select>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-coreui-dismiss="modal">Cancel</button>
                                            <button type="submit" class="btn btn-primary">Join</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.addEventListener("DOMContentLoaded", function() {
            const cancelBtn = document.querySelector('.cancel-join');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = "{{ route('events') }}";
                });
            }
        });
    </script>
    @if (Auth::user()->root)
        <div class="container-fluid px-4 mt-4">
            <!-- /.row-->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">Participants</h4>
                </div>
                <div class="">
                    <div class="card-body px-4">
                        <div class="table-responsive">
                            <table class="table border mb-0" id="member">
                                <thead class="fw-semibold text-nowrap">
                                    <tr class="align-middle">
                                        <th class="bg-body-secondary">Name</th>
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
                                        <tr>
                                            <td>{{ $member->given_name . ' ' . $member->family_name }}</td>
                                            <td>{{ $member->institution_name }}</td>
                                            <td>{{ $member->country->country_name }}</td>
                                            <td>
                                                @if (count($roleNames) > 1)
                                                    @foreach ($roleNames as $roleName)
                                                        <div>{{ $roleName }}</div>
                                                    @endforeach
                                                @else
                                                    {{ implode(', ', $roleNames) }}
                                                @endif
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
            $('#member').DataTable({});
        </script>
        <script>
            @if (session('success'))
                toastr.success("{{ session('success') }}");
            @elseif ($errors->any())
                toastr.error("{{ $errors->first() }}");
            @endif
        </script>
    @endif
@endsection
