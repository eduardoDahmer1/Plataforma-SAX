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
use App\Models\Attribute;
use App\Models\Language;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Subcategory;
use App\Models\CategoriasFilhas;

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
            $allTranslations = Cache::remember('all_translations_db', now()->addHours(24), function () {
                return Language::all();
            });

            foreach ($allTranslations as $translation) {
                app('translator')->addLines(["messages.{$translation->key}" => $translation->pt], 'pt_BR');
                app('translator')->addLines(["messages.{$translation->key}" => $translation->es], 'es');
                app('translator')->addLines(["messages.{$translation->key}" => $translation->en], 'en');
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
         * 6. MEGA MENU COMPOSER (Header)
         * Aqui carregamos a árvore completa para o menu superior
         */
        View::composer(['layout.layout', 'layout.header', 'components.header'], function ($view) {
            $headerCategories = Cache::remember('header_categories_tree', now()->addHours(24), function () {
                return Category::where('status', 1)
                    ->with([
                        'subcategories' => function ($q) {
                            // Removido o where status aqui
                            $q->orderBy('name')->with([
                                'categoriasfilhas' => function ($sq) {
                                    // Verifica se na tabela childcategories existe a coluna status,
                                    // se não existir, remova daqui também.
                                    $sq->orderBy('name');
                                },
                            ]);
                        },
                    ])
                    ->orderBy('name')
                    ->get();
            });

            $view->with('headerCategories', $headerCategories);
        });

        /**
         * 7. ATRIBUTOS E BANNERS GLOBAIS
         */
        View::composer('*', function ($view) {
            $attribute = Cache::remember('global_attributes_db', now()->addHours(24), function () {
                return DB::table('attributes')->where('id', 1)->first();
            });

            $view->with([
                'locale' => App::getLocale(),
                'webpImage' => $attribute?->header_image ?? null,
                'banner1' => $attribute?->banner1 ?? null,
                'logo_palace' => $attribute?->logo_palace ?? null,
                'logo_bridal' => $attribute?->logo_bridal ?? null,
                'logo_cafe_bistro' => $attribute?->logo_cafe_bistro ?? null,
                'banner_horizontal' => $attribute?->banner_horizontal ?? null,
                'banner2' => $attribute?->banner2 ?? null,
                'banner3' => $attribute?->banner3 ?? null,
                'banner4' => $attribute?->banner4 ?? null,
                'banner5' => $attribute?->banner5 ?? null,
                'banner6' => $attribute?->banner6 ?? null,
                'banner7' => $attribute?->banner7 ?? null,
                'banner8' => $attribute?->banner8 ?? null,
                'banner9' => $attribute?->banner9 ?? null,
                'banner10' => $attribute?->banner10 ?? null,
            ]);
        });

        /**
         * 8. FILTRO LATERAL GERAL (Sidebar)
         * Centraliza Marcas e a Hierarquia de Categorias (3 níveis)
         */
        View::composer(['site.products.index', 'site.categories.show', 'components.sidebar-filters'], function ($view) {
            $sidebarFilters = Cache::remember('sidebar_filters_data', now()->addHours(12), function () {
                return [
                    // Hierarquia: Categoria -> Subcategoria -> Categoria Filha
                    'categories' => Category::where('status', 1) // Mantém aqui se a Category principal tiver status
                        ->withCount('products')
                        ->with([
                            'subcategories' => function ($q) {
                                $q->withCount('products') // Removi o where status
                                    ->with([
                                        'categoriasfilhas' => function ($sq) {
                                            $sq->withCount('products'); // Removi o where status
                                        },
                                    ]);
                            },
                        ])
                        ->orderBy('name')
                        ->get(),

                    // Marcas
                    'brands' => Brand::where('status', 1)->withCount('products')->orderBy('name')->get(),
                ];
            });

            $view->with('sidebarFilters', $sidebarFilters);
        });
    }
}
