@extends('layout.dashboard')

@section('content')
<div class="sax-wishlist-wrapper">
    {{-- Cabeçalho --}}
    <div class="dashboard-header mb-5">
        <h2 class="sax-title text-uppercase letter-spacing-2">Mi Lista de Deseos</h2>
        <div class="sax-divider-dark"></div>
    </div>

    @if (session('success'))
        <div class="alert alert-dark border-0 rounded-0 x-small letter-spacing-1 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($favoriteProducts->count() == 0)
        <div class="empty-wishlist text-center py-5">
            <i class="far fa-heart fa-3x mb-3 opacity-25"></i>
            <p class="text-muted text-uppercase letter-spacing-1 small">Tu lista está vacía.</p>
            <a href="{{ route('home') }}" class="btn btn-outline-dark rounded-0 px-4 mt-3 x-small fw-bold">EXPLORAR PRODUCTOS</a>
        </div>
    @else
        <div class="row g-4">
            @foreach ($favoriteProducts as $product)
                <div class="col-6 col-md-4 col-lg-3">
                    <div class="sax-product-card">
                        {{-- Imagem do Produto --}}
                        <div class="product-img-wrapper">
                            <a href="{{ route('products.show', $product->id) }}">
                                <img src="{{ $product->photo ? asset('storage/' . $product->photo) : asset('storage/uploads/noimage.webp') }}"
                                    alt="{{ $product->external_name }}" class="img-fluid">
                            </a>
                            
                            {{-- Botão de Remover (Ícone Flutuante) --}}
                            <form action="{{ route('user.preferences.toggle') }}" method="POST" class="remove-fav-form">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn-remove-wish" title="Remover">
                                    <i class="fas fa-times"></i>
                                </button>
                            </form>
                        </div>

                        {{-- Detalhes --}}
                        <div class="product-info mt-3">
                            <span class="product-sku text-muted">SKU: {{ $product->sku }}</span>
                            <h6 class="product-name">
                                <a href="{{ route('products.show', $product->id) }}">
                                    {{ $product->external_name }}
                                </a>
                            </h6>
                            <div class="product-price fw-bold">
                                {{ currency_format($product->price) }}
                            </div>
                            
                            <a href="{{ route('products.show', $product->id) }}" class="btn btn-dark w-100 rounded-0 mt-3 x-small fw-bold letter-spacing-1 py-2">
                                VER PRODUCTO
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $favoriteProducts->links('pagination::bootstrap-4') }}
        </div>
    @endif
</div>
@endsection
<style>
    /* Estilo Base */
.sax-wishlist-wrapper { font-family: 'Inter', sans-serif; }
.sax-divider-dark { width: 40px; height: 3px; background: #000; margin-top: 10px; }
.x-small { font-size: 0.7rem; }
.letter-spacing-1 { letter-spacing: 1.5px; }
.letter-spacing-2 { letter-spacing: 3px; }

/* Product Card */
.sax-product-card {
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.product-img-wrapper {
    position: relative;
    background: #f9f9f9;
    padding: 20px;
    overflow: hidden;
}

.product-img-wrapper img {
    mix-blend-mode: multiply; /* Ótimo para remover fundos brancos de fotos */
    transition: transform 0.5s ease;
}

.sax-product-card:hover .product-img-wrapper img {
    transform: scale(1.08);
}

/* Botão de Remover Flutuante */
.btn-remove-wish {
    position: absolute;
    top: 10px;
    right: 10px;
    background: #fff;
    border: none;
    width: 30px;
    height: 30px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.8rem;
    box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    transition: all 0.3s;
    opacity: 0;
    transform: translateY(-10px);
}

.sax-product-card:hover .btn-remove-wish {
    opacity: 1;
    transform: translateY(0);
}

.btn-remove-wish:hover {
    background: #000;
    color: #fff;
}

/* Informações do Produto */
.product-sku {
    font-size: 0.6rem;
    letter-spacing: 1px;
    display: block;
    margin-bottom: 5px;
}

.product-name a {
    text-decoration: none;
    color: #333;
    font-size: 0.85rem;
    font-weight: 500;
    text-transform: uppercase;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    height: 40px;
    line-height: 1.2;
}

.product-price {
    font-size: 1rem;
    color: #000;
    margin-top: 10px;
}

/* Paginação Customizada */
.pagination {
    gap: 5px;
}

.pagination .page-link {
    border: none;
    color: #000;
    font-weight: bold;
    font-size: 0.8rem;
}

.pagination .page-item.active .page-link {
    background-color: #000;
    border-color: #000;
}
</style>