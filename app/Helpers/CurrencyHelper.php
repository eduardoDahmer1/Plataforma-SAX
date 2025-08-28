<?php

use App\Models\Currency;

if (!function_exists('currency_format')) {
    function currency_format($price)
    {
        // Garante que price seja float
        if (is_object($price)) {
            // Se for stdClass, tenta pegar 'value' ou 'price'
            $price = (float) ($price->value ?? $price->price ?? 0);
        } elseif (is_array($price)) {
            // Se for array, pega o primeiro índice ou 'value'
            $price = (float) ($price['value'] ?? $price['price'] ?? $price[0] ?? 0);
        } else {
            $price = (float) $price;
        }

        // Pega a moeda da sessão ou a padrão
        $currency = null;
        $currencySession = session('currency');
        if ($currencySession) {
            $currencyId = is_object($currencySession) ? ($currencySession->id ?? null)
                        : (is_array($currencySession) ? ($currencySession['id'] ?? $currencySession[0] ?? null)
                        : $currencySession);
            $currency = Currency::find($currencyId);
        }

        if (!$currency) {
            $currency = Currency::where('is_default', 1)->first();
        }

        // Se não houver moeda, retorna preço simples
        if (!$currency) {
            return number_format($price, 2, ',', '.');
        }

        $converted = $price * ($currency->value ?? 1);
        $decimals = $currency->decimal_digits ?? 2;
        $decPoint = $currency->decimal_separator ?? ',';
        $thousandsSep = $currency->thousands_separator ?? '.';
        $sign = $currency->sign ?? '';

        return trim($sign . ' ' . number_format($converted, $decimals, $decPoint, $thousandsSep));
    }
}
