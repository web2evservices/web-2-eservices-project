@extends('office.layouts.app')

@section('title', 'Edit appointment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-pencil-square me-2 text-primary"></i>Edit appointment</h3>
        <p class="text-muted mb-0">Update time, status, or notes.</p>
    </div>
    <a href="{{ route('office.appointments.show', $appointment->id) }}" class="btn btn-outline-secondary btn-sm">Cancel</a>
</div>

<div class="card border-0 shadow-sm mb-3">
    <div class="card-body p-4">
        <form action="{{ route('office.appointments.update', $appointment->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label for="service_id" class="form-label fw-semibold">Service</label>
                <select name="service_id" id="service_id" class="form-select" required>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}" {{ (int) $appointment->service_id === (int) $service->id ? 'selected' : '' }}>
                            {{ $service->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="citizen_name" class="form-label fw-semibold">Citizen name</label>
                    <input type="text" name="citizen_name" id="citizen_name" class="form-control" required
                           value="{{ old('citizen_name', $appointment->citizen_name ?? '') }}">
                </div>
                <div class="col-md-6">
                    <label for="citizen_email" class="form-label fw-semibold">Citizen email</label>
                    <input type="email" name="citizen_email" id="citizen_email" class="form-control" required
                           value="{{ old('citizen_email', $appointment->citizen_email ?? '') }}">
                </div>
            </div>

            <div class="mb-3">
                <label for="citizen_phone" class="form-label fw-semibold">Phone</label>
                <input type="tel" name="citizen_phone" id="citizen_phone" class="form-control"
                       value="{{ old('citizen_phone', $appointment->citizen_phone ?? '') }}">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="appointment_date" class="form-label fw-semibold">Date</label>
                    @php
                        $d = $appointment->appointment_date ?? $appointment->date ?? null;
                    @endphp
                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" required
                           value="{{ old('appointment_date', $d ? \Illuminate\Support\Carbon::parse($d)->format('Y-m-d') : '') }}">
                </div>
                <div class="col-md-6">
                    <label for="appointment_time" class="form-label fw-semibold">Time</label>
                    @php
                        $t = $appointment->appointment_time ?? $appointment->time_slot ?? '';
                        if (is_string($t) && strlen($t) > 5) {
                            try { $t = \Illuminate\Support\Carbon::parse($t)->format('H:i'); } catch (\Throwable $e) { /* keep */ }
                        }
                    @endphp
                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" required
                           value="{{ old('appointment_time', $t) }}">
                </div>
            </div>

            <div class="mb-3">
                <label for="status" class="form-label fw-semibold">Status</label>
                <select name="status" id="status" class="form-select" required>
                    @foreach(['Scheduled', 'Confirmed', 'Completed', 'Cancelled'] as $st)
                        <option value="{{ $st }}" {{ old('status', $appointment->status) === $st ? 'selected' : '' }}>{{ $st }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label fw-semibold">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="form-control">{{ old('notes', $appointment->notes ?? '') }}</textarea>
            </div>

            <div class="d-flex justify-content-end">
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Save changes
                </button>
            </div>
        </form>
    </div>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <h6 class="text-danger mb-3">Danger zone</h6>
        <form action="{{ route('office.appointments.destroy', $appointment->id) }}" method="POST" onsubmit="return confirm('Delete this appointment?');">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn btn-outline-danger btn-sm">Delete appointment</button>
        </form>
    </div>
</div>
@endsection
