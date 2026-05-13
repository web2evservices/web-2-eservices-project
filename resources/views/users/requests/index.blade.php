@extends('users.layout')

@section('title', 'My Requests')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-inbox me-2 text-primary"></i>My requests</h3>
        <p class="text-muted mb-0">Submit new requests, upload documents, and track status updates.</p>
    </div>
    <a href="{{ route('user.dashboard') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-speedometer2"></i> Dashboard
    </a>
</div>

<div class="row gy-4">
    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0"><i class="bi bi-send me-2"></i>Submit request</h5>
            </div>
            <div class="card-body">
                <form id="requestForm">
                    @csrf
                    <div class="mb-3">
                        <label for="service" class="form-label">Service</label>
                        <select id="service" name="service_id" class="form-select" required>
                            <option value="">Select a service</option>
                            @foreach($services as $service)
                                <option value="{{ $service->id }}">
                                    {{ $service->name }} @if($service->office) — {{ $service->office->name }}@endif
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="appointment" class="form-label">Appointment ID <span class="text-muted">(optional)</span></label>
                        <input type="text" id="appointment" name="appointment_id" class="form-control" placeholder="If you have one">
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-check-lg me-1"></i> Submit request
                    </button>
                </form>
                <div id="requestFormAlert" class="mt-3"></div>
            </div>
        </div>
    </div>

    <div class="col-lg-8">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Request tracking</h5>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0" id="requestsTable">
                        <thead class="table-light">
                            <tr>
                                <th>#</th>
                                <th>Service</th>
                                <th>Status</th>
                                <th>Submitted</th>
                                <th></th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($requests as $index => $request)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>{{ $request->service?->name ?? 'Unknown' }}</td>
                                    <td>
                                        @php
                                            $statusClass = [
                                                'Pending' => 'badge bg-warning text-dark',
                                                'In Review' => 'badge bg-info text-dark',
                                                'Approved' => 'badge bg-success',
                                                'Rejected' => 'badge bg-danger',
                                                'Completed' => 'badge bg-secondary',
                                                'Missing Documents' => 'badge bg-danger',
                                            ][$request->status] ?? 'badge bg-light text-dark';
                                        @endphp
                                        <span class="{{ $statusClass }}">{{ $request->status }}</span>
                                    </td>
                                    <td>{{ $request->created_at->format('Y-m-d H:i') }}</td>
                                    <td>
                                        <a href="{{ route('user.requests.show', $request->id) }}" class="btn btn-sm btn-outline-primary">Details</a>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-4 text-muted">No requests yet.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                <div class="p-3 border-top bg-light small text-muted mb-0">
                    Workflow: Pending → In Review → Approved / Rejected → Completed (Missing documents may apply).
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const requestForm = document.querySelector('#requestForm');
    const formAlert = document.querySelector('#requestFormAlert');

    requestForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        formAlert.innerHTML = '';
        const data = new FormData(requestForm);
        const response = await fetch('/user/requests', {
            method: 'POST',
            body: data,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        const result = await response.json();
        if (!response.ok) {
            formAlert.innerHTML = `<div class="alert alert-danger mb-0">${result.message || 'Failed to submit request.'}</div>`;
            return;
        }
        formAlert.innerHTML = `<div class="alert alert-success mb-0">${result.message}</div>`;
        requestForm.reset();
        window.location.reload();
    });
</script>
@endpush
