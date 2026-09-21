<?php

namespace App\Services;

use App\Models\StorefrontFavicon;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Throwable;

class StorefrontFaviconService
{
    private const CACHE_PREFIX = 'storefront.favicon.v1.';

    public function url(?string $layout = null): string
    {
        $layout = $layout ?: app(StorefrontLayoutService::class)->effective();
        $layout = in_array($layout, ['sax', 'vista'], true) ? $layout : 'sax';
        $fallback = asset('images/favicons/'.$layout.'.svg');

        try {
            if (! Schema::hasTable('storefront_favicons')) {
                return $fallback;
            }

            $path = Cache::remember(self::CACHE_PREFIX.$layout, now()->addMinutes(10),
                fn (): ?string => StorefrontFavicon::query()->where('layout', $layout)->value('path'));

            return $path && Storage::disk('public')->exists($path)
                ? Storage::disk('public')->url($path)
                : $fallback;
        } catch (Throwable) {
            return $fallback;
        }
    }

    public function clear(string $layout): void
    {
        Cache::forget(self::CACHE_PREFIX.$layout);
    }
}
