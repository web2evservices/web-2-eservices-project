@extends('office.layouts.app')

@section('title', 'Request #' . $request->id)

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-file-earmark-text me-2 text-primary"></i>Request #{{ $request->id }}</h3>
        <p class="text-muted mb-0">Update status, upload official response documents, and share QR tracking.</p>
    </div>
    <a href="{{ route('office.requests.index') }}" class="btn btn-outline-secondary btn-sm">
        <i class="bi bi-arrow-left"></i> All requests
    </a>
</div>

<div class="row g-4">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Request summary</h5>
            </div>
            <div class="card-body">
                <div class="row g-4">
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Service</h6>
                        <p class="mb-1"><strong>{{ $request->service->name }}</strong></p>
                        <p class="text-muted small mb-0">{{ $request->service->category->name }}</p>
                        <p class="text-muted small mb-0">{{ $request->service->office->name }}</p>
                    </div>
                    <div class="col-md-6">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Citizen</h6>
                        <p class="mb-1"><strong>{{ $request->citizen->username ?? 'N/A' }}</strong></p>
                        <p class="text-muted small mb-0">{{ $request->citizen->email ?? '' }}</p>
                        <p class="text-muted small mb-0">{{ $request->citizen->tel ?? '' }}</p>
                    </div>
                    <div class="col-12">
                        <h6 class="text-uppercase text-muted small fw-bold mb-2">Status</h6>
                        @php
                            $badge = match($request->status) {
                                'Pending' => 'warning',
                                'In Review' => 'info',
                                'Missing Documents' => 'warning',
                                'Approved' => 'success',
                                'Rejected' => 'danger',
                                'Completed' => 'secondary',
                                default => 'secondary',
                            };
                        @endphp
                        <span class="badge bg-{{ $badge }} fs-6">{{ $request->status }}</span>
                    </div>
                    @if($request->notes)
                        <div class="col-12">
                            <h6 class="text-uppercase text-muted small fw-bold mb-2">Notes</h6>
                            <p class="mb-0">{{ $request->notes }}</p>
                        </div>
                    @endif
                    <div class="col-12">
                        <p class="small text-muted mb-0">
                            <strong>Created:</strong> {{ $request->created_at->format('M d, Y H:i') }}
                            · <strong>Updated:</strong> {{ $request->updated_at->format('M d, Y H:i') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Update status</h5>
            </div>
            <div class="card-body">
                <form action="{{ route('office.requests.update-status', $request->id) }}" method="POST">
                    @csrf
                    @method('PATCH')
                    <div class="row g-3 align-items-end">
                        <div class="col-md-8">
                            <label for="status" class="form-label fw-semibold">New status</label>
                            <select name="status" id="status" class="form-select" required>
                                <option value="">Select…</option>
                                @if($request->status == 'Pending')
                                    <option value="In Review">In Review</option>
                                @elseif($request->status == 'In Review')
                                    <option value="Missing Documents">Missing Documents</option>
                                    <option value="Approved">Approved</option>
                                    <option value="Rejected">Rejected</option>
                                @elseif($request->status == 'Missing Documents')
                                    <option value="In Review">In Review</option>
                                @elseif($request->status == 'Approved')
                                    <option value="Completed">Completed</option>
                                @endif
                            </select>
                        </div>
                        <div class="col-md-4">
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="bi bi-arrow-repeat me-1"></i> Update
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Documents</h5>
            </div>
            <div class="card-body">
                @if($request->documents->count() > 0)
                    <div class="list-group mb-4">
                        @foreach($request->documents as $document)
                            <div class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <div class="fw-semibold">{{ $document->document_type }}</div>
                                    <small class="text-muted">{{ $document->created_at->format('M d, Y H:i') }}</small>
                                </div>
                                <a href="{{ route('office.requests.download-document', [$request->id, $document->id]) }}" class="btn btn-sm btn-outline-primary">
                                    <i class="bi bi-download"></i> Download
                                </a>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-muted">No documents yet.</p>
                @endif

                <h6 class="fw-bold mb-3">Upload official response</h6>
                <form action="{{ route('office.requests.upload-document', $request->id) }}" method="POST" enctype="multipart/form-data" class="row g-3">
                    @csrf
                    <div class="col-md-6">
                        <label for="type" class="form-label">Document type</label>
                        <select name="type" id="type" class="form-select" required>
                            <option value="">Select…</option>
                            <option value="Approval Letter">Approval letter</option>
                            <option value="Rejection Notice">Rejection notice</option>
                            <option value="Certificate">Certificate</option>
                            <option value="Receipt">Receipt</option>
                            <option value="Official Response">Official response</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label for="document" class="form-label">File</label>
                        <input type="file" name="document" id="document" class="form-control" required accept=".pdf,.doc,.docx,.jpg,.jpeg,.png">
                    </div>
                    <div class="col-12">
                        <button type="submit" class="btn btn-success">
                            <i class="bi bi-upload me-1"></i> Upload document
                        </button>
                    </div>
                </form>

                <hr class="my-4">

                <h6 class="fw-bold mb-2"><i class="bi bi-file-earmark-text me-1"></i>Automated summary</h6>
                <p class="text-muted small">Creates a printable HTML file attached to this request (citizen can open it and use <strong>Print → Save as PDF</strong>).</p>
                <form action="{{ route('office.requests.generate-summary', $request->id) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-outline-primary btn-sm">
                        <i class="bi bi-printer me-1"></i> Generate printable summary
                    </button>
                </form>
            </div>
        </div>
    </div>

    <div class="col-lg-4">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0"><i class="bi bi-qr-code me-2"></i>QR tracking</h5>
            </div>
            <div class="card-body d-grid gap-2">
                <a href="{{ route('office.qr.show', $request->id) }}" class="btn btn-outline-primary">
                    <i class="bi bi-eye"></i> View QR code
                </a>
                <a href="{{ route('office.qr.download', $request->id) }}" class="btn btn-outline-secondary">
                    <i class="bi bi-download"></i> Download QR
                </a>
            </div>
        </div>
    </div>
</div>
@endsection
