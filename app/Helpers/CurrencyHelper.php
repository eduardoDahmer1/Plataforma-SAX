<?php

use App\Models\Currency;

if (!function_exists('currency_format')) {
    function currency_format($price)
    {
        // Garante que price seja float
        if (is_object($price)) {
            $price = (float) ($price->value ?? $price->price ?? 0);
        } elseif (is_array($price)) {
            $price = (float) ($price['value'] ?? $price['price'] ?? $price[0] ?? 0);
        } else {
            $price = (float) $price;
        }

        // Pega a moeda da sessão ou a moeda padrão do sistema (is_default = USD)
        $currency = null;
        $currencySession = session('currency');
        if ($currencySession) {
            $currencyId = is_object($currencySession) ? ($currencySession->id ?? null)
                        : (is_array($currencySession) ? ($currencySession['id'] ?? $currencySession[0] ?? null)
                        : $currencySession);
            $currency = Currency::find($currencyId);
        }

        if (!$currency) {
            $currency = Currency::where('is_default', 1)->first()
                ?? Currency::first();
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

if (!function_exists('currency')) {
    function currency($price)
    {
        return currency_format($price);
    }
}
  // Função para converter um valor base para todas as moedas disponíveis, ordenando pela moeda padrão primeiro
if (!function_exists('order_all_currencies')) {
    function order_all_currencies($valorBase)
    {
        $valorBase = (float) $valorBase;

        return Currency::orderByDesc('is_default')->get()->map(function ($currency) use ($valorBase) {
            $converted = $valorBase * ($currency->value ?? 1);

            return $currency->sign . ' ' . number_format(
                $converted,
                $currency->decimal_digits ?? 2,
                $currency->decimal_separator ?? ',',
                $currency->thousands_separator ?? '.'
            );
        })->all();
    }
}
