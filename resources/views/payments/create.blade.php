@extends('users.layout')
@section('title', 'Payment')
@section('content')

<style>
    .method-card {
        transition: transform 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275), box-shadow 0.3s ease;
        border-radius: 1rem;
    }
    .method-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px rgba(0,0,0,0.1) !important;
    }
    .crypto-option {
        transition: all 0.2s;
        border-radius: 0.75rem;
    }
    .crypto-option:hover {
        background: #fffdf5;
        border-color: #ffc107 !important;
    }
    .payment-header {
        background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
        border-radius: 1rem;
        padding: 1.5rem;
        box-shadow: inset 0 2px 4px rgba(255,255,255,0.5), 0 4px 6px rgba(0,0,0,0.02);
    }
</style>

<div class="d-flex justify-content-between align-items-center mb-4 payment-header">
    <div>
        <h3 class="mb-1"><i class="bi bi-credit-card me-2 text-primary"></i>Pay for Request #{{ $request->id }}</h3>
        <p class="text-muted mb-0">
            {{ $request->service->name }} —
            <strong class="text-success">${{ number_format($request->service->price, 2) }}</strong>
            @if($mode === 'test')
                <span class="badge bg-warning ms-2">TEST MODE</span>
            @else
                <span class="badge bg-success ms-2">LIVE MODE</span>
            @endif
        </p>
    </div>
    <a href="{{ route('user.requests.show', $request->id) }}" class="btn btn-outline-secondary btn-sm">Back</a>
</div>

<div class="row g-4">
    {{-- Method cards --}}
    <div class="col-12">
        <div class="row g-3">
            <div class="col-md-6">
                <div class="card h-100 method-card border-2 border-primary" id="cardMethodCard" style="cursor:pointer;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-credit-card fs-1 text-primary"></i>
                        <h5 class="mt-2">Card Payment</h5>
                        @if($mode === 'test')
                            <p class="text-muted small mb-0">Stripe (Test)<br><code>4242 4242 4242 4242</code></p>
                        @else
                            <p class="text-muted small mb-0">Tap Payments — Visa/Mastercard<br><span class="badge bg-success">Works in Lebanon</span></p>
                        @endif
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card h-100 method-card border" id="cryptoMethodCard" style="cursor:pointer;">
                    <div class="card-body text-center py-4">
                        <i class="bi bi-currency-bitcoin fs-1 text-warning"></i>
                        <h5 class="mt-2">Cryptocurrency</h5>
                        <p class="text-muted small mb-0">
                            NOWPayments — USDT, BTC, ETH<br>
                            @if(config('payment.nowpayments.sandbox'))
                                <span class="badge bg-warning">Sandbox</span>
                            @else
                                <span class="badge bg-success">Live</span>
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Card section --}}
    <div class="col-12" id="cardSection">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0">
                    @if($mode === 'test')
                        <i class="bi bi-stripe me-2"></i>Pay with Stripe (Test)
                    @else
                        <i class="bi bi-credit-card me-2"></i>Pay with Tap Payments (Live)
                    @endif
                </h5>
            </div>
            <div class="card-body">
                @if($mode === 'test')
                    <div class="alert alert-warning small">
                        <strong>Test mode:</strong> Use card <code>4242 4242 4242 4242</code>, any future date, any CVV.
                    </div>
                    {{-- Stripe card element --}}
                    <div id="stripe-card-element" class="form-control mb-3" style="height:45px;padding-top:11px;"></div>
                    <div id="stripe-errors" class="text-danger small mb-2"></div>
                    <button id="payStripeBtn" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-lock-fill me-2"></i>Pay ${{ number_format($request->service->price, 2) }}
                    </button>
                @else
                    <div class="alert alert-info small">
                        You will be redirected to a secure Tap Payments page to complete payment with 3D Secure.
                    </div>
                    <button id="payTapBtn" class="btn btn-primary btn-lg w-100">
                        <i class="bi bi-lock-fill me-2"></i>Pay ${{ number_format($request->service->price, 2) }} with Card
                    </button>
                @endif
                <div id="cardError" class="mt-2"></div>
            </div>
        </div>
    </div>

    {{-- Crypto section --}}
    <div class="col-12 d-none" id="cryptoSection">
        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white py-3">
                <h5 class="mb-0"><i class="bi bi-currency-bitcoin me-2"></i>Cryptocurrency via NOWPayments</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info small">
                    <strong>Recommended for Lebanon:</strong> USDT (TRC20) — lowest network fees (~$0.50).
                </div>

                <div class="row g-2 mb-4" id="cryptoPicker">
                    @foreach([
                        ['value'=>'usdttrc20','label'=>'USDT TRC20','icon'=>'💵','note'=>'Lowest fees'],
                        ['value'=>'usdterc20','label'=>'USDT ERC20','icon'=>'💵','note'=>'Ethereum'],
                        ['value'=>'btc',      'label'=>'Bitcoin',   'icon'=>'₿', 'note'=>'Most popular'],
                        ['value'=>'eth',      'label'=>'Ethereum',  'icon'=>'Ξ', 'note'=>'ETH network'],
                        ['value'=>'ltc',      'label'=>'Litecoin',  'icon'=>'Ł', 'note'=>'Fast & cheap'],
                        ['value'=>'bnb',      'label'=>'BNB',       'icon'=>'🔶','note'=>'BSC network'],
                    ] as $coin)
                    <div class="col-md-4">
                        <div class="card border crypto-option p-2 text-center" data-value="{{ $coin['value'] }}" style="cursor:pointer;">
                            <div class="fs-4">{{ $coin['icon'] }}</div>
                            <div class="fw-semibold small">{{ $coin['label'] }}</div>
                            <div class="text-muted" style="font-size:11px;">{{ $coin['note'] }}</div>
                        </div>
                    </div>
                    @endforeach
                </div>

                <div id="cryptoEstimate" class="alert alert-secondary small d-none mb-3"></div>

                <button id="payCryptoBtn" class="btn btn-warning btn-lg w-100" disabled>
                    <i class="bi bi-arrow-right-circle me-2"></i>Continue to Crypto Payment
                </button>
                <div id="cryptoError" class="mt-2"></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
