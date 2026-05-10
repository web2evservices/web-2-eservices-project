@extends('office.layouts.app')
@section('title', 'QR Code')
@section('content')

<div class="mb-4">
    <a href="{{ route('office.dashboard') }}" class="text-decoration-none text-muted">
        <i class="bi bi-arrow-left"></i> Back to Dashboard
    </a>
    <h3 class="mt-2">QR Code — Request #{{ $serviceRequest->id }}</h3>
</div>

<div class="row">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm text-center p-4">
            <p class="text-muted mb-3">Citizens scan this to track their request status.</p>

            {{-- SVG QR Code output --}}
            <div class="d-flex justify-content-center mb-3">
                {!! $qrSvg !!}
            </div>

            <p class="mb-1"><strong>Request Code:</strong></p>
            <code class="fs-6">{{ $serviceRequest->qr_code }}</code>

            <div class="mt-4 d-flex gap-2 justify-content-center">
                <a href="{{ route('office.qr.download', $serviceRequest->id) }}"
                   class="btn btn-outline-primary">
                    <i class="bi bi-download me-1"></i> Download PNG
                </a>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white fw-semibold">
                Request Details
            </div>
            <div class="card-body">
                <table class="table table-sm table-borderless mb-0">
                    <tr><th width="140">Request ID</th><td>#{{ $serviceRequest->id }}</td></tr>
                    <tr><th>Service</th><td>{{ $serviceRequest->service->name }}</td></tr>
                    <tr><th>Citizen</th><td>{{ $serviceRequest->citizen->first_name }} {{ $serviceRequest->citizen->last_name }}</td></tr>
                    <tr><th>Status</th>
                        <td>
                            <span class="badge bg-{{ $serviceRequest->status === 'Approved' ? 'success' : 'warning' }}">
                                {{ $serviceRequest->status }}
                            </span>
                        </td>
                    </tr>
                    <tr><th>Submitted</th><td>{{ $serviceRequest->created_at->format('M d, Y') }}</td></tr>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection