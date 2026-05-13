@extends('office.layouts.app')

@section('title', 'Schedule appointment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-calendar-plus me-2 text-primary"></i>Schedule appointment</h3>
        <p class="text-muted mb-0">Book an in-person slot for a citizen at your office.</p>
    </div>
    <a href="{{ route('office.appointments.index') }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('office.appointments.store') }}" method="POST">
            @csrf

            <div class="mb-3">
                <label for="service_id" class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                <select name="service_id" id="service_id" class="form-select" required>
                    <option value="">Select…</option>
                    @foreach($services as $service)
                        <option value="{{ $service->id }}">{{ $service->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="citizen_name" class="form-label fw-semibold">Citizen name <span class="text-danger">*</span></label>
                    <input type="text" name="citizen_name" id="citizen_name" class="form-control" required>
                </div>
                <div class="col-md-6">
                    <label for="citizen_email" class="form-label fw-semibold">Citizen email <span class="text-danger">*</span></label>
                    <input type="email" name="citizen_email" id="citizen_email" class="form-control" required>
                </div>
            </div>

            <div class="mb-3">
                <label for="citizen_phone" class="form-label fw-semibold">Phone</label>
                <input type="tel" name="citizen_phone" id="citizen_phone" class="form-control">
            </div>

            <div class="row g-3 mb-3">
                <div class="col-md-6">
                    <label for="appointment_date" class="form-label fw-semibold">Date <span class="text-danger">*</span></label>
                    <input type="date" name="appointment_date" id="appointment_date" class="form-control" required min="{{ date('Y-m-d', strtotime('+1 day')) }}">
                </div>
                <div class="col-md-6">
                    <label for="appointment_time" class="form-label fw-semibold">Time <span class="text-danger">*</span></label>
                    <input type="time" name="appointment_time" id="appointment_time" class="form-control" required>
                </div>
            </div>

            <div class="mb-4">
                <label for="notes" class="form-label fw-semibold">Notes</label>
                <textarea name="notes" id="notes" rows="3" class="form-control" placeholder="Internal notes…"></textarea>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('office.appointments.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Schedule
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
