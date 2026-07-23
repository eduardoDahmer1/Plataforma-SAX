<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Throwable;

class MarketingSetting extends Model
{
    public const CACHE_KEY = 'marketing.settings';

    protected $guarded = [];

    protected $casts = [
        'enabled' => 'boolean',
    ];

    public static function current(): self
    {
        try {
            return Cache::remember(self::CACHE_KEY, now()->addHour(), function () {
                if (!Schema::hasTable('marketing_settings')) {
                    return new self(['enabled' => false]);
                }

                return self::query()->first() ?? new self([
                    'enabled' => true,
                    'site_name' => 'SAX Department',
                    'robots' => 'index,follow',
                ]);
            });
        } catch (Throwable) {
            return new self(['enabled' => false]);
        }
    }

    public static function clearCache(): void
    {
        Cache::forget(self::CACHE_KEY);
    }
}
