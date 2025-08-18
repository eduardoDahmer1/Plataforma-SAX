@extends('layout.layout')

@section('content')
<div class="container py-4">

    <h2 class="mb-4">Bem-vindo à Página Inicial</h2>
    <p>Veja abaixo os produtos recentes.</p>

    @if(session('success'))
    <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <!-- Formulário de busca -->
    <form id="filterForm" action="{{ url('/') }}" method="GET" class="mb-4">
        <div class="row g-2">
            <div class="col-md-4">
                <input type="text" name="search" class="form-control" placeholder="Buscar por nome ou SKU"
                    value="{{ request('search') }}">
            </div>
            <div class="col-md-3">
                <select name="brand" class="form-select">
                    <option value="">Marcas</option>
                    @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                        {{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-3">
                <select name="category" class="form-select">
                    <option value="">Categorias</option>
                    @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                        {{ $category->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="subcategory" class="form-select">
                    <option value="">Subcategorias</option>
                    @foreach($subcategories as $subcategory)
                    <option value="{{ $subcategory->id }}"
                        {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>{{ $subcategory->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2">
                <select name="childcategory" class="form-select">
                    <option value="">Categorias filhas</option>
                    @foreach($childcategories as $childcategory)
                    <option value="{{ $childcategory->id }}"
                        {{ request('childcategory') == $childcategory->id ? 'selected' : '' }}>
                        {{ $childcategory->name }}
                    </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-1 d-grid">
                <button type="submit" class="btn btn-primary">Filtrar</button>
            </div>
        </div>
    </form>

    <script>
    document.getElementById('filterForm').addEventListener('submit', function() {
        // Após enviar o formulário, limpa todos os inputs/selects
        setTimeout(() => {
            this.querySelectorAll('input, select').forEach(el => el.value = '';
        }, 10); 
    });
    </script>

    <h4 class="mt-4 mb-3">Produtos</h4>

    <div class="row">
        @foreach($paginated as $item)
        <div class="col-6 col-md-4 col-lg-3 mb-4">
            <div class="card h-100 shadow-sm">

                <img src="{{ $item->photo_url }}" class="card-img-top img-fluid" alt="{{ $item->external_name }}"
                    style="max-height: 200px; object-fit: cover;">

                <div class="card-body d-flex flex-column justify-content-between">
                    <h5 class="card-title">
                        <a href="{{ route('produto.show', $item->id) }}">
                            {{ $item->external_name ?? 'Sem nome' }}
                        </a>
                    </h5>
                    <p class="card-text mb-2">
                        <strong>Marca:</strong> {{ $item->brand->name ?? 'Sem marca' }}<br>
                        <strong>SKU:</strong> {{ $item->sku ?? 'Sem SKU' }}<br>
                        <strong>Preço:</strong> R$
                        {{ isset($item->price) ? number_format($item->price, 2, ',', '.') : 'Não informado' }}<br>
                    </p>

                    <div class="d-flex flex-column">
                        <a href="{{ route('produto.show', $item->id) }}" class="btn btn-sm btn-info mb-2">Ver Detalhes</a>

                        @auth
                        @php
                            $currentQty = $cartItems[$item->id] ?? 0;
                        @endphp

                        @if(in_array(auth()->user()->user_type, [0,1,2]))
                            <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm btn-success me-2"
                                    @if($currentQty >= $item->stock) disabled @endif>
                                    + 🛒 Adicionar
                                </button>
                            </form>

                            <form action="{{ route('checkout.index') }}" method="GET" class="d-flex">
                                <input type="hidden" name="product_id" value="{{ $item->id }}">
                                <button type="submit" class="btn btn-sm btn-primary">Comprar Agora 🛒</button>
                            </form>
                        @endif
                        @else
                            <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                                data-bs-target="#loginModal">Login para Comprar</a>
                        @endauth
                    </div>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $paginated->links('pagination::bootstrap-4') }}
    </div>

</div>
@endsection
