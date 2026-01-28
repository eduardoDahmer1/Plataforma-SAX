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

    @if ($favoriteProducts->isEmpty())
        <div class="empty-wishlist text-center py-5 border">
            <i class="far fa-heart fa-3x mb-3 opacity-25"></i>
            <p class="text-muted text-uppercase letter-spacing-1 small">Tu lista está vacía.</p>
            <a href="{{ route('home') }}" class="btn btn-dark rounded-0 px-5 mt-3 x-small fw-bold letter-spacing-2">
                EXPLORAR PRODUCTOS
            </a>
        </div>
    @else
        {{-- Grid Ajustado --}}
        <div class="row g-2"> {{-- g-2 para manter o visual de grid colado --}}
            @foreach ($favoriteProducts as $product)
                <div class="col-6 col-md-4 col-lg-3 mb-3" id="product-{{ $product->id }}">
                    <div class="card h-100 border-0 rounded-0 jw-product-card">
                        
                        {{-- Área da Imagem --}}
                        <div class="jw-img-container position-relative">
                            <a href="{{ route('products.show', $product->id) }}" class="w-100 h-100">
                                <img src="{{ $product->photo ? asset('storage/' . $product->photo) : asset('storage/uploads/noimage.webp') }}" 
                                     class="card-img-top img-fluid rounded-0" 
                                     alt="{{ $product->external_name }}">
                            </a>

                            {{-- Botão de Remover (X) - Estilo Minimalista --}}
                            <div class="position-absolute top-0 end-0 p-3">
                                <form action="{{ route('user.preferences.toggle') }}" method="POST" class="remove-fav-form">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn-remove-jw" title="Remover">
                                        <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.2">
                                            <path d="M18 6L6 18M6 6l12 12"></path>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Info do Produto --}}
                        <div class="card-body px-3 py-4">
                            {{-- Marca (Se houver no objeto, senão fixo SAX ou marca do produto) --}}
                            <div class="jw-brand fw-bold text-uppercase mb-1">
                                {{ $product->brand->name ?? 'SAX' }}
                            </div>

                            {{-- Nome --}}
                            <div class="jw-product-name text-muted mb-2">
                                <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-muted">
                                    {{ $product->external_name }}
                                </a>
                            </div>

                            {{-- Preço --}}
                            <div class="jw-price fw-bold">
                                {{ currency_format($product->price) }}
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $favoriteProducts->links() }}
        </div>
    @endif
</div>
@endsection

<style>
    /* Estilo Base */
    .sax-wishlist-wrapper { font-family: 'Inter', sans-serif; }
    .sax-divider-dark { width: 40px; height: 3px; background: #000; margin-top: 10px; }

    /* Card JW Style */
    .jw-product-card {
        background-color: #f2f2f2 !important; 
        transition: opacity 0.3s ease;
    }

    .jw-product-card:hover {
        opacity: 0.9;
    }

    .jw-img-container {
        aspect-ratio: 4 / 5;
        display: flex;
        align-items: center;
        justify-content: center;
        overflow: hidden;
        background-color: #f2f2f2;
    }

    .jw-img-container img {
        width: 100%;
        height: 100%;
        object-fit: contain; /* 'contain' para bolsas/produtos com fundo limpo */
        mix-blend-mode: multiply;
    }

    /* Tipografia */
    .jw-brand {
        font-size: 0.7rem;
        letter-spacing: 0.1em;
        color: #000;
    }

    .jw-product-name {
        font-size: 0.8rem;
        letter-spacing: 0.02em;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .jw-price {
        font-size: 0.85rem;
        color: #000;
    }

    /* Botão de Remover */
    .btn-remove-jw {
        background: transparent;
        border: none;
        color: #000;
        padding: 0;
        transition: transform 0.2s ease, opacity 0.2s;
        opacity: 0.6;
    }

    .btn-remove-jw:hover {
        transform: scale(1.2);
        opacity: 1;
    }

    /* Utilitários */
    .letter-spacing-2 { letter-spacing: 3px; }
    .x-small { font-size: 0.7rem; }
</style>