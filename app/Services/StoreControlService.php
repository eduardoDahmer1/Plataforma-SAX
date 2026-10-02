<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class StoreControlService
{
    public const CACHE_KEY = 'store_manual_controls';
    public const TEMPORARY_TEST_MINUTES = 5;

    private const TEMPORARY_TEST_FIELDS = [
        'cart_enabled',
        'checkout_enabled',
        'add_to_cart_enabled',
        'deposit_enabled',
        'bancard_enabled',
        'pix_enabled',
        'whatsapp_enabled',
        'geonames_enabled',
    ];

    private ?array $memoizedTemporaryTestStatus = null;

    /**
     * Controles efetivos usados pela loja. Durante a janela de teste, somente
     * recursos do fluxo de compra são sobrepostos; a configuração persistida
     * continua intacta e volta a valer automaticamente após a expiração.
     */
    public function settings(): array
    {
        $settings = $this->manualSettings();
        $temporaryTest = $this->temporaryPurchaseTestStatus();

        if ($temporaryTest['active']) {
            foreach (self::TEMPORARY_TEST_FIELDS as $field) {
                $settings[$field] = true;
            }
        }

        return array_merge($settings, [
            'purchase_test_active' => $temporaryTest['active'],
            'purchase_test_until' => $temporaryTest['expires_at'],
            'purchase_test_remaining_seconds' => $temporaryTest['remaining_seconds'],
        ]);
    }

    /** Configuração manual sem a sobreposição temporária, usada no painel. */
    public function manualSettings(): array
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
                ->mapWithKeys(function (string $key) use ($settings): array {
                    $value = $settings->{$key};
                    return [$key => $key === 'store_profile' ? ($value ?: 'stage') : (bool) $value];
                })
                ->all());
        });
    }

    public function defaults(): array
    {
        return [
            'store_profile' => 'stage',
            'cart_enabled' => true,
            'checkout_enabled' => true,
            'add_to_cart_enabled' => true,
            'deposit_enabled' => true,
            'bancard_enabled' => true,
            'pix_enabled' => true,
            'whatsapp_enabled' => true,
            // O catálogo mundial deve ser liberado conscientemente no painel.
            'geonames_enabled' => false,
            'header_categories_enabled' => true,
            'header_institucional_enabled' => true,
            'header_bridal_enabled' => true,
            'header_palace_enabled' => true,
            'header_cafe_enabled' => true,
            'header_blog_enabled' => true,
            'header_contact_enabled' => true,
            'header_guide_enabled' => true,
            'footer_categories_enabled' => true,
            'footer_institucional_enabled' => true,
            'footer_bridal_enabled' => true,
            'footer_palace_enabled' => true,
            'footer_cafe_enabled' => true,
            'footer_blog_enabled' => true,
            'footer_contact_enabled' => true,
            'footer_guide_enabled' => true,
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

    public function temporaryPurchaseTestStatus(): array
    {
        if ($this->memoizedTemporaryTestStatus !== null) {
            return $this->memoizedTemporaryTestStatus;
        }

        $inactive = [
            'active' => false,
            'expires_at' => null,
            'remaining_seconds' => 0,
            'activated_by' => null,
        ];

        if (! Schema::hasTable('system_settings') || ! Schema::hasColumn('system_settings', 'purchase_test_until')) {
            return $this->memoizedTemporaryTestStatus = $inactive;
        }

        $settings = SystemSetting::query()->first();
        $expiresAt = $settings?->purchase_test_until;

        if (! $expiresAt || ! now()->lt($expiresAt)) {
            return $this->memoizedTemporaryTestStatus = $inactive;
        }

        return $this->memoizedTemporaryTestStatus = [
            'active' => true,
            'expires_at' => $expiresAt,
            'remaining_seconds' => max(0, (int) now()->diffInSeconds($expiresAt)),
            'activated_by' => $settings->purchase_test_activated_by,
        ];
    }

    public function temporaryPurchaseTestActive(): bool
    {
        return (bool) $this->temporaryPurchaseTestStatus()['active'];
    }

    public function activateTemporaryPurchaseTest(?int $userId): array
    {
        $this->ensureTemporaryTestColumnsExist();

        $settings = SystemSetting::query()->firstOrCreate([], ['maintenance' => false]);
        $settings->forceFill([
            'purchase_test_until' => now()->addMinutes(self::TEMPORARY_TEST_MINUTES),
            'purchase_test_activated_by' => $userId,
        ])->save();

        $this->memoizedTemporaryTestStatus = null;

        return $this->temporaryPurchaseTestStatus();
    }

    public function deactivateTemporaryPurchaseTest(): void
    {
        $this->ensureTemporaryTestColumnsExist();

        SystemSetting::query()->update([
            'purchase_test_until' => null,
            'purchase_test_activated_by' => null,
        ]);

        $this->memoizedTemporaryTestStatus = null;
    }

    public function storeProfile(): string
    {
        return (string) ($this->settings()['store_profile'] ?? 'stage');
    }

    public function isOtica(): bool
    {
        return $this->storeProfile() === 'otica';
    }

    public function navigationVisible(string $area, string $section): bool
    {
        return (bool) ($this->settings()[sprintf('%s_%s_enabled', $area, $section)] ?? true);
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
        $this->memoizedTemporaryTestStatus = null;
    }

    private function ensureTemporaryTestColumnsExist(): void
    {
        if (! Schema::hasTable('system_settings') || ! Schema::hasColumn('system_settings', 'purchase_test_until')) {
            throw new RuntimeException('Execute as migrações antes de ativar a janela temporária de testes.');
        }
    }
}
