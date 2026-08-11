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

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'base_url' => env('OPENAI_BASE_URL', 'https://api.openai.com/v1'),
        'model' => env('OPENAI_MODEL', 'gpt-5.6-luna'),
        'reasoning_effort' => env('OPENAI_REASONING_EFFORT', 'low'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 90),
        'max_output_tokens' => (int) env('OPENAI_MAX_OUTPUT_TOKENS', 3000),
    ],

    'geonames' => [
        'username' => env('GEONAMES_USERNAME'),
        'base_url' => env('GEONAMES_BASE_URL', 'https://secure.geonames.org'),
        'timeout' => (int) env('GEONAMES_TIMEOUT', 12),
    ],

];
