<?php

namespace App\Services;

use Illuminate\Support\Facades\Artisan;

class CacheService
{
    public static function clearAll()
    {
        Artisan::call('config:clear');
        Artisan::call('cache:clear');
        Artisan::call('route:clear');
        Artisan::call('optimize:clear');
    }
}