@extends('layout.layout')

@section('content')
    <div class="child-detail-wrapper">
        {{-- Header Minimalista --}}
        <div class="child-hero py-5 border-bottom">
            <div class="container text-center">
                <a href="{{ route('categorias-filhas.index') }}" class="back-link">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER
                </a>

                <div class="child-logo-main my-4">
                    @if ($categoriasfilhas->photo && Storage::disk('public')->exists($categoriasfilhas->photo))
                        <img src="{{ Storage::url($categoriasfilhas->photo) }}" alt="{{ $categoriasfilhas->name }}"
                            class="main-child-img">
                    @else
                        <h1 class="sax-child-title">{{ $categoriasfilhas->name }}</h1>
                    @endif
                </div>

                {{-- Breadcrumb de luxo discreto --}}
                <div class="child-breadcrumb">
                    <span>{{ $categoriasfilhas->subcategory->category->name ?? '' }}</span>
                    <i class="fas fa-chevron-right mx-2"></i>
                    <span>{{ $categoriasfilhas->subcategory->name ?? '' }}</span>
                </div>
            </div>
        </div>

        <div class="container-fluid px-2 py-5">
            {{-- Alterado para percorrer a variável $products paginada --}}
            @if (isset($products) && $products->count())
                <div class="row g-1"> {{-- Grid colado JW PEI Style --}}
                    @foreach ($products as $product)
                        <div class="col-6 col-md-4 col-lg-2">
                            <div class="card h-100 border-0 rounded-0 jw-product-card">

                                {{-- Área da Imagem com fundo cinza --}}
                                <div class="jw-img-container position-relative">
                                    <a href="{{ route('products.show', $product->id) }}" class="w-100 h-100">
                                        @php
                                            $photoUrl =
                                                $product->photo_url ??
                                                ($product->photo && Storage::disk('public')->exists($product->photo)
                                                    ? Storage::url($product->photo)
                                                    : asset('storage/uploads/noimage.webp'));
                                        @endphp
                                        <img src="{{ $photoUrl }}" class="card-img-top img-fluid rounded-0"
                                            alt="{{ $product->name }}">
                                    </a>

                                    {{-- Botão de Favorito Estilo Minimal --}}
                                    <div class="position-absolute top-0 end-0 p-3" style="z-index: 10;">
                                        @auth
                                            <x-product-favorite-button :item="$product" />
                                        @endauth
                                    </div>
                                </div>

                                {{-- Info do Produto --}}
                                <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none">
                                    <div class="card-body px-3 py-4">
                                        <div class="jw-brand fw-bold text-uppercase mb-1">
                                            {{ $product->brand->name ?? 'EXCLUSIVO' }}
                                        </div>
                                        <div class="jw-product-name text-muted mb-2">
                                            {{ Str::limit($product->name ?? $product->external_name, 35) }}
                                        </div>
                                        <div class="jw-price fw-bold text-dark">
                                            {{ isset($product->price) ? currency_format($product->price) : '0,00' }}
                                        </div>
                                    </div>
                                </a>
                            </div>
                        </div>
                    @endforeach
                </div>

                {{-- Bloco de Paginação --}}
                <div class="d-flex justify-content-center mt-5 pagination-minimal">
                    {!! $products->links() !!}
                </div>

            @else
                <div class="text-center py-5">
                    <p class="text-muted text-uppercase tracking-widest">No hay productos disponibles en este momento.</p>
                </div>
            @endif

            {{-- Banner Opcional --}}
            @if ($categoriasfilhas->banner && Storage::disk('public')->exists($categoriasfilhas->banner))
                <div class="mt-5 container">
                    <img src="{{ Storage::url($categoriasfilhas->banner) }}" class="img-fluid w-100 shadow-sm"
                        style="max-height: 400px; object-fit: cover;">
                </div>
            @endif
        </div>
    </div>
@endsection

<style>
    .child-detail-wrapper {
        background-color: #fff;
    }

    .child-hero {
        background-color: #fcfcfc;
    }

    .back-link {
        color: #888;
        font-size: 0.7rem;
        letter-spacing: 2px;
        text-decoration: none;
        text-transform: uppercase;
    }

    .main-child-img {
        max-height: 60px;
        width: auto;
        object-fit: contain;
    }

    .sax-child-title {
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 5px;
        color: #000;
        margin: 0;
    }

    .child-breadcrumb {
        font-size: 0.65rem;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    /* Card Padrão JW PEI */
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
    }

    .jw-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .jw-brand {
        font-size: 0.75rem;
        letter-spacing: 0.05em;
        color: #000;
    }

    .jw-product-name {
        font-size: 0.8rem;
        color: #666 !important;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }

    .jw-price {
        font-size: 0.85rem;
        color: #000;
    }

    /* Estilo da Paginação */
    .pagination-minimal .pagination {
        gap: 5px;
    }

    .pagination-minimal .page-link {
        color: #000;
        border: none;
        background: transparent;
        font-size: 0.8rem;
    }

    .pagination-minimal .page-item.active .page-link {
        background-color: transparent;
        color: #000;
        font-weight: bold;
        text-decoration: underline;
    }

    /* Grid colado */
    .g-1 {
        margin-right: -2px;
        margin-left: -2px;
    }

    .g-1>[class*="col-"] {
        padding-right: 2px;
        padding-left: 2px;
    }
</style>