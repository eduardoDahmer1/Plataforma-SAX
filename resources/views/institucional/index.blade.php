@extends('layout.institucional')

@section('content')
@php
    // 1. Resgata o idioma atual do front-end de forma centralizada
    $locale = app()->getLocale();
    $dbLocale = $locale === 'pt' ? 'pt-br' : $locale;
    
    // 2. Filtra a tradução do idioma ativo uma única vez
    $translation = $institucional->translations->where('locale', $dbLocale)->first();

    // 3. Monta o objeto de fallbacks globais para a página se preferir, 
    // ou passa a própria model e a tradução para os sub-componentes resolverem.
@endphp

    {{-- Passamos o $translation e o $locale explicitamente para cada bloco --}}
    @include('institucional.componentes.hero', ['institucional' => $institucional, 'translation' => $translation, 'locale' => $locale])
    
    @include('institucional.componentes.sobre', ['institucional' => $institucional, 'translation' => $translation])
    
    @include('institucional.componentes.features', ['institucional' => $institucional, 'translation' => $translation])
    
    @include('institucional.componentes.stats', ['institucional' => $institucional, 'translation' => $translation])
    
    @include('institucional.componentes.brands-gallery', ['institucional' => $institucional])
    
    @include('institucional.componentes.cta', ['institucional' => $institucional, 'translation' => $translation])
    
    @include('institucional.componentes.video', ['institucional' => $institucional, 'translation' => $translation])
@endsection