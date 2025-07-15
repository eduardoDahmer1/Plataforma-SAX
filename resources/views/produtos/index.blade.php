@extends('layouts.app') <!-- Ou outro layout que você estiver utilizando -->

@section('content')
    <div class="container">
        <h1>Lista de Produtos</h1>

        <!-- Filtro de pesquisa -->
        <form action="{{ route('products.index') }}" method="GET">
            <input type="text" name="search" placeholder="Buscar por nome, SKU ou slug" value="{{ request()->search }}">
            <button type="submit">Buscar</button>
        </form>

        <!-- Exibe a lista de produtos -->
        <div class="product-list">
            @foreach($products as $product)
                <div class="product-card">
                    <h3><a href="{{ route('product.show', $product->id) }}">{{ $product->external_name }}</a></h3>
                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    <p><strong>Preço:</strong> {{ $product->price }}</p>
                    <p><strong>Status:</strong> {{ $product->status }}</p>
                    <p><strong>Categoria:</strong> {{ $product->category_id }}</p>
                </div>
            @endforeach
        </div>

        <!-- Paginação -->
        {{ $products->links() }}
    </div>
@endsection
