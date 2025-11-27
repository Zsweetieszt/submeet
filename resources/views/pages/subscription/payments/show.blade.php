@extends('layouts.app')

@section('title', 'Invoice #' . $subscription->id)

@section('content')
<div class="container-lg">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4">
                <div class="card-header">
                    <strong>Invoice #{{ $subscription->id }}</strong>
                    <span class="float-end badge bg-{{ $subscription->status == 'active' ? 'success' : 'warning' }}">
                        {{ strtoupper($subscription->status) }}
                    </span>
                </div>
                <div class="card-body">
                    @if(session('success'))
                        <div class="alert alert-success">{{ session('success') }}</div>
                    @endif

                   
                    <div class="mb-4">
                        <h5>Detail Pesanan</h5>
                        <table class="table table-bordered">
                            <tr>
                                <th width="150">Paket</th>
                                <td>{{ $subscription->plan_name }}</td>
                            </tr>
                            <tr>
                                <th>Total price</th>
                                <td class="fw-bold fs-5">IDR {{ number_format($subscription->price, 0, ',', '.') }}</td>
                            </tr>
                         
                        </table>
                    </div>

                   
                    @if($subscription->status == 'pending')
                        <div class="alert alert-info">
                            <h5 class="alert-heading"><i class="cil-bank me-2"></i>Payment Instructions</h5>
                            <p>Please transfer the above amount to the following account:</p>
                            <hr>
                            <ul class="mb-0 list-unstyled">
                                <li><strong>Bank:</strong> {{ $bankInfo['bank_name'] }}</li>
                                <li><strong>No. Rekening:</strong> {{ $bankInfo['account_number'] }}</li>
                                <li><strong>Atas Nama:</strong> {{ $bankInfo['account_holder'] }}</li>
                            </ul>
                        </div>

                       
                        <div class="mt-4">
                            <h5>Payment Confirmation</h5>
                            <p class="text-muted">Have you made the transfer? Please upload proof of your transfer here so the admin can process your package activation.</p>
                            
                            <form action="{{ route('subscription.payment.upload', $subscription->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <div class="mb-3">
                                    <label for="payment_proof" class="form-label">Upload Proof of Transfer (JPG/PNG)</label>
                                    <input type="file" class="form-control @error('payment_proof') is-invalid @enderror" id="payment_proof" name="payment_proof" required>
                                    @error('payment_proof')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                
                                @if($subscription->payment_proof)
                                    <div class="mb-3">
                                        <small class="text-success">
                                            <i class="cil-check-circle"></i> Proof of transfer has been uploaded. You can re-upload it if you need to revise it.
                                        </small>
                                        <div class="mt-2">
                                            <a href="{{ Storage::url($subscription->payment_proof) }}" target="_blank" class="btn btn-sm btn-outline-secondary">View Uploaded Proof</a>
                                        </div>
                                    </div>
                                @endif

                                <button type="submit" class="btn btn-primary">
                                    <i class="cil-cloud-upload me-2"></i> Upload Proof of Payment
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($subscription->status == 'active')
                        <div class="alert alert-success text-center">
                            <h4>Paket Anda Sudah Aktif!</h4>
                            <p>Anda sekarang sudah bisa mengakses menu Organizer.</p>
                            <a href="{{ route('events') }}" class="btn btn-success text-white">Buat Event Sekarang</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection