<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;
use App\Models\Product;
use App\Models\ProductReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Route;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * The path to your application's "home" route.
     *
     * Typically, users are redirected here after authentication.
     *
     * @var string
     */
    public const HOME = '/';

    /**
     * Define your route model bindings, pattern filters, and other route configuration.
     */
    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('product-reviews', function (Request $request) {
            return Limit::perMinute(10)
                ->by('product-reviews:'.($request->user()?->id ?: $request->ip()))
                ->response(function (Request $request, array $headers) {
                    $product = $request->route('product');
                    $review = $request->route('review');

                    if (! $product instanceof Product && $review instanceof ProductReview) {
                        $product = $review->product;
                    }

                    if ($product instanceof Product) {
                        $anchor = Product::withoutGlobalScopes()->find($product->reviewAnchorId()) ?? $product;
                        $target = route('produto.show', $anchor->slug ?: $anchor->id);
                    } else {
                        $target = url()->previous() ?: route('home');
                    }

                    $locale = strtolower(str_replace('_', '-', app()->getLocale()));
                    $message = str_starts_with($locale, 'es')
                        ? 'Espera unos segundos antes de enviar otra valoración.'
                        : (str_starts_with($locale, 'en')
                            ? 'Please wait a few seconds before submitting another review.'
                            : 'Aguarde alguns segundos antes de enviar outra avaliação.');

                    return redirect()->to($target)->withHeaders($headers)->with('warning', $message);
                });
        });

        $this->routes(function () {
            Route::middleware('api')
                ->prefix('api')
                ->group(base_path('routes/api.php'));

            Route::middleware('web')
                ->group(base_path('routes/web.php'));
        });
    }
}
