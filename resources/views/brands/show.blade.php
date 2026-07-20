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
                                <x-product-card :item="$item" gridClass="col-6 col-md-4 col-xl-3" />
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
