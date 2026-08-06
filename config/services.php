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
        'failure_alert_after_minutes' => env('INTEGRATION_FAILURE_ALERT_AFTER_MINUTES', 1440),
        'email_alerts' => env('INTEGRATION_EMAIL_ALERTS_ENABLED', true),
    ],

    'deepseek' => [
        'api_key' => env('DEEPSEEK_API_KEY'),
        'base_url' => env('DEEPSEEK_BASE_URL', 'https://api.deepseek.com'),
        'model' => env('DEEPSEEK_MODEL', 'deepseek-v4-flash'),
        'timeout' => (int) env('DEEPSEEK_TIMEOUT', 60),
    ],

    'geonames' => [
        'username' => env('GEONAMES_USERNAME'),
        'base_url' => env('GEONAMES_BASE_URL', 'https://secure.geonames.org'),
        'timeout' => (int) env('GEONAMES_TIMEOUT', 12),
    ],

];
