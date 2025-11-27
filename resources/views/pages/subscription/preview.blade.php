@extends('layouts.app')

@section('title', 'Konfirmasi Paket')

@section('content')
<style>
    body {
        background-color: #212529; 
        color: #ffffff;
    }
    .card {
        background-color: #2d323b;
        border: 1px solid #495057; 
        border-radius: 12px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.3);
    }
    .card-header {
        background-color: #6f42c1; 
        border-bottom: 1px solid #5a36a0;
    }
    .card-body {
        background-color: #2d323b; 
    }
    .card-footer {
        background-color: #2d323b;
        border-top: 1px solid #495057;
    }
    .alert-warning {
        background-color: #ffc107;
        color: #212529;
        border: 1px solid #e0a800;
    }
    .list-group-item {
        background-color: transparent;
        border: none;
        color: #ffffff;
    }
    .btn-primary {
        background-color: #6f42c1;
        border-color: #5a36a0;
    }
    .btn-outline-secondary {
        color: #adb5bd;
        border-color: #495057;
    }
    .btn-outline-secondary:hover {
        background-color: #495057;
        color: #ffffff;
    }
</style>

<div class="container-lg py-4">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card mb-4 shadow-lg border-0">

               
                <div class="card-header text-white py-3">
                    <h4 class="mb-0 fw-bold"><i class="cil-cart me-2"></i> Konfirmasi Pilihan Paket</h4>
                </div>

                <div class="card-body p-4">
                   
                    <div class="text-center mb-4 pb-3 border-bottom border-secondary border-opacity-50">
                        <h5 class="text-muted text-uppercase ls-1 mb-1">Paket yang dipilih</h5>
                        <h2 class="text-primary fw-bold display-6">{{ $selectedPlan->name }}</h2>
                        <h3 class="text-white fw-extrabold">{{ $selectedPlan->price }}</h3>
                    </div>

                    <div class="alert alert-warning d-flex align-items-center" role="alert">
                        <svg class="bi flex-shrink-0 me-2" width="24" height="24" fill="currentColor">
                            <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-info') }}"/>
                        </svg>
                        <div>
                            <strong>Info:</strong> Anda belum dikenakan biaya. Silakan tinjau detail di bawah ini.
                        </div>
                    </div>

                    
                    <div class="card bg-dark border border-secondary border-opacity-50 mb-4">
                        <div class="card-body">
                            <h5 class="card-title text-primary mb-3">
                                <i class="cil-star me-2"></i> Keuntungan Paket:
                            </h5>
                            <ul class="list-group list-group-flush bg-transparent">
                                @foreach ($selectedPlan->features as $feature)
                                    <li class="list-group-item bg-transparent border-0 ps-0 py-2">
                                        <i class="cil-check-circle text-success me-2"></i> 
                                        <span class="fw-medium">{{ $feature }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>

                    
                    <div class="d-grid gap-2">
                        <form action="{{ route('subscription.purchase') }}" method="POST">
                            @csrf
                            <input type="hidden" name="plan_id" value="{{ $selectedPlan->id }}">
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100 mb-2 py-3 fw-bold shadow-sm">
                                <i class="cil-description me-2"></i> BUAT TAGIHAN (INVOICE)
                            </button>
                        </form>
                        
                        <a href="{{ route('subscription') }}" class="btn btn-outline-secondary py-2">
                            <i class="cil-arrow-left me-2"></i> Batal / Pilih Paket Lain
                        </a>
                    </div>
                </div>
                
             
                <div class="card-footer text-center text-muted py-3">
                    <small>Submeet Event Management System</small>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection