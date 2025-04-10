@extends('layout.layout')

@section('content')
<div class="container">
    @if (auth()->check())

        <h2>Bem-vindo à Página Inicial</h2>
        <p>Esta é a página de uploads. Aqui você pode ver os arquivos que foram carregados.</p>

        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        {{-- Verifica se o usuário é admin master --}}
        @if(auth()->user()->user_type == 1)
            <!-- Exibir botão apenas para admin master -->
            <a href="{{ route('admin.index') }}" class="btn btn-primary mb-3">Admin</a>
            <a href="{{ route('admin.users.index') }}" class="btn btn-primary mb-3">Usuarios</a>
            <a href="{{ route('uploads.index') }}" class="btn btn-primary mb-3">Adicionar novos arquivos</a>
        @endif

        <h4 class="mt-4">Arquivos Recentes:</h4>
        <ul class="list-group">
            @foreach($uploads as $upload)
                <li class="list-group-item mb-3">
                    <strong>{{ $upload->title ?? 'Sem título' }}</strong> <br>
                    <p class="text-home">{{ $upload->description ?? 'Sem descrição' }}</p>
                    <a href="{{ route('uploads.show', $upload->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a>
                </li>
            @endforeach
        </ul>

        <!-- Links de paginação -->
        <div class="d-flex justify-content-center mt-4">
            {{ $uploads->links('pagination::bootstrap-4') }}
        </div>

    @else
        <div class="alert alert-warning">
            Você precisa estar logado para acessar esta página.
        </div>
    @endif
</div>
@endsection
