<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\URL;

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
        // Força HTTPS em produção
        if (config('app.env') === 'production') {
            URL::forceScheme('https');
        }

        // Usa o estilo Bootstrap para a paginação
        Paginator::useBootstrap();

        // View Composer para passar a imagem header para todas as views
        View::composer('*', function ($view) {
            $webpImage = DB::table('attributes')->where('id', 1)->value('header_image');
            $view->with('webpImage', $webpImage);
        });
    }
}