@if(config('payment.mode') === 'test')
<script src="https://js.stripe.com/v3/"></script>
@endif
<script>
const mode         = '{{ $mode }}';
const requestId    = {{ $request->id }};
const amount       = {{ $request->service->price }};
const csrfToken    = document.querySelector('meta[name="csrf-token"]').content;
let selectedCrypto = null;

// ── Method switching ─────────────────────────────────────────────────────
document.getElementById('cardMethodCard').addEventListener('click', () => switchMethod('card'));
document.getElementById('cryptoMethodCard').addEventListener('click', () => switchMethod('crypto'));

function switchMethod(method) {
    document.getElementById('cardSection').classList.toggle('d-none', method !== 'card');
    document.getElementById('cryptoSection').classList.toggle('d-none', method !== 'crypto');
    document.getElementById('cardMethodCard').classList.toggle('border-primary', method === 'card');
    document.getElementById('cardMethodCard').classList.toggle('border-2', method === 'card');
    document.getElementById('cryptoMethodCard').classList.toggle('border-warning', method === 'crypto');
    document.getElementById('cryptoMethodCard').classList.toggle('border-2', method === 'crypto');
}

// ── Stripe (test mode) ───────────────────────────────────────────────────
@if($mode === 'test' && $stripeClientSecret)
const stripe   = Stripe('{{ $stripeKey }}');
const elements = stripe.elements();
const cardEl   = elements.create('card', { style: { base: { fontSize: '16px', color: '#333' } } });
cardEl.mount('#stripe-card-element');
cardEl.on('change', e => {
    document.getElementById('stripe-errors').textContent = e.error ? e.error.message : '';
});

document.getElementById('payStripeBtn').addEventListener('click', async () => {
    const btn = document.getElementById('payStripeBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Processing...';

    const { error, paymentIntent } = await stripe.confirmCardPayment('{{ $stripeClientSecret }}', {
        payment_method: { card: cardEl }
    });

    if (error) {
        document.getElementById('stripe-errors').textContent = error.message;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay ${{ number_format($request->service->price, 2) }}';
        return;
    }

    const res = await fetch(`/payments/${requestId}/stripe/confirm`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ payment_intent_id: paymentIntent.id })
    });
    const data = await res.json();

    if (data.error) {
        document.getElementById('cardError').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay ${{ number_format($request->service->price, 2) }}';
    } else {
        window.location.href = '/user/requests/${requestId}/payment/receipt';
    }
});
@endif

// ── Tap (live mode) ──────────────────────────────────────────────────────
@if($mode === 'live')
document.getElementById('payTapBtn')?.addEventListener('click', async () => {
    const btn = document.getElementById('payTapBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Redirecting...';

    const res = await fetch(`/payments/${requestId}/tap/initiate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({})
    });
    const data = await res.json();

    if (data.error) {
        document.getElementById('cardError').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-lock-fill me-2"></i>Pay with Card';
    } else {
        window.location.href = data.redirect_url;
    }
});
@endif

// ── Crypto coin picker ───────────────────────────────────────────────────
document.querySelectorAll('.crypto-option').forEach(card => {
    card.addEventListener('click', async function() {
        document.querySelectorAll('.crypto-option').forEach(c => {
            c.classList.remove('border-warning', 'border-2');
        });
        this.classList.add('border-warning', 'border-2');
        selectedCrypto = this.dataset.value;
        document.getElementById('payCryptoBtn').disabled = true;

        const est = document.getElementById('cryptoEstimate');
        est.classList.remove('d-none');
        est.textContent = 'Fetching estimate...';

        try {
            const res  = await fetch(`/payments/${requestId}/crypto/estimate?currency=${selectedCrypto}`);
            const data = await res.json();
            est.textContent = data.estimated_amount
                ? `≈ ${data.estimated_amount} ${selectedCrypto.toUpperCase()} for $${amount.toFixed(2)} USD`
                : 'Estimate unavailable — exact amount shown on payment page.';
        } catch {
            est.textContent = 'Could not fetch estimate.';
        }

        document.getElementById('payCryptoBtn').disabled = false;
    });
});

document.getElementById('payCryptoBtn').addEventListener('click', async () => {
    if (!selectedCrypto) return;
    const btn = document.getElementById('payCryptoBtn');
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2"></span>Creating invoice...';

    const res  = await fetch(`/payments/${requestId}/crypto/initiate`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrfToken },
        body: JSON.stringify({ pay_currency: selectedCrypto })
    });
    const data = await res.json();

    if (data.error) {
        document.getElementById('cryptoError').innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        btn.disabled = false;
        btn.innerHTML = '<i class="bi bi-arrow-right-circle me-2"></i>Continue to Crypto Payment';
    } else {
        window.location.href = data.redirect_url;
    }
});
</script>
@endpush