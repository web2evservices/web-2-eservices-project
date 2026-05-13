@extends('users.layout')

@section('title', 'Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-speedometer2 me-2 text-primary"></i>Citizen Dashboard</h3>
        <p class="text-muted mb-0">Submit service requests, upload required documents, and track progress from one place.</p>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-12">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0"><i class="bi bi-person-circle me-2 text-primary"></i>Your profile</h5>
            </div>
            <div class="card-body">
                <p class="mb-1"><strong>Name:</strong> {{ auth()->user()->username }}</p>
                <p class="mb-0"><strong>Email:</strong> {{ auth()->user()->email }}</p>
            </div>
        </div>
    </div>
</div>

<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                    <i class="bi bi-grid-3x3-gap fs-3 text-primary"></i>
                </div>
                <div>
                    <h5 class="card-title">Browse Services</h5>
                    <p class="card-text text-muted small">Explore available government services by office and category.</p>
                    <a href="{{ route('services.index') }}" class="btn btn-primary btn-sm">Browse Services</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="bg-success bg-opacity-10 rounded-3 p-3">
                    <i class="bi bi-inbox fs-3 text-success"></i>
                </div>
                <div>
                    <h5 class="card-title">My Requests</h5>
                    <p class="card-text text-muted small">Monitor request status, view history, and download receipts or certificates.</p>
                    <a href="{{ route('user.requests.index') }}" class="btn btn-success btn-sm">View Requests</a>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-start gap-3">
                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                    <i class="bi bi-cloud-upload fs-3 text-warning"></i>
                </div>
                <div>
                    <h5 class="card-title">Document Upload</h5>
                    <p class="card-text text-muted small">Upload supporting files for active requests from request details.</p>
                    <a href="{{ route('user.requests.index') }}" class="btn btn-warning btn-sm">Go to Requests</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
