@extends('users.layout')

@section('title', $service->name)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-file-earmark-text me-2 text-primary"></i>{{ $service->name }}</h3>
        <p class="text-muted mb-0">{{ $service->office->name }}</p>
    </div>
    <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left"></i> Back
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Service details</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Office</h6>
                        <p class="mb-1 fw-semibold">{{ $service->office->name }}</p>
                        <p class="text-muted small mb-0">{{ $service->office->address }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Category</h6>
                        <p class="mb-0">{{ $service->category->name }}</p>
                    </div>
                    @if($service->price)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">Price</h6>
                            <p class="fs-4 fw-bold text-success mb-0">${{ number_format($service->price, 2) }}</p>
                        </div>
                    @endif
                    @if($service->duration)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">Duration</h6>
                            <p class="mb-0">{{ $service->duration }}</p>
                        </div>
                    @endif
                    @if($service->required_documents)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">Required documents</h6>
                            <ul class="mb-0">
                                @foreach($service->required_documents as $document)
                                    <li>{{ $document }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Actions</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('services.index') }}" class="btn btn-outline-secondary">
                    <i class="bi bi-grid-3x3-gap"></i> All services
                </a>
                @auth
                    <a href="{{ route('user.requests.create', ['service_id' => $service->id]) }}" class="btn btn-primary">
                        <i class="bi bi-send"></i> Request this service
                    </a>
                @else
                    <a href="{{ route('login') }}" class="btn btn-success">
                        <i class="bi bi-box-arrow-in-right"></i> Login to request
                    </a>
                @endauth
            </div>
        </div>
    </div>
</div>
@endsection
