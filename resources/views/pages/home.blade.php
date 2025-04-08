@extends('layout.layout')

@section('content')
<div class="container">
    <h2>Bem-vindo à Home</h2>
    <p>Esta é a página inicial da aplicação.</p>
    <a href="{{ route('pages.contato') }}">contato</a>
    <a href="{{ route('pages.sobre') }}">Sobre Nós</a>

</div>
@endsection
