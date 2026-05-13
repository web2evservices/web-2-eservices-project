@extends('office.layouts.app')

@section('title', 'Office Dashboard')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1">Welcome back, {{ Auth::user()->username }}!</h3>
        <p class="text-muted mb-0">{{ $office ? $office->name : 'No office assigned yet' }}</p>
    </div>
</div>

@if(!$office)
    <div class="alert alert-warning">
        <i class="bi bi-exclamation-triangle-fill me-2"></i>
        Your account has not been assigned to an office yet. Contact the admin.
    </div>
@else

{{-- Stats Row --}}
<div class="row g-4 mb-4">
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-primary bg-opacity-10 rounded-3 p-3">
                    <i class="bi bi-list-check fs-3 text-primary"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $totalServices }}</div>
                    <div class="text-muted">Total Services</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('office.services.index') }}" class="btn btn-primary btn-sm">Manage Services</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-warning bg-opacity-10 rounded-3 p-3">
                    <i class="bi bi-hourglass-split fs-3 text-warning"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $pendingRequests }}</div>
                    <div class="text-muted">Pending Requests</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('office.requests.index') }}" class="btn btn-warning btn-sm">Manage Requests</a>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body d-flex align-items-center gap-3">
                <div class="bg-info bg-opacity-10 rounded-3 p-3">
                    <i class="bi bi-calendar-check fs-3 text-info"></i>
                </div>
                <div>
                    <div class="fs-2 fw-bold">{{ $recentRequests->count() }}</div>
                    <div class="text-muted">Recent Activity</div>
                </div>
            </div>
            <div class="card-footer bg-transparent border-0">
                <a href="{{ route('office.appointments.index') }}" class="btn btn-info btn-sm">Manage Appointments</a>
            </div>
        </div>
    </div>
</div>

{{-- Recent Requests Table --}}
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white border-bottom">
        <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Recent Requests</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>#ID</th>
                        <th>Citizen</th>
                        <th>Service</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>QR</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentRequests as $req)
                    <tr>
                        <td><span class="text-muted">#{{ $req->id }}</span></td>
                        <td>{{ $req->citizen->username ?? $req->citizen->email ?? 'N/A' }}</td>
                        <td>{{ $req->service->name ?? 'N/A' }}</td>
                        <td>
                            @php
                                $badgeColor = match($req->status) {
                                    'Pending'  => 'warning',
                                    'Approved' => 'success',
                                    'Rejected' => 'danger',
                                    'Completed'=> 'info',
                                    default    => 'secondary',
                                };
                            @endphp
                            <span class="badge bg-{{ $badgeColor }}">{{ $req->status }}</span>
                        </td>
                        <td>{{ $req->created_at->format('M d, Y') }}</td>
                        <td>
                            <a href="{{ route('office.qr.show', $req->id) }}"
                               class="btn btn-outline-secondary btn-sm">
                                <i class="bi bi-qr-code"></i>
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-4">No requests yet.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endif
@endsection