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
    /** Idiomas suportados pelo site. */
    public const LOCALES = ['pt_BR', 'en', 'es'];

    /** Idioma exibido para quem chega no site sem escolha na sessão. */
    public const DEFAULT_LOCALE = 'pt_BR';

    public function handle(Request $request, Closure $next)
    {
        try {
            // Moeda e idioma são independentes: a moeda padrão é a marcada como
            // is_default (USD) e o idioma padrão é pt_BR, sem um derivar o outro.
            if (!Session::has('currency')) {
                $defaultCurrency = Currency::where('is_default', 1)->first()
                    ?? Currency::first();

                if ($defaultCurrency) {
                    Session::put('currency', $defaultCurrency->id);
                    Session::put('currency_value', $defaultCurrency->value);
                    Session::put('currency_sign', $defaultCurrency->sign);
                }
            }

            $locale = Session::get('locale');

            if (!in_array($locale, self::LOCALES, true)) {
                $locale = self::DEFAULT_LOCALE;
                Session::put('locale', $locale);
            }

            App::setLocale($locale);
        } catch (\Throwable $e) {
            Log::warning('Falha ao definir locale, usando fallback pt_BR.', [
                'message' => $e->getMessage(),
            ]);

            App::setLocale(self::DEFAULT_LOCALE);
        }

        return $next($request);
    }
}
