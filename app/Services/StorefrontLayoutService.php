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
        return $this->effective() === 'vista' ? 'storefront.vista.home' : 'home';
    }

    public function effective(): string
    {
        return match (app(StoreControlService::class)->storeProfile()) {
            'otica' => 'vista',
            'sax' => 'sax',
            default => $this->current(),
        };
    }

    public function availableLayouts(): array
    {
        $effective = $this->effective();

        return match (app(StoreControlService::class)->storeProfile()) {
            'otica', 'sax' => [$effective => self::LAYOUTS[$effective]],
            default => self::LAYOUTS,
        };
    }

    /**
     * A identidade da instalação tem prioridade no cabeçalho de todas as áreas.
     * No stage, onde os dois temas são testados, permanece valendo o seletor de layout.
     */
    public function headerLayout(): string
    {
        return $this->effective();
    }

    public function headerPartial(): string
    {
        $themed = "storefront.{$this->headerLayout()}.header";

        return view()->exists($themed) ? $themed : 'components.header';
    }

    public function partial(string $name): string
    {
        $themed = "storefront.{$this->effective()}.{$name}";

        return view()->exists($themed) ? $themed : "components.{$name}";
    }

    public function clear(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
