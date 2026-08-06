<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class StoreControlService
{
    public const CACHE_KEY = 'store_manual_controls';

    public function settings(): array
    {
        return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): array {
            $defaults = $this->defaults();

            if (! Schema::hasTable('system_settings') || ! Schema::hasColumn('system_settings', 'cart_enabled')) {
                return $defaults;
            }

            $settings = SystemSetting::query()->first();
            if (! $settings) {
                return $defaults;
            }

            return array_merge($defaults, collect(array_keys($defaults))
                ->mapWithKeys(fn (string $key): array => [$key => (bool) $settings->{$key}])
                ->all());
        });
    }

    public function defaults(): array
    {
        return [
            'cart_enabled' => true,
            'checkout_enabled' => true,
            'add_to_cart_enabled' => true,
            'deposit_enabled' => true,
            'bancard_enabled' => true,
            'pix_enabled' => true,
            'whatsapp_enabled' => true,
            // O catálogo mundial deve ser liberado conscientemente no painel.
            'geonames_enabled' => false,
        ];
    }

    public function enabled(string $feature): bool
    {
        $settings = $this->settings();

        return match ($feature) {
            'cart' => $settings['cart_enabled'],
            'add_to_cart' => $settings['cart_enabled'] && $settings['add_to_cart_enabled'],
            'checkout' => $settings['cart_enabled'] && $settings['checkout_enabled'],
            'deposit' => $settings['deposit_enabled'],
            'bancard' => $settings['bancard_enabled'],
            'pix' => $settings['pix_enabled'],
            'whatsapp' => $settings['whatsapp_enabled'],
            'geonames' => $settings['geonames_enabled'],
            default => false,
        };
    }

    public function paymentEnabled(string $method): bool
    {
        return match ($method) {
            'deposito' => $this->enabled('deposit'),
            'bancard_v2' => $this->enabled('bancard'),
            'rendix_pix' => $this->enabled('pix'),
            'whatsapp' => $this->enabled('whatsapp'),
            default => false,
        };
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
