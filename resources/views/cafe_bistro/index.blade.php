@extends('layout.cafe_bistro')

@php
    // Traducción del idioma actual; fallback a la tabla principal si no existe
    $t = $cafeBistro->translations->firstWhere('locale', translation_locale());
@endphp

@section('title', ($t?->cafe_meta_title ?? $cafeBistro->meta_title) ?? 'SAX Café & Bistrô')

@section('content')
    @include('cafe_bistro.componentes.hero')
    @include('cafe_bistro.componentes.sobre')
    @include('cafe_bistro.componentes.carta')
    @include('cafe_bistro.componentes.eventos')
    @include('cafe_bistro.componentes.horarios')
@endsection
