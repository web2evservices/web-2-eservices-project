@extends('users.layout')
@section('title','Demo Payment')
@section('content')

<div class="container py-5">
    <div class="card shadow-sm">
        <div class="card-body">
            <h3 class="mb-2">Demo Payment — ${{ number_format($amount,2) }}</h3>
            <p class="text-muted">This is a public demo page showing Stripe (test) and NOWPayments (sandbox) flows.</p>

            <div class="row g-4 mt-3">
                <div class="col-md-6">
                    <div class="card p-3">
                        <h5>Card (Stripe Test)</h5>
                        @if($mode === 'test' && $stripeClientSecret)
                            <div id="stripe-card-element" class="form-control mb-3" style="height:45px;padding-top:11px;"></div>
                            <div id="stripe-errors" class="text-danger small mb-2"></div>
                            <button id="payStripeBtn" class="btn btn-primary">Pay ${{ number_format($amount,2) }}</button>
                        @else
                            <div class="alert alert-warning small">Stripe not configured. Set STRIPE_KEY and STRIPE_SECRET in .env</div>
                        @endif
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card p-3">
                        <h5>Cryptocurrency (NOWPayments)</h5>
                        <p class="small text-muted">Sandbox invoices will open in a new tab.</p>
                        @unless($nowPaymentsConfigured)
                            <div class="alert alert-warning">NOWPayments is not configured. Set <code>NOWPAYMENTS_API_KEY</code> in <code>.env</code> and reload.</div>
                        @endunless
                        <div class="d-flex gap-2 flex-wrap">
                            <button class="btn btn-outline-secondary crypto-btn" data-value="usdttrc20" @if(! $nowPaymentsConfigured) disabled @endif>USDT TRC20</button>
                            <button class="btn btn-outline-secondary crypto-btn" data-value="btc" @if(! $nowPaymentsConfigured) disabled @endif>BTC</button>
                            <button class="btn btn-outline-secondary crypto-btn" data-value="eth" @if(! $nowPaymentsConfigured) disabled @endif>ETH</button>
                        </div>
                        <div class="mt-3"><button id="payCryptoBtn" class="btn btn-warning" @if(! $nowPaymentsConfigured) disabled @endif>Create Invoice</button></div>
                        <div id="cryptoMsg" class="mt-2"></div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

@endsection

@push('scripts')
@if($mode === 'test' && $stripeClientSecret)
<script src="https://js.stripe.com/v3/"></script>
<script>
const stripe = Stripe('{{ $stripeKey }}');
const elements = stripe.elements();
const cardEl = elements.create('card');
cardEl.mount('#stripe-card-element');
cardEl.on('change', e => document.getElementById('stripe-errors').textContent = e.error ? e.error.message : '');

document.getElementById('payStripeBtn').addEventListener('click', async () => {
    const btn = document.getElementById('payStripeBtn');
    btn.disabled = true; btn.textContent = 'Processing...';
    const { error, paymentIntent } = await stripe.confirmCardPayment('{{ $stripeClientSecret }}', { payment_method: { card: cardEl } });
    if (error) { document.getElementById('stripe-errors').textContent = error.message; btn.disabled=false; btn.textContent='Pay'; return; }

    const res = await fetch('/demo/payments/stripe/confirm', { method:'POST', headers: {'Content-Type':'application/json','X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content }, body: JSON.stringify({ payment_intent_id: paymentIntent.id }) });
    const data = await res.json();
    if (data.success) { window.location.href = '/demo/payments/receipt'; }
    else { document.getElementById('stripe-errors').textContent = data.error || 'Confirmation failed'; btn.disabled=false; btn.textContent='Pay'; }
});
</script>
@endif

<script>
let selected = null;
document.querySelectorAll('.crypto-btn').forEach(b => b.addEventListener('click', function(){
    document.querySelectorAll('.crypto-btn').forEach(x=>x.classList.remove('btn-primary'));
    this.classList.add('btn-primary'); selected = this.dataset.value; document.getElementById('payCryptoBtn').disabled = false;
}));

document.getElementById('payCryptoBtn').addEventListener('click', async () => {
    if (!selected) return;
    const btn = document.getElementById('payCryptoBtn'); btn.disabled = true; btn.textContent = 'Creating invoice...';
    try {
        const res = await fetch('/demo/payments/crypto/initiate', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            },
            body: JSON.stringify({ pay_currency: selected })
        });

        if (!res.ok) {
            const text = await res.text();
            document.getElementById('cryptoMsg').innerHTML = '<div class="alert alert-danger">Server error: '+(text||res.statusText)+'</div>';
            btn.disabled = false; btn.textContent = 'Create Invoice';
            return;
        }

        let data;
        try { data = await res.json(); } catch (err) { data = null; }

        if (!data) {
            document.getElementById('cryptoMsg').innerHTML = '<div class="alert alert-danger">Unexpected server response.</div>';
            btn.disabled = false; btn.textContent = 'Create Invoice';
            return;
        }

        if (data.error) {
            document.getElementById('cryptoMsg').innerHTML = '<div class="alert alert-danger">'+data.error+'</div>';
            btn.disabled = false; btn.textContent = 'Create Invoice';
        } else {
            window.open(data.redirect_url, '_blank');
            document.getElementById('cryptoMsg').innerHTML = '<div class="alert alert-success">Invoice opened in new tab.</div>';
            btn.disabled = false; btn.textContent = 'Create Invoice';
        }
    } catch (err) {
        console.error('Crypto initiate failed', err);
        document.getElementById('cryptoMsg').innerHTML = '<div class="alert alert-danger">Network error: '+(err.message||err)+'</div>';
        btn.disabled = false; btn.textContent = 'Create Invoice';
    }
});
</script>
@endpush
