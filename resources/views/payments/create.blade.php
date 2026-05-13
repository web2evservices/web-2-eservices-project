@extends('users.layout')

@section('title', 'Payment')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h3 class="mb-1"><i class="bi bi-credit-card me-2 text-primary"></i>Payment for request #{{ $request->id }}</h3>
        <p class="text-muted mb-0">Pay by card or cryptocurrency (simulated).</p>
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

                    <div id="cardFields" class="d-none">
                        <div class="mb-3">
                            <label for="card_number" class="form-label">Card number <span class="text-danger">*</span></label>
                            <input type="text" id="card_number" name="card_number" class="form-control" placeholder="1234 5678 9012 3456">
                        </div>
                        <div class="row g-2">
                            <div class="col-md-6 mb-3">
                                <label for="expiry_date" class="form-label">Expiry (MM/YY)</label>
                                <input type="text" id="expiry_date" name="expiry_date" class="form-control" placeholder="12/26">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label for="cvv" class="form-label">CVV</label>
                                <input type="text" id="cvv" name="cvv" class="form-control" placeholder="123">
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
                        <div class="mb-3">
                            <label for="crypto_wallet" class="form-label">Wallet address</label>
                            <input type="text" id="crypto_wallet" name="crypto_wallet" class="form-control" placeholder="Your wallet address">
                        </div>
                    </div>

                    <div class="mb-4">
                        <label for="amount" class="form-label">Amount <span class="text-danger">*</span></label>
                        <input type="number" id="amount" name="amount" step="0.01" class="form-control" required
                               value="{{ $request->service->price ?? 0 }}">
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
    const cardFields = document.getElementById('cardFields');
    const cryptoFields = document.getElementById('cryptoFields');
    const paymentMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const form = document.getElementById('paymentForm');
    const payButton = document.getElementById('payButton');

    paymentMethodRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'card') {
                cardFields.classList.remove('d-none');
                cryptoFields.classList.add('d-none');
                document.getElementById('crypto_type').value = '';
                document.getElementById('crypto_wallet').value = '';
            } else if (this.value === 'crypto') {
                cryptoFields.classList.remove('d-none');
                cardFields.classList.add('d-none');
                document.getElementById('card_number').value = '';
                document.getElementById('expiry_date').value = '';
                document.getElementById('cvv').value = '';
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
            } else {
                alert('Payment processed successfully!');
                window.location.href = `/user/requests/{{ $request->id }}/payment/receipt`;
            }
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
