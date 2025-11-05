@extends('layouts.event')

@section('title', 'My Payments - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route('index.my-payments', request()->route('event')) }}">My Payments</a>
    </li>
    <li class="breadcrumb-item active"><span>Payment Information</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="">
            <!-- /.row-->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">Payment Information</h4>
                </div>
                <div class="">
                    <div class="card-body px-4">
                        <form enctype="multipart/form-data" id="apply_info_form"
                            action="{{ route('upload.apply_payment_info', [request()->route('event'), $paper->paper_sub_id]) }}"
                            method="post">
                            @csrf
                            @method('POST')
                            <div class="mb-3">
                                <label for="paper_title" class="form-label">Paper Title</label>
                                <input disabled value="{{ $paper->title }}" type="text" class="form-control"
                                    id="paper_title" name="paper_title" required>
                            </div>

                            <div class="mb-3">
                                <input disabled value="{{ $paper->paper_sub_id }}" type="text" class="form-control"
                                    id="paper" hidden name="paper" required>
                            </div>

                            <div class="mb-3">
                                <label for="presenter" class="form-label">Presenter<span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select @error('presenter') is-invalid @enderror" id="presenter"
                                    name="presenter" {{ $current_payment && $current_payment->status != 'Unpaid' && $current_payment->status != null ? 'disabled' : 'required' }}>
                                    <option value="">Select Presenter</option>
                                    @foreach ($paper_authors as $pa)
                                        <option value="{{ $pa['name'] . ' - ' . $pa['email'] }}" {{ $current_payment && ($pa['name'] . ' - ' . $pa['email']) == $current_payment?->presenter ? 'selected' : '' }}>{{ $pa['name'] . ' - ' . $pa['email'] }}</option>
                                    @endforeach
                                </select>
                                @error('presenter')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="country_of_nationality" class="form-label">Country of Nationality<span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select @error('country_of_nationality') is-invalid @enderror"
                                    id="country_of_nationality" name="country_of_nationality"  {{ $current_payment && $current_payment->status != 'Unpaid' && $current_payment->status != null ? 'disabled' : 'required' }}>
                                    <option value="">Select Country</option>
                                    @foreach ($countries as $country)
                                        <option value="{{ $country->country_id }}" {{ $current_payment && $country->country_id == $current_payment?->nationality_country_id ? 'selected' : '' }}>
                                            {{ $country->country_name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('country_of_nationality')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="attendance" class="form-label">Attendance<span
                                        class="text-danger">*</span></label>
                                <select class="form-control form-select @error('attendance') is-invalid @enderror" id="attendance"
                                    name="attendance" {{ $current_payment && $current_payment->status != 'Unpaid' && $current_payment->status != null ? 'disabled' : 'required' }}>
                                    <option value="">Select Attendance Type</option>
                                    <option value="0" {{ $current_payment && $current_payment->is_offline == 0 ? 'selected' : '' }}>
                                        Online</option>
                                    <option value="1" {{ $current_payment && $current_payment->is_offline == 1 ? 'selected' : '' }}>
                                        Offline</option>
                                </select>
                                @error('attendance')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            @if (!($current_payment && $current_payment->status != 'Unpaid' && $current_payment->status != null))
                                <script>
                                    document.getElementById('country_of_nationality').addEventListener('change', function () {
                                        const countrySelect = this;
                                        const attendanceSelect = document.getElementById('attendance');
                                        const eventCountryId = {{ $event->country_id ?? 'null' }}; // Assuming event has country_id

                                        if (countrySelect.value && eventCountryId && countrySelect.value == eventCountryId) {
                                            attendanceSelect.value = '1';
                                            attendanceSelect.setAttribute('disabled', true);

                                            const hiddenAttendance = document.createElement('input');
                                            hiddenAttendance.type = 'hidden';
                                            hiddenAttendance.name = 'attendance';
                                            hiddenAttendance.value = '1';
                                            attendanceSelect.parentNode.appendChild(hiddenAttendance);
                                        } else {
                                            const existingHiddenInputs = attendanceSelect.parentNode.querySelectorAll('input[type="hidden"][name="attendance"]');
                                            existingHiddenInputs.forEach(input => input.remove());
                                            attendanceSelect.removeAttribute('disabled');
                                            if (!countrySelect.value) {
                                                attendanceSelect.value = '';
                                            }
                                        }
                                    });

                                    // Run on page load in case country is already selected
                                    document.addEventListener('DOMContentLoaded', function () {
                                        document.getElementById('country_of_nationality').dispatchEvent(new Event('change'));
                                    });
                                </script>
                            @endif

                            @if (((isset($current_payment) && ($current_payment->status == 'Unpaid' || $current_payment->status == null)) || !isset($current_payment)))
                                <button type="button" id="submit_button" onclick="submitForm()"
                                class="btn btn-primary">Apply</button>
                            @endif
                        </form>
                    </div>
                    
                    @if ($current_payment && isset($current_payment))
                        <div class="p-4">
                            <div class="card mt-2">
                                <div class="card-header">
                                    <h5 class="card-title mb-0">Registration Fee Payment</h5>
                                </div>
                                <div class="card-body">
                                    @php
                                        $amount_display = '';
                                        $currency = '';
                                        $role = '';

                                        if ($payment_set) {
                                            switch ($current_payment->nationality_country_id == $event->country_id) {
                                                case true: // National
                                                    switch ($current_payment->is_offline) {
                                                        case 1: // Offline
                                                            $amount_display = number_format($payment_set->pay_as_pstr_off_ntl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_pstr_off_ntl_curr ?? 'IDR';
                                                            $role = 'National Presenter (Offline)';
                                                            break;
                                                        default: // Online
                                                            $amount_display = number_format($payment_set->pay_as_pstr_on_ntl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_pstr_on_ntl_curr ?? 'IDR';
                                                            $role = 'National Presenter (Online)';
                                                            break;
                                                    }
                                                    break;
                                                default: // International
                                                    switch ($current_payment->is_offline) {
                                                        case 1: // Offline
                                                            $amount_display = number_format($payment_set->pay_as_pstr_off_intl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_pstr_off_intl_curr ?? 'USD';
                                                            $role = 'International Presenter (Offline)';
                                                            break;
                                                        default: // Online
                                                            $amount_display = number_format($payment_set->pay_as_pstr_on_intl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_pstr_on_intl_curr ?? 'USD';
                                                            $role = 'International Presenter (Online)';
                                                            break;
                                                    }
                                                    break;
                                            }
                                        } else {
                                            $amount_display = 0;
                                            $currency = '';
                                            $role = '';
                                        }
                                    @endphp
                                    
                                    @if($current_payment->nationality_country_id == $event->country_id)
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 class="fw-bold">Virtual Account</h6>
                                                <div class="border p-3 rounded">
                                                    <p class="mb-1"><strong>VA Number:</strong>
                                                        {{ $existingPaymentHistory->brivano ?? '000' }}</p>
                                                    <p class="mb-1"><strong>Bank:</strong>
                                                        {{ $payment_set->acc_bank_name ?? 'Not Available' }}</p>
                                                    <p class="mb-0"><strong>Registration Fee:</strong>
                                                        {{ $currency }} {{ $amount_display }}
                                                        <small class="text-muted">(excludes transfer fee)</small>
                                                        <strong>for {{ $role }}</strong>
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @else
                                        <div class="row">
                                            <div class="col-md-12">
                                                <h6 class="fw-bold">Bank Transfer</h6>
                                                <div class="border p-3 rounded">
                                                    <p class="mb-1"><strong>Bank:</strong>
                                                        {{ $payment_set->acc_bank_name ?? 'Not Available' }} </p>
                                                    <p class="mb-1"><strong>Account Number:</strong>
                                                        {{ $payment_set->acc_bank_acc ?? 'Not Available' }} </p>
                                                    <p class="mb-1"><strong>Account Name:</strong>
                                                        {{ $payment_set->acc_beneficiary_name ?? 'Not Available' }} </p>
                                                    <p class="mb-0"><strong>Registration Fee:</strong>
                                                        {{ $currency }} {{ $amount_display }}
                                                        <strong>for {{ $role }}</strong>
                                                    </p>
                                                    <p class="mb-0"><strong>Swift Code:</strong>
                                                        {{ $payment_set->acc_swift_code ?? 'Not Available' }}
                                                    </p>
                                                </div>
                                            </div>
                                        </div>
                                    @endif

                                    <div class="mt-4">
                                        <h6 class="fw-bold">Payment Status</h6>
                                        @if($current_payment->status == 'Paid')
                                            <span class="badge bg-success">
                                                Paid
                                            </span>
                                        @elseif($current_payment->status == 'Unpaid')
                                            <span class="badge bg-danger">
                                                Unpaid
                                            </span>
                                        @elseif($current_payment->status == 'Pending')
                                            <span class="badge bg-warning">
                                                Pending
                                            </span>
                                        @else
                                            -
                                        @endif
                                    </div>

                                    @if ($existingPaymentHistory && $existingPaymentHistory->upload_receipt_at && $current_payment->status != 'Unpaid')
                                        <div class="mt-4">  
                                            <h6 class="fw-bold">Payment Proof</h6>
                                            <div class="d-flex align-items-center">
                                                <form
                                                    action="{{ route('download.receipt', [request()->route('event'), $current_payment->payment_id]) }}"
                                                    method="post">
                                                    @csrf
                                                    <button type="submit" class="btn btn-primary btn-sm">
                                                        <i class="fas fa-download"></i> Download Payment Proof
                                                    </button>
                                                </form>
                                                <span class="ms-2 text-muted">Uploaded on {{ $existingPaymentHistory->upload_receipt_at }}</span>
                                            </div>
                                        </div>
                                    @elseif($current_payment->status == 'Unpaid' || $current_payment->status == null)
                                        <div class="mt-4">
                                            <form id="form_payment_proof" enctype="multipart/form-data"
                                                action="{{ route('upload.payment_proof', [request()->route('event'), $current_payment->payment_id]) }}"
                                                method="post">
                                                @csrf
                                                <div class="mb-3">
                                                    <label for="receipt" class="form-label">Upload Payment Proof<span
                                                            class="text-danger">*</span></label>
                                                    <input type="file" class="form-control" id="receipt" name="receipt" required>
                                                    <small class="form-text text-muted">
                                                        <i class="fas fa-info-circle"></i> Allowed file types: PNG, JPG, JPEG, PDF
                                                        (Max size: 2MB)
                                                    </small>
                                                </div>
                                                <div class="mb-3">
                                                    <label for="desc" class="form-label">Description (Optional)</label>
                                                    <textarea class="form-control @error('desc') is-invalid @enderror" id="desc"
                                                        name="desc" rows="3" maxlength="500">{{ old('desc') }}</textarea>
                                                    <small class="form-text text-muted">Maximum 500 characters</small>
                                                    @error('desc')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <button type="button" onclick="submitForm2()" class="btn btn-success mt-4">Upload
                                                    Payment Proof</button>
                                            </form>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        function submitForm() {
            Swal.fire({
                title: 'Are you sure want to apply?',
                text: `this action will decide ur registration fee!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, apply it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('apply_info_form').submit();
                }
            });
        }

        function submitForm2() {
            Swal.fire({
                title: 'Are you sure want to upload?',
                text: `this action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, upload it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById('form_payment_proof').submit();
                }
            });
        }
    </script>

@endsection