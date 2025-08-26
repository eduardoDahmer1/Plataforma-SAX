<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

class AppServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        Paginator::useBootstrap();

        // View composer seguro
        View::composer('*', function ($view) {
            $attribute = DB::table('attributes')->where('id', 1)->first();

            $view->with([
                'webpImage' => $attribute?->header_image ?? null,
                'banner1'   => $attribute?->banner1 ?? null,
                'banner2'   => $attribute?->banner2 ?? null,
                'banner3'   => $attribute?->banner3 ?? null,
                'banner4'   => $attribute?->banner4 ?? null,
                'banner5'   => $attribute?->banner5 ?? null,
            ]);
        });
    }
}
