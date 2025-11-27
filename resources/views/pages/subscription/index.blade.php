@extends('layouts.app')

@section('title', 'Subscription Packages')

@section('breadcrumb')
    <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a>
    </li>
    <li class="breadcrumb-item active"><span>Subscription</span>
    </li>
@endsection

<?php 

$hasAccessReviewer = Auth::user()
    ->user_events()
    ->whereHas('role', function ($q) {
        $q->where('role_name', 'Paper Reviewer');
    })->exists();

$hasAccessPresenter = Auth::user()
    ->user_events()
    ->whereHas('role', function ($q) {
        $q->where('role_name', 'Presenter');
    })->exists();

$hasAccessEditor = Auth::user()
    ->user_events()
    ->whereHas('role', function ($q) {
        $q->where('role_name', 'Editor');
    })->exists();

$hasAccessOrganizer = Auth::user()
    ->user_events()
    ->whereHas('role', function ($q) {
        $q->where('role_name', 'Organizer');
    })->exists();

$hasAccessNonPresenter = Auth::user()
    ->user_events()
    ->whereHas('role', function ($q) {
        $q->where('role_name', 'Non-Presenter');
    })->exists();

?>

@section('content')
    <div class="container-lg">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <strong>Subscription Packages</strong>
                    </div>
                    <div class="card-body">
                        <p class="text-medium-emphasis">The package will be activated globally for your account once payment
                            is confirmed by the Admin.</p>


                        <ul class="nav nav-tabs" role="tablist">
                            <li class="nav-item" role="presentation">
                                <a class="nav-link active" data-coreui-toggle="tab" href="#organizer" role="tab"
                                    aria-selected="true">
                                    <i class="cil-briefcase me-2"></i> Organizer
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content rounded-bottom">

                            <!-- Paket Organizer -->

                            <div class="tab-pane p-3 active" role="tabpanel" id="organizer">
                                <h5 class="mb-3">Paket Organizer</h5>
                                <div class="row">
                                    @forelse ($organizer_plans as $plan)
                                        @include('pages.subscription.partial_plan_card', ['plan' => $plan])
                                    @empty
                                        <p>Paket untuk Organizer belum tersedia.</p>
                                    @endforelse
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection