@extends('layout.layout')

@section('content')
<div class="container">
    @if (auth()->check()) <!-- Verifica se o usuário está logado -->
        <a href="{{ route('pages.home') }}" class="btn btn-link">Home</a>

        <h2>Bem-vindo à Página Inicial</h2>
        <p>Esta é a página de uploads. Aqui você pode ver os arquivos que foram carregados.</p>

        @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        <!-- Exibir botão para todos os arquivos -->
        <a href="{{ route('uploads.index') }}" class="btn btn-primary mb-3">Ver Todos os Arquivos</a>

        <h4 class="mt-4">Arquivos Recentes:</h4>
        <ul>
            @foreach($uploads->take(5) as $upload) <!-- Exibe somente os 5 uploads mais recentes -->
            <li>
                <strong>{{ $upload->title ?? 'Sem título' }}</strong> <br>
                <p>{{ $upload->description ?? 'Sem descrição' }}</p>
                <a href="{{ route('uploads.show', $upload->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a> <!-- Botão para visualizar o upload -->
            </li>
            @endforeach
        </ul>
    @else <!-- Caso o usuário não esteja logado -->
        <div class="alert alert-warning">
            Você precisa estar logado para acessar esta página. <a href="{{ route('login') }}">Clique aqui para fazer login</a>.
        </div>
    @endif
</div>
@endsection
