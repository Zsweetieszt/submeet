@extends('layouts.event')

@section('title', 'Payments - ' . $event->event_name)

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><a href="{{ route(name: 'events') }}">Events</a>
    </li>
    <li class="breadcrumb-item active"><a
            href="{{ route('dashboard.event', request()->route('event')) }}">{{ $event->event_name }}</a>
    </li>
    <li class="breadcrumb-item active"><span>Payments</span>
    </li>
@endsection

@section('content')
    <div class="container-fluid px-4">
        <div class="card mb-4">
            <div class="card-header">
                <h4 class="card-title mb-0">Payments</h4>
            </div>
            <div class="">
                <div class="card-body px-4">
                    <div class="table-responsive">
                        <table class="table border mb-0" id="member">
                            <thead class="fw-semibold text-nowrap">
                                <tr class="align-middle">
                                    <th class="bg-body-secondary text-start">No.</th>
                                    <th class="bg-body-secondary text-start">Paper ID</th>
                                    <th class="bg-body-secondary text-start">Submission Date</th>
                                    <th class="bg-body-secondary text-start">Paper Title</th>
                                    <th class="bg-body-secondary text-start">Submitted by</th>
                                    <th class="bg-body-secondary text-start">Presenter</th>
                                    <th class="bg-body-secondary text-start">Country of Nationality</th>
                                    <th class="bg-body-secondary text-start">Attendance</th>
                                    <th class="bg-body-secondary text-start">Registration Fee</th>
                                    <th class="bg-body-secondary text-start">Join As</th>
                                    <th class="bg-body-secondary text-start">Status</th>
                                    <th class="bg-body-secondary text-start">Last Updated</th>
                                    <th class="bg-body-secondary text-start">Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($payments as $payment)
                                    @php
                                        $paper = $payment->paper ?? null;
                                        $role = $paper ? 'Presenter' : 'Non-Presenter';

                                        $is_offline = $payment->is_offline;

                                        $is_national = $payment->nationality_country_id == $event->country_id;
                                    @endphp
                                    <tr>
                                        <td class="text-center"></td>
                                        <td class="text-center">
                                            {{ $payment->first_paper_sub_id ?? '-' }}
                                        </td>
                                        <td>
                                            @if ($paper->created_at ?? false)
                                                <span
                                                    title="{{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->format('M d, Y H:i:s \G\M\TP') }}">
                                                    {{ \Carbon\Carbon::parse($paper->created_at)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:sP') }}
                                                </span>
                                            @else
                                                -
                                            @endif
                                        </td>
                                        @if ($paper->subtitle ?? false)
                                            <td>{{ $paper->title . ' : ' . $paper->subtitle ?? '-' }}</td>
                                        @else
                                            <td>{{ $paper->title ?? '-' }}</td>
                                        @endif
                                        <td>
                                            {{ ($paper?->user ?? $payment?->user)?->given_name . ' ' . ($paper?->user ?? $payment?->user)?->family_name }}
                                        </td>
                                        <td>
                                            {{ ($paper?->payment?->presenter ?? 'Non-Presenter') }}
                                        </td>
                                        <td>
                                            {{ ($payment?->country?->country_name) }}
                                        </td>
                                        <td>
                                            {{ $payment?->is_offline == true ? 'Offline' : 'Online' }}
                                        </td>
                                        <td>
                                            @if ($role == 'Presenter')
                                                @if ($is_offline == true)
                                                    @if ($is_national)
                                                        {{ $payment_set->pay_as_pstr_off_ntl_curr ?? 'IDR' }}
                                                        {{ number_format($payment_set->pay_as_pstr_off_ntl_amount ?? 0) }}
                                                    @else
                                                        {{ $payment_set->pay_as_pstr_off_intl_curr ?? 'USD' }}
                                                        {{ number_format($payment_set->pay_as_pstr_off_intl_amount ?? 0) }}
                                                    @endif
                                                @else
                                                    @if ($is_national)
                                                        {{ $payment_set->pay_as_pstr_on_ntl_curr ?? 'IDR' }}
                                                        {{ number_format($payment_set->pay_as_pstr_on_ntl_amount ?? 0) }}
                                                    @else
                                                        {{ $payment_set->pay_as_pstr_on_intl_curr ?? 'USD' }}
                                                        {{ number_format($payment_set->pay_as_pstr_on_intl_amount ?? 0) }}
                                                    @endif
                                                @endif
                                            @else
                                                @if ($is_offline == true)
                                                    @if ($is_national)
                                                        {{ $payment_set->pay_as_npstr_off_ntl_curr ?? 'IDR' }}
                                                        {{ number_format($payment_set->pay_as_npstr_off_ntl_amount ?? 0) }}
                                                    @else
                                                        {{ $payment_set->pay_as_npstr_off_intl_curr ?? 'USD' }}
                                                        {{ number_format($payment_set->pay_as_npstr_off_intl_amount ?? 0) }}
                                                    @endif
                                                @else
                                                    @if ($is_national)
                                                        {{ $payment_set->pay_as_npstr_on_ntl_curr ?? 'IDR' }}
                                                        {{ number_format($payment_set->pay_as_npstr_on_ntl_amount ?? 0) }}
                                                    @else
                                                        {{ $payment_set->pay_as_npstr_on_intl_curr ?? 'USD' }}
                                                        {{ number_format($payment_set->pay_as_npstr_on_intl_amount ?? 0) }}
                                                    @endif
                                                @endif
                                            @endif
                                        </td>
                                        <td>
                                            @if ($is_national)
                                                National
                                            @else
                                                International
                                            @endif
                                            {{ ' ' . $role . ' ' . ($is_offline == true ? "(Offline)" : "(Online)")}}
                                        </td>
                                        <td>
                                            @php
                                                $status = $paper?->payment->status ?: $payment->status ?: 'Unpaid';
                                                $badgeClass = match ($status) {
                                                    'Paid' => 'bg-success',
                                                    'Pending' => 'bg-warning',
                                                    default => 'bg-danger'
                                                };
                                            @endphp
                                            <span class="badge {{ $badgeClass }}">{{ $status }}</span>
                                        </td>
                                        <td class="text-start">{{ $paper?->payment->updated_at ?? $payment->updated_at }}</td>
                                        <td>
                                            @if(\Carbon\Carbon::parse($event->payment_start) <= \Carbon\Carbon::now() && \Carbon\Carbon::parse($event->payment_end)->endOfDay() >= \Carbon\Carbon::now())
                                                <div class="d-flex gap-2">
                                                    @if ($payment->status === 'Pending' || $payment->status === 'Unpaid')
                                                        <form
                                                            action="{{ route('organizer.payment_update', ([$event->event_code, $payment->payment_id])) }}"
                                                            method="POST" id="payment-form-{{ $payment->payment_id }}">
                                                            @csrf
                                                            @method('PUT')
                                                            <button type="button" class="btn btn-success btn-sm"
                                                                onclick="confirmPaymentUpdate('{{ $payment->payment_id }}')">
                                                                Confirm
                                                            </button>
                                                        </form>
                                                    @elseif ($payment->status === 'Paid')
                                                        <div class="d-flex gap-2">
                                                            <form
                                                                action="{{ route('organizer.payment_update', ([$event->event_code, $payment->payment_id])) }}"
                                                                method="POST" id="payment-status-{{ $payment->payment_id }}">
                                                                @csrf
                                                                @method('PUT')
                                                                <button type="button" class="btn btn-danger btn-sm"
                                                                    onclick="changePaymentStatus('{{ $payment->payment_id }}')">
                                                                    Change to Unpaid
                                                                </button>
                                                            </form>
                                                        </div>
                                                    @endif
                                                    @if ($payment->status !== 'Unpaid')
                                                        <form
                                                            action="{{ route('download.receipt', [request()->route('event'), $payment->payment_id]) }}"
                                                            method="post">
                                                            @csrf
                                                            @method('POST')
                                                            <button type="submit" class="btn btn-primary btn-sm">
                                                                Download Payment Proof</button>
                                                        </form>
                                                    @endif
                                                </div>
                                            @endif
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
            {
                targets: 0,
                orderable: false,
                searchable: false,
                width: '4%',
                render: function (data, type, row, meta) {
                    return meta.row + 1;
                }
            },
            { targets: 12, orderable: false, searchable: false }
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
        function changePaymentStatus(paperId) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to change this payment to unpaid?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`payment-status-${paperId}`).submit();
                }
            });
        }
    </script>
    <script>
        function confirmPaymentUpdate(paperId) {
            Swal.fire({
                title: 'Are you sure?',
                text: `Do you want to confirm this payment?`,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#3085d6',
                cancelButtonColor: '#d33',
                confirmButtonText: 'Yes, update it!'
            }).then((result) => {
                if (result.isConfirmed) {
                    document.getElementById(`payment-form-${paperId}`).submit();
                }
            });
        }
    </script>
@endsection