@props(['variant' => 'desktop'])

{{-- Idioma e moeda agora são escolhas independentes; ambos vivem no mesmo seletor. --}}
<x-locale-currency-selector :variant="$variant" />
