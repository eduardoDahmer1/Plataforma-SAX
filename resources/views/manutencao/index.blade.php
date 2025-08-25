@extends('layout.manutencao')

@section('content')
<div class="d-flex flex-column justify-content-center align-items-center vh-100 text-center">
    <h1 class="display-4 mb-3">🚧 Em Manutenção 🚧</h1>
    <p class="lead mb-4">Estamos trabalhando para melhorar sua experiência. Volte em alguns instantes!</p>
    <a href="{{ url('/') }}" class="btn btn-primary">Tentar novamente</a>
</div>
@endsection
