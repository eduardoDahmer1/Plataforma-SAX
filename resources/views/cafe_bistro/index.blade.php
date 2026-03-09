@extends('layout.cafe_bistro')

@section('title', 'SAX Café & Bistrô')

@section('content')
    @include('cafe_bistro.componentes.hero')
    @include('cafe_bistro.componentes.sobre')
    @include('cafe_bistro.componentes.carta')
    @include('cafe_bistro.componentes.eventos')
    @include('cafe_bistro.componentes.horarios')
    @include('cafe_bistro.componentes.cta-contato')
@endsection
