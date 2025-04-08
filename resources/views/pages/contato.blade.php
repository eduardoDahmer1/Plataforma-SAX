@extends('layout.layout')

@section('content')
<div class="container">
    <h2>Bem-vindo à pagina de contato</h2>
    <p>Esta é a página inicial da aplicação.</p>
    <a href="{{ route('pages.sobre') }}">Sobre Nós</a>
    <a href="{{ route('pages.home') }}">Home</a>

</div>
@endsection
