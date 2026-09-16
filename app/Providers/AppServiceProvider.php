<?php

namespace App\Providers;

use App\Models\Attribute;
use App\Models\Brand;
use App\Models\CategoriasFilhas;
use App\Models\Category;
use App\Models\Language;
use App\Models\Product;
use App\Models\Subcategory;
use App\Services\CatalogIntegrationAvailabilityService;
use App\Services\IntegrationMonitorService;
use App\Services\OpticalNavigationService;
use App\Services\StoreControlService;
use App\Services\StorefrontLayoutService;
use App\Services\ThemeSettingsService;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(CatalogIntegrationAvailabilityService::class);
        $this->app->singleton(StoreControlService::class);
        $this->app->singleton(ThemeSettingsService::class);
        $this->app->singleton(StorefrontLayoutService::class);
        $this->app->singleton(OpticalNavigationService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 1. Tradução Dinâmica via Banco de Dados
        $languagesTableExists = Cache::remember(
            'schema.languages_table_exists',
            now()->addHours(24),
            fn () => Schema::hasTable('languages')
        );

        if ($languagesTableExists) {
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
        $attributesTableExists = Cache::remember(
            'schema.attributes_table_exists',
            now()->addHours(24),
            fn () => Schema::hasTable('attributes')
        );

        $attributes = $attributesTableExists
            ? Cache::remember('global_attributes_model', now()->addHours(24), fn () => Attribute::first())
            : null;

        View::share('attributes', $attributes);

        // 3. Carrega helper de moeda
        if (file_exists(app_path('Helpers/CurrencyHelper.php'))) {
            require_once app_path('Helpers/CurrencyHelper.php');
        }

        // 3b. Carrega helper de locale (normaliza pt_BR -> pt-br para page_translations)
        if (file_exists(app_path('Helpers/LocaleHelper.php'))) {
            require_once app_path('Helpers/LocaleHelper.php');
        }

        // 3c. Carrega helper de rotação de imagens (sliders/galeria institucional)
        if (file_exists(app_path('Helpers/ImageRotationHelper.php'))) {
            require_once app_path('Helpers/ImageRotationHelper.php');
        }

        // 4. Força HTTPS em produção
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // 5. Paginação com Bootstrap
        Paginator::useBootstrap();

        foreach ([Category::class, Subcategory::class, CategoriasFilhas::class] as $taxonomyModel) {
            $taxonomyModel::saved(fn () => app(OpticalNavigationService::class)->clear());
            $taxonomyModel::deleted(fn () => app(OpticalNavigationService::class)->clear());
        }
        Product::saved(fn () => app(OpticalNavigationService::class)->clear());
        Product::deleted(fn () => app(OpticalNavigationService::class)->clear());

        /**
         * 6. NAVEGAÇÃO COMPARTILHADA (Header e Footer)
         * Aqui carregamos a árvore completa e a lista principal uma única vez.
         */
        $headerViewData = null;
        View::composer(['layout.layout', 'layout.header', 'components.header', 'components.footer'], function ($view) use (&$headerViewData) {
            if ($headerViewData === null) {
                $headerViewData = [
                    'headerCategories' => Cache::remember('header_categories_tree', now()->addHours(24), function () {
                        return Category::where('status', 1)
                            ->with([
                                'subcategories' => function ($q) {
                                    $q->orderBy('name')->with([
                                        'categoriasfilhas' => fn ($sq) => $sq->orderBy('name'),
                                    ]);
                                },
                            ])
                            ->orderBy('name')
                            ->get();
                    }),
                    'mainCategories' => Cache::remember('header_main_categories', now()->addHours(24), function () {
                        return Category::query()
                            ->where('status', 1)
                            ->whereIn('slug', ['feminino', 'masculino', 'infantil', 'optico', 'casa'])
                            ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
                            ->get(['id', 'name', 'slug']);
                    }),
                ];
            }

            $view->with($headerViewData);
        });

        View::composer('admin.notifications-menu', function ($view) {
            $admin = auth()->user();

            if (! $admin || ! $admin->isAdmin()) {
                $view->with([
                    'adminNotifications' => collect(),
                    'adminUnreadNotificationsCount' => 0,
                ]);

                return;
            }

            // Segurança adicional: o cron continua sendo o mecanismo principal,
            // mas o painel detecta indisponibilidade mesmo se o scheduler do
            // ambiente ainda não estiver configurado.
            if (Cache::add('integration_monitor_admin_fallback_check', true, now()->addMinutes(5))) {
                // A verificação pode consultar serviços e tabelas de integração. Rode
                // após enviar a resposta para não bloquear a navegação do painel.
                app()->terminating(function () {
                    try {
                        app(IntegrationMonitorService::class)->checkForStaleIntegrations();
                    } catch (\Throwable $exception) {
                        report($exception);
                    }
                });
            }

            $view->with([
                'adminNotifications' => $admin->adminNotifications()
                    ->latest()
                    ->get(),
                'adminUnreadNotificationsCount' => $admin->adminNotifications()
                    ->whereNull('read_at')
                    ->whereNull('archived_at')
                    ->count(),
            ]);
        });

        View::composer('users.notifications-menu', function ($view) {
            $customer = auth()->user();

            if (! $customer || $customer->isAdmin()) {
                $view->with([
                    'customerNotifications' => collect(),
                    'customerOperationalAlerts' => collect(),
                    'customerPersistedUnreadNotificationsCount' => 0,
                    'customerUnreadNotificationsCount' => 0,
                ]);

                return;
            }

            $operationalAlerts = collect();
            $catalogStatus = app(CatalogIntegrationAvailabilityService::class)->status();

            if (! ($catalogStatus['available'] ?? true)) {
                $operationalAlerts->push([
                    'category' => 'updates',
                    'icon' => 'fa-arrows-rotate',
                    'title' => __('messages.catalog_purchase_paused_title'),
                    'message' => __('messages.checkout_pause_message'),
                ]);
            }

            $persistedUnreadCount = $customer->adminNotifications()->whereNull('read_at')->count();

            $view->with([
                'customerNotifications' => $customer->adminNotifications()->latest()->limit(30)->get(),
                'customerOperationalAlerts' => $operationalAlerts,
                'customerPersistedUnreadNotificationsCount' => $persistedUnreadCount,
                'customerUnreadNotificationsCount' => $persistedUnreadCount + $operationalAlerts->count(),
            ]);
        });

        $globalViewData = null;
        View::composer('*', function ($view) use (&$globalViewData, $attributesTableExists) {
            if ($globalViewData === null) {
                $attribute = $attributesTableExists
                    ? Cache::remember('global_attributes_db', now()->addHours(24), function () {
                        return DB::table('attributes')->where('id', 1)->first();
                    })
                    : null;

                $globalViewData = [
                    'catalogIntegrationStatus' => app(CatalogIntegrationAvailabilityService::class)->status(),
                    'storeControls' => app(StoreControlService::class)->settings(),
                    'storefrontLayout' => app(StorefrontLayoutService::class)->current(),
                    'locale' => App::getLocale(),
                    'webpImage' => $attribute?->header_image ?? null,
                    'banner1' => $attribute?->banner1 ?? null,
                    'logo_palace' => $attribute?->logo_palace ?? null,
                    'logo_bridal' => $attribute?->logo_bridal ?? null,
                    'logo_cafe_bistro' => $attribute?->logo_cafe_bistro ?? null,
                    'logo_cafe_bistro_asuncion' => $attribute?->logo_cafe_bistro_asuncion ?? null,
                    'banner2' => $attribute?->banner2 ?? null,
                    'banner3' => $attribute?->banner3 ?? null,
                    'banner4' => $attribute?->banner4 ?? null,
                    'banner5' => $attribute?->banner5 ?? null,
                    'banner6' => $attribute?->banner6 ?? null,
                    'banner7' => $attribute?->banner7 ?? null,
                    'banner8' => $attribute?->banner8 ?? null,
                    'banner9' => $attribute?->banner9 ?? null,
                    'banner1_link' => $attribute?->banner1_link ?? null,
                    'banner2_link' => $attribute?->banner2_link ?? null,
                    'banner3_link' => $attribute?->banner3_link ?? null,
                    'banner4_link' => $attribute?->banner4_link ?? null,
                    'banner5_link' => $attribute?->banner5_link ?? null,
                    'banner6_link' => $attribute?->banner6_link ?? null,
                    'banner7_link' => $attribute?->banner7_link ?? null,
                    'banner8_link' => $attribute?->banner8_link ?? null,
                    'banner9_link' => $attribute?->banner9_link ?? null,
                    'whatsapp_banner' => $attribute?->whatsapp_banner ?? null,
                ];
            }

            $view->with($globalViewData);
        });

        View::composer(['site.products.index', 'site.categories.show', 'components.sidebar-filters'], function ($view) {
            $sidebarFilters = Cache::remember('sidebar_filters_data', now()->addHours(12), function () {
                return [
                    'categories' => Category::where('status', 1)
                        ->withCount('products')
                        ->with([
                            'subcategories' => function ($q) {
                                $q->withCount('products')
                                    ->with([
                                        'categoriasfilhas' => function ($sq) {
                                            $sq->withCount('products');
                                        },
                                    ]);
                            },
                        ])
                        ->orderBy('name')
                        ->get(),

                    'brands' => Brand::where('status', 1)->withCount('products')->orderBy('name')->get(),
                ];
            });

            $view->with('sidebarFilters', $sidebarFilters);
        });
    }
}
