@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h2 class="mb-4">Bem-vindo à Página Inicial</h2>
    <p>Veja abaixo os produtos recentes.</p>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Formulário de busca -->
    <form action="{{ url('/') }}" method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Buscar por nome ou SKU"
                value="{{ request('search') }}">
            <button class="btn btn-primary" type="submit">Buscar</button>
        </div>
    </form>

    <h4 class="mt-4 mb-3">Produtos Recentes:</h4>

    <div class="row">
        @foreach($items as $item)
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">

                {{-- Imagem --}}
                <img src="{{ $item->photo_url }}" class="card-img-top img-fluid" alt="{{ $item->external_name }}"
                     style="max-height: 200px; object-fit: cover;">

                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">
                        <a href="{{ route('produto.show', $item->id) }}">
                            {{ $item->external_name ?? 'Sem nome' }}
                        </a>
                    </h5>

                    <p class="card-text mb-2">
                        <strong>SKU:</strong> {{ $item->sku ?? 'Sem SKU' }}<br>
                        <strong>Preço:</strong> R$ {{ isset($item->price) ? number_format($item->price, 2, ',', '.') : 'Não informado' }}<br>
                        <small>ID: {{ $item->id }}</small>
                    </p>

                    <div class="d-flex flex-column">
                        <a href="{{ route('produto.show', $item->id) }}" class="btn btn-sm btn-info mb-2">Ver Detalhes</a>

                        @auth
                            @if(in_array(auth()->user()->user_type, [0,1,2]))
                                <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-sm btn-success me-2">+ 🛒 Adicionar</button>
                                </form>

                                <form action="{{ route('checkout.index') }}" method="GET" class="d-flex">
                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-sm btn-primary">Comprar Agora 🛒</button>
                                </form>
                            @endif
                        @else
                            <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal" data-bs-target="#loginModal">Login para Comprar</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Paginação -->
    <div class="d-flex justify-content-center mt-4">
        {{ $items->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection
