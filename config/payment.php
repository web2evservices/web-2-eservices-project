<?php
return [
    'mode' => env('PAYMENT_MODE', 'test'), // 'test' or 'live'

    'stripe' => [
        'key'    => env('STRIPE_KEY'),
        'secret' => env('STRIPE_SECRET'),
    ],

    'tap' => [
        'secret_key' => env('TAP_SECRET_KEY'),
        'public_key' => env('TAP_PUBLIC_KEY'),
        'sandbox'    => env('TAP_SANDBOX', true),
        'base_url'   => 'https://api.tap.company/v2',
    ],

    'nowpayments' => [
        'api_key'    => env('NOWPAYMENTS_API_KEY'),
        'ipn_secret' => env('NOWPAYMENTS_IPN_SECRET'),
        'sandbox'    => env('NOWPAYMENTS_SANDBOX', true),
        'base_url'   => env('NOWPAYMENTS_SANDBOX', true)
            ? 'https://api.sandbox.nowpayments.io/v1'
            : 'https://api.nowpayments.io/v1',
    ],
];
