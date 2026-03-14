<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use App\Models\Category;
use App\Models\Attribute;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Compartilha atributos globais de forma simples
        View::share('attributes', Attribute::first());

        // 2. Carrega helper de moeda
        if (file_exists(app_path('Helpers/CurrencyHelper.php'))) {
            require_once app_path('Helpers/CurrencyHelper.php');
        }
        
        // 3. Força HTTPS em produção
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 4. Paginação com Bootstrap
        Paginator::useBootstrap();

        /**
         * 5. MEGA MENU COMPOSER
         * Carrega a árvore de categorias apenas para as views que precisam (layout e header)
         */
        View::composer(['layout.layout', 'layout.header', 'components.header'], function ($view) {
            $headerCategories = Cache::remember('header_categories_tree', now()->addHours(24), function () {
                return Category::where('status', 1)
                    ->with(['subcategories' => function($q) {
                        $q->orderBy('name');
                    }, 'subcategories.categoriasfilhas' => function($q) {
                        $q->orderBy('name');
                    }])
                    ->orderBy('name')
                    ->get();
            });

            $view->with('headerCategories', $headerCategories);
        });

        /**
         * 6. ATRIBUTOS E BANNERS COMPOSER
         * Mantive sua lógica original, mas otimizada em uma única variável de objeto
         */
        View::composer('*', function ($view) {
            $attribute = Cache::remember('global_attributes_db', now()->addHours(24), function() {
                return DB::table('attributes')->where('id', 1)->first();
            });

            $view->with([
                'webpImage'         => $attribute?->header_image ?? null,
                'banner1'           => $attribute?->banner1 ?? null,
                'logo_palace'       => $attribute?->logo_palace ?? null,
                'logo_bridal'       => $attribute?->logo_bridal ?? null,
                'logo_cafe_bistro'  => $attribute?->logo_cafe_bistro ?? null,
                'banner_horizontal' => $attribute?->banner_horizontal ?? null,
                'banner2'           => $attribute?->banner2 ?? null,
                'banner3'           => $attribute?->banner3 ?? null,
                'banner4'           => $attribute?->banner4 ?? null,
                'banner5'           => $attribute?->banner5 ?? null,
                'banner6'           => $attribute?->banner6 ?? null,
                'banner7'           => $attribute?->banner7 ?? null,
                'banner8'           => $attribute?->banner8 ?? null,
                'banner9'           => $attribute?->banner9 ?? null,
                'banner10'          => $attribute?->banner10 ?? null,
            ]);
        });
    }
}