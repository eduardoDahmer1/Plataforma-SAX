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

    'integration_monitor' => [
        'token' => env('INTEGRATION_MONITOR_TOKEN'),
        'stale_after_minutes' => env('INTEGRATION_STALE_AFTER_MINUTES', 180),
        'alert_cooldown_minutes' => env('INTEGRATION_ALERT_COOLDOWN_MINUTES', 720),
    ],

];
