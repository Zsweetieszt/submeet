<div class="col-md-5 mb-4">
    <div class="card h-100">
        <div class="card-header text-center bg-primary text-white">
            <h5 class="mb-0">{{ $plan->name }}</h5>
        </div>
        <div class="card-body d-flex flex-column">
            <h3 class="text-center fw-bold">{{ $plan->price }}</h3>
            <ul class="list-group list-group-flush mt-3 mb-4">
                @if(isset($plan->features) && is_array($plan->features))
                    @foreach ($plan->features as $feature)
                        <li class="list-group-item">
                            <svg class="icon me-2">
                                <use xlink:href="{{ asset('vendors/@coreui/icons/svg/free.svg#cil-check-circle') }}">
                                </use>
                            </svg>
                            {{ $feature }}
                        </li>
                    @endforeach
                @endif
            </ul>
            <div class="mt-auto">
                <a href="{{ route('subscription.preview', $plan->id) }}" class="btn btn-primary w-100">
                    Pilih Paket
                </a>
            </div>
        </div>
    </div>
</div>