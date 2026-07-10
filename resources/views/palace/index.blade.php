@extends('layout.palace')

@section('content')
@php
    // Define o idioma e busca a tradução correspondente ou usa o padrão
    $currentLang = translation_locale();
    $t = $palace->translations->firstWhere('locale', $currentLang) ?? $palace;
@endphp

{{-- Passamos $palace e $t (tradução ativa) para todos os componentes --}}
@include('palace.components.experiencia', ['palace' => $palace, 't' => $t])
@include('palace.components.sobre', ['palace' => $palace, 't' => $t])
@include('palace.components.sections', ['palace' => $palace, 't' => $t])
@include('palace.components.galeria', ['palace' => $palace, 't' => $t])
@endsection