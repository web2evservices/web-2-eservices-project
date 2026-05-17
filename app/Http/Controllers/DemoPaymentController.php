<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\StripeService;
use App\Services\NowPaymentsService;
use Illuminate\Support\Facades\Log;

class DemoPaymentController extends Controller
{
    public function __construct(private StripeService $stripe, private NowPaymentsService $nowpayments) {}

    public function index()
    {
        $mode = config('payment.mode', 'test');
        $amount = 9.99; // demo amount

        $stripeClientSecret = null;
        if ($mode === 'test' && config('payment.stripe.secret')) {
            $result = $this->stripe->createPaymentIntent($amount, 0);
            $stripeClientSecret = $result['success'] ? $result['client_secret'] : null;
        }

        $nowPaymentsConfigured = filled(config('payment.nowpayments.api_key')) && config('payment.nowpayments.api_key') !== 'YOUR_API_KEY';

        return view('demo.payment', [
            'mode' => $mode,
            'amount' => $amount,
            'stripeKey' => config('payment.stripe.key'),
            'stripeClientSecret' => $stripeClientSecret,
            'nowPaymentsConfigured' => $nowPaymentsConfigured,
        ]);
    }

    public function confirmStripe(Request $request)
    {
        $validated = $request->validate(['payment_intent_id' => 'required|string']);

        $result = $this->stripe->retrieveIntent($validated['payment_intent_id']);
        if (! $result['success'] || $result['status'] !== 'succeeded') {
            return response()->json(['error' => 'Payment not confirmed.'], 422);
        }

        return response()->json(['success' => true, 'intent_id' => $result['id']]);
    }

    public function initiateCrypto(Request $request)
    {
        $validated = $request->validate(['pay_currency' => 'required|string']);
        $amount = 9.99;

        $result = $this->nowpayments->createInvoice([
            'price_amount' => $amount,
            'pay_currency' => $validated['pay_currency'],
            'order_id'     => 'demo-'.time(),
            'description'  => 'Demo E-Services Payment',
            'success_url'  => route('demo.payments.receipt'),
            'cancel_url'   => route('demo.payment'),
        ]);

        if (! $result['success']) {
            return response()->json(['error' => $result['error'] ?? 'NOWPayments error'], 422);
        }

        return response()->json(['redirect_url' => $result['invoice_url']]);
    }

    public function receipt()
    {
        return view('demo.receipt');
    }
}
