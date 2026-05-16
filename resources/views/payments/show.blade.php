@extends('users.layout')

@section('title', 'Payment receipt')

@section('content')
<style>
    .receipt-card {
        border-radius: 1.25rem;
        border-top: 8px solid #198754;
        box-shadow: 0 10px 30px rgba(0,0,0,0.08);
        background: url('data:image/svg+xml;utf8,<svg width="100%" height="100%" xmlns="http://www.w3.org/2000/svg"><defs><pattern id="dots" x="0" y="0" width="20" height="20" patternUnits="userSpaceOnUse"><circle fill="rgba(0,0,0,0.02)" cx="2" cy="2" r="2"></circle></pattern></defs><rect width="100%" height="100%" fill="url(%23dots)"></rect></svg>') bg-light;
    }
    .receipt-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: inset 0 2px 4px rgba(255,255,255,0.5), 0 4px 6px rgba(0,0,0,0.02);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 receipt-header">
    <div>
        <h3 class="mb-1"><i class="bi bi-receipt-cutoff me-2 text-success"></i>Payment Receipt</h3>
        <p class="text-muted mb-0">Request #{{ $request->id }}</p>
    </div>
    <a href="{{ route('user.requests.show', $request->id) }}" class="btn btn-outline-secondary btn-sm shadow-sm rounded-pill px-3">Back to request</a>
</div>

@if($request->payment)
    <div class="card border-0 receipt-card">
        <div class="card-header bg-white border-bottom py-4 px-4 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold">Transaction details</h5>
            <i class="bi bi-check-circle-fill text-success fs-3"></i>
        </div>
        <div class="card-body">
            <div class="row g-3">
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">Amount</p>
                    <p class="fs-4 fw-bold text-success mb-0">${{ number_format($request->payment->amount, 2) }} {{ $request->payment->currency }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">Method</p>
                    <p class="fw-semibold mb-0 text-capitalize">{{ $request->payment->payment_method }}</p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">Status</p>
                    <p class="mb-0"><span class="badge bg-success">{{ $request->payment->status }}</span></p>
                </div>
                <div class="col-md-6">
                    <p class="mb-1 text-muted small">Transaction ID</p>
                    <p class="mb-0 font-monospace">{{ $request->payment->transaction_id }}</p>
                </div>
                <div class="col-12">
                    <p class="mb-1 text-muted small">Service</p>
                    <p class="mb-0">{{ $request->service->name ?? ""}}</p>
                </div>
            </div>
        </div>
    </div>
@else
    <div class="alert alert-warning mb-0">
        No payment has been recorded for this request yet.
        <a href="{{ route('user.requests.payment.create', $request->id) }}" class="alert-link">Make a payment</a>
    </div>
@endif
@endsection
