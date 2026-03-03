@extends('layout.layout')

@section('content')
    <div class="brand-detail-wrapper">
        @php
            $bannerUrl = null;
            $storagePath = 'uploads/';

            if ($brand->internal_banner && Storage::disk('public')->exists($brand->internal_banner)) {
                $bannerUrl = Storage::url($brand->internal_banner);
            } elseif (isset($banner10) && $banner10 && Storage::disk('public')->exists($storagePath . $banner10)) {
                $bannerUrl = Storage::url($storagePath . $banner10);
            }

            if (!$bannerUrl && !empty($banner_horizontal)) {
                $bannerUrl = Storage::disk('public')->exists($storagePath . $banner_horizontal) 
                    ? Storage::url($storagePath . $banner_horizontal) 
                    : null;
            }

            $bannerLateralUrl = null;
            if ($brand->banner && Storage::disk('public')->exists($brand->banner)) {
                $bannerLateralUrl = Storage::url($brand->banner);
            } elseif (!empty($banner_horizontal)) {
                $bannerLateralUrl = Storage::disk('public')->exists($storagePath . $banner_horizontal) 
                    ? Storage::url($storagePath . $banner_horizontal) 
                    : null;
            }

            $fallbackImg = asset('storage/uploads/banner_horizontal.webp');
        @endphp

        @if ($bannerUrl)
            <div class="brand-hero-fullwidth">
                <img src="{{ $bannerUrl }}" class="hero-img-render" alt="{{ $brand->name }}"
                    onerror="this.src='{{ $fallbackImg }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-4"></div>
        @endif

        <div class="brand-identity-section py-4 border-bottom bg-white">
            <div class="container text-center">
                <a href="{{ route('brands.index') }}" class="back-link-minimal">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER A MARCAS
                </a>
                <div class="brand-logo-container mt-3">
                    @if ($brand->image && Storage::disk('public')->exists($brand->image))
                        <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" class="brand-main-logo">
                    @else
                        <h1 class="brand-name-text">{{ $brand->name }}</h1>
                    @endif
                </div>
            </div>
        </div>

        <div class="container-fluid px-1 px-md-4 py-4">
            <div class="row g-1">
                @if ($bannerLateralUrl)
                    <div class="col-12 col-lg-3 d-none d-lg-block">
                        <div class="sticky-banner-lateral">
                            <img src="{{ $bannerLateralUrl }}" class="img-fluid banner-v-render"
                                alt="Promo {{ $brand->name }}"
                                onerror="this.src='{{ $fallbackImg }}'">
                        </div>
                    </div>
                @endif

                <div class="col-12 {{ $bannerLateralUrl ? 'col-lg-9' : 'col-lg-12' }}">
                    <div class="row g-1">
                        @foreach ($products as $item)
                            <div class="col-6 col-md-4 {{ $bannerLateralUrl ? 'col-xl-3' : 'col-xl-2' }}">
                                <a href="{{ route('produto.show', $item->slug) }}" class="text-decoration-none">
                                    <div class="card h-100 border-0 rounded-0 jw-product-card bg-light">
                                        <div class="jw-img-container position-relative">
                                            <img src="{{ $item->photo_url }}" class="card-img-top img-fluid"
                                                alt="{{ $item->external_name }}">
                                            <div class="position-absolute top-0 end-0 p-2">
                                                @auth <x-product-favorite-button :item="$item" /> @endauth
                                            </div>
                                        </div>
                                        <div class="card-body px-2 py-3 text-center bg-white">
                                            <div class="jw-brand fw-bold text-uppercase mb-1"
                                                style="font-size: 0.65rem; letter-spacing: 1px;">
                                                {{ $brand->name }}
                                            </div>
                                            <div class="jw-product-name text-muted small mb-2 text-truncate">
                                                {{ $item->external_name }}
                                            </div>
                                            <div class="jw-price fw-bold text-dark small">
                                                {{ isset($item->price) ? currency_format($item->price) : '0,00' }}
                                            </div>
                                        </div>
                                    </div>
                                </a>
                            </div>
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-5">
                        {{ $products->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
<style>
    .brand-detail-wrapper {
        background-color: #fff;
    }

    /* 1. Banner Horizontal (Edge to Edge) */
    .brand-hero-fullwidth {
        width: 100vw;
        min-height: 300px;
        position: relative;
        overflow: hidden;
        margin-left: calc(-50vw + 50%);
    }

    .hero-img-render {
        width: 100%;
        object-fit: cover;
    }

    .hero-overlay-soft {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        background: rgba(0, 0, 0, 0.05);
    }

    /* 2. Identidade da Marca */
    .brand-main-logo {
        max-height: 60px;
        width: auto;
        object-fit: contain;
    }

    .back-link-minimal {
        color: #999;
        font-size: 0.65rem;
        letter-spacing: 2px;
        text-decoration: none;
        text-transform: uppercase;
    }

    .brand-name-text {
        font-weight: 300;
        text-transform: uppercase;
        letter-spacing: 4px;
    }

    /* 3. Banner Lateral (Vertical) */
    .sticky-banner-lateral {
        position: sticky;
        top: 20px;
        /* Faz o banner lateral acompanhar o scroll */
        height: fit-content;
    }

    .banner-v-render {
        width: 100%;
        object-fit: cover;
        border: 1px solid #f0f0f0;
    }

    /* 4. Grid de Produtos (Igual Categorias) */
    .jw-product-card {
        transition: opacity 0.3s ease;
        border: 1px solid #f8f8f8 !important;
    }

    .jw-product-card:hover {
        opacity: 0.85;
    }

    .jw-img-container {
        aspect-ratio: 3 / 4;
        background-color: #fcfcfc;
    }

    .jw-img-container img {
        width: 100%;
        object-fit: cover;
    }

    /* Ajustes Mobile */
    @media (max-width: 991px) {
        .col-lg-3 {
            display: none;
        }

        /* Esconde o banner vertical no mobile para não poluir */
    }
</style>
