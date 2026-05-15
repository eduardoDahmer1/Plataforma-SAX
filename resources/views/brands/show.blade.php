@extends('layout.layout')

@section('content')
    <div class="brand-detail-wrapper">
        @php
            $storagePath = 'uploads/';
            $bannerUrl = null;

            // Lógica de Banner Principal (Topo) - Identica a Categorias
            if ($brand->internal_banner) {
                $bannerUrl = Storage::url($brand->internal_banner);
            } elseif (isset($banner10) && $banner10) {
                $bannerUrl = Storage::url($storagePath . $banner10);
            }

            // Lógica de Banner Lateral
            $bannerLateralUrl = null;
            if ($brand->banner) {
                $bannerLateralUrl = Storage::url($brand->banner);
            } elseif (!empty($banner_horizontal)) {
                $bannerLateralUrl = Storage::url($storagePath . $banner_horizontal);
            }

            $fallbackImg = asset('storage/uploads/banner_horizontal.webp');
        @endphp

        {{-- Banner de Topo (Hero) --}}
        @if ($bannerUrl)
            <div class="brand-hero-fullwidth">
                <img src="{{ $bannerUrl }}" class="hero-img-render" alt="{{ $brand->name }}"
                    onerror="this.src='{{ $fallbackImg }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-3"></div>
        @endif

        {{-- Identidade da Marca (Logo e Título) --}}
        <div class="brand-identity-section py-4 border-bottom bg-white">
            <div class="container text-center">
                <a href="{{ route('brands.index') }}" class="back-link-minimal">
                    <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar_marcas') }}
                </a>
                <div class="brand-logo-container mt-3">
                    @if ($brand->image)
                        <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name }}" class="brand-main-logo">
                    @else
                        <h1 class="brand-name-text">{{ $brand->name }}</h1>
                    @endif
                </div>
            </div>
        </div>

        {{-- Conteúdo Principal --}}
        <div class="container-fluid px-1 px-md-4 py-4 bg-white">
            <div class="row g-1">

                {{-- Coluna Lateral: Filtros + Banner --}}
                <div class="col-12 col-lg-3 d-none d-lg-block">
                    <div class="sticky-sidebar-content" style="position: sticky; top: 100px;">

                        {{-- CHAMADA DO COMPONENTE --}}
                        <div class="mb-5 p-3 border">
                            <x-product-filters :categories="$categories" :brands="$brands" :currentCategory="$categoriasfilhas->subcategory->category_id ?? null" />
                        </div>

                        {{-- Banner Lateral --}}
                        @if ($bannerLateralUrl)
                            <div class="sticky-banner-lateral">
                                <img src="{{ $bannerLateralUrl }}" class="img-fluid banner-v-render"
                                    alt="{{ $brand->name }} Promo" onerror="this.src='{{ $fallbackImg }}'">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Coluna de Produtos --}}
                <div class="col-12 col-lg-9">
                    @if ($products->count())
                        <div class="row g-1">
                            @foreach ($products as $item)
                                <div class="col-6 col-md-4 col-xl-3">
                                    <a href="{{ route('produto.show', $item->slug ?? $item->id) }}"
                                        class="text-decoration-none jw-product-link">
                                        <div class="card h-100 border-0 rounded-0 jw-product-card bg-transparent">
                                            <div class="jw-img-container position-relative bg-light">
                                                <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-0"
                                                    alt="{{ $item->external_name }}">

                                                <div class="position-absolute top-0 end-0 p-3 z-index-2">
                                                    @auth <x-product-favorite-button :item="$item" /> @endauth
                                                </div>
                                            </div>

                                            <div class="card-body px-2 py-3 d-flex flex-column">
                                                <div class="sax-brand fw-bold text-uppercase mb-1">
                                                    {{ $brand->name }}
                                                </div>

                                                <div class="sax-product-name text-muted mb-3">
                                                    {{ $item->name ?? $item->external_name }}
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                                    <div class="sax-price fw-bold text-dark">
                                                        {{ isset($item->price) ? currency_format($item->price, 2, ',', '.') : '0,00' }}
                                                    </div>
                                                    <div class="sax-sku text-muted small">
                                                        SKU: {{ $item->sku ?? 'N/A' }}
                                                    </div>
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
                            <p class="text-muted text-uppercase tracking-widest small">
                                No se encontraron productos de esta marca.
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .sticky-sidebar-content {
            position: -webkit-sticky;
            position: sticky;
            top: 20px;
            height: fit-content;
        }

        .brand-main-logo {
            max-height: 80px;
            width: auto;
            object-fit: contain;
        }

        .back-link-minimal {
            text-decoration: none;
            color: #000;
            font-size: 0.75rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: opacity 0.3s;
        }

        .back-link-minimal:hover {
            opacity: 0.6;
        }
    </style>
@endpush
