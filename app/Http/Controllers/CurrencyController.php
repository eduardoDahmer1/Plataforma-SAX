<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Session;

class CurrencyController extends Controller
{
    public function change(Request $request)
    {
        $currency = Currency::find($request->currency_id);

        if ($currency) {
            // Salva a moeda na sessão
            session(['currency' => $currency->id]);

            // Mapeia a moeda para o idioma correspondente
            // Ajuste as siglas (BRL, PYG) de acordo com o que está no seu banco de dados
            $locale = match ($currency->name) {
                'BRL' => 'pt_BR',
                'PYG' => 'es',
                default => 'en',
            };

            // Salva o novo idioma na sessão para o Middleware SetLocale ler
            session(['locale' => $locale]);
        }

        return back();
    }
}