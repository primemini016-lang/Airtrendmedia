<?php

return [

    'paystack' => [
        'public_key' => env('PAYSTACK_PUBLIC_KEY', ''),
        'secret_key' => env('PAYSTACK_SECRET_KEY', ''),
        'base_url'   => env('PAYSTACK_BASE_URL', 'https://api.paystack.co'),
        'webhook_url'=> env('PAYSTACK_WEBHOOK_URL'),
        'callback_url' => env('PAYSTACK_CALLBACK_URL'),
        // Paystack only accepts certain local currencies (NGN, GHS, ZAR, KES).
        // Dollar payments are converted to the configured paystack currency.
        'currency'   => env('PAYSTACK_CURRENCY', 'NGN'),
    ],

    'app' => [
        'default_currency' => env('DEFAULT_CURRENCY', 'USD'),
        'activation_fee'   => (float) env('ACTIVATION_FEE_USD', 5.00),
        'affiliate_reward' => (float) env('AFFILIATE_REWARD_USD', 1.50),
    ],

    'mailgun' => [
        'domain' => env('MAILGUN_DOMAIN'),
        'secret' => env('MAILGUN_SECRET'),
        'endpoint' => env('MAILGUN_ENDPOINT', 'api.mailgun.net'),
        'scheme' => 'https',
    ],

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

];
