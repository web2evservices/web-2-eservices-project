@extends('users.layout')

@section('title', 'Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-credit-card me-2 text-primary"></i>Payment for request #{{ $request->id }}</h3>
        <p class="text-muted mb-0">Choose card or cryptocurrency to complete your payment.</p>
    </div>
    <a href="{{ route('user.requests.show', $request->id) }}" class="btn btn-outline-secondary btn-sm">Back to request</a>
</div>

<div class="row g-4">
    <div class="col-lg-5">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Request summary</h5>
            </div>
            <div class="card-body">
                <p class="mb-2"><strong>Service:</strong> {{ $request->service->name }}</p>
                <p class="mb-2"><strong>Status:</strong> <span class="badge bg-secondary">{{ $request->status }}</span></p>
                <p class="mb-2"><strong>Submitted:</strong> {{ $request->created_at->format('M d, Y H:i') }}</p>
                @if($request->service->price)
                    <p class="mb-0"><strong>Amount due:</strong> <span class="text-success fw-bold">${{ number_format($request->service->price, 2) }}</span></p>
                @endif
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom py-3">
                <h5 class="mb-0">Payment information</h5>
            </div>
            <div class="card-body">
                <form id="paymentForm">
                    @csrf

                    <p class="fw-semibold mb-2">Payment method <span class="text-danger">*</span></p>
                    <div class="row g-2 mb-4">
                        <div class="col-md-6">
                            <div class="form-check card border p-3 h-100">
                                <input class="form-check-input" type="radio" name="payment_method" id="card" value="card" required>
                                <label class="form-check-label fw-semibold" for="card">Credit / debit card</label>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-check card border p-3 h-100">
                                <input class="form-check-input" type="radio" name="payment_method" id="crypto" value="crypto" required>
                                <label class="form-check-label fw-semibold" for="crypto">Cryptocurrency</label>
                            </div>
                        </div>
                    </div>

                    <div id="cryptoFields" class="d-none">
                        <div class="mb-3">
                            <label for="crypto_type" class="form-label">Cryptocurrency</label>
                            <select id="crypto_type" name="crypto_type" class="form-select">
                                <option value="">Select…</option>
                                <option value="BTC">Bitcoin (BTC)</option>
                                <option value="ETH">Ethereum (ETH)</option>
                                <option value="USDT">Tether (USDT)</option>
                            </select>
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="amount" class="form-label">Amount</label>
                        <input type="number" id="amount" name="amount" step="0.01" class="form-control" readonly
                               value="{{ $request->service->price ?? 0 }}">
                        <div class="form-text">Amount is set by the service price and cannot be changed here.</div>
                    </div>

                    <button type="submit" id="payButton" class="btn btn-primary">
                        <i class="bi bi-lock-fill me-1"></i> Process payment
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cryptoFields = document.getElementById('cryptoFields');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const form = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'card') {
                cryptoFields.classList.add('d-none');
                document.getElementById('crypto_type').value = '';
            } else if (this.value === 'crypto') {
                cryptoFields.classList.remove('d-none');
            }
        });
    });

    form.addEventListener('submit', function(e) {
        e.preventDefault();
        payButton.disabled = true;
        payButton.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Processing…';

        const formData = new FormData(form);

        fetch(`/user/requests/{{ $request->id }}/payment`, {
            method: 'POST',
            body: formData,
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                alert(data.error);
                payButton.disabled = false;
                payButton.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Process payment';
                return;
            }

            window.location.href = data.url;
        })
        .catch(() => {
            alert('An error occurred. Please try again.');
            payButton.disabled = false;
            payButton.innerHTML = '<i class="bi bi-lock-fill me-1"></i> Process payment';
        });
    });
});
</script>
@endpush
