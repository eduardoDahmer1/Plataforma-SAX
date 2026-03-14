@extends('layout.institucional')

@section('content')
    @include('institucional.componentes.hero')
    @include('institucional.componentes.sobre')
    @include('institucional.componentes.features')
    @include('institucional.componentes.stats')
    @include('institucional.componentes.brands-gallery')
    @include('institucional.componentes.cta')
    @include('institucional.componentes.video')
@endsection