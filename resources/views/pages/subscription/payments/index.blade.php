@extends('layouts.app')

@section('title', 'Subscription History')

@section('content')
<div class="container-lg">
    <div class="card mb-4">
        <div class="card-header"><strong>Riwayat Subscription & Pembayaran</strong></div>
        <div class="card-body">
            @if(session('success'))
                <div class="alert alert-success">{{ session('success') }}</div>
            @endif

            <div class="table-responsive">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Paket</th>
                            <th>Harga</th>
                            <th>Status</th>
                            <th>Bukti Bayar</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($subscriptions as $sub)
                            <tr>
                                <td>{{ $sub->plan_name }}</td>
                                <td>IDR {{ number_format($sub->price, 0, ',', '.') }}</td>
                                <td>
                                    @if($sub->status == 'pending')
                                        <span class="badge bg-warning text-dark">Menunggu Pembayaran/Verifikasi</span>
                                    @elseif($sub->status == 'active')
                                        <span class="badge bg-success">Aktif</span>
                                    @elseif($sub->status == 'rejected')
                                        <span class="badge bg-danger">Ditolak</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sub->payment_proof)
                                        <a href="{{ Storage::url($sub->payment_proof) }}" target="_blank">Lihat File</a>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    @if($sub->status == 'pending')
                                        <a href="{{ route('subscription.payment.show', $sub->id) }}" class="btn btn-sm btn-primary">
                                            Bayar / Upload
                                        </a>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="text-center">Belum ada riwayat subscription.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection