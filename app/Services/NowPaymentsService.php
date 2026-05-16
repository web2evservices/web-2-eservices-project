<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class NowPaymentsService
{
    private string $apiKey;
    private string $baseUrl;
    private string $ipnSecret;

    public function __construct()
    {
        $this->apiKey    = config('payment.nowpayments.api_key');
        $this->baseUrl   = config('payment.nowpayments.base_url');
        $this->ipnSecret = config('payment.nowpayments.ipn_secret');
    }

    public function createInvoice(array $data): array
    {
        $payload = [
            'price_amount'      => $data['price_amount'],
            'price_currency'    => 'usd',
            'pay_currency'      => $data['pay_currency'] ?? 'usdttrc20',
            'order_id'          => (string) $data['order_id'],
            'order_description' => $data['description'] ?? 'E-Services Payment',
            'ipn_callback_url'  => route('payments.nowpayments.webhook'),
            'success_url'       => $data['success_url'],
            'cancel_url'        => $data['cancel_url'],
        ];

        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->post("{$this->baseUrl}/invoice", $payload);

        if ($response->failed()) {
            Log::error('NOWPayments invoice failed', $response->json());
            return ['success' => false, 'error' => $response->json()['message'] ?? 'NOWPayments error'];
        }

        $body = $response->json();
        return ['success' => true, 'invoice_id' => $body['id'], 'invoice_url' => $body['invoice_url']];
    }

    public function estimateAmount(float $usdAmount, string $currency = 'usdttrc20'): ?float
    {
        $response = Http::withHeaders(['x-api-key' => $this->apiKey])
            ->get("{$this->baseUrl}/estimate", [
                'amount'        => $usdAmount,
                'currency_from' => 'usd',
                'currency_to'   => $currency,
            ]);

        return $response->ok() ? ($response->json()['estimated_amount'] ?? null) : null;
    }

    public function verifyWebhookSignature(string $rawBody, string $signature): bool
    {
        $sorted = json_decode($rawBody, true);
        ksort($sorted);
        $expected = hash_hmac('sha512', json_encode($sorted), $this->ipnSecret);
        return hash_equals($expected, $signature);
    }
}