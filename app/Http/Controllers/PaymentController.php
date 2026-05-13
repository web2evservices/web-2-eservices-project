<?php

namespace App\Http\Controllers;

use App\Models\Payments;
use App\Models\ServiceRequests;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class PaymentController extends Controller
{
    public function create($requestId)
    {
        $request = ServiceRequests::with(['service'])->findOrFail($requestId);

        // Check if payment already exists
        if ($request->payment) {
            return redirect()->back()->with('error', 'Payment already processed for this request.');
        }

        return view('payments.create', compact('request'));
    }

    public function store(Request $request, $requestId)
    {
        $serviceRequest = ServiceRequests::findOrFail($requestId);

        // Check if payment already exists
        if ($serviceRequest->payment) {
            return response()->json(['error' => 'Payment already processed'], 400);
        }

        $validated = $request->validate([
            'payment_method' => 'required|string|in:card,crypto',
            'amount' => 'required|numeric|min:0',
            'card_number' => 'required_if:payment_method,card|string',
            'expiry_date' => 'required_if:payment_method,card|string',
            'cvv' => 'required_if:payment_method,card|string',
            'crypto_wallet' => 'required_if:payment_method,crypto|string',
            'crypto_type' => 'required_if:payment_method,crypto|string|in:BTC,ETH,USDT',
        ]);

        // Simulate payment processing
        $paymentStatus = $this->processPayment($validated);

        if (!$paymentStatus['success']) {
            return response()->json(['error' => $paymentStatus['message']], 400);
        }

        // Create payment record
        $payment = Payments::create([
            'service_request_id' => $serviceRequest->id,
            'amount' => $validated['amount'],
            'currency' => 'USD',
            'payment_method' => $validated['payment_method'],
            'transaction_id' => 'TXN_' . Str::random(10),
            'status' => 'Completed',
        ]);

        return response()->json([
            'message' => 'Payment processed successfully',
            'data' => $payment,
        ]);
    }

    public function show($requestId)
    {
        $request = ServiceRequests::with(['service', 'payment'])->findOrFail($requestId);

        return view('payments.show', compact('request'));
    }

    private function processPayment($data)
    {
        // Simulate payment processing
        // In a real application, you would integrate with payment gateways

        if ($data['payment_method'] === 'card') {
            // Simulate card payment validation
            if (strlen($data['card_number']) < 13 || strlen($data['card_number']) > 19) {
                return ['success' => false, 'message' => 'Invalid card number'];
            }

            if (!preg_match('/^\d{2}\/\d{2}$/', $data['expiry_date'])) {
                return ['success' => false, 'message' => 'Invalid expiry date format'];
            }

            if (strlen($data['cvv']) < 3 || strlen($data['cvv']) > 4) {
                return ['success' => false, 'message' => 'Invalid CVV'];
            }

            // Simulate processing delay
            sleep(1);

            return ['success' => true, 'message' => 'Card payment processed successfully'];

        } elseif ($data['payment_method'] === 'crypto') {
            // Simulate crypto payment validation
            if (empty($data['crypto_wallet'])) {
                return ['success' => false, 'message' => 'Crypto wallet address is required'];
            }

            // Simulate processing delay
            sleep(2);

            return ['success' => true, 'message' => 'Crypto payment initiated successfully'];
        }

        return ['success' => false, 'message' => 'Invalid payment method'];
    }
}