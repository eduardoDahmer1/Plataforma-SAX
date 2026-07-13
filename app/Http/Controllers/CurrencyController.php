<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function change(Request $request)
    {
        $request->validate([
            'currency_id' => 'required|exists:currencies,id'
        ]);

        $currency = Currency::find($request->currency_id);

        if ($currency) {
            // Troca apenas a cotação. O idioma é independente e continua
            // sendo controlado pela rota lang.switch.
            session([
                'currency'       => $currency->id,
                'currency_value' => $currency->value,
                'currency_sign'  => $currency->sign,
            ]);

            session()->save();
        }

        return back();
    }
}
