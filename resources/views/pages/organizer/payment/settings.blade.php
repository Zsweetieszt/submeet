@extends('layouts.event')

@section('title', 'Payment Settings - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><span>Payment Settings</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Payment Settings</h4>
            </div>
            <form action="{{ route('organizer.payment-settings-store', $event->event_code) }}" method="post" id="payment-form">
                @csrf
                @method('POST')
                <div class="card-body px-4">
                    <!-- Bank Account Settings Section -->
                    <div class="card mb-4">
                        <div class="card-header bg-dark text-white">
                            <h5 class="card-title mb-0"><i class="fas fa-university me-2"></i>Bank Account Settings</h5>
                        </div>
                        <div class="card-body">
                            <fieldset class="border p-3 mb-3 rounded">
                                <legend class="fs-6 fw-bold text-primary">Account Details</legend>
                                <div class="row">
                                    <div class="col-12 mb-3">
                                        <label for="acc_beneficiary_name" class="form-label">Beneficiary Name<span
                                                class="text-danger">*</span></label>
                                        <input type="text" required
                                            class="form-control @error('acc_beneficiary_name') is-invalid @enderror"
                                            id="acc_beneficiary_name" name="acc_beneficiary_name"
                                            value="{{ old('acc_beneficiary_name', $paymentSettings->acc_beneficiary_name ?? '') }}"
                                            placeholder="Beneficiary Name" maxlength="255">
                                        @error('acc_beneficiary_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-12 mb-3">
                                        <label for="acc_bank_name" class="form-label">Bank Name<span
                                                class="text-danger">*</span></label>
                                        <input type="text" required
                                            class="form-control @error('acc_bank_name') is-invalid @enderror"
                                            id="acc_bank_name" name="acc_bank_name"
                                            value="{{ old('acc_bank_name', $paymentSettings->acc_bank_name ?? '') }}"
                                            placeholder="Bank Name" maxlength="255">
                                        @error('acc_bank_name')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="acc_bank_acc" class="form-label">Account Number<span
                                                class="text-danger">*</span></label>
                                        <input type="text" required
                                            class="form-control @error('acc_bank_acc') is-invalid @enderror"
                                            id="acc_bank_acc" name="acc_bank_acc"
                                            value="{{ old('acc_bank_acc', $paymentSettings->acc_bank_acc ?? '') }}"
                                            placeholder="Account Number" maxlength="255">
                                        @error('acc_bank_acc')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                    <div class="col-6 mb-3">
                                        <label for="acc_swift_code" class="form-label">SWIFT Code<span
                                                class="text-danger">*</span></label>
                                        <input type="text" required
                                            class="form-control @error('acc_swift_code') is-invalid @enderror"
                                            id="acc_swift_code" name="acc_swift_code"
                                            value="{{ old('acc_swift_code', $paymentSettings->acc_swift_code ?? '') }}"
                                            placeholder="SWIFT Code" maxlength="255">
                                        @error('acc_swift_code')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>
                                </div>
                            </fieldset>
                        </div>
                    </div>


                    <div class="row">
                        <!-- National Section -->
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-primary text-white">
                                    <h5 class="card-title mb-0"><i class="fas fa-flag me-2"></i>National</h5>
                                </div>
                                <div class="card-body">
                                    <!-- Presenter Section -->
                                    <div class="mb-4">
                                        <h6 class="text-success mb-3"><i class="fas fa-user-tie me-2"></i>Presenter
                                        </h6>

                                        <!-- Online Presenter -->
                                        <fieldset class="border p-3 mb-3 rounded">
                                            <legend class="fs-6 fw-bold text-info">Online</legend>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="pay_as_pstr_on_ntl" class="form-label">Payment
                                                        Description</label>
                                                    <input type="text"
                                                        class="form-control @error('pay_as_pstr_on_ntl') is-invalid @enderror"
                                                        id="pay_as_pstr_on_ntl" name="pay_as_pstr_on_ntl"
                                                        value="{{ old('pay_as_pstr_on_ntl', $paymentSettings->pay_as_pstr_on_ntl ?? '') }}"
                                                        placeholder="Payment Description" maxlength="75">
                                                    @error('pay_as_pstr_on_ntl')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_on_ntl_curr" class="form-label">Currency<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" required
                                                        class="form-control @error('pay_as_pstr_on_ntl_curr') is-invalid @enderror"
                                                        id="pay_as_pstr_on_ntl_curr" name="pay_as_pstr_on_ntl_curr"
                                                        value="{{ old('pay_as_pstr_on_ntl_curr', $paymentSettings->pay_as_pstr_on_ntl_curr ?? '') }}"
                                                        placeholder="IDR" maxlength="10">
                                                    @error('pay_as_pstr_on_ntl_curr')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_on_ntl_amount" class="form-label">Amount<span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" required
                                                        class="form-control @error('pay_as_pstr_on_ntl_amount') is-invalid @enderror"
                                                        id="pay_as_pstr_on_ntl_amount" name="pay_as_pstr_on_ntl_amount"
                                                        value="{{ old('pay_as_pstr_on_ntl_amount', $paymentSettings->pay_as_pstr_on_ntl_amount ?? '') }}"
                                                        placeholder="0.00">
                                                    @error('pay_as_pstr_on_ntl_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </fieldset>

                                        <!-- Offline Presenter -->
                                        <fieldset class="border p-3 mb-3 rounded">
                                            <legend class="fs-6 fw-bold text-info">Offline</legend>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="pay_as_pstr_off_ntl" class="form-label">Payment
                                                        Description</label>
                                                    <input type="text"
                                                        class="form-control @error('pay_as_pstr_off_ntl') is-invalid @enderror"
                                                        id="pay_as_pstr_off_ntl" name="pay_as_pstr_off_ntl"
                                                        value="{{ old('pay_as_pstr_off_ntl', $paymentSettings->pay_as_pstr_off_ntl ?? '') }}"
                                                        placeholder="Payment Description" maxlength="75">
                                                    @error('pay_as_pstr_off_ntl')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_off_ntl_curr" class="form-label">Currency<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" required
                                                        class="form-control @error('pay_as_pstr_off_ntl_curr') is-invalid @enderror"
                                                        id="pay_as_pstr_off_ntl_curr" name="pay_as_pstr_off_ntl_curr"
                                                        value="{{ old('pay_as_pstr_off_ntl_curr', $paymentSettings->pay_as_pstr_off_ntl_curr ?? '') }}"
                                                        placeholder="IDR" maxlength="10">
                                                    @error('pay_as_pstr_off_ntl_curr')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_off_ntl_amount" class="form-label">Amount<span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" required
                                                        class="form-control @error('pay_as_pstr_off_ntl_amount') is-invalid @enderror"
                                                        id="pay_as_pstr_off_ntl_amount" name="pay_as_pstr_off_ntl_amount"
                                                        value="{{ old('pay_as_pstr_off_ntl_amount', $paymentSettings->pay_as_pstr_off_ntl_amount ?? '') }}"
                                                        placeholder="0.00">
                                                    @error('pay_as_pstr_off_ntl_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>

                                    <!-- Non-Presenter Section -->
                                    <div class="mb-4">
                                        <h6 class="text-warning mb-3"><i class="fas fa-user me-2"></i>Non-Presenter
                                        </h6>

                                        <!-- Offline Non-Presenter -->
                                        <fieldset class="border p-3 mb-3 rounded">
                                            <legend class="fs-6 fw-bold text-info">Offline</legend>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="pay_as_npstr_off_ntl" class="form-label">Payment
                                                        Description</label>
                                                    <input type="text"
                                                        class="form-control @error('pay_as_npstr_off_ntl') is-invalid @enderror"
                                                        id="pay_as_npstr_off_ntl" name="pay_as_npstr_off_ntl"
                                                        value="{{ old('pay_as_npstr_off_ntl', $paymentSettings->pay_as_npstr_off_ntl ?? '') }}"
                                                        placeholder="Payment Description" maxlength="75">
                                                    @error('pay_as_npstr_off_ntl')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_npstr_off_ntl_curr" class="form-label">Currency<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" required
                                                        class="form-control @error('pay_as_npstr_off_ntl_curr') is-invalid @enderror"
                                                        id="pay_as_npstr_off_ntl_curr" name="pay_as_npstr_off_ntl_curr"
                                                        value="{{ old('pay_as_npstr_off_ntl_curr', $paymentSettings->pay_as_npstr_off_ntl_curr ?? '') }}"
                                                        placeholder="IDR" maxlength="10">
                                                    @error('pay_as_npstr_off_ntl_curr')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_npstr_off_ntl_amount" class="form-label">Amount<span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" required
                                                        class="form-control @error('pay_as_npstr_off_ntl_amount') is-invalid @enderror"
                                                        id="pay_as_npstr_off_ntl_amount" name="pay_as_npstr_off_ntl_amount"
                                                        value="{{ old('pay_as_npstr_off_ntl_amount', $paymentSettings->pay_as_npstr_off_ntl_amount ?? '') }}"
                                                        placeholder="0.00">
                                                    @error('pay_as_npstr_off_ntl_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- International Section -->
                        <div class="col-lg-6">
                            <div class="card h-100">
                                <div class="card-header bg-success text-white">
                                    <h5 class="card-title mb-0"><i class="fas fa-globe me-2"></i>International
                                    </h5>
                                </div>
                                <div class="card-body">
                                    <!-- Presenter Section -->
                                    <div class="mb-4">
                                        <h6 class="text-success mb-3"><i class="fas fa-user-tie me-2"></i>Presenter
                                        </h6>

                                        <!-- Online Presenter -->
                                        <fieldset class="border p-3 mb-3 rounded">
                                            <legend class="fs-6 fw-bold text-info">Online</legend>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="pay_as_pstr_on_intl" class="form-label">Payment
                                                        Description</label>
                                                    <input type="text"
                                                        class="form-control @error('pay_as_pstr_on_intl') is-invalid @enderror"
                                                        id="pay_as_pstr_on_intl" name="pay_as_pstr_on_intl"
                                                        value="{{ old('pay_as_pstr_on_intl', $paymentSettings->pay_as_pstr_on_intl ?? '') }}"
                                                        placeholder="Payment Description" maxlength="75">
                                                    @error('pay_as_pstr_on_intl')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_on_intl_curr" class="form-label">Currency<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" required
                                                        class="form-control @error('pay_as_pstr_on_intl_curr') is-invalid @enderror"
                                                        id="pay_as_pstr_on_intl_curr" name="pay_as_pstr_on_intl_curr"
                                                        value="{{ old('pay_as_pstr_on_intl_curr', $paymentSettings->pay_as_pstr_on_intl_curr ?? '') }}"
                                                        placeholder="USD" maxlength="10">
                                                    @error('pay_as_pstr_on_intl_curr')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_on_intl_amount" class="form-label">Amount<span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" required
                                                        class="form-control @error('pay_as_pstr_on_intl_amount') is-invalid @enderror"
                                                        id="pay_as_pstr_on_intl_amount" name="pay_as_pstr_on_intl_amount"
                                                        value="{{ old('pay_as_pstr_on_intl_amount', $paymentSettings->pay_as_pstr_on_intl_amount ?? '') }}"
                                                        placeholder="0.00">
                                                    @error('pay_as_pstr_on_intl_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </fieldset>

                                        <!-- Offline Presenter -->
                                        <fieldset class="border p-3 mb-3 rounded">
                                            <legend class="fs-6 fw-bold text-info">Offline</legend>
                                            <div class="row">
                                                <div class="col-12 mb-3">
                                                    <label for="pay_as_pstr_off_intl" class="form-label">Payment
                                                        Description</label>
                                                    <input type="text"
                                                        class="form-control @error('pay_as_pstr_off_intl') is-invalid @enderror"
                                                        id="pay_as_pstr_off_intl" name="pay_as_pstr_off_intl"
                                                        value="{{ old('pay_as_pstr_off_intl', $paymentSettings->pay_as_pstr_off_intl ?? '') }}"
                                                        placeholder="Payment Description" maxlength="75">
                                                    @error('pay_as_pstr_off_intl')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_off_intl_curr" class="form-label">Currency<span
                                                            class="text-danger">*</span></label>
                                                    <input type="text" required
                                                        class="form-control @error('pay_as_pstr_off_intl_curr') is-invalid @enderror"
                                                        id="pay_as_pstr_off_intl_curr" name="pay_as_pstr_off_intl_curr"
                                                        value="{{ old('pay_as_pstr_off_intl_curr', $paymentSettings->pay_as_pstr_off_intl_curr ?? '') }}"
                                                        placeholder="USD" maxlength="10">
                                                    @error('pay_as_pstr_off_intl_curr')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                                <div class="col-6 mb-3">
                                                    <label for="pay_as_pstr_off_intl_amount" class="form-label">Amount<span
                                                            class="text-danger">*</span></label>
                                                    <input type="number" step="0.01" required
                                                        class="form-control @error('pay_as_pstr_off_intl_amount') is-invalid @enderror"
                                                        id="pay_as_pstr_off_intl_amount" name="pay_as_pstr_off_intl_amount"
                                                        value="{{ old('pay_as_pstr_off_intl_amount', $paymentSettings->pay_as_pstr_off_intl_amount ?? '') }}"
                                                        placeholder="0.00">
                                                    @error('pay_as_pstr_off_intl_amount')
                                                        <div class="invalid-feedback">{{ $message }}</div>
                                                    @enderror
                                                </div>
                                            </div>
                                        </fieldset>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </form>

            <div class="text-end p-4">
                <button class="btn btn-primary btn-md" type="submit" form="payment-form">
                    <i class="fas fa-save me-2"></i>Save Settings
                </button>
            </div>
        </div>
    </div>
    </div>
    </div>

    <script>
        document.querySelector('#payment-form').addEventListener('submit', function (e) {
            e.preventDefault();
            this.submit();
        });
    </script>
@endsection