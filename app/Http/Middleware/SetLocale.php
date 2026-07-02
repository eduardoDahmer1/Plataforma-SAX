<?php

namespace App\Http\Middleware;

use App\Models\Currency;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;

class SetLocale
{
    public function handle(Request $request, Closure $next)
    {
        try {
            $defaultCurrency = Currency::where('name', 'BRL')->first()
                ?? Currency::where('is_default', 1)->first();

            if (!Session::has('currency') && $defaultCurrency) {
                Session::put('currency', $defaultCurrency->id);
                Session::put('currency_value', $defaultCurrency->value);
                Session::put('currency_sign', $defaultCurrency->sign);
            }

            $locale = Session::get('locale');

            if (!$locale) {
                $currencyId = Session::get('currency');
                $currency = $currencyId ? Currency::find($currencyId) : $defaultCurrency;

                $locale = match (strtoupper($currency?->name ?? '')) {
                    'BRL' => 'pt_BR',
                    'PYG' => 'es',
                    'USD' => 'en',
                    default => 'pt_BR',
                };

                Session::put('locale', $locale);
            }

            App::setLocale($locale ?? 'pt_BR');
        } catch (\Throwable $e) {
            Log::warning('Falha ao definir locale, usando fallback pt_BR.', [
                'message' => $e->getMessage(),
            ]);

            App::setLocale('pt_BR');
        }

        return $next($request);
    }
}