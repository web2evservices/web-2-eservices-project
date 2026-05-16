<?php
namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class TapPaymentService
{
    private string $secretKey;
    private string $baseUrl;

    public function __construct()
    {
        $this->secretKey = config('payment.tap.secret_key');
        $this->baseUrl   = config('payment.tap.base_url');
    }

    public function createCharge(array $data): array
    {
        $payload = [
            'amount'               => $data['amount'],
            'currency'             => 'USD',
            'threeDSecure'         => true,
            'save_card'            => false,
            'description'          => 'E-Services Payment - Request #' . $data['service_request_id'],
            'reference'            => ['order' => 'order_' . $data['service_request_id']],
            'customer'             => [
                'first_name' => $data['first_name'] ?? 'Citizen',
                'last_name'  => $data['last_name']  ?? '',
                'email'      => $data['email'],
                'phone'      => ['country_code' => '961', 'number' => ltrim($data['phone'] ?? '71000000', '0')],
            ],
            'source'               => ['id' => 'src_card'],
            'post'                 => ['url' => route('payments.tap.webhook')],
            'redirect'             => ['url' => $data['redirect_url']],
        ];

        $response = Http::withToken($this->secretKey)->post("{$this->baseUrl}/charges", $payload);

        if ($response->failed()) {
            Log::error('Tap charge failed', $response->json());
            return ['success' => false, 'error' => $response->json()['message'] ?? 'Tap API error'];
        }

        $body = $response->json();
        return [
            'success'      => true,
            'charge_id'    => $body['id'],
            'redirect_url' => $body['transaction']['url'],
        ];
    }

    public function retrieveCharge(string $chargeId): array
    {
        $response = Http::withToken($this->secretKey)->get("{$this->baseUrl}/charges/{$chargeId}");
        if ($response->failed()) return ['success' => false];
        $body = $response->json();
        return ['success' => true, 'status' => $body['status'], 'charge_id' => $body['id']];
    }
}