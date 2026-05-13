@extends('office.layouts.app')

@section('title', 'Appointments')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-calendar-check me-2 text-primary"></i>Appointments</h3>
        <p class="text-muted mb-0">Schedule in-person visits and manage upcoming slots.</p>
    </div>
    <a href="{{ route('office.appointments.create') }}" class="btn btn-primary btn-sm">
        <i class="bi bi-plus-lg"></i> Schedule
    </a>
</div>

<div class="card border-0 shadow-sm mb-4">
    <div class="card-header bg-white border-bottom py-3">
        <h5 class="mb-0"><i class="bi bi-funnel me-2"></i>Filters</h5>
    </div>
    <div class="card-body">
        <form method="GET" action="{{ route('office.appointments.index') }}" class="row g-3 align-items-end">
            <div class="col-md-4">
                <label for="date" class="form-label fw-semibold">Date</label>
                <input type="date" name="date" id="date" value="{{ request('date') }}" class="form-control">
            </div>
            <div class="col-md-4">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" id="status" class="form-select">
                    <option value="">All</option>
                    <option value="Scheduled" {{ request('status') == 'Scheduled' ? 'selected' : '' }}>Scheduled</option>
                    <option value="Confirmed" {{ request('status') == 'Confirmed' ? 'selected' : '' }}>Confirmed</option>
                    <option value="Completed" {{ request('status') == 'Completed' ? 'selected' : '' }}>Completed</option>
                    <option value="Cancelled" {{ request('status') == 'Cancelled' ? 'selected' : '' }}>Cancelled</option>
                </select>
            </div>
            <div class="col-md-2">
                <button type="submit" class="btn btn-primary w-100">Apply</button>
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
                        <th>Citizen</th>
                        <th>Service</th>
                        <th>Date &amp; time</th>
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($appointments as $appointment)
                        <tr>
                            <td>#{{ $appointment->id }}</td>
                            <td>
                                @if(!empty($appointment->citizen_name))
                                    {{ $appointment->citizen_name }}<br>
                                    <small class="text-muted">{{ $appointment->citizen_email }}</small>
                                @elseif($appointment->citizen)
                                    {{ $appointment->citizen->username ?? 'Citizen' }}<br>
                                    <small class="text-muted">{{ $appointment->citizen->email }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td>{{ $appointment->service->name ?? '—' }}</td>
                            <td>
                                @if(isset($appointment->appointment_date))
                                    {{ \Illuminate\Support\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $appointment->appointment_time }}</small>
                                @elseif(isset($appointment->date))
                                    {{ \Illuminate\Support\Carbon::parse($appointment->date)->format('M d, Y') }}<br>
                                    <small class="text-muted">{{ $appointment->time_slot ?? '' }}</small>
                                @else
                                    —
                                @endif
                            </td>
                            <td>
                                @php
                                    $b = match($appointment->status ?? '') {
                                        'Scheduled' => 'primary',
                                        'Confirmed' => 'success',
                                        'Completed' => 'secondary',
                                        'Cancelled' => 'danger',
                                        default => 'light',
                                    };
                                @endphp
                                <span class="badge bg-{{ $b }}">{{ $appointment->status }}</span>
                            </td>
                            <td class="text-nowrap">
                                <a href="{{ route('office.appointments.show', $appointment->id) }}" class="btn btn-sm btn-outline-primary">View</a>
                                <a href="{{ route('office.appointments.edit', $appointment->id) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center text-muted py-4">No appointments found.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@if($appointments->hasPages())
    <div class="mt-3 d-flex justify-content-center">{{ $appointments->links() }}</div>
@endif
@endsection
