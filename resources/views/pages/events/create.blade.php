@extends('layouts.app')

@section('title', 'Create Event')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><span>Create Event</span>
    </li>
@endsection
@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="mb-0">Create Event</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <form class="needs-validation" novalidate action="{{ route('events.store') }}" method="post"
                        enctype="multipart/form-data" id="eventForm">
                        @csrf
                        <div id="form-steps">
                            {{-- Step 1: Event --}}
                            <div class="form-step" id="step-1">
                                <fieldset>
                                    <legend>Event</legend>
                                    <div class="row align-items-start">
                                        <div class="mb-3">
                                            <label for="event_name" class="form-label">Event Name<span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('event_name') is-invalid @enderror"
                                                id="event_name" name="event_name" required value="{{ old('event_name') }}"
                                                placeholder="Event Name">
                                            @error('event_name')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_shortname" class="form-label">Event Shortname<span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('event_shortname') is-invalid @enderror"
                                                id="event_shortname" name="event_shortname" required
                                                value="{{ old('event_shortname') }}" placeholder="Event Shortname">
                                            @error('event_shortname')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_desc" class="form-label">Event Description<span
                                                    class="text-danger">*</span></label>
                                            <textarea class="form-control @error('event_desc') is-invalid @enderror" id="event_desc" name="event_desc" required
                                                placeholder="Event Description">{{ old('event_desc') }}</textarea>
                                            @error('event_desc')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_code" class="form-label">Event Code<span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('event_code') is-invalid @enderror"
                                                id="event_code" name="event_code" required value="{{ old('event_code') }}"
                                                placeholder="Event Code">
                                            @error('event_code')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3 d-flex justify-content-center">
                                            <img id="logo_preview" src="#" alt="Logo Preview"
                                                style="display:none; max-width: 200px; margin-top: 10px;">
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_logo" class="form-label">Event Logo<span
                                                    class="text-danger">*</span></label>
                                            <input type="file"
                                                class="form-control @error('event_logo') is-invalid @enderror"
                                                id="event_logo" name="event_logo" required onchange="previewLogo(event)"
                                                placeholder="Event Logo">
                                            @error('event_logo')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                            <small class="form-text">Max. image resolution: 450 x 450 px. Allowed file type:
                                                PNG/JPG/JPEG. Max. file size: 2 MB</small>
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_country" class="form-label">Event Country<span
                                                    class="text-danger">*</span></label>
                                            <select class="form-select @error('event_country') is-invalid @enderror"
                                                id="event_country" name="event_country" required>
                                                <option value="" disabled selected>Select Country</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->country_id }}"
                                                        {{ old('event_country') == $country->country_id ? 'selected' : '' }}>
                                                        {{ $country->country_name }}</option>
                                                @endforeach
                                            </select>
                                            @error('event_country')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_organizer" class="form-label">Event Organizer<span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('event_organizer') is-invalid @enderror"
                                                id="event_organizer" name="event_organizer" required
                                                value="{{ old('event_organizer') }}" placeholder="Event Organizer">
                                            @error('event_organizer')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                        <div class="mb-3">
                                            <label for="topics" class="form-label">Event Topics<span
                                                    class="text-danger">*</span></label>
                                            <input type="text"
                                                class="form-control @error('topics') is-invalid @enderror" id="topics"
                                                name="topics" required value="{{ old('topics') }}"
                                                placeholder="Event Topics">
                                            @error('topics')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="d-flex justify-content-end mt-4">
                                    <button type="button" class="btn btn-primary next-step">Next</button>
                                </div>
                            </div>
                            {{-- Step 2: Timeline --}}
                            <div class="form-step d-none" id="step-2">
                                <fieldset>
                                    <legend>Timeline</legend>
                                    <div class="row align-items-start">
                                        <div class="mb-3">
                                            <label for="event_date" class="form-label">Event Date<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-daterange">
                                                <input type="text"
                                                    class="form-control @error('event_start') is-invalid
                                                @enderror"
                                                    id="event_start" name="event_start" placeholder="Event Start Date"
                                                    value="{{ old('event_start') }}" autocomplete="off">
                                                <div class="input-group-text">to</div>
                                                <input type="text"
                                                    class="form-control @error('event_end') is-invalid
                                                @enderror"
                                                    id="event_end" name="event_end" placeholder="Event End Date"
                                                    value="{{ old('event_end') }}" autocomplete="off">
                                                @error('event_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('event_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_date" class="form-label">Submission Date<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-daterange">
                                                <input type="text"
                                                    class="form-control @error('submission_start') is-invalid
                                                @enderror"
                                                    id="submission_start" name="submission_start"
                                                    placeholder="Submission Start Date"
                                                    value="{{ old('submission_start') }}" autocomplete="off">
                                                <div class="input-group-text">to</div>
                                                <input type="text"
                                                    class="form-control @error('submission_end') is-invalid
                                                @enderror"
                                                    id="submission_end" name="submission_end"
                                                    placeholder="Submission End Date" value="{{ old('submission_end') }}"
                                                    autocomplete="off">
                                                @error('submission_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('submission_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_date" class="form-label">Revision Date<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-daterange">
                                                <input type="text"
                                                    class="form-control @error('revision_start') is-invalid
                                                @enderror"
                                                    id="revision_start" name="revision_start"
                                                    placeholder="Revision Start Date" value="{{ old('revision_start') }}"
                                                    autocomplete="off">
                                                <div class="input-group-text">to</div>
                                                <input type="text"
                                                    class="form-control @error('revision_end') is-invalid
                                                @enderror"
                                                    id="revision_end" name="revision_end" placeholder="Revision End Date"
                                                    value="{{ old('revision_end') }}" autocomplete="off">
                                                @error('revision_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('revision_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="event_date" class="form-label">Join Non Presenter Date<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-daterange">
                                                <input type="text"
                                                    class="form-control @error('join_np_start') is-invalid
                                                @enderror"
                                                    id="join_np_start" name="join_np_start"
                                                    placeholder="Join Non Presenter Start Date"
                                                    value="{{ old('join_np_start') }}" autocomplete="off">
                                                <div class="input-group-text">to</div>
                                                <input type="text"
                                                    class="form-control @error('join_np_end') is-invalid
                                                @enderror"
                                                    id="join_np_end" name="join_np_end"
                                                    placeholder="Join Non Presenter End Date"
                                                    value="{{ old('join_np_end') }}" autocomplete="off">
                                                @error('join_np_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('join_np_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="camera_ready_date" class="form-label">Camera Ready Date<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-daterange">
                                                <input type="text"
                                                    class="form-control @error('camera_ready_start') is-invalid
                                                @enderror"
                                                    id="camera_ready_start" name="camera_ready_start"
                                                    placeholder="Camera Ready Start Date"
                                                    value="{{ old('camera_ready_start') }}" autocomplete="off">
                                                <div class="input-group-text">to</div>
                                                <input type="text"
                                                    class="form-control @error('camera_ready_end') is-invalid
                                                @enderror"
                                                    id="camera_ready_end" name="camera_ready_end"
                                                    placeholder="Camera Ready End Date"
                                                    value="{{ old('camera_ready_end') }}" autocomplete="off">
                                                @error('camera_ready_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('camera_ready_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                        <div class="mb-3">
                                            <label for="payment_date" class="form-label">Payment Date<span
                                                    class="text-danger">*</span></label>
                                            <div class="input-group input-daterange">
                                                <input type="text"
                                                    class="form-control @error('payment_start') is-invalid
                                                @enderror"
                                                    id="payment_start" name="payment_start"
                                                    placeholder="Payment Start Date" value="{{ old('payment_start') }}"
                                                    autocomplete="off">
                                                <div class="input-group-text">to</div>
                                                <input type="text"
                                                    class="form-control @error('payment_end') is-invalid
                                                @enderror"
                                                    id="payment_end" name="payment_end" placeholder="Payment End Date"
                                                    value="{{ old('payment_end') }}" autocomplete="off">
                                                @error('payment_start')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                                @error('payment_end')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>
                                        </div>
                                    </div>
                                </fieldset>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step">Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next</button>
                                </div>
                            </div>
                            {{-- Step 3: Manager --}}
                            <div class="form-step d-none" id="step-3">
                                <fieldset class="mb-3">
                                    <legend>
                                        Manager
                                    </legend>
                                    <div class="mb-3">
                                        <label for="manager_name" class="form-label">Manager Name<span
                                                class="text-danger">*</span></label>
                                        <input type="text"
                                            class="form-control @error('manager_name') is-invalid @enderror"
                                            id="manager_name" name="manager_name" required
                                            value="{{ old('manager_name') }}" placeholder="Manager Name">
                                        @error('manager_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="manager_contact_email" class="form-label">Manager Email<span
                                                class="text-danger">*</span></label>
                                        <input type="email"
                                            class="form-control @error('manager_contact_email') is-invalid @enderror"
                                            id="manager_contact_email" name="manager_contact_email" required
                                            value="{{ old('manager_contact_email') }}" placeholder="Manager Email">
                                        @error('manager_contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="manager_contact_number" class="form-label">Manager Contact<span
                                                class="text-danger">*</span></label>
                                        <div class="input-group">
                                            <select class="form-select" name="manager_contact_country_code"
                                                id="manager_contact_country_code" style="max-width: 110px;">
                                                <option value="" selected disabled>Select Country Code</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->phonecode }}"
                                                        data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                        {{ old('manager_contact_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                        {{ $country->country_name . ' +' . $country->phonecode }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number"
                                                class="form-control @error('manager_contact_number') is-invalid @enderror"
                                                id="manager_contact_number" name="manager_contact_number" required
                                                value="{{ old('manager_contact_number') }}"
                                                placeholder="Manager Contact">
                                        </div>
                                        @error('manager_contact_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('manager_contact_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step">Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next</button>
                                </div>
                            </div>
                            {{-- Step 4: Treasurer --}}
                            <div class="form-step d-none" id="step-4">
                                <fieldset class="mb-3">
                                    <legend>Treasurer</legend>
                                    <div class="mb-3">
                                        <label for="treasure_name" class="form-label">Treasurer Name</label>
                                        <input type="text"
                                            class="form-control @error('treasure_name') is-invalid @enderror"
                                            id="treasure_name" name="treasure_name" value="{{ old('treasure_name') }}"
                                            placeholder="Treasurer Name">
                                        @error('treasure_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="treasure_contact_email" class="form-label">Treasurer Email</label>
                                        <input type="email"
                                            class="form-control @error('treasure_contact_email') is-invalid @enderror"
                                            id="treasure_contact_email" name="treasure_contact_email"
                                            value="{{ old('treasure_contact_email') }}" placeholder="Treasurer Email">
                                        @error('treasure_contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="treasure_contact_number" class="form-label">Treasurer Contact</label>
                                        <div class="input-group">
                                            <select class="form-select" name="treasure_contact_country_code"
                                                id="treasure_contact_country_code" style="max-width: 110px;">
                                                <option value="" selected disabled>Select Country Code</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->phonecode }}"
                                                        data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                        {{ old('treasure_contact_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                        {{ $country->country_name . ' +' . $country->phonecode }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number"
                                                class="form-control @error('treasure_contact_number') is-invalid @enderror"
                                                id="treasure_contact_number" name="treasure_contact_number"
                                                value="{{ old('treasure_contact_number') }}"
                                                placeholder="Treasurer Contact">
                                        </div>
                                        @error('treasure_contact_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('treasure_contact_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step">Previous</button>
                                    <button type="button" class="btn btn-primary next-step">Next</button>
                                </div>
                            </div>
                            {{-- Step 5: Support --}}
                            <div class="form-step d-none" id="step-5">
                                <fieldset class="mb-3">
                                    <legend>Support</legend>
                                    <div class="mb-3">
                                        <label for="support_name" class="form-label">Support Name</label>
                                        <input type="text"
                                            class="form-control @error('support_name') is-invalid @enderror"
                                            id="support_name" name="support_name" value="{{ old('support_name') }}"
                                            placeholder="Support Name">
                                        @error('support_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="support_contact_email" class="form-label">Support Email</label>
                                        <input type="email"
                                            class="form-control @error('support_contact_email') is-invalid @enderror"
                                            id="support_contact_email" name="support_contact_email"
                                            value="{{ old('support_contact_email') }}" placeholder="Support Email">
                                        @error('support_contact_email')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="mb-3">
                                        <label for="support_contact_number" class="form-label">Support Contact</label>
                                        <div class="input-group">
                                            <select class="form-select" name="support_contact_country_code"
                                                id="support_contact_country_code" style="max-width: 110px;">
                                                <option value="" selected disabled>Select Country Code</option>
                                                @foreach ($countries as $country)
                                                    <option value="{{ $country->phonecode }}"
                                                        data-label="{{ $country->country_name . ' +' . $country->phonecode }}"
                                                        {{ old('support_contact_country_code') == $country->phonecode ? 'selected' : '' }}>
                                                        {{ $country->country_name . ' +' . $country->phonecode }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            <input type="number"
                                                class="form-control @error('support_contact_number') is-invalid @enderror"
                                                id="support_contact_number" name="support_contact_number"
                                                value="{{ old('support_contact_number') }}"
                                                placeholder="Support Contact">
                                        </div>
                                        @error('support_contact_number')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                        @error('support_contact_country_code')
                                            <div class="invalid-feedback d-block">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </fieldset>
                                <div class="d-flex justify-content-between mt-4">
                                    <button type="button" class="btn btn-secondary prev-step">Previous</button>
                                    <button type="submit" class="btn btn-primary confirm-submit">Submit</button>
                                </div>
                            </div>
                        </div>
                        {{-- Page Number Indicator --}}
                        <div class="d-flex justify-content-center my-3">
                            <ul class="pagination mb-0" id="step-pagination">
                                <li class="page-item"><span class="page-link step-page" data-step="1">1</span></li>
                                <li class="page-item"><span class="page-link step-page" data-step="2">2</span></li>
                                <li class="page-item"><span class="page-link step-page" data-step="3">3</span></li>
                                <li class="page-item"><span class="page-link step-page" data-step="4">4</span></li>
                                <li class="page-item"><span class="page-link step-page" data-step="5">5</span></li>
                            </ul>
                        </div>
                        <div class="d-flex justify-content-between mt-4">
                            <button type="button" class="btn btn-danger cancel-submit">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener("DOMContentLoaded", function() {
            var inputTopics = document.querySelector('input[name=topics]');
            new Tagify(inputTopics)
        });

        function previewLogo(event) {
            var reader = new FileReader();
            reader.onload = function() {
                var output = document.getElementById('logo_preview');
                output.src = reader.result;
                output.style.display = 'block';
            };
            reader.readAsDataURL(event.target.files[0]);
        }
        document.getElementById('event_shortname').addEventListener('input', function() {
            const shortname = this.value;
            document.getElementById('event_code').placeholder = "Suggestion: " + shortname.toLowerCase().replace(
                /\s+/g, '_').replace(/[^a-z0-9_]/g, '');
        });
        $('.input-daterange input').each(function() {
            $(this).datepicker({
                orientation: "bottom"
            });
        });

        // Multi-step form logic
        document.addEventListener("DOMContentLoaded", function() {
            let currentStep = 1;
            const totalSteps = 5;

            function showStep(step) {
                for (let i = 1; i <= totalSteps; i++) {
                    document.getElementById('step-' + i).classList.add('d-none');
                    document.querySelector('#step-pagination .step-page[data-step="' + i + '"]').classList.remove(
                        'active');
                }
                document.getElementById('step-' + step).classList.remove('d-none');
                document.querySelector('#step-pagination .step-page[data-step="' + step + '"]').classList.add(
                    'active');
            }

            document.querySelectorAll('.next-step').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (currentStep < totalSteps) {
                        currentStep++;
                        showStep(currentStep);
                        window.scrollTo(0, 0);
                    }
                });
            });

            document.querySelectorAll('.prev-step').forEach(btn => {
                btn.addEventListener('click', function() {
                    if (currentStep > 1) {
                        currentStep--;
                        showStep(currentStep);
                        window.scrollTo(0, 0);
                    }
                });
            });

            // Pagination click
            document.querySelectorAll('#step-pagination .step-page').forEach(page => {
                page.addEventListener('click', function() {
                    const step = parseInt(this.getAttribute('data-step'));
                    if (!isNaN(step)) {
                        currentStep = step;
                        showStep(currentStep);
                        window.scrollTo(0, 0);
                    }
                });
            });

            showStep(currentStep);

            document.querySelectorAll(".confirm-submit").forEach(button => {
                button.addEventListener("click", function(e) {
                    e.preventDefault();
                    let form = this.closest("form");

                    Swal.fire({
                        title: "Are you sure want to submit?",
                        text: "You won't be able to revert this!",
                        icon: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#3085d6",
                        cancelButtonColor: "#d33",
                        cancelButtonText: "No, cancel!",
                        confirmButtonText: "Yes, submit!",
                    }).then((result) => {
                        if (result.isConfirmed) {
                            form.submit();
                        }
                    });
                });
            });

            const cancelBtn = document.querySelector('.cancel-submit');
            if (cancelBtn) {
                cancelBtn.addEventListener('click', function(e) {
                    e.preventDefault();
                    window.location.href = "{{ route('events') }}";
                });
            }
        });
    </script>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/css/intlTelInput.min.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/intl-tel-input/17.0.3/js/intlTelInput.min.js"></script>

    <script>
        function handleCountrySelect(selectId) {
            const select = document.getElementById(selectId);
            if (!select) return;

            const options = select.options;

            // Restore full label for all options
            for (let option of options) {
                const fullLabel = option.getAttribute('data-label');
                if (fullLabel) {
                    option.textContent = fullLabel;
                }
            }

            // Shorten only the selected one
            const selectedOption = select.options[select.selectedIndex];
            selectedOption.textContent = '+' + selectedOption.value;

            // When dropdown is opened, restore all labels for full list
            select.addEventListener('mousedown', () => {
                for (let option of options) {
                    const fullLabel = option.getAttribute('data-label');
                    if (fullLabel) {
                        option.textContent = fullLabel;
                    }
                }
            });

            // When changed, reapply short label to selected only
            select.addEventListener('change', () => {
                handleCountrySelect(selectId);
            });
        }

        // Initialize on page load
        handleCountrySelect('treasure_contact_country_code');
        handleCountrySelect('support_contact_country_code');
        handleCountrySelect('manager_contact_country_code');
    </script>
    
@endsection
