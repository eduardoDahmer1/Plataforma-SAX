<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Currency;

class CurrencyController extends Controller
{
    public function change(Request $request)
    {
        $currency = Currency::find($request->currency_id);

        if ($currency) {
            session(['currency' => $currency->id]);
        }

        return back(); // volta pra página que estava
    }
}
