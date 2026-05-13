@extends('users.layout')

@section('title', 'Request #' . $id)

@section('content')
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h3 class="mb-1"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request details</h3>
            <p class="text-muted mb-0">Track workflow status, upload documents, and download records.</p>
        </div>
        <a href="{{ route('user.requests.index') }}" class="btn btn-outline-secondary btn-sm">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <div id="requestError" class="alert alert-danger d-none"></div>
    <div id="requestId" data-id="{{ $id }}" class="d-none"></div>

    <div class="row gy-4">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0" id="requestTitle">Request #{{ $id }}</h5>
                </div>
                <div class="card-body" id="requestSummary">
                    <div class="mb-3"><strong>Status:</strong> <span id="requestStatus" class="badge bg-secondary">Loading...</span></div>
                    <div class="mb-2"><strong>Service:</strong> <span id="requestService"></span></div>
                    <div class="mb-2"><strong>Office:</strong> <span id="requestOffice"></span></div>
                    <div class="mb-2"><strong>Submitted:</strong> <span id="requestCreated"></span></div>
                    <div class="mb-2"><strong>QR Code:</strong> <span id="requestQr"></span></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-clock-history me-2"></i>Workflow history</h5>
                </div>
                <div class="card-body" id="historyContent">
                    <p class="text-muted">Loading history...</p>
                </div>
            </div>

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-folder2-open me-2"></i>Documents</h5>
                </div>
                <div class="card-body" id="documentsContent">
                    <p class="text-muted">Loading documents...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white border-bottom py-3"><h5 class="mb-0"><i class="bi bi-cloud-upload me-2"></i>Upload</h5></div>
                <div class="card-body">
                    <form id="documentForm" enctype="multipart/form-data">
                        @csrf
                        <div class="mb-3">
                            <label for="file" class="form-label">Choose file</label>
                            <input type="file" id="file" name="file" class="form-control" accept=".pdf,.jpg,.jpeg,.png" required>
                        </div>
                        <button type="submit" class="btn btn-primary w-100">Upload</button>
                    </form>
                    <div id="documentAlert" class="mt-3"></div>
                </div>
            </div>

            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white border-bottom py-3"><h5 class="mb-0"><i class="bi bi-file-earmark-pdf me-2"></i>PDF</h5></div>
                <div class="card-body">
                    <p class="text-muted small">Generates a downloadable PDF summary you can open or save directly.</p>
                    <button id="generatePdfBtn" class="btn btn-success w-100 mb-3"><i class="bi bi-file-earmark-arrow-down me-1"></i> Generate PDF summary</button>
                    <div id="pdfActionResult"></div>
                </div>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    const requestId = document.querySelector('#requestId').dataset.id;
    const requestError = document.querySelector('#requestError');
    const requestStatus = document.querySelector('#requestStatus');
    const requestService = document.querySelector('#requestService');
    const requestOffice = document.querySelector('#requestOffice');
    const requestCreated = document.querySelector('#requestCreated');
    const requestQr = document.querySelector('#requestQr');
    const historyContent = document.querySelector('#historyContent');
    const documentsContent = document.querySelector('#documentsContent');
    const documentForm = document.querySelector('#documentForm');
    const documentAlert = document.querySelector('#documentAlert');
    const generatePdfBtn = document.querySelector('#generatePdfBtn');
    const pdfActionResult = document.querySelector('#pdfActionResult');

    const statusClasses = {
        'Pending': 'badge bg-warning text-dark',
        'In Review': 'badge bg-info text-dark',
        'Approved': 'badge bg-success',
        'Rejected': 'badge bg-danger',
        'Completed': 'badge bg-secondary',
        'Missing Documents': 'badge bg-danger'
    };

    async function renderRequest() {
        const response = await fetch(`/user/requests/${requestId}/data`);
        if (!response.ok) {
            requestError.textContent = 'Unable to load request details.';
            requestError.classList.remove('d-none');
            return;
        }

        const result = await response.json();
        const request = result.data;
        requestError.classList.add('d-none');
        requestStatus.innerHTML = `<span class="${statusClasses[request.status] || 'badge bg-secondary'}">${request.status}</span>`;
        requestService.textContent = request.service?.name || 'Unknown';
        requestOffice.textContent = request.service?.office?.name || 'Not available';
        requestCreated.textContent = new Date(request.created_at).toLocaleString();
        requestQr.textContent = request.qr_code || 'N/A';

        if (Array.isArray(request.requestHistories) && request.requestHistories.length) {
            historyContent.innerHTML = request.requestHistories.map(item => `
                <div class="mb-3">
                    <strong>${item.old_status}</strong> → <strong>${item.new_status}</strong><br>
                    <small class="text-muted">Updated at ${new Date(item.created_at).toLocaleString()}</small>
                </div>
            `).join('');
        } else {
            historyContent.innerHTML = '<p class="text-muted">No status updates available yet.</p>';
        }

        if (Array.isArray(request.documents) && request.documents.length) {
            documentsContent.innerHTML = `<div class="list-group">${request.documents.map(doc => `
                    <div class="list-group-item d-flex justify-content-between align-items-start">
                        <div>
                            <div class="fw-semibold">${doc.document_type === 'generated' ? 'Generated PDF' : 'Uploaded document'}</div>
                            <div class="text-muted small">${doc.file_path.split('/').pop()}</div>
                        </div>
                        <a href="/user/requests/${requestId}/documents/${doc.id}/download" class="btn btn-sm btn-outline-primary">Download</a>
                    </div>
                `).join('')}</div>`;
        } else {
            documentsContent.innerHTML = '<p class="text-muted">No documents attached yet.</p>';
        }
    }

    documentForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        documentAlert.innerHTML = '';
        const data = new FormData(documentForm);
        const response = await fetch(`/user/requests/${requestId}/documents`, {
            method: 'POST',
            body: data,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            }
        });
        const result = await response.json();
        if (!response.ok) {
            documentAlert.innerHTML = `<div class="alert alert-danger">${result.message || 'Upload failed.'}</div>`;
            return;
        }
        documentAlert.innerHTML = `<div class="alert alert-success">${result.message}</div>`;
        documentForm.reset();
        renderRequest();
    });

    generatePdfBtn.addEventListener('click', async () => {
        pdfActionResult.innerHTML = '';
        generatePdfBtn.disabled = true;

        try {
            const response = await fetch(`/user/requests/${requestId}/pdf`, {
                method: 'POST',
                credentials: 'same-origin',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || '',
                    'Accept': 'application/json'
                }
            });

            let result;
            const contentType = response.headers.get('content-type') || '';
            if (contentType.includes('application/json')) {
                result = await response.json();
            } else {
                const text = await response.text();
                result = { message: text || 'PDF generation failed.' };
            }

            if (!response.ok) {
                pdfActionResult.innerHTML = `<div class="alert alert-danger">${result.message || 'PDF generation failed.'}</div>`;
                return;
            }

            const doc = result.data;
            const downloadUrl = result.download_url || `/user/requests/${requestId}/documents/${doc.id}/download`;
            pdfActionResult.innerHTML = `
                <div class="alert alert-success">
                    ${result.message}. <a href="${downloadUrl}" target="_blank" rel="noopener" class="link-primary">Open PDF</a>
                </div>
            `;
            renderRequest();
        } catch (error) {
            pdfActionResult.innerHTML = `<div class="alert alert-danger">Unable to generate PDF. Please try again.</div>`;
            console.error('PDF generation error:', error);
        } finally {
            generatePdfBtn.disabled = false;
        }
    });

    renderRequest();
</script>
@endpush
