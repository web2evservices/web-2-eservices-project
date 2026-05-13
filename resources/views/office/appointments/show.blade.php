@extends('office.layouts.app')

@section('title', 'Appointment #' . $appointment->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-calendar-event me-2 text-primary"></i>Appointment #{{ $appointment->id }}</h3>
        <p class="text-muted mb-0">View details and follow-up actions.</p>
    </div>
    <div class="d-flex gap-2">
        <a href="{{ route('office.appointments.edit', $appointment->id) }}" class="btn btn-outline-primary btn-sm">Edit</a>
        <a href="{{ route('office.appointments.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
    </div>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Details</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Service:</strong> {{ $appointment->service->name ?? '—' }}</p>
                <p class="mb-2"><strong>Status:</strong> <span class="badge bg-primary">{{ $appointment->status }}</span></p>
                @if(!empty($appointment->citizen_name))
                    <p class="mb-2"><strong>Citizen:</strong> {{ $appointment->citizen_name }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $appointment->citizen_email }}</p>
                    @if($appointment->citizen_phone)
                        <p class="mb-2"><strong>Phone:</strong> {{ $appointment->citizen_phone }}</p>
                    @endif
                @elseif($appointment->citizen)
                    <p class="mb-2"><strong>Citizen:</strong> {{ $appointment->citizen->username ?? 'User #' . $appointment->citizen_id }}</p>
                    <p class="mb-2"><strong>Email:</strong> {{ $appointment->citizen->email }}</p>
                @endif
                <p class="mb-2"><strong>Date:</strong>
                    @if(isset($appointment->appointment_date))
                        {{ \Illuminate\Support\Carbon::parse($appointment->appointment_date)->format('M d, Y') }}
                    @elseif(isset($appointment->date))
                        {{ \Illuminate\Support\Carbon::parse($appointment->date)->format('M d, Y') }}
                    @else
                        —
                    @endif
                </p>
                <p class="mb-2"><strong>Time:</strong> {{ $appointment->appointment_time ?? $appointment->time_slot ?? '—' }}</p>
                @if(!empty($appointment->notes))
                    <p class="mb-0"><strong>Notes:</strong> {{ $appointment->notes }}</p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Reminders</h5>
            </div>
            <div class="card-body">
                <p class="text-muted small mb-0">Use your office email tools to send a reminder to the citizen before the visit.</p>
            </div>
        </div>
    </div>
</div>
@endsection
