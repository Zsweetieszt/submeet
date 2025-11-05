@extends('layouts.event')

@section('title', $event_specific->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: "events") }}">Events</a>
    <li class="breadcrumb-item active"><span>{{ $event_specific->event_name }}</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4" style="margin-top: 35px;">
        <div class="card">
            <div class="card-header py-3">
                <div class="d-flex align-items-center">
                    @if (!empty($event_specific->event_logo))
                        <img class="me-3" width="80"
                            src="{{ asset('storage/' . config('path.logo_event') . $event_specific->event_logo) }}"
                            alt="Event logo">
                    @endif
                    <div>
                        <div class="d-flex align-items-center mb-1">
                            <h4 class="fw-bold mb-0 me-2">{{ $event_specific->event_name }}</h4>
                            @if (!empty($event_specific->event_status))
                                <span
                                    class="badge rounded-pill
                                    {{ match($event_specific->event_status) {
                                        'Ongoing' => 'bg-success',
                                        'Upcoming' => 'bg-primary',
                                        'Finished' => 'bg-secondary',
                                        'Canceled' => 'bg-danger',
                                        default => 'bg-secondary'
                                    } }}">
                                    {{ $event_specific->event_status }}
                                </span>
                            @endif
                        </div>
                        @if (!empty($event_specific->event_shortname))
                            <p class="text-muted mb-0">{{ $event_specific->event_shortname }}</p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="card-body px-4">
                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Event Details</h5>
                        @if (!empty($event_specific->event_desc))
                            <div class="mb-3">
                                <h6 class="fw-bold mb-1">Description</h6>
                                <p>{{ $event_specific->event_desc }}</p>
                            </div>
                        @endif
                        @if (
                            !empty($event_specific->country?->country_name) ||
                                !empty($event_specific->event_code) ||
                                !empty($event_specific->event_organizer))
                            <div class="col mb-3">
                                @if (!empty($event_specific->country?->country_name))
                                    <div class="col-md-6 mb-2">
                                        <span class="fw-bold">Country:</span>
                                        {{ $event_specific->country->country_name }}
                                    </div>
                                @endif
                                @if (!empty($event_specific->event_code))
                                    <div class="col-md-6 mb-2">
                                        <span class="fw-bold">Event Code:</span> {{ $event_specific->event_code }}
                                    </div>
                                @endif
                                @if (!empty($event_specific->event_organizer))
                                    <div class="col-md-6 mb-2">
                                        <span class="fw-bold">Organizer:</span>
                                        {{ $event_specific->event_organizer }}
                                    </div>
                                @endif
                            </div>
                        @endif
                    </div>
                    <div class="col-md-6 mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Important Dates</h5>
                        @if (!empty($event_specific->event_start) && !empty($event_specific->event_end))
                            <div class="mb-2">
                                <span class="fw-bold">Event:</span>
                                {{ \Carbon\Carbon::parse($event_specific->event_start)->translatedFormat('j F Y') }}
                                to
                                {{ \Carbon\Carbon::parse($event_specific->event_end)->translatedFormat('j F Y') }}
                                <span
                                    class="text-muted">(GMT{{ \Carbon\Carbon::parse($event_specific->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                            </div>
                        @endif
                        @if (!empty($event_specific->submission_start) && !empty($event_specific->submission_end))
                            <div class="mb-2">
                                <span class="fw-bold">Submission:</span>
                                {{ \Carbon\Carbon::parse($event_specific->submission_start)->translatedFormat('j F Y') }}
                                to
                                {{ \Carbon\Carbon::parse($event_specific->submission_end)->translatedFormat('j F Y') }}
                                <span
                                    class="text-muted">(GMT{{ \Carbon\Carbon::parse($event_specific->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                            </div>
                        @endif
                        @if (!empty($event_specific->revision_start) && !empty($event_specific->revision_end))
                            <div class="mb-2">
                                <span class="fw-bold">Revision:</span>
                                {{ \Carbon\Carbon::parse($event_specific->revision_start)->translatedFormat('j F Y') }}
                                to
                                {{ \Carbon\Carbon::parse($event_specific->revision_end)->translatedFormat('j F Y') }}
                                <span
                                    class="text-muted">(GMT{{ \Carbon\Carbon::parse($event_specific->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                            </div>
                        @endif
                        @if (!empty($event_specific->join_np_start) && !empty($event_specific->join_np_end))
                            <div class="mb-2">
                                <span class="fw-bold">Non-Presenter Registration:</span>
                                {{ \Carbon\Carbon::parse($event_specific->join_np_start)->translatedFormat('j F Y') }}
                                to
                                {{ \Carbon\Carbon::parse($event_specific->join_np_end)->translatedFormat('j F Y') }}
                                <span
                                    class="text-muted">(GMT{{ \Carbon\Carbon::parse($event_specific->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                            </div>
                        @endif
                        @if (!empty($event_specific->camera_ready_start) && !empty($event_specific->camera_ready_end))
                            <div class="mb-2">
                                <span class="fw-bold">Camera Ready:</span>
                                {{ \Carbon\Carbon::parse($event_specific->camera_ready_start)->translatedFormat('j F Y') }}
                                to
                                {{ \Carbon\Carbon::parse($event_specific->camera_ready_end)->translatedFormat('j F Y') }}
                                <span
                                    class="text-muted">(GMT{{ \Carbon\Carbon::parse($event_specific->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                            </div>
                        @endif
                        @if (!empty($event_specific->payment_start) && !empty($event_specific->payment_end))
                            <div class="mb-2">
                                <span class="fw-bold">Payment:</span>
                                {{ \Carbon\Carbon::parse($event_specific->payment_start)->translatedFormat('j F Y') }}
                                to
                                {{ \Carbon\Carbon::parse($event_specific->payment_end)->translatedFormat('j F Y') }}
                                <span
                                    class="text-muted">(GMT{{ \Carbon\Carbon::parse($event_specific->event_start)->setTimezone('Asia/Jakarta')->format('P') }})</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="row mt-2">
                    <div class="col-md-12 mb-4">
                        <h5 class="border-bottom pb-2 mb-3">Contact Information</h5>
                        <div class="row">
                            @if (
                                !empty($event_specific->manager_name) ||
                                    !empty($event_specific->manager_contact_email) ||
                                    !empty($event_specific->manager_contact_number))
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0">
                                        <div class="card-body px-4">
                                            <h6 class="card-title fw-bold">Manager</h6>
                                            @if (!empty($event_specific->manager_name))
                                                <p class="mb-1">{{ $event_specific->manager_name }}</p>
                                            @endif
                                            @if (!empty($event_specific->manager_contact_email))
                                                <p class="mb-1"><i
                                                        class="fas fa-envelope"></i> {{ $event_specific->manager_contact_email }}
                                                </p>
                                            @endif
                                            @if (!empty($event_specific->manager_contact_number))
                                                <p class="mb-1"><i
                                                        class="fas fa-phone"></i> {{ $event_specific->manager_contact_ct . $event_specific->manager_contact_number }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (
                                !empty($event_specific->support_name) ||
                                    !empty($event_specific->support_contact_email) ||
                                    !empty($event_specific->support_contact_number))
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0">
                                        <div class="card-body px-4">
                                            <h6 class="card-title fw-bold">Support</h6>
                                            @if (!empty($event_specific->support_name))
                                                <p class="mb-1">{{ $event_specific->support_name }}</p>
                                            @endif
                                            @if (!empty($event_specific->support_contact_email))
                                                <p class="mb-1"><i
                                                        class="fas fa-envelope"></i> {{ $event_specific->support_contact_email }}
                                                </p>
                                            @endif
                                            @if (!empty($event_specific->support_contact_number))
                                                <p class="mb-1"><i
                                                        class="fas fa-phone"></i> {{ $event_specific->support_contact_ct . $event_specific->support_contact_number }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                            @if (
                                !empty($event_specific->treasurer_name) ||
                                    !empty($event_specific->treasurer_contact_email) ||
                                    !empty($event_specific->treasurer_contact_number))
                                <div class="col-md-4 mb-3">
                                    <div class="card h-100 border-0">
                                        <div class="card-body px-4">
                                            <h6 class="card-title fw-bold">Treasurer</h6>
                                            @if (!empty($event_specific->treasurer_name))
                                                <p class="mb-1">{{ $event_specific->treasurer_name }}</p>
                                            @endif
                                            @if (!empty($event_specific->treasurer_contact_email))
                                                <p class="mb-1"><i
                                                        class="fas fa-envelope"></i> {{ $event_specific->treasurer_contact_email }}
                                                </p>
                                            @endif
                                            @if (!empty($event_specific->treasurer_contact_number))
                                                <p class="mb-1"><i
                                                        class="fas fa-phone"></i> {{ $event_specific->treasurer_contact_ct . $event_specific->treasurer_contact_number }}
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row">
                    <div class="col-md-12">
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <div class="text-muted small">
                                @if (!empty($event_specific->user?->given_name) || !empty($event_specific->user?->family_name))
                                    Created by: {{ $event_specific->user->given_name ?? '' }}
                                    {{ $event_specific->user->family_name ?? '' }}
                                @endif
                            </div>
                            <button type="button" class="btn btn-secondary cancel-join">
                                <i class="fas fa-arrow-left me-1"></i> Back
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
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
