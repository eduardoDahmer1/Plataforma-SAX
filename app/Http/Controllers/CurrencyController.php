<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Cache;

class CurrencyController extends Controller
{
    public function change(Request $request)
    {
        // Validação básica
        $request->validate([
            'currency_id' => 'required|exists:currencies,id'
        ]);

        $currency = Currency::find($request->currency_id);

        if ($currency) {
            // 1. Salva a moeda na sessão
            session(['currency' => $currency->id]);

            // 2. Mapeia a moeda para o idioma
            $locale = match (strtoupper($currency->name)) {
                'BRL'   => 'pt_BR',
                'PYG'   => 'es',
                'USD'   => 'en',
                default => 'pt_BR',
            };

            // 3. Salva o novo idioma na sessão
            session(['locale' => $locale]);
            
            // 4. Força a alteração no ambiente atual
            App::setLocale($locale);

            /**
             * 5. LIMPEZA DE CACHE
             * Como o AppServiceProvider armazena as traduções no cache 'all_translations_db',
             * precisamos removê-lo para que o provedor recarregue os dados do banco 
             * com o novo locale na próxima requisição.
             */
            Cache::forget('all_translations_db');
            
            // 6. Garante a gravação da sessão
            session()->save();
        }

        return back()->with('success', 'Idioma e moeda alterados com sucesso!');
    }
}