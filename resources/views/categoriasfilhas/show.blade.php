@extends('layout.layout')

@section('content')
    <div class="category-detail-wrapper">

        @php
            $bannerUrl = null;

            // 1. Tenta o banner específico da categoria filha (no Storage uploads)
            if ($categoriasfilhas->banner && Storage::disk('public')->exists('uploads/' . $categoriasfilhas->banner)) {
                $bannerUrl = asset('storage/uploads/' . $categoriasfilhas->banner);
            }
            // 2. Se vazio, tenta o banner10 global (seguindo seu exemplo da imagem)
            elseif (isset($attribute->banner10) && $attribute->banner10) {
                // Verificamos se o arquivo existe na pasta uploads antes de atribuir
                if (Storage::disk('public')->exists('uploads/' . $attribute->banner10)) {
                    $bannerUrl = asset('storage/uploads/' . $attribute->banner10);
                }
            }
            
            // 3. Fallback final para o banner_horizontal se os anteriores falharem
            if (!$bannerUrl && !empty($banner_horizontal)) {
                $bannerUrl = asset('img/' . $banner_horizontal);
            }
        @endphp

        @if ($bannerUrl)
            <div class="category-hero-fullwidth">
                <img src="{{ $bannerUrl }}" class="hero-img-render" alt="{{ $categoriasfilhas->name }}" onerror="this.src='{{ asset('img/banner_horizontal.webp') }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-3"></div>
        @endif

        {{-- 2. ILHA DE IDENTIDADE --}}
        <div class="category-identity-section py-4 border-bottom bg-white">
            <div class="container text-center">
                <a href="{{ route('categorias-filhas.index') }}" class="back-link-minimal">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER A CATEGORIAS FILHAS
                </a>

                <div class="category-logo-container mt-3">
                    @if ($categoriasfilhas->photo && Storage::disk('public')->exists($categoriasfilhas->photo))
                        <img src="{{ Storage::url($categoriasfilhas->photo) }}" alt="{{ $categoriasfilhas->name }}"
                            class="category-main-logo">
                    @else
                        <h1 class="category-name-text">{{ $categoriasfilhas->name }}</h1>
                    @endif
                </div>

                {{-- Breadcrumb minimalista --}}
                <div class="child-breadcrumb mt-2">
                    <span class="opacity-50">{{ $categoriasfilhas->subcategory->category->name ?? '' }}</span>
                    <i class="fas fa-chevron-right mx-2 small opacity-25"></i>
                    <span class="opacity-75">{{ $categoriasfilhas->subcategory->name ?? '' }}</span>
                </div>
            </div>
        </div>

        {{-- 3. ÁREA DE CONTEÚDO --}}
        <div class="container-fluid px-1 px-md-4 py-4 bg-white">
            <div class="row g-1">
                <div class="col-12">
                    @if (isset($products) && $products->count())
                        <div class="row g-1">
                            @foreach ($products as $item)
                                <div class="col-6 col-md-4 col-xl-2">
                                    <a href="{{ route('produto.show', $item->slug ?? $item->id) }}"
                                        class="text-decoration-none jw-product-link">
                                        <div class="card h-100 border-0 rounded-0 jw-product-card bg-transparent">

                                            <div class="jw-img-container position-relative bg-light">
                                                @php
                                                    $photoUrl = $item->photo && Storage::disk('public')->exists($item->photo)
                                                        ? Storage::url($item->photo)
                                                        : asset('storage/uploads/noimage.webp');
                                                @endphp
                                                <img src="{{ $photoUrl }}" class="card-img-top img-fluid rounded-0" alt="{{ $item->name }}">
                                                
                                                <div class="position-absolute top-0 end-0 p-3 z-index-2">
                                                    @auth <x-product-favorite-button :item="$item" /> @endauth
                                                </div>
                                            </div>

                                            <div class="card-body px-1 py-4 text-center">
                                                <div class="jw-brand fw-bold text-uppercase mb-1">
                                                    {{ $item->brand->name ?? 'EXCLUSIVO' }}
                                                </div>
                                                <div class="jw-product-name text-muted mb-2">
                                                    {{ Str::limit($item->name ?? $item->external_name, 40) }}
                                                </div>
                                                <div class="jw-price fw-bold text-dark">
                                                    {{ isset($item->price) ? currency_format($item->price, 2, ',', '.') : '0,00' }}
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center mt-5 pagination-sax">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted text-uppercase tracking-widest small">No se encontraron productos en esta categoría.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    /* CSS Unificado para manter a identidade visual identica */
    .category-detail-wrapper {
        background-color: #fff;
        overflow-x: hidden;
    }

    .tracking-widest {
        letter-spacing: 0.2em;
    }

    .category-hero-fullwidth {
        width: 100vw;
        height: 45vh;
        min-height: 300px;
        position: relative;
        overflow: hidden;
        margin-left: calc(-50vw + 50%);
    }

    .hero-img-render {
        width: 100%;
        object-fit: cover;
        object-position: center;
    }

    .hero-overlay-soft {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.03);
    }

    .category-main-logo {
        max-height: 60px;
        width: auto;
        object-fit: contain;
    }

    .category-name-text {
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 5px;
        color: #000;
        margin: 0;
    }

    .back-link-minimal {
        color: #999;
        font-size: 0.65rem;
        letter-spacing: 2px;
        text-decoration: none;
        text-transform: uppercase;
        transition: color 0.2s;
    }

    .back-link-minimal:hover {
        color: #000;
    }

    .child-breadcrumb {
        font-size: 0.65rem;
        color: #aaa;
        text-transform: uppercase;
        letter-spacing: 1px;
    }

    .g-1 {
        margin-right: -4px;
        margin-left: -4px;
    }

    .g-1>[class*="col-"] {
        padding-right: 4px;
        padding-left: 4px;
        margin-bottom: 10px;
    }

    .jw-product-card {
        transition: opacity 0.3s ease;
    }

    .jw-product-link:hover .jw-product-card {
        opacity: 0.8;
    }

    .jw-img-container {
        aspect-ratio: 3 / 4;
        background-color: #fcfcfc;
        overflow: hidden;
    }

    .jw-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    .jw-brand {
        font-size: 0.7rem;
        letter-spacing: 1px;
        color: #000;
    }

    .jw-product-name {
        font-size: 0.75rem;
        color: #777 !important;
        height: 2.2rem;
        overflow: hidden;
    }

    .jw-price {
        font-size: 0.85rem;
        color: #000;
    }

    .pagination-sax .page-link {
        color: #000;
        border: none;
        background: transparent;
        font-size: 0.8rem;
    }

    .pagination-sax .page-item.active .page-link {
        background: none;
        text-decoration: underline;
        font-weight: bold;
    }

    @media (max-width: 991px) {
        .category-hero-fullwidth {
            height: 35vh;
        }
    }
</style>
