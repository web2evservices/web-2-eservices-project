@extends('office.layouts.app')

@section('title', 'Service requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-inbox me-2 text-primary"></i>Incoming requests</h3>
        <p class="text-muted mb-0">Review and manage citizen submissions for your office.</p>
    </div>
    <a href="{{ route('office.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filter by status</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('office.requests.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All statuses</option>
                    <option value="Pending" {{ request('status') == 'Pending' ? 'selected' : '' }}>Pending</option>
                    <option value="In Review" {{ request('status') == 'In Review' ? 'selected' : '' }}>In Review</option>
                    <option value="Missing Documents" {{ request('status') == 'Missing Documents' ? 'selected' : '' }}>Missing Documents</option>
                    <option value="Approved" {{ request('status') == 'Approved' ? 'selected' : '' }}>Approved</option>
                    <option value="Rejected" {{ request('status') == 'Rejected' ? 'selected' : '' }}>Rejected</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">
                    <i class="bi bi-search"></i> Apply
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover mb-0">
                <thead class="table-light">
                    <tr>
                        <th>ID</th>
                        <th>Service</th>
                        <th>Citizen</th>
                        <th>Status</th>
                        <th>Created</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($requests as $request)
                        <tr>
                            <td><span class="text-muted">#{{ $request->id }}</span></td>
                            <td>{{ $request->service->name }}</td>
                            <td>{{ $request->citizen->username ?? ($request->citizen->first_name ?? 'N/A') }}</td>
                            <td>
                                @php
                                    $badge = match($request->status) {
                                        'Pending' => 'warning',
                                        'In Review' => 'info',
                                        'Missing Documents' => 'warning',
                                        'Approved' => 'success',
                                        'Rejected' => 'danger',
                                        'Completed' => 'secondary',
                                        default => 'secondary',
                                    };
                                @endphp
                                <span class="badge bg-{{ $badge }}">{{ $request->status }}</span>
                            </td>
                            <td>{{ $request->created_at->format('M d, Y') }}</td>
                            <td class="text-nowrap">
                                <a href="{{ route('office.requests.show', $request->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('office.qr.show', $request->id) }}" class="btn btn-sm btn-outline-secondary" title="QR"><i class="bi bi-qr-code"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No requests found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($requests->hasPages())
    <div class="mt-3 d-flex justify-content-center">
        {{ $requests->links() }}
    </div>
@endif
@endsection
