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
    <li class="breadcrumb-item active"><span>My Payments</span>
    </li>
@endsection

@section('content')
    <?php
    $hasAccessPresenter = Auth::user()
        ->user_events()
        ->whereHas('role', function ($q) {
            $q->where('role_name', 'Presenter');
        })
        ->whereHas('event', function ($q) {
            $q->where('event_code', request()->route('event'));
        })
        ->exists();
    $hasAccessNonPresenter = Auth::user()
        ->user_events()
        ->whereHas('role', function ($q) {
            $q->where('role_name', 'Non-Presenter');
        })
        ->whereHas('event', function ($q) {
            $q->where('event_code', request()->route('event'));
        })
        ->exists() ?? null;

    $is_offline = Auth::user()
        ->user_events()
        ->whereHas('role', function ($q) {
            $q->whereIn('role_name', ['Presenter', 'Non-Presenter']);
        })
        ->whereHas('event', function ($q) {
            $q->where('event_code', request()->route('event'));
        })
        ->first()->is_offline;
            ?>
    <div class="container-fluid px-4">
        <div class="">
            <!-- /.row-->
            <div class="card mb-4">
                <div class="card-header">
                    <h4 class="card-title mb-0">My Payments</h4>
                </div>
                <div class="">
                    <div class="card-body px-4">
                        @if ($hasAccessPresenter)
                            <div class="table-responsive">
                                <table class="table border mb-0" id="mypayments">
                                    <thead class="fw-semibold text-nowrap">
                                        <tr class="align-middle">
                                            <th class="bg-body-secondary text-start">No.</th>
                                            <th class="bg-body-secondary text-start">Paper ID</th>
                                            <th class="bg-body-secondary text-start">Submission Date</th>
                                            <th class="bg-body-secondary text-start">Paper Title</th>
                                            <th class="bg-body-secondary text-start">Presenter</th>
                                            <th class="bg-body-secondary text-start">Country of Nationality</th>
                                            <th class="bg-body-secondary text-start">Attendance</th>
                                            <th class="bg-body-secondary text-start">Status</th>
                                            <th class="bg-body-secondary text-start">Last Updated</th>
                                            <th class="bg-body-secondary text-start">Action</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($papers as $paper)
                                            <tr class="align-middle">
                                                <td class="text-start"></td>
                                                <td class="text-start">
                                                    <span title="Current Paper ID: {{ $paper->paper_sub_id }}">
                                                        {{ $paper->first_paper_sub_id }}
                                                    </span>
                                                </td>
                                                <td> <span
                                                        title="{{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->format('M d, Y H:i:s \G\M\TP') }}">
                                                        {{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:sP') }}
                                                    </span>
                                                </td>
                                                <td>
                                                    @if ($paper->subtitle)
                                                        {{ $paper->title . ' : ' . $paper->subtitle }}
                                                    @else
                                                        {{ $paper->title }}
                                                    @endif
                                                </td>
                                                <td>{{ $paper?->payment?->presenter ?? '-' }}</td>
                                                <td>{{ $paper?->payment?->country?->country_name ?? '-' }}</td>
                                                <td>{{ $paper?->payment?->is_offline !== null ? ($paper?->payment?->is_offline == true ? 'Offline' : 'Online') : '-' }}</td>
                                                <td>
                                                    @if ($paper->payment->status ?? false)
                                                        @if ($paper->payment->status == 'Pending')
                                                            <i class="bi bi-clock-history text-warning"></i>
                                                            <span class="text-muted fst-italic">Awaiting payment
                                                                confirmation</span>
                                                        @elseif ($paper->payment->status == 'Paid')
                                                            <span class="badge bg-success">Paid</span>
                                                        @else
                                                            <span class="badge bg-danger">{{ $paper->payment->status }}</span>
                                                        @endif
                                                    @else
                                                        <span class="badge bg-danger">Unpaid</span>
                                                    @endif
                                                </td>
                                                <td class="text-start">
                                                    @if ($paper->payment->updated_at ?? false)
                                                        <span
                                                            title="{{ \Carbon\Carbon::parse($paper->payment->updated_at)->setTimezone('Asia/Jakarta')->format('M d, Y H:i:s \G\M\TP') }}">
                                                            {{ \Carbon\Carbon::parse($paper->payment->updated_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:sP') }}
                                                        </span>
                                                    @else
                                                        -
                                                    @endif
                                                </td>
                                                <td>
                                                    @if (isset($event->payment_start) && \Carbon\Carbon::now()->lt(\Carbon\Carbon::parse($event->payment_start)->startOfDay()))
                                                        <span class="text-muted">Not yet available</span>
                                                    @else
                                                        <a href="{{ route('index.receipt', [request()->route('event'), $paper->paper_sub_id]) }}"
                                                            class="btn btn-primary btn-sm">
                                                        {{ isset($paper->payment) && $paper->payment->status != 'Unpaid' ? 'Detail' : 'Pay' }}
                                                        </a>
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        @elseif($hasAccessNonPresenter)
                            <div class="">
                                <form enctype="multipart/form-data" id="apply_info_form"
                                    action="{{ route('upload.apply_payment_info', [request()->route('event'), 'non-presenter']) }}"
                                    method="post">
                                    @csrf
                                    @method('POST')
                                    @php
                                        $title_nationality = ($current_payment && $current_payment->nationality_country_id ?? auth()->user()->country_id) == $event->event_id ? 'National' : 'International';
                                        $title_offline = ($current_payment->is_offline ?? $is_offline) == true ? 'Offline' : 'Online';
                                    @endphp
                                    <!-- <div class="mb-3">
                                        <label for="paper_title" class="form-label">Title</label>
                                        <input disabled value="{{ $title_nationality . ' Non-Presenter ' . '('. $title_offline .')' }}" type="text" class="form-control"
                                            id="paper_title" name="paper_title" required>
                                    </div> -->

                                    <div class="mb-3">
                                        <label for="country_of_nationality" class="form-label">Country of Nationality<span
                                                class="text-danger">*</span></label>
                                        <select class="form-control form-select @error('country_of_nationality') is-invalid @enderror"
                                            id="country_of_nationality" name="country_of_nationality" {{ $current_payment && $current_payment->status != 'Unpaid' && $current_payment->status != null ? 'disabled' : 'required' }}>
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

                                    @if (((isset($current_payment) && ($current_payment->status == 'Unpaid' || $current_payment->status == null)) || !isset($current_payment)))
                                        <button type="button" id="submit_button" onclick="submitForm()"
                                            class="btn btn-primary">Apply</button>
                                    @endif
                                </form>
                            </div>
                            @if ($current_payment && isset($current_payment))
                                <div class="mt-4">
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
                                                            $amount_display = number_format($payment_set->pay_as_npstr_off_ntl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_npstr_off_ntl_curr ?? 'IDR';
                                                            $role = 'National Presenter (Offline)';
                                                            break;
                                                        default: // Online
                                                            $amount_display = number_format($payment_set->pay_as_npstr_on_ntl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_npstr_on_ntl_curr ?? 'IDR';
                                                            $role = 'National Presenter (Online)';
                                                            break;
                                                    }
                                                    break;
                                                default: // International
                                                    switch ($current_payment->is_offline) {
                                                        case 1: // Offline
                                                            $amount_display = number_format($payment_set->pay_as_npstr_off_intl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_npstr_off_intl_curr ?? 'USD';
                                                            $role = 'International Presenter (Offline)';
                                                            break;
                                                        default: // Online
                                                            $amount_display = number_format($payment_set->pay_as_npstr_on_intl_amount ?? 0, 0, ',', '.');
                                                            $currency = $payment_set->pay_as_npstr_on_intl_curr ?? 'USD';
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
                                                        <span class="ms-2 text-muted">Uploaded on {{ $current_payment->upload_receipt_at }}</span>
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
                                                            <input type="file" class="form-control" id="receipt" name="receipt"
                                                                required>
                                                            <small class="form-text text-muted">
                                                                <i class="fas fa-info-circle"></i> Allowed file types: PNG, JPG, JPEG,
                                                                PDF
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
                                                        <button type="button" onclick="submitForm2()"
                                                            class="btn btn-success mt-4">Upload
                                                            Payment Proof</button>
                                                    </form>
                                                </div>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>
        $('#mypayments').DataTable({
            order: [[1, 'asc']],
            columnDefs: [
                {
                    targets: 0,
                    orderable: false,
                    searchable: false,
                    render: function (data, type, row, meta) {
                        return meta.row + 1;
                    }
                },
                { targets: 9, orderable: false, searchable: false }
            ],
            drawCallback: function(settings) {
                var api = this.api();
                api.column(0, {page: 'current'}).nodes().each(function(cell, i) {
                    cell.innerHTML = i + 1;
                });
            }
        });
    </script>
    <script>
        function submitForm() {
            Swal.fire({
                title: 'Do you want to upload this receipt?',
                text: `this action cannot be undone!`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, upload it!'
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