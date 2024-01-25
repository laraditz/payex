<?php

/*
 * You can place your custom package configuration in here.
 */
return [
    'email' => env('PAYEX_EMAIL'),
    'key' => env('PAYEX_KEY'),
    'secret' => env('PAYEX_SECRET'),
    'currency_code' => env('PAYEX_CURRENCY_CODE', 'MYR'),
    'base_url' => 'https://api.payex.io',
    'callback_url' => env('PAYEX_CALLBACK_URL'),
    'sandbox' => [
        'mode' => env('PAYEX_SANDBOX_MODE', false),
        'base_url' => 'https://sandbox-payexapi.azurewebsites.net',
    ],
    'routes' => [
        'prefix' => 'payex',
        'auth_token' => '/api/Auth/Token',
        'payment_intent' => '/api/v1/PaymentIntents',
        'transactions' => [
            'index' => '/api/v1/Transactions',
            'one' => '/api/v1/Transactions/{id}'
        ]
    ],
    'middleware' => ['api'],
    'log_request' => env('PAYEX_LOG_REQUEST', false),
];
