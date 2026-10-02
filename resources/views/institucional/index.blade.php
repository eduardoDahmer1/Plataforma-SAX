@extends('layout.institucional')

@section('content')
@php
    $dbLocale = translation_locale();
    $translation = $institucional->translations->firstWhere('locale', $dbLocale)
        ?: $institucional->translations->firstWhere('locale', 'pt-br');
    $copy = fn (string $field, string $fallback) => filled(data_get($translation, $field)) ? data_get($translation, $field) : $fallback;
    $sceneryUrls = collect($sceneryPool ?? [])->map(fn ($path) => asset('storage/' . $path))->values();
@endphp

@include('institucional.componentes.hero')
@include('institucional.componentes.sobre')
@include('institucional.componentes.features')
@include('institucional.componentes.experiences')
@include('institucional.componentes.banner')
@include('institucional.componentes.stats')
@include('institucional.componentes.brands-gallery')
@include('institucional.componentes.history')
@include('institucional.componentes.video')
@include('institucional.componentes.cta')
@endsection
