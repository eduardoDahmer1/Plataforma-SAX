<?php

namespace App\Services\Dhl;

use App\Models\DhlSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

final class DhlSettingsService
{
    public const CACHE_KEY = 'dhl:settings:resolved';

    public function resolved(): array
    {
        $fallback = (array) config('services.dhl', []);

        if (! Schema::hasTable('dhl_settings')) {
            return $fallback;
        }

        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function () use ($fallback): array {
            $setting = DhlSetting::query()->first();

            return $setting ? $this->toServiceConfig($setting, $fallback) : $fallback;
        });
    }

    public function current(): DhlSetting
    {
        return DhlSetting::query()->firstOrCreate([], $this->databaseDefaults());
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    public function databaseDefaults(): array
    {
        $config = (array) config('services.dhl', []);
        $origin = (array) ($config['origin'] ?? []);

        return [
            'enabled' => (bool) ($config['enabled'] ?? false),
            'environment' => (string) ($config['environment'] ?? 'sandbox'),
            'api_key' => $config['api_key'] ?? null,
            'api_secret' => $config['api_secret'] ?? null,
            'account_number' => $config['account_number'] ?? null,
            'currency' => (string) ($config['currency'] ?? 'USD'),
            'unit_of_measurement' => (string) ($config['unit_of_measurement'] ?? 'metric'),
            'duties_taxes_payer' => (string) ($config['duties_taxes_payer'] ?? 'receiver'),
            'incoterm' => (string) ($config['incoterm'] ?? 'DAP'),
            'excluded_country_codes' => (array) ($config['excluded_country_codes'] ?? ['PY']),
            'volumetric_divisor' => (float) ($config['volumetric_divisor'] ?? 5000),
            'rate_markup_percent' => (float) ($config['rate_markup_percent'] ?? 5),
            'rate_markup_enabled' => (bool) ($config['rate_markup_enabled'] ?? true),
            'fallback_measurements_enabled' => (bool) ($config['fallback_measurements_enabled'] ?? true),
            'free_shipping_enabled' => (bool) ($config['free_shipping_enabled'] ?? false),
            'free_shipping_min_items' => $config['free_shipping_min_items'] ?? null,
            'free_shipping_min_subtotal' => $config['free_shipping_min_subtotal'] ?? null,
            'free_shipping_max_billable_weight_kg' => $config['free_shipping_max_billable_weight_kg'] ?? null,
            'origin_country_code' => $origin['country_code'] ?? 'PY',
            'origin_postal_code' => $origin['postal_code'] ?? null,
            'origin_city_name' => $origin['city_name'] ?? null,
            'origin_province_code' => $origin['province_code'] ?? null,
            'origin_province_name' => $origin['province_name'] ?? null,
            'origin_district_name' => $origin['district_name'] ?? null,
            'origin_address_line_1' => $origin['address_line_1'] ?? null,
            'origin_address_line_2' => $origin['address_line_2'] ?? null,
            'origin_company_name' => $origin['company_name'] ?? null,
            'origin_trading_name' => $origin['trading_name'] ?? null,
            'origin_tax_id' => $origin['tax_id'] ?? null,
            'origin_contact_name' => $origin['contact_name'] ?? null,
            'origin_phone' => $origin['phone'] ?? null,
            'origin_email' => $origin['email'] ?? null,
            'test_package_id' => null,
            'test_weight_kg' => 1,
            'test_length_cm' => 10,
            'test_width_cm' => 10,
            'test_height_cm' => 10,
            'test_declared_value' => 100,
            'test_destination_country_code' => 'US',
            'test_destination_province_code' => 'NY',
            'test_destination_province_name' => 'New York',
            'test_destination_postal_code' => '10001',
            'test_destination_city' => 'New York',
        ];
    }

    private function toServiceConfig(DhlSetting $setting, array $fallback): array
    {
        $environment = $setting->environment === 'production' ? 'production' : 'sandbox';

        return array_replace_recursive($fallback, [
            'enabled' => (bool) $setting->enabled,
            'environment' => $environment,
            'base_url' => $environment === 'production'
                ? 'https://express.api.dhl.com/mydhlapi'
                : 'https://express.api.dhl.com/mydhlapi/test',
            'api_key' => (string) $setting->api_key,
            'api_secret' => (string) $setting->api_secret,
            'account_number' => (string) $setting->account_number,
            'currency' => $setting->currency,
            'unit_of_measurement' => $setting->unit_of_measurement,
            'duties_taxes_payer' => $setting->duties_taxes_payer,
            'incoterm' => $setting->incoterm,
            'excluded_country_codes' => $setting->excluded_country_codes ?? ['PY'],
            'volumetric_divisor' => (float) $setting->volumetric_divisor,
            'rate_markup_percent' => (float) $setting->rate_markup_percent,
            'rate_markup_enabled' => (bool) $setting->rate_markup_enabled,
            'fallback_measurements_enabled' => (bool) $setting->fallback_measurements_enabled,
            'free_shipping_enabled' => (bool) $setting->free_shipping_enabled,
            'free_shipping_min_items' => $setting->free_shipping_min_items,
            'free_shipping_min_subtotal' => $setting->free_shipping_min_subtotal,
            'free_shipping_max_billable_weight_kg' => $setting->free_shipping_max_billable_weight_kg,
            'origin' => [
                'country_code' => $setting->origin_country_code,
                'postal_code' => $setting->origin_postal_code,
                'city_name' => $setting->origin_city_name,
                'province_code' => $setting->origin_province_code,
                'province_name' => $setting->origin_province_name,
                'district_name' => $setting->origin_district_name,
                'address_line_1' => $setting->origin_address_line_1,
                'address_line_2' => $setting->origin_address_line_2,
                'company_name' => $setting->origin_company_name,
                'trading_name' => $setting->origin_trading_name,
                'tax_id' => $setting->origin_tax_id,
                'contact_name' => $setting->origin_contact_name,
                'phone' => $setting->origin_phone,
                'email' => $setting->origin_email,
            ],
        ]);
    }
}
