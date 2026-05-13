@extends('users.layout')

@section('title', 'Submit request')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-send me-2 text-primary"></i>Submit service request</h3>
        <p class="text-muted mb-0">Select a service, attach required documents.</p>
    </div>
    <a href="{{ route('user.requests.index') }}" class="btn btn-outline-secondary btn-sm">Back to requests</a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-4">
        <form action="{{ route('user.requests.store') }}" method="POST" enctype="multipart/form-data" id="requestForm">
            @csrf

            <div class="mb-4">
                <label for="service_id" class="form-label fw-semibold">Service <span class="text-danger">*</span></label>
                <select name="service_id" id="service_id" class="form-select" required>
                    <option value="">Choose a service…</option>
                    @foreach($services as $svc)
                        <option value="{{ $svc->id }}" {{ isset($service) && $service->id === $svc->id ? 'selected' : '' }}>
                            {{ $svc->name }} — {{ $svc->office->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div id="serviceDetails" class="mb-4 p-3 bg-light rounded border d-none">
                <h6 class="fw-bold mb-2"><i class="bi bi-info-circle me-1"></i>Service details</h6>
                <div id="serviceInfo" class="small"></div>
            </div>

            <div class="mb-4">
                <label class="form-label fw-semibold">Required documents</label>
                <div id="documentUploads"></div>
                <button type="button" id="addDocument" class="btn btn-link btn-sm px-0">
                    <i class="bi bi-plus-lg"></i> Add optional document
                </button>
            </div>

            <div class="mb-4">
                <label for="appointment_id" class="form-label fw-semibold">Appointment <span class="text-muted fw-normal">(optional)</span></label>
                <select name="appointment_id" id="appointment_id" class="form-select">
                    <option value="">No appointment</option>
                </select>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('user.requests.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="bi bi-check-lg me-1"></i> Submit request
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const serviceSelect = document.getElementById('service_id');
    const serviceDetails = document.getElementById('serviceDetails');
    const serviceInfo = document.getElementById('serviceInfo');
    const documentUploads = document.getElementById('documentUploads');
    const appointmentSelect = document.getElementById('appointment_id');

    serviceSelect.addEventListener('change', function() {
        const serviceId = this.value;
        if (serviceId) {
            fetch(`/api/services/${serviceId}`)
                .then(response => response.json())
                .then(data => {
                    updateServiceDetails(data.data);
                    updateDocumentUploads(data.data.required_documents || []);
                    appointmentSelect.innerHTML = '<option value="">No appointment</option>';
                });
        } else {
            serviceDetails.classList.add('d-none');
            documentUploads.innerHTML = '';
            appointmentSelect.innerHTML = '<option value="">No appointment</option>';
        }
    });

    if (serviceSelect.value) {
        serviceSelect.dispatchEvent(new Event('change'));
    }

    function updateServiceDetails(service) {
        serviceInfo.innerHTML = `
            <p class="mb-1"><strong>Office:</strong> ${service.office.name}</p>
            <p class="mb-1"><strong>Category:</strong> ${service.category.name}</p>
            ${service.price ? `<p class="mb-1"><strong>Price:</strong> $${Number(service.price).toFixed(2)}</p>` : ''}
            ${service.duration ? `<p class="mb-0"><strong>Duration:</strong> ${service.duration}</p>` : ''}
        `;
        serviceDetails.classList.remove('d-none');
    }

    function updateDocumentUploads(requiredDocs) {
        documentUploads.innerHTML = '';
        requiredDocs.forEach((doc, index) => {
            const div = document.createElement('div');
            div.className = 'mb-3';
            div.innerHTML = `
                <label class="form-label small">${doc} <span class="text-danger">*</span></label>
                <input type="file" name="documents[${index}][file]" class="form-control" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                <input type="hidden" name="documents[${index}][type]" value="${String(doc).replace(/&/g, '&amp;').replace(/"/g, '&quot;')}">
            `;
            documentUploads.appendChild(div);
        });
    }

    document.getElementById('addDocument').addEventListener('click', function() {
        const index = document.querySelectorAll('#documentUploads > div').length;
        const div = document.createElement('div');
        div.className = 'mb-3 border rounded p-3 bg-white';
        div.innerHTML = `
            <label class="form-label small">Additional file</label>
            <input type="file" name="documents[${index}][file]" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
            <input type="hidden" name="documents[${index}][type]" value="Additional Document">
            <button type="button" class="btn btn-link btn-sm text-danger px-0 mt-1 remove-doc">Remove</button>
        `;
        documentUploads.appendChild(div);
        div.querySelector('.remove-doc').addEventListener('click', function() {
            div.remove();
        });
    });
});
</script>
@endpush
