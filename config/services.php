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
        'catalog_reasoning_effort' => env('OPENAI_CATALOG_REASONING_EFFORT', 'medium'),
        'catalog_search_context_size' => env('OPENAI_CATALOG_SEARCH_CONTEXT_SIZE', 'high'),
        'timeout' => (int) env('OPENAI_TIMEOUT', 90),
        'max_output_tokens' => (int) env('OPENAI_MAX_OUTPUT_TOKENS', 3000),
    ],

    'geonames' => [
        'username' => env('GEONAMES_USERNAME'),
        'base_url' => env('GEONAMES_BASE_URL', 'https://secure.geonames.org'),
        'timeout' => (int) env('GEONAMES_TIMEOUT', 12),
    ],

    'dhl' => [
        'enabled' => env('DHL_ENABLED', false),
        'environment' => env('DHL_ENVIRONMENT', 'sandbox'),
        'base_url' => env('DHL_BASE_URL', 'https://express.api.dhl.com/mydhlapi/test'),
        'api_key' => env('DHL_API_KEY'),
        'api_secret' => env('DHL_API_SECRET'),
        'account_number' => env('DHL_ACCOUNT_NUMBER'),
        'timeout' => (int) env('DHL_TIMEOUT', 20),
        'retry_times' => (int) env('DHL_RETRY_TIMES', 2),
        'currency' => env('DHL_CURRENCY', 'USD'),
        'unit_of_measurement' => env('DHL_UNIT_OF_MEASUREMENT', 'metric'),
        'duties_taxes_payer' => env('DHL_DUTIES_TAXES_PAYER', 'receiver'),
        'incoterm' => env('DHL_INCOTERM', 'DAP'),
        'excluded_country_codes' => array_values(array_filter(array_map(
            static fn (string $countryCode): string => strtoupper(trim($countryCode)),
            explode(',', env('DHL_EXCLUDED_COUNTRY_CODES', 'PY'))
        ))),
        'volumetric_divisor' => (float) env('DHL_VOLUMETRIC_DIVISOR', 5000),
        'rate_markup_percent' => (float) env('DHL_RATE_MARKUP_PERCENT', 5),
        'rate_markup_enabled' => filter_var(env('DHL_RATE_MARKUP_ENABLED', true), FILTER_VALIDATE_BOOL),
        'fallback_measurements_enabled' => env('DHL_FALLBACK_MEASUREMENTS_ENABLED', true),
        'free_shipping_enabled' => env('DHL_FREE_SHIPPING_ENABLED', false),
        'free_shipping_min_items' => env('DHL_FREE_SHIPPING_MIN_ITEMS'),
        'free_shipping_min_subtotal' => env('DHL_FREE_SHIPPING_MIN_SUBTOTAL'),
        'free_shipping_max_billable_weight_kg' => env('DHL_FREE_SHIPPING_MAX_BILLABLE_WEIGHT_KG'),
        'origin' => [
            'country_code' => env('DHL_ORIGIN_COUNTRY_CODE', 'PY'),
            'postal_code' => env('DHL_ORIGIN_POSTAL_CODE'),
            'city_name' => env('DHL_ORIGIN_CITY', 'Ciudad del Este'),
            'province_code' => env('DHL_ORIGIN_PROVINCE_CODE'),
            'province_name' => env('DHL_ORIGIN_PROVINCE_NAME'),
            'district_name' => env('DHL_ORIGIN_DISTRICT'),
            'address_line_1' => env('DHL_ORIGIN_ADDRESS_LINE_1'),
            'address_line_2' => env('DHL_ORIGIN_ADDRESS_LINE_2'),
            'company_name' => env('DHL_ORIGIN_COMPANY_NAME'),
            'trading_name' => env('DHL_ORIGIN_TRADING_NAME', 'SAX Department Store'),
            'tax_id' => env('DHL_ORIGIN_TAX_ID'),
            'contact_name' => env('DHL_ORIGIN_CONTACT_NAME'),
            'phone' => env('DHL_ORIGIN_PHONE'),
            'email' => env('DHL_ORIGIN_EMAIL'),
        ],
    ],

    'ip_geolocation' => [
        'enabled' => env('IP_GEOLOCATION_ENABLED', true),
        'url' => env('IP_GEOLOCATION_URL', 'https://ipwho.is/{ip}?fields=success,country,country_code'),
        'timeout' => (int) env('IP_GEOLOCATION_TIMEOUT', 2),
    ],

];
