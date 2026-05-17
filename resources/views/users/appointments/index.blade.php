@extends('users.layout')

@section('title', 'My Appointments')

@section('content')
<div class="container-fluid py-4">
    <div class="row mb-4">
        <div class="col-12 d-flex justify-content-between align-items-center">
            <h3 class="mb-0 text-dark fw-bold"><i class="bi bi-calendar-event text-primary me-2"></i>My Appointments</h3>
            <a href="{{ route('user.appointments.create') }}" class="btn btn-primary rounded-pill px-4 shadow-sm">
                <i class="bi bi-plus-lg me-1"></i> Book Appointment
            </a>
        </div>
    </div>

    <div class="row">
        <div class="col-12">
            @if($appointments->isEmpty())
                <div class="card border-0 shadow-sm rounded-4 text-center p-5">
                    <div class="card-body">
                        <i class="bi bi-calendar-x text-muted" style="font-size: 3rem;"></i>
                        <h4 class="mt-3 text-dark fw-bold">No Appointments</h4>
                        <p class="text-muted">You haven't scheduled any appointments yet.</p>
                        <a href="{{ route('user.appointments.create') }}" class="btn btn-outline-primary mt-3">Book Your First Appointment</a>
                    </div>
                </div>
            @else
                <div class="row row-cols-1 row-cols-md-2 row-cols-xl-3 g-4">
                    @foreach($appointments as $appt)
                        <div class="col">
                            <div class="card h-100 border-0 shadow-sm rounded-4 position-relative overflow-hidden">
                                <div class="card-body p-4">
                                    <div class="d-flex justify-content-between align-items-start mb-3">
                                        <div class="bg-primary bg-opacity-10 text-primary rounded-3 p-3 text-center" style="min-width: 80px;">
                                            <span class="d-block h3 fw-bold mb-0">{{ \Carbon\Carbon::parse($appt->date)->format('d') }}</span>
                                            <span class="d-block small fw-semibold text-uppercase">{{ \Carbon\Carbon::parse($appt->date)->format('M') }}</span>
                                        </div>
                                        <div class="text-end">
                                            @if($appt->status === 'Scheduled')
                                                <span class="badge bg-success rounded-pill px-3 py-2">Scheduled</span>
                                            @elseif($appt->status === 'Completed')
                                                <span class="badge bg-secondary rounded-pill px-3 py-2">Completed</span>
                                            @else
                                                <span class="badge bg-danger rounded-pill px-3 py-2">{{ $appt->status }}</span>
                                            @endif
                                        </div>
                                    </div>

                                    <h5 class="fw-bold text-dark mb-1">{{ $appt->service->name ?? 'General Service' }}</h5>
                                    <p class="text-muted small mb-3"><i class="bi bi-building me-1"></i>{{ $appt->office->name ?? 'N/A' }}</p>

                                    <div class="d-flex align-items-center mb-2">
                                        <i class="bi bi-clock text-primary me-2"></i>
                                        <span class="text-dark fw-medium">{{ $appt->formatted_time_slot }}</span>
                                    </div>
                                    <div class="d-flex align-items-start">
                                        <i class="bi bi-geo-alt text-primary me-2 mt-1"></i>
                                        <span class="text-secondary small">{{ $appt->office->address ?? 'Location info not available' }}</span>
                                    </div>
                                    
                                    @if($appt->notes)
                                        <div class="mt-3 p-2 bg-light rounded small text-muted border-start border-3 border-secondary">
                                            <i class="bi bi-info-circle me-1"></i> {{ $appt->notes }}
                                        </div>
                                    @endif
                                </div>
                                @if($appt->status === 'Scheduled')
                                    <div class="card-footer bg-white border-top-0 pt-0 pb-4 px-4 d-flex justify-content-end">
                                        <form action="{{ route('user.appointments.destroy', $appt->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this appointment?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-outline-danger btn-sm rounded-pill px-3">Cancel</button>
                                        </form>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
