<?php

return [
    'default_gateway' => env('PAYMENT_DEFAULT_GATEWAY', 'midtrans'),

    'currency' => env('PAYMENT_CURRENCY', 'IDR'),

    'gateways' => [
        'midtrans' => [
            'server_key' => env('MIDTRANS_SERVER_KEY'),
            'client_key' => env('MIDTRANS_CLIENT_KEY'),
            'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
            'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
            'sanitize' => true,
        ],

        'xendit' => [
            'secret_key' => env('XENDIT_SECRET_KEY'),
            'public_key' => env('XENDIT_PUBLIC_KEY'),
            'webhook_token' => env('XENDIT_WEBHOOK_TOKEN'),
            'is_production' => env('XENDIT_IS_PRODUCTION', false),
        ],

        'stripe' => [
            'secret_key' => env('STRIPE_SECRET_KEY'),
            'publishable_key' => env('STRIPE_PUBLISHABLE_KEY'),
            'webhook_secret' => env('STRIPE_WEBHOOK_SECRET'),
        ],
    ],

    'webhook' => [
        'routes' => [
            'prefix' => 'api/webhooks',
            'middleware' => ['api'],
        ],
    ],

    'retry' => [
        'max_attempts' => 3,
        'backoff_seconds' => 5,
    ],

    'logging' => [
        'enabled' => true,
        'store_payload' => true,
    ],
];
