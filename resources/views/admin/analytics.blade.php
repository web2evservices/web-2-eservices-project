@extends('admin.layouts.app')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="mb-0">Analytics Dashboard</h2>
        <small class="text-muted">Last updated: {{ now()->format('M d, Y H:i') }}</small>
    </div>

    <!-- Key Metrics -->
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-primary">{{ number_format($totalRequests) }}</div>
                    <div class="text-muted">Total Requests</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-warning">{{ number_format($pendingRequests) }}</div>
                    <div class="text-muted">Pending Requests</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-success">${{ number_format($totalRevenue, 2) }}</div>
                    <div class="text-muted">Total Revenue</div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body text-center">
                    <div class="fs-1 fw-bold text-info">{{ number_format($totalAppointments) }}</div>
                    <div class="text-muted">Total Appointments</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="row g-4 mb-4">
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Recent Activity (30 days)</h5>
                </div>
                <div class="card-body">
                    <div class="d-flex justify-content-between mb-2">
                        <span>New Requests:</span>
                        <strong>{{ number_format($recentRequests) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between mb-2">
                        <span>Payments:</span>
                        <strong>{{ number_format($recentPayments) }}</strong>
                    </div>
                    <div class="d-flex justify-content-between">
                        <span>Appointments:</span>
                        <strong>{{ number_format($recentAppointments) }}</strong>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Requests by Status</h5>
                </div>
                <div class="card-body">
                    @foreach($requestsByStatus as $status)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $status->status }}:</span>
                            <strong>{{ number_format($status->count) }}</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Payments by Method</h5>
                </div>
                <div class="card-body">
                    @foreach($paymentsByMethod as $method)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ ucfirst($method->payment_method) }}:</span>
                            <strong>{{ number_format($method->count) }} (${{ number_format($method->total, 2) }})</strong>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="row g-4">
        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Popular Services</h5>
                </div>
                <div class="card-body">
                    @forelse($popularServices as $service)
                        <div class="d-flex justify-content-between mb-2">
                            <span>{{ $service->name }}</span>
                            <strong>{{ number_format($service->count) }}</strong>
                        </div>
                    @empty
                        <p class="text-muted mb-0">No data available</p>
                    @endforelse
                </div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-light">
                    <h5 class="mb-0">Requests per office (ID &amp; total)</h5>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Office ID</th>
                                    <th>Office name</th>
                                    <th class="text-end">Total requests</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($requestsPerOffice as $row)
                                    <tr>
                                        <td><code>{{ $row->office_id }}</code></td>
                                        <td>{{ $row->name }}</td>
                                        <td class="text-end fw-semibold">{{ number_format($row->count) }}</td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="text-muted text-center py-4">No offices in the system yet.</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($requestsPerOffice->count() && $requestsPerOffice->sum('count') === 0)
                        <p class="text-muted small mb-0 px-3 py-2">Offices exist but have no service requests yet (all counts are zero).</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection