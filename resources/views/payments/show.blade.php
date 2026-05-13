@extends('users.layout')

@section('title', 'Payment receipt')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-receipt me-2 text-primary"></i>Payment receipt</h3>
        <p class="text-muted mb-0">Request #{{ $request->id }}</p>
    </div>
    <a href="{{ route('user.requests.show', $request->id) }}" class="btn btn-outline-secondary btn-sm">Back to request</a>
</div>

@if($request->payment)
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom py-3">
            <h5 class="mb-0">Transaction details</h5>
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
                    <p class="mb-0">{{ $request->service->name }}</p>
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
