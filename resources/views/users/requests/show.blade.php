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

    <style>
        .cursor-pointer { cursor: pointer; }
        .rating-stars .bi-star-fill { color: #ffc107; }
        .action-card {
            background: linear-gradient(135deg, #0d6efd 0%, #0a58ca 100%);
            border: none;
            transition: transform 0.2s;
        }
        .action-card:hover {
            transform: translateY(-3px);
        }
    </style>

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

            <div class="card border-0 shadow-sm mt-4">
                <div class="card-header bg-white border-bottom py-3">
                    <h5 class="mb-0"><i class="bi bi-chat-left-text me-2"></i>Feedback</h5>
                </div>
                <div class="card-body" id="feedbackContent">
                    <p class="text-muted">Loading feedback...</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="card border-0 shadow-sm mb-4 action-card text-white d-none" id="actionsWrapper">
                <div class="card-body">
                    <h5 class="mb-3"><i class="bi bi-lightning-fill me-2"></i>Actions</h5>
                    <div id="actionButtons" class="d-grid gap-2">
                        <!-- Dynamic buttons -->
                    </div>
                </div>
            </div>

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

    <!-- Feedback Modal -->
    <div class="modal fade" id="feedbackModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light">
                    <h5 class="modal-title fw-bold">Rate & Review Service</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <form id="feedbackForm">
                        <div class="mb-4 text-center">
                            <label class="form-label d-block text-muted fw-bold mb-3">Your Rating</label>
                            <div class="rating-stars fs-1 text-warning" id="starRating">
                                <i class="bi bi-star cursor-pointer" data-rating="1"></i>
                                <i class="bi bi-star cursor-pointer" data-rating="2"></i>
                                <i class="bi bi-star cursor-pointer" data-rating="3"></i>
                                <i class="bi bi-star cursor-pointer" data-rating="4"></i>
                                <i class="bi bi-star cursor-pointer" data-rating="5"></i>
                            </div>
                            <input type="hidden" name="rating" id="ratingValue" value="5">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-bold">Comments</label>
                            <textarea name="comment" class="form-control" rows="4" placeholder="How was your experience? (Optional)"></textarea>
                        </div>
                        <div id="feedbackAlert"></div>
                        <button type="submit" class="btn btn-primary w-100 py-2 fw-bold" id="submitFeedbackBtn">
                            Submit Feedback
                        </button>
                    </form>
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
                    <div class="list-group-item d-flex justify-content-between align-items-start border-0 border-bottom">
                        <div>
                            <div class="fw-semibold">${doc.document_type === 'generated' ? 'Generated PDF' : 'Uploaded document'}</div>
                            <div class="text-muted small">${doc.file_path.split('/').pop()}</div>
                        </div>
                        <a href="/user/requests/${requestId}/documents/${doc.id}/download" class="btn btn-sm btn-outline-primary rounded-pill">Download</a>
                    </div>
                `).join('')}</div>`;
        } else {
            documentsContent.innerHTML = '<p class="text-muted">No documents attached yet.</p>';
        }

        // Feedback summary
        if (Array.isArray(request.feedbacks) && request.feedbacks.length) {
            const feedback = request.feedbacks[0];
            const stars = '★'.repeat(feedback.rating) + '☆'.repeat(5 - feedback.rating);
            const responseMessage = feedback.response ? `<div class="mt-3"><strong>Office response:</strong> ${feedback.response}</div>` : '';
            document.getElementById('feedbackContent').innerHTML = `
                <div class="mb-3">
                    <div class="fw-semibold">Your rating</div>
                    <div class="text-warning fs-5">${stars}</div>
                </div>
                <div class="mb-3">
                    <div class="fw-semibold">Your comments</div>
                    <p class="mb-0">${feedback.comment || '<span class="text-muted">No comment provided.</span>'}</p>
                </div>
                ${responseMessage}
            `;
        } else if (request.status === 'Completed') {
            document.getElementById('feedbackContent').innerHTML = '<p class="text-muted">No feedback submitted yet. Rate your experience once the service is completed.</p>';
        } else {
            document.getElementById('feedbackContent').innerHTML = '<p class="text-muted">Feedback can be submitted after the request is completed.</p>';
        }

        // ── Handle Action Buttons ──────────────────────────────────────────
        const actionWrapper = document.getElementById('actionsWrapper');
        const actionButtons = document.getElementById('actionButtons');
        actionButtons.innerHTML = '';
        let hasActions = false;

        // 1. Chat button (always show if office is available)
        if (request.service?.office?.user_id) {
            actionButtons.innerHTML += `
                <a href="/chat/${request.service.office.user_id}" class="btn btn-light text-primary fw-bold">
                    <i class="bi bi-chat-dots-fill me-2"></i>Chat with Office
                </a>
            `;
            hasActions = true;
        }

        // 2. Pay button (if Approved and no payment yet)
        if (request.status === 'Approved' && !request.payment) {
            actionButtons.innerHTML += `
                <a href="/user/requests/${requestId}/payment" class="btn btn-warning text-dark fw-bold">
                    <i class="bi bi-credit-card-fill me-2"></i>Pay for Service
                </a>
            `;
            hasActions = true;
        }

        // 3. Rate button (if Completed and no feedback yet)
        const hasFeedback = request.feedbacks && request.feedbacks.length > 0;
        if (request.status === 'Completed' && !hasFeedback) {
            actionButtons.innerHTML += `
                <button type="button" class="btn btn-info text-white fw-bold" data-bs-toggle="modal" data-bs-target="#feedbackModal">
                    <i class="bi bi-star-fill me-2"></i>Rate & Review
                </button>
            `;
            hasActions = true;
        }

        if (hasActions) {
            actionWrapper.classList.remove('d-none');
        } else {
            actionWrapper.classList.add('d-none');
        }
    }

    // ── Star Rating Logic ───────────────────────────────────────────────────
    document.querySelectorAll('#starRating i').forEach(star => {
        star.addEventListener('mouseover', function() {
            const rating = this.dataset.rating;
            updateStars(rating);
        });
        
        star.addEventListener('click', function() {
            const rating = this.dataset.rating;
            document.getElementById('ratingValue').value = rating;
            updateStars(rating, true);
        });
    });

    document.getElementById('starRating').addEventListener('mouseleave', function() {
        const currentRating = document.getElementById('ratingValue').value;
        updateStars(currentRating);
    });

    updateStars(5);

    function updateStars(rating, isFinal = false) {
        document.querySelectorAll('#starRating i').forEach(s => {
            if (s.dataset.rating <= rating) {
                s.classList.remove('bi-star');
                s.classList.add('bi-star-fill');
            } else {
                s.classList.remove('bi-star-fill');
                s.classList.add('bi-star');
            }
        });
    }

    // ── Feedback Submission ──────────────────────────────────────────────────
    document.getElementById('feedbackForm').addEventListener('submit', async (e) => {
        e.preventDefault();
        const btn = document.getElementById('submitFeedbackBtn');
        const alertDiv = document.getElementById('feedbackAlert');
        
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Submitting...';
        
        const formData = new FormData(e.target);
        try {
            const res = await fetch(`/user/requests/${requestId}/feedback`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
                }
            });
            const data = await res.json();
            
            if (res.ok) {
                alertDiv.innerHTML = `<div class="alert alert-success">${data.message}</div>`;
                setTimeout(() => {
                    bootstrap.Modal.getInstance(document.getElementById('feedbackModal')).hide();
                    renderRequest();
                }, 1500);
            } else {
                alertDiv.innerHTML = `<div class="alert alert-danger">${data.error || 'Submission failed'}</div>`;
                btn.disabled = false;
                btn.innerHTML = 'Submit Feedback';
            }
        } catch (err) {
            alertDiv.innerHTML = `<div class="alert alert-danger">An error occurred.</div>`;
            btn.disabled = false;
            btn.innerHTML = 'Submit Feedback';
        }
    });

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
