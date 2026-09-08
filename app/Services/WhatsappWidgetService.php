<?php

namespace App\Services;

use App\Models\WhatsappContact;
use App\Models\WhatsappWidgetSetting;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class WhatsappWidgetService
{
    private const CACHE_KEY = 'whatsapp_widget_configuration';

    public static function contextOptions(): array
    {
        return [
            'all' => __('messages.whatsapp_context_all'),
            'home' => __('messages.whatsapp_context_home'),
            'catalog' => __('messages.whatsapp_context_catalog'),
            'product' => __('messages.whatsapp_context_product'),
            'cart' => __('messages.whatsapp_context_cart'),
            'checkout' => __('messages.whatsapp_context_checkout'),
            'account' => __('messages.whatsapp_context_account'),
            'blog' => __('messages.whatsapp_context_blog'),
            'institutional' => __('messages.whatsapp_context_institutional'),
            'bridal' => __('messages.whatsapp_context_bridal'),
            'palace' => __('messages.whatsapp_context_palace'),
            'cafe' => __('messages.whatsapp_context_cafe'),
            'other' => __('messages.whatsapp_context_other'),
        ];
    }

    public function configuration(Request $request): array
    {
        $configuration = Cache::remember(self::CACHE_KEY, now()->addHours(12), function (): array {
            if (! Schema::hasTable('whatsapp_widget_settings') || ! Schema::hasTable('whatsapp_contacts')) {
                return $this->fallbackConfiguration();
            }

            $setting = WhatsappWidgetSetting::query()->first();

            return [
                'enabled' => (bool) ($setting?->enabled ?? true),
                'settings' => $setting,
                'contacts' => WhatsappContact::query()
                    ->where('active', true)
                    ->orderBy('sort_order')
                    ->orderBy('id')
                    ->get(),
            ];
        });

        $context = $this->resolveContext($request);
        $setting = $configuration['settings'] ?? null;
        $configuration['title'] = $setting?->translated('title') ?: 'Concierge Digital SAX';
        $configuration['subtitle'] = $setting?->translated('subtitle') ?: 'Escolha uma opção e fale com nosso time.';
        unset($configuration['settings']);
        $configuration['context'] = $context;
        $configuration['contacts'] = collect($configuration['contacts'])
            ->filter(function (WhatsappContact $contact) use ($context): bool {
                $contexts = $contact->page_contexts ?: ['all'];

                return in_array('all', $contexts, true) || in_array($context, $contexts, true);
            })
            ->values();

        return $configuration;
    }

    public function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }

    private function fallbackConfiguration(): array
    {
        return [
            'enabled' => false,
            'settings' => new WhatsappWidgetSetting([
                'title' => 'Concierge Digital SAX',
                'title_en' => 'SAX Digital Concierge',
                'title_es' => 'Concierge Digital SAX',
                'subtitle' => 'Escolha uma opção e fale com nosso time.',
                'subtitle_en' => 'Choose an option and talk to our team.',
                'subtitle_es' => 'Elige una opción y habla con nuestro equipo.',
            ]),
            'contacts' => new Collection(),
        ];
    }

    private function resolveContext(Request $request): string
    {
        if ($request->is('*bridal*')) {
            return 'bridal';
        }

        if ($request->is('*palace*')) {
            return 'palace';
        }

        if ($request->is('*cafe*', '*bistro*')) {
            return 'cafe';
        }

        if ($request->is('*institucional*')) {
            return 'institutional';
        }

        if ($request->routeIs('home')) {
            return 'home';
        }

        if ($request->routeIs('checkout.*')) {
            return 'checkout';
        }

        if ($request->routeIs('cart.*')) {
            return 'cart';
        }

        if ($request->routeIs('user.*', 'dashboard')) {
            return 'account';
        }

        if ($request->routeIs('produto.*', 'product.*', 'products.show')) {
            return 'product';
        }

        if ($request->routeIs('blog.*', 'blogs.*')) {
            return 'blog';
        }

        if ($request->routeIs(
            'categories.*',
            'category.*',
            'brands.*',
            'brand.*',
            'search',
            'search.*',
            'all-categories.*',
            'subcategories.*',
            'categorias-filhas.*',
            'products.byCategory',
            'products.bySubcategory',
            'products.byCategoriaFilha',
            'produtos.index'
        )) {
            return 'catalog';
        }

        return 'other';
    }
}
