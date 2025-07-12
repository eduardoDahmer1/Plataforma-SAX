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
    <a href="{{ route('admin.users.index') }}" class="btn btn-primary mb-3">Usuários</a>
    <a href="{{ route('uploads.index') }}" class="btn btn-primary mb-3">Adicionar novos arquivos</a>
    @endif

    <!-- Formulário de busca -->
    <form action="{{ url('/') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por título ou descrição"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    <h4 class="mt-4">Arquivos e Produtos Recentes:</h4>

        <div class="row">
        @foreach($items as $item)
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100">
                    <div class="card-body">
                        @if($item->type === 'upload')
                            <h5 class="card-title">{{ $item->title ?? 'Sem título' }}</h5>
                            <p class="card-text">{{ $item->description ?? 'Sem descrição' }}</p>
                            <a href="{{ route('uploads.show', $item->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a>
                        @elseif($item->type === 'product')
                            <h5 class="card-title">
                                <a href="{{ route('product.show', $item->id) }}">
                                    {{ $item->title ?? 'Sem nome' }}
                                </a>
                            </h5>
                            <p class="card-text">
                                <strong>SKU:</strong> {{ $item->description ?? 'Sem SKU' }}<br>
                                <strong>Preço:</strong> R$ {{ number_format($item->price, 2, ',', '.') }}<br>
                                <small>ID: {{ $item->id }}</small>
                            </p>
                            <a href="{{ route('product.show', $item->id) }}" class="btn btn-sm btn-info">Ver Detalhes</a>
                        @endif
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <!-- Link de paginação -->
    <div class="d-flex justify-content-center mt-4">
        {{ $items->links('pagination::bootstrap-4') }}
    </div>

    @else
    <div class="alert alert-warning">
        Você precisa estar logado para acessar esta página.
    </div>
    @endif
</div>
@endsection
