<?php

return [

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

    'bancard' => [
        'public_key' => env('BANCARD_PUBLIC_KEY'),
        'private_key' => env('BANCARD_PRIVATE_KEY'),
        'sandbox' => filter_var(env('BANCARD_SANDBOX', env('BANCARD_MODE', 'sandbox') === 'sandbox'), FILTER_VALIDATE_BOOLEAN),
    ],

    'pagopar' => [
        'public_key' => env('PAGOPAR_PUBLIC_KEY'),
        'private_key' => env('PAGOPAR_PRIVATE_KEY'),
    ],

];
