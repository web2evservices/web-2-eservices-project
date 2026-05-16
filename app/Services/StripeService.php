<?php
namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        Stripe::setApiKey(config('payment.stripe.secret'));
    }

    public function createPaymentIntent(float $amount, int $serviceRequestId): array
    {
        try {
            $intent = PaymentIntent::create([
                'amount'   => (int) round($amount * 100), // cents
                'currency' => 'usd',
                'metadata' => ['service_request_id' => $serviceRequestId],
            ]);
            return ['success' => true, 'client_secret' => $intent->client_secret, 'intent_id' => $intent->id];
        } catch (\Exception $e) {
            Log::error('Stripe error: ' . $e->getMessage());
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    public function retrieveIntent(string $intentId): array
    {
        try {
            $intent = PaymentIntent::retrieve($intentId);
            return ['success' => true, 'status' => $intent->status, 'id' => $intent->id];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}