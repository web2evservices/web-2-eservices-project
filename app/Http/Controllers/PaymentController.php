<?php
namespace App\Http\Controllers;

use App\Models\Payments;
use App\Models\ServiceRequests;
use App\Services\StripeService;
use App\Services\TapPaymentService;
use App\Services\NowPaymentsService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private StripeService       $stripe,
        private TapPaymentService   $tap,
        private NowPaymentsService  $nowpayments
    ) {}

    // ── Show payment page ────────────────────────────────────────────────────
    public function create($requestId)
    {
        $serviceRequest = ServiceRequests::with('service')
            ->where('citizen_id', Auth::id())
            ->findOrFail($requestId);

        if ($serviceRequest->payment) {
            return redirect()->route('user.requests.payment.show', $requestId)
                ->with('info', 'Payment already processed.');
        }

        $mode = config('payment.mode'); // 'test' or 'live'

        // For test mode with Stripe, pre-create a PaymentIntent
        $stripeClientSecret = null;
        if ($mode === 'test') {
            $result = $this->stripe->createPaymentIntent(
                $serviceRequest->service->price,
                $serviceRequest->id
            );
            $stripeClientSecret = $result['success'] ? $result['client_secret'] : null;
        }

        return view('payments.create', [
            'request'            => $serviceRequest,
            'mode'               => $mode,
            'stripeKey'          => config('payment.stripe.key'),
            'stripeClientSecret' => $stripeClientSecret,
            'tapPublicKey'       => config('payment.tap.public_key'),
        ]);
    }

    // ── TEST: Confirm Stripe payment ─────────────────────────────────────────
    public function confirmStripe(Request $request, $requestId)
    {
        $validated = $request->validate(['payment_intent_id' => 'required|string']);

        $serviceRequest = ServiceRequests::with('service')
            ->where('citizen_id', Auth::id())
            ->findOrFail($requestId);

        if ($serviceRequest->payment) {
            return response()->json(['error' => 'Already paid.'], 400);
        }

        $result = $this->stripe->retrieveIntent($validated['payment_intent_id']);

        if (! $result['success'] || $result['status'] !== 'succeeded') {
            return response()->json(['error' => 'Payment not confirmed.'], 422);
        }

        Payments::create([
            'service_request_id' => $serviceRequest->id,
            'amount'             => $serviceRequest->service->price,
            'currency'           => 'USD',
            'payment_method'     => 'card',
            'gateway'            => 'stripe',
            'gateway_reference'  => $result['id'],
            'payment_mode'       => 'test',
            'transaction_id'     => $result['id'],
            'status'             => 'Completed',
        ]);

        return response()->json(['success' => true]);
    }

    // ── LIVE: Initiate Tap charge → redirect to 3DS ──────────────────────────
    public function initiateTap(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::with('service')
            ->where('citizen_id', Auth::id())
            ->findOrFail($requestId);

        if ($serviceRequest->payment) {
            return response()->json(['error' => 'Already paid.'], 400);
        }

        $user   = Auth::user();
        $result = $this->tap->createCharge([
            'amount'             => $serviceRequest->service->price,
            'service_request_id' => $serviceRequest->id,
            'first_name'         => $user->username,
            'last_name'          => '',
            'email'              => $user->email,
            'phone'              => $user->tel ?? '71000000',
            'redirect_url'       => route('payments.tap.callback', $serviceRequest->id),
        ]);

        if (! $result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }

        // Save pending record
        Payments::create([
            'service_request_id' => $serviceRequest->id,
            'amount'             => $serviceRequest->service->price,
            'currency'           => 'USD',
            'payment_method'     => 'card',
            'gateway'            => 'tap',
            'gateway_reference'  => $result['charge_id'],
            'payment_mode'       => 'live',
            'transaction_id'     => $result['charge_id'],
            'status'             => 'Pending',
        ]);

        return response()->json(['redirect_url' => $result['redirect_url']]);
    }

    // ── LIVE: Tap redirects back after 3DS ───────────────────────────────────
    public function tapCallback(Request $request, $requestId)
    {
        $chargeId = $request->query('tap_id');

        if (! $chargeId) {
            return redirect()->route('user.requests.show', $requestId)
                ->with('error', 'Payment verification failed.');
        }

        $result = $this->tap->retrieveCharge($chargeId);

        $payment = Payments::where('gateway_reference', $chargeId)
            ->where('service_request_id', $requestId)
            ->first();

        if ($payment && $result['success']) {
            $newStatus = $result['status'] === 'CAPTURED' ? 'Completed' : 'Failed';
            $payment->update(['status' => $newStatus]);

            if ($newStatus === 'Completed') {
                return redirect()->route('user.requests.payment.show', $requestId)
                    ->with('success', 'Payment successful!');
            }
        }

        return redirect()->route('user.requests.show', $requestId)
            ->with('error', 'Payment was not completed.');
    }

    // ── Tap webhook ───────────────────────────────────────────────────────────
    public function tapWebhook(Request $request)
    {
        $data = $request->all();
        Log::info('Tap webhook', $data);

        if (($data['status'] ?? '') === 'CAPTURED' && isset($data['id'])) {
            $payment = Payments::where('gateway_reference', $data['id'])->first();
            if ($payment && $payment->status !== 'Completed') {
                $payment->update(['status' => 'Completed']);
            }
        }

        return response()->json(['received' => true]);
    }

    // ── Crypto: create NOWPayments invoice (both modes) ───────────────────────
    public function initiateCrypto(Request $request, $requestId)
    {
        $validated = $request->validate([
            'pay_currency' => 'required|string|in:usdttrc20,usdterc20,btc,eth,ltc,bnb',
        ]);

        $serviceRequest = ServiceRequests::with('service')
            ->where('citizen_id', Auth::id())
            ->findOrFail($requestId);

        if ($serviceRequest->payment) {
            return response()->json(['error' => 'Already paid.'], 400);
        }

        $result = $this->nowpayments->createInvoice([
            'price_amount' => $serviceRequest->service->price,
            'pay_currency' => $validated['pay_currency'],
            'order_id'     => $serviceRequest->id,
            'description'  => 'E-Services Payment - Request #' . $serviceRequest->id,
            'success_url'  => route('payments.nowpayments.success', $serviceRequest->id),
            'cancel_url'   => route('user.requests.payment.create', $serviceRequest->id),
        ]);

        if (! $result['success']) {
            return response()->json(['error' => $result['error']], 422);
        }

        $mode = config('payment.nowpayments.sandbox') ? 'test' : 'live';

        Payments::create([
            'service_request_id' => $serviceRequest->id,
            'amount'             => $serviceRequest->service->price,
            'currency'           => 'USD',
            'payment_method'     => 'crypto',
            'gateway'            => 'nowpayments',
            'gateway_reference'  => $result['invoice_id'],
            'payment_mode'       => $mode,
            'transaction_id'     => $result['invoice_id'],
            'status'             => 'Pending',
        ]);

        return response()->json(['redirect_url' => $result['invoice_url']]);
    }

    // ── Crypto: NOWPayments success return page ───────────────────────────────
    public function nowPaymentsSuccess(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::with(['service', 'payment'])
            ->where('citizen_id', Auth::id())
            ->findOrFail($requestId);

        if ($serviceRequest->payment && $serviceRequest->payment->status === 'Pending') {
            $serviceRequest->payment->update(['status' => 'Completed']);
        }

        return redirect()->route('user.requests.payment.show', $requestId)
            ->with('success', 'Crypto payment received! Confirmation will arrive shortly.');
    }

    // ── NOWPayments IPN webhook ───────────────────────────────────────────────
    public function nowPaymentsWebhook(Request $request)
    {
        $rawBody  = $request->getContent();
        $sig      = $request->header('x-nowpayments-sig', '');

        if (! $this->nowpayments->verifyWebhookSignature($rawBody, $sig)) {
            Log::warning('NOWPayments IPN signature mismatch');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $data    = json_decode($rawBody, true);
        $orderId = $data['order_id']       ?? null;
        $status  = $data['payment_status'] ?? null;

        if ($orderId && in_array($status, ['confirmed', 'finished'])) {
            $payment = Payments::where('service_request_id', $orderId)
                ->where('payment_method', 'crypto')
                ->first();

            if ($payment && $payment->status !== 'Completed') {
                $payment->update(['status' => 'Completed', 'transaction_id' => $data['payment_id'] ?? $payment->transaction_id]);
            }
        }

        return response()->json(['received' => true]);
    }

    // ── Crypto estimate helper ────────────────────────────────────────────────
    public function cryptoEstimate(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::with('service')->findOrFail($requestId);
        $currency = $request->query('currency', 'usdttrc20');
        $estimate = $this->nowpayments->estimateAmount($serviceRequest->service->price, $currency);
        return response()->json(['estimated_amount' => $estimate]);
    }

    // ── Receipt ───────────────────────────────────────────────────────────────
    public function show($requestId)
    {
        $serviceRequest = ServiceRequests::with(['service', 'payment'])
            ->where('citizen_id', Auth::id())
            ->findOrFail($requestId);

        return view('payments.show', ['request' => $serviceRequest]);
    }
}