<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Str;

class ProductFeedRefreshService
{
    public const DIRTY_CACHE_KEY = 'product_feed_refresh_pending_v1';

    private const LOCK_CACHE_KEY = 'product_feed_refresh_lock_v1';

    public function __construct(private readonly ProductFeedService $feed)
    {
    }

    /** Mark the public feed as outdated without rebuilding it inside the save request. */
    public function markDirty(): void
    {
        Cache::put(self::DIRTY_CACHE_KEY, (string) Str::uuid(), now()->addDays(2));

        // A geração pode percorrer todo o catálogo. O scheduler consolida as
        // alterações e a executa fora dos processos que atendem o site.
    }

    /** Rebuild once even when many products were changed in the same interval. */
    public function refreshPending(bool $force = false): bool
    {
        if (! $force && ! Cache::has(self::DIRTY_CACHE_KEY)) {
            return false;
        }

        $refreshed = Cache::lock(self::LOCK_CACHE_KEY, 300)->get(function () use ($force): bool {
            $marker = Cache::get(self::DIRTY_CACHE_KEY);
            if (! $force && $marker === null) {
                return false;
            }

            $this->feed->generate();

            // A new change during generation receives a different marker and is
            // intentionally left pending for the next consolidated refresh.
            if ($marker === Cache::get(self::DIRTY_CACHE_KEY)) {
                Cache::forget(self::DIRTY_CACHE_KEY);
            }

            return true;
        });

        return $refreshed === true;
    }

    public function isPending(): bool
    {
        return Cache::has(self::DIRTY_CACHE_KEY);
    }
}
