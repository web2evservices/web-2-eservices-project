<?php

namespace App\Services;

use Stripe\Stripe;
use Stripe\PaymentIntent;
use Illuminate\Support\Facades\Log;

class StripeService
{
    public function __construct()
    {
        // Load Stripe secret key safely from .env
        Stripe::setApiKey(env('STRIPE_SECRET'));

        // Optional safety check (prevents null issues)
        if (!env('STRIPE_SECRET')) {
            throw new \Exception('STRIPE_SECRET is missing in .env file');
        }
    }

    /**
     * Create Stripe Payment Intent
     */
    public function createPaymentIntent(float $amount, int $serviceRequestId): array
    {
        try {
            $intent = PaymentIntent::create([
                'amount' => (int) round($amount * 100), // Stripe uses cents
                'currency' => 'usd',
                'metadata' => [
                    'service_request_id' => $serviceRequestId
                ],
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            return [
                'success' => true,
                'client_secret' => $intent->client_secret,
                'intent_id' => $intent->id,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe createPaymentIntent error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Retrieve Payment Intent status
     */
    public function retrieveIntent(string $intentId): array
    {
        try {
            $intent = PaymentIntent::retrieve($intentId);

            return [
                'success' => true,
                'status' => $intent->status,
                'id' => $intent->id,
            ];
        } catch (\Exception $e) {
            Log::error('Stripe retrieveIntent error: ' . $e->getMessage());

            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }
}