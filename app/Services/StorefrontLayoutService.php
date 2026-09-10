<?php

namespace App\Services;

use App\Models\Generalsetting;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class StorefrontLayoutService
{
    public const CACHE_KEY = 'storefront_layout.v1';

    public const LAYOUTS = [
        'sax' => [
            'label' => 'SAX',
            'description' => 'Layout atual da SAX Department.',
            'icon' => 'fa-gem',
        ],
        'vista' => [
            'label' => 'Vista & Co',
            'description' => 'Experiência minimalista dedicada à ótica.',
            'icon' => 'fa-glasses',
        ],
    ];

    public function current(): string
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addMinutes(10), function (): string {
                if (! Schema::hasTable('generalsettings') || ! Schema::hasColumn('generalsettings', 'storefront_layout')) {
                    return app(StoreControlService::class)->isOtica() ? 'vista' : 'sax';
                }

                return $this->normalize(Generalsetting::query()->value('storefront_layout'));
            });
        } catch (Throwable) {
            return 'sax';
        }
    }

    public function normalize(?string $layout): string
    {
        return array_key_exists((string) $layout, self::LAYOUTS) ? (string) $layout : 'sax';
    }

    public function homeView(): string
    {
        return $this->current() === 'vista' ? 'storefront.vista.home' : 'home';
    }

    public function partial(string $name): string
    {
        $themed = "storefront.{$this->current()}.{$name}";

        return view()->exists($themed) ? $themed : "components.{$name}";
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
