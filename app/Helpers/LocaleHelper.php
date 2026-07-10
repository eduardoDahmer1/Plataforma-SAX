<?php

if (!function_exists('translation_locale')) {
    /**
     * Normaliza el locale de la app al formato usado en la tabla `page_translations`.
     * app()->getLocale() devuelve 'pt_BR', pero las traducciones se guardan como 'pt-br'.
     * Sin esto, el portugués nunca encuentra su traducción.
     */
    function translation_locale($locale = null)
    {
        return strtolower(str_replace('_', '-', $locale ?? app()->getLocale()));
    }
}
