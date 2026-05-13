@extends('users.layout')

@section('title', 'Browse Services')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-grid-3x3-gap me-2 text-primary"></i>Browse services</h3>
        <p class="text-muted mb-0">Filter by office, category, or search by name.</p>
    </div>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('services.index') }}" class="row g-3 align-items-end">
            <div class="col-md-3">
                <label for="office_id" class="form-label fw-semibold">Office</label>
                <select name="office_id" id="office_id" class="form-select">
                    <option value="">All offices</option>
                    @foreach($offices as $office)
                        <option value="{{ $office->id }}" {{ request('office_id') == $office->id ? 'selected' : '' }}>
                            {{ $office->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <label for="category_id" class="form-label fw-semibold">Category</label>
                <select name="category_id" id="category_id" class="form-select">
                    <option value="">All categories</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="search" class="form-label fw-semibold">Search</label>
                <input type="text" name="search" id="search" value="{{ request('search') }}" class="form-control" placeholder="Search services…">
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search me-1"></i> Filter
                </button>
            </div>
        </form>
    </div>
</div>

<div class="row g-4">
    @forelse($services as $service)
        <div class="col-md-6 col-lg-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column">
                    <h5 class="card-title">{{ $service->name }}</h5>
                    <p class="text-muted small mb-1"><i class="bi bi-building me-1"></i>{{ $service->office->name }}</p>
                    <p class="text-muted small mb-3"><i class="bi bi-tag me-1"></i>{{ $service->category->name }}</p>
                    @if($service->price)
                        <p class="fs-5 fw-bold text-success mb-3">${{ number_format($service->price, 2) }}</p>
                    @endif
                    <div class="mt-auto d-flex flex-wrap gap-2">
                        <a href="{{ route('services.show', $service->id) }}" class="btn btn-outline-primary btn-sm">
                            <i class="bi bi-eye"></i> Details
                        </a>
                        @auth
                            <a href="{{ route('user.requests.create', ['service_id' => $service->id]) }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-send"></i> Request
                            </a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="col-12">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center text-muted py-5">
                    <i class="bi bi-inbox fs-1 d-block mb-3"></i>
                    No services found matching your criteria.
                </div>
            </div>
        </div>
    @endforelse
</div>

@if($services->hasPages())
    <div class="mt-4 d-flex justify-content-center">
        {{ $services->links() }}
    </div>
@endif
@endsection
