@extends('users.layout')

@section('title', 'Book Appointment')

@push('scripts')
<style>
    .step-indicator {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: #e9ecef;
        color: #6c757d;
        display: flex;
        align-items: center;
        justify-content: center;
        font-weight: bold;
        font-size: 1.2rem;
        margin-right: 15px;
    }
    .step-active {
        background: #0d6efd;
        color: #fff;
    }
    .step-completed {
        background: #198754;
        color: #fff;
    }
    .time-slot-btn {
        transition: all 0.2s;
    }
    .time-slot-btn:hover, .time-slot-btn.active {
        background-color: #0d6efd;
        color: #fff;
        border-color: #0d6efd;
    }
</style>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const timeSlotBtns = document.querySelectorAll('.time-slot-btn');
        const timeSlotInput = document.getElementById('time_slot_input');

        timeSlotBtns.forEach(btn => {
            btn.addEventListener('click', function() {
                timeSlotBtns.forEach(b => b.classList.remove('active'));
                this.classList.add('active');
                timeSlotInput.value = this.dataset.time;
            });
        });

        // Simple filtering logic: when office changes, optionally reload page with ?office_id=
        const officeSelect = document.getElementById('office_id');
        officeSelect.addEventListener('change', function() {
            if(this.value) {
                window.location.href = "{{ route('user.appointments.create') }}?office_id=" + this.value;
            }
        });
    });
</script>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12">
            <a href="{{ route('user.appointments.index') }}" class="text-decoration-none text-secondary mb-3 d-inline-block"><i class="bi bi-arrow-left"></i> Back to Appointments</a>
            <h3 class="mb-0 text-dark fw-bold">Book an Appointment</h3>
            <p class="text-muted">Schedule an in-person visit to a government office.</p>
        </div>
    </div>

    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <form action="{{ route('user.appointments.store') }}" method="POST">
                        @csrf
                        
                        <!-- Step 1: Location & Service -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="step-indicator step-active">1</div>
                                <h4 class="mb-0 fw-bold text-dark">Location & Service</h4>
                            </div>
                            
                            <div class="row g-4 ms-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Select Office</label>
                                    <select name="office_id" id="office_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Office --</option>
                                        @foreach($offices as $o)
                                            <option value="{{ $o->id }}" {{ (isset($office) && $office->id == $o->id) ? 'selected' : '' }}>
                                                {{ $o->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Select Service</label>
                                    <select name="service_id" id="service_id" class="form-select form-select-lg" required>
                                        <option value="">-- Choose Service --</option>
                                        @foreach($services as $s)
                                            <option value="{{ $s->id }}" {{ (isset($service) && $service->id == $s->id) ? 'selected' : '' }}>
                                                {{ $s->name }} ({{ $s->office->name ?? 'General' }})
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>

                        <!-- Step 2: Date & Time -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="step-indicator step-active">2</div>
                                <h4 class="mb-0 fw-bold text-dark">Date & Time</h4>
                            </div>
                            
                            <div class="row g-4 ms-5">
                                <div class="col-md-6">
                                    <label class="form-label fw-semibold">Select Date</label>
                                    <input type="date" name="date" class="form-control form-control-lg" min="{{ date('Y-m-d', strtotime('+1 day')) }}" required>
                                </div>
                                <div class="col-md-12 mt-4">
                                    <label class="form-label fw-semibold d-block">Select Time Slot</label>
                                    <input type="hidden" name="time_slot" id="time_slot_input" required>
                                    <div class="d-flex flex-wrap gap-2">
                                        @php
                                            $slots = ['09:00 AM', '09:30 AM', '10:00 AM', '10:30 AM', '11:00 AM', '11:30 AM', '01:00 PM', '01:30 PM', '02:00 PM', '02:30 PM', '03:00 PM'];
                                        @endphp
                                        @foreach($slots as $slot)
                                            <button type="button" class="btn btn-outline-primary time-slot-btn rounded-pill px-4" data-time="{{ $slot }}">{{ $slot }}</button>
                                        @endforeach
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Step 3: Additional Details -->
                        <div class="mb-5">
                            <div class="d-flex align-items-center mb-4">
                                <div class="step-indicator step-active">3</div>
                                <h4 class="mb-0 fw-bold text-dark">Additional Details</h4>
                            </div>
                            
                            <div class="ms-5">
                                <label class="form-label fw-semibold">Notes (Optional)</label>
                                <textarea name="notes" class="form-control" rows="3" placeholder="Briefly describe the purpose of your visit..."></textarea>
                            </div>
                        </div>

                        <hr class="my-4">

                        <div class="text-end">
                            <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">Confirm Booking <i class="bi bi-check-lg ms-1"></i></button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
