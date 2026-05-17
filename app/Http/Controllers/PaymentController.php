<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use App\Models\ServiceRequests;
use GuzzleHttp\Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function create($requestId)
    {
        $request = ServiceRequests::with(['service'])->findOrFail($requestId);

        if ($request->payment) {
            return redirect()->back()->with('error', 'Payment already exists for this request.');
        }

        return view('payments.create', compact('request'));
    }

    public function store(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::with('service')->findOrFail($requestId);

        if ($serviceRequest->payment) {
            return response()->json(['error' => 'Payment already processed for this request.'], 400);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:card,crypto',
            'crypto_type' => 'required_if:payment_method,crypto|string|in:BTC,ETH,USDT',
        ]);

        $amount = $serviceRequest->service->price ?? 0;
        $currency = 'USD';

        if ($validated['payment_method'] === 'card') {
            return $this->createStripeCheckout($serviceRequest, $amount, $currency);
        }

        return $this->createCryptoCharge($serviceRequest, $amount, $currency, $validated['crypto_type']);
    }

    public function show($requestId)
    {
        $request = ServiceRequests::with(['service', 'payment'])->findOrFail($requestId);

        return view('payments.show', compact('request'));
    }

    public function stripeWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signatureHeader = $request->header('Stripe-Signature');
        $endpointSecret = config('services.stripe.webhook_secret');

        if (!$this->verifyStripeSignature($payload, $signatureHeader, $endpointSecret)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if (($event['type'] ?? '') === 'checkout.session.completed') {
            $session = $event['data']['object'] ?? [];
            $transactionId = $session['id'] ?? null;

            if ($transactionId) {
                $payment = Payments::where('transaction_id', $transactionId)->first();
                if ($payment && $payment->status !== 'Completed') {
                    $payment->status = 'Completed';
                    $payment->transaction_id = $session['payment_intent'] ?? $payment->transaction_id;
                    $payment->save();
                }
            }
        }

        return response()->json(['received' => true]);
    }

    public function coinbaseWebhook(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-CC-Webhook-Signature');
        $sharedSecret = config('services.coinbase.webhook_secret');

        if (!$this->verifyCoinbaseSignature($payload, $signature, $sharedSecret)) {
            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($payload, true);

        if (isset($event['event']['type']) && in_array($event['event']['type'], ['charge:confirmed', 'charge:resolved'])) {
            $charge = $event['event']['data'] ?? [];
            $transactionId = $charge['id'] ?? null;

            if ($transactionId) {
                $payment = Payments::where('transaction_id', $transactionId)->first();
                if ($payment && $payment->status !== 'Completed') {
                    $payment->status = 'Completed';
                    $payment->save();
                }
            }
        }

        return response()->json(['received' => true]);
    }

    private function createStripeCheckout(ServiceRequests $serviceRequest, float $amount, string $currency)
    {
        $client = new Client();

        $response = $client->post('https://api.stripe.com/v1/checkout/sessions', [
            'auth' => [config('services.stripe.secret'), ''],
            'form_params' => [
                'payment_method_types[]' => 'card',
                'line_items[0][price_data][currency]' => $currency,
                'line_items[0][price_data][product_data][name]' => 'Service request #' . $serviceRequest->id,
                'line_items[0][price_data][unit_amount]' => (int)round($amount * 100),
                'line_items[0][quantity]' => 1,
                'mode' => 'payment',
                'success_url' => route('user.requests.payment.show', $serviceRequest->id),
                'cancel_url' => route('user.requests.payment.create', $serviceRequest->id),
                'metadata[service_request_id]' => $serviceRequest->id,
                'metadata[customer_id]' => Auth::id(),
            ],
        ]);

        $data = json_decode($response->getBody()->getContents(), true);
        $sessionUrl = $data['url'] ?? null;
        $sessionId = $data['id'] ?? null;

        if (!$sessionUrl || !$sessionId) {
            return response()->json(['error' => 'Unable to create Stripe checkout session.'], 500);
        }

        Payments::create([
            'service_request_id' => $serviceRequest->id,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'card',
            'transaction_id' => $sessionId,
            'status' => 'Pending',
        ]);

        return response()->json(['url' => $sessionUrl]);
    }

    private function createCryptoCharge(ServiceRequests $serviceRequest, float $amount, string $currency, string $cryptoType)
    {
        $client = new Client();

        $payload = [
            'name' => 'Service request #' . $serviceRequest->id,
            'description' => 'Cryptocurrency payment for request #' . $serviceRequest->id,
            'local_price' => [
                'amount' => number_format($amount, 2, '.', ''),
                'currency' => $currency,
            ],
            'pricing_type' => 'fixed_price',
            'metadata' => [
                'service_request_id' => $serviceRequest->id,
                'crypto_type' => $cryptoType,
                'customer_id' => Auth::id(),
            ],
            'redirect_url' => route('user.requests.payment.show', $serviceRequest->id),
            'cancel_url' => route('user.requests.payment.create', $serviceRequest->id),
        ];

        $response = $client->post('https://api.commerce.coinbase.com/charges', [
            'headers' => [
                'X-CC-Api-Key' => config('services.coinbase.api_key'),
                'X-CC-Version' => '2018-03-22',
                'Content-Type' => 'application/json',
            ],
            'json' => $payload,
        ]);

        $body = json_decode($response->getBody()->getContents(), true);
        $chargeData = $body['data'] ?? null;

        if (!is_array($chargeData) || empty($chargeData['hosted_url'])) {
            return response()->json(['error' => 'Unable to create crypto payment.'], 500);
        }

        Payments::create([
            'service_request_id' => $serviceRequest->id,
            'amount' => $amount,
            'currency' => $currency,
            'payment_method' => 'crypto',
            'transaction_id' => $chargeData['id'],
            'status' => 'Pending',
        ]);

        return response()->json(['url' => $chargeData['hosted_url']]);
    }

    private function verifyStripeSignature(string $payload, ?string $signatureHeader, ?string $secret): bool
    {
        if (empty($signatureHeader) || empty($secret)) {
            return false;
        }

        $parts = explode(',', $signatureHeader);
        $timestamp = null;
        $signatures = [];

        foreach ($parts as $part) {
            [$key, $value] = explode('=', $part, 2) + [null, null];
            if ($key === 't') {
                $timestamp = $value;
            }
            if ($key === 'v1') {
                $signatures[] = $value;
            }
        }

        if (!$timestamp || empty($signatures)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $secret);

        foreach ($signatures as $signature) {
            if (hash_equals($expectedSignature, $signature)) {
                return true;
            }
        }

        return false;
    }

    private function verifyCoinbaseSignature(string $payload, ?string $signature, ?string $sharedSecret): bool
    {
        if (empty($signature) || empty($sharedSecret)) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $sharedSecret);

        return hash_equals($expectedSignature, $signature);
    }
}
