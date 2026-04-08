<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\App;
use App\Models\Category;
use App\Models\Attribute;
use App\Models\Language;

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
        // 1. Tradução Dinâmica via Banco de Dados
        if (Schema::hasTable('languages')) {
            
            // Usamos um cache único para todas as traduções para evitar múltiplas queries
            $allTranslations = Cache::remember('all_translations_db', now()->addHours(24), function () {
                return Language::all();
            });

            foreach ($allTranslations as $translation) {
                // Registra a tradução para cada idioma disponível no banco
                // Isso permite que o helper __('messages.chave') funcione instantaneamente após a troca de locale
                
                // Português
                app('translator')->addLines([
                    "messages.{$translation->key}" => $translation->pt
                ], 'pt_BR');

                // Espanhol
                app('translator')->addLines([
                    "messages.{$translation->key}" => $translation->es
                ], 'es');

                // Inglês
                app('translator')->addLines([
                    "messages.{$translation->key}" => $translation->en
                ], 'en');
            }
        }

        // 2. Compartilha atributos globais
        View::share('attributes', Attribute::first());

        // 3. Carrega helper de moeda
        if (file_exists(app_path('Helpers/CurrencyHelper.php'))) {
            require_once app_path('Helpers/CurrencyHelper.php');
        }

        // 4. Força HTTPS em produção
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 5. Paginação com Bootstrap
        Paginator::useBootstrap();

        /**
         * 6. MEGA MENU COMPOSER
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
         * 7. ATRIBUTOS E BANNERS COMPOSER
         */
        View::composer('*', function ($view) {
            $attribute = Cache::remember('global_attributes_db', now()->addHours(24), function() {
                return DB::table('attributes')->where('id', 1)->first();
            });

            // Injeta também o locale atual para facilitar nas views
            $view->with([
                'locale'            => App::getLocale(),
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