@props(['variant' => 'desktop'])

{{-- Usado no header do checkout: idioma e moeda separados. --}}
<x-locale-currency-selector :variant="$variant" />
