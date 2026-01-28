@extends('layout.layout')

@section('content')
    <div class="brand-detail-wrapper">
        {{-- Header da Marca --}}
        <div class="brand-hero py-5 border-bottom">
            <div class="container text-center">
                <a href="{{ route('brands.index') }}" class="back-link">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER
                </a>
                
                <div class="brand-logo-main my-4">
                    @if ($brand->image && Storage::disk('public')->exists($brand->image))
                        <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" class="main-logo-img">
                    @else
                        <h1 class="sax-brand-title">{{ $brand->name }}</h1>
                    @endif
                </div>
            </div>
        </div>

        <div class="container-fluid px-2 py-5"> {{-- container-fluid para ocupar mais espaço como na busca --}}
            @if ($products->count())
                <div class="row g-1"> {{-- g-1 para o grid colado idêntico à imagem --}}
                    @foreach ($products as $item)
                        <div class="col-6 col-md-4 col-lg-2">
                            <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none">
                                <div class="card h-100 border-0 rounded-0 jw-product-card">
                                    
                                    {{-- Área da Imagem --}}
                                    <div class="jw-img-container position-relative">
                                        <img src="{{ $item->photo_url }}" 
                                             class="card-img-top img-fluid rounded-0" 
                                             alt="{{ $item->external_name }}">

                                        {{-- Favorito --}}
                                        <div class="position-absolute top-0 end-0 p-3">
                                            @auth
                                                <x-product-favorite-button :item="$item" />
                                            @endauth
                                        </div>
                                    </div>

                                    {{-- Info do Produto --}}
                                    <div class="card-body px-3 py-4">
                                        <div class="jw-brand fw-bold text-uppercase mb-1">
                                            {{ $brand->name }}
                                        </div>
                                        <div class="jw-product-name text-muted mb-2">
                                            {{ Str::limit($item->external_name, 35) }}
                                        </div>
                                        <div class="jw-price fw-bold text-dark">
                                            {{ isset($item->price) ? currency_format($item->price) : '0,00' }}
                                        </div>
                                    </div>
                                </div>
                            </a>
                        </div>
                    @endforeach
                </div>

                {{-- Paginação --}}
                <div class="d-flex justify-content-center mt-5">
                    {{ $products->links() }}
                </div>
            @else
                <div class="alert alert-light text-center py-5">
                    No se encontraron productos para esta marca.
                </div>
            @endif
        </div>
    </div>
@endsection
<style>
    /* Container de Marca */
.brand-detail-wrapper { background-color: #fff; }

.main-logo-img { max-height: 60px; width: auto; object-fit: contain; }

.back-link {
    color: #888;
    font-size: 0.7rem;
    letter-spacing: 2px;
    text-decoration: none;
    text-transform: uppercase;
}

/* Card JW Estilo */
.jw-product-card {
    background-color: #f2f2f2 !important; /* Fundo cinza da imagem */
    transition: opacity 0.3s ease;
}

.jw-product-card:hover { opacity: 0.9; }

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
    object-fit: cover;
}

/* Tipografia */
.jw-brand {
    font-size: 0.75rem;
    letter-spacing: 0.05em;
    color: #000;
}

.jw-product-name {
    font-size: 0.8rem;
    letter-spacing: 0.02em;
    color: #666 !important;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.jw-price {
    font-size: 0.85rem;
    color: #000;
}

/* Botão Favorito */
.btn-favorite-sax {
    background: transparent;
    border: none;
    color: #000;
    padding: 0;
    transition: transform 0.2s ease;
}

.btn-favorite-sax:hover { transform: scale(1.1); }

/* Ajuste de Grid */
.g-1 {
    margin-right: -2px;
    margin-left: -2px;
}
.g-1 > [class*="col-"] {
    padding-right: 2px;
    padding-left: 2px;
}
</style>