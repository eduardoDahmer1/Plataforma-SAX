@extends('layout.layout')

@section('content')
    <div class="category-detail-wrapper">
        @php
            $storagePath = 'uploads/';
            $bannerUrl = null;

            // Lógica de Banner Principal (Hero)
            if (
                $categoriasfilhas->banner &&
                Storage::disk('public')->exists($storagePath . $categoriasfilhas->banner)
            ) {
                $bannerUrl = Storage::url($storagePath . $categoriasfilhas->banner);
            } elseif (isset($banner10) && $banner10 && Storage::disk('public')->exists($storagePath . $banner10)) {
                $bannerUrl = Storage::url($storagePath . $banner10);
            }

            if (!$bannerUrl && !empty($banner_horizontal)) {
                $bannerUrl = Storage::disk('public')->exists($storagePath . $banner_horizontal)
                    ? Storage::url($storagePath . $banner_horizontal)
                    : null;
            }

            // Lógica de Banner Lateral
            $bannerLateralUrl = null;
            if (isset($categoriasfilhas->image) && Storage::disk('public')->exists($categoriasfilhas->image)) {
                $bannerLateralUrl = Storage::url($categoriasfilhas->image);
            } elseif (!empty($banner_horizontal)) {
                $bannerLateralUrl = Storage::disk('public')->exists($storagePath . $banner_horizontal)
                    ? Storage::url($storagePath . $banner_horizontal)
                    : null;
            }

            $fallbackImg = asset('storage/uploads/banner_horizontal.webp');
        @endphp

        {{-- Banner de Topo (Hero) --}}
        @if ($bannerUrl)
            <div class="category-hero-fullwidth">
                <img src="{{ $bannerUrl }}" class="hero-img-render" alt="{{ $categoriasfilhas->name }}"
                    onerror="this.src='{{ $fallbackImg }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-3"></div>
        @endif

        {{-- Identidade da Categoria Filha --}}
        <div class="category-identity-section py-4 border-bottom bg-white">
            <div class="container text-center">
                <a href="{{ route('categorias-filhas.index') }}" class="back-link-minimal">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER A CATEGORIAS FILHAS
                </a>

                <div class="category-logo-container mt-3">
                    @if ($categoriasfilhas->photo && Storage::disk('public')->exists($storagePath . $categoriasfilhas->photo))
                        <img src="{{ Storage::url($storagePath . $categoriasfilhas->photo) }}"
                            alt="{{ $categoriasfilhas->name }}" class="category-main-logo">
                    @else
                        <h1 class="category-name-text text-uppercase fw-light" style="letter-spacing: 3px;">
                            {{ $categoriasfilhas->name }}
                        </h1>
                    @endif
                </div>

                <div class="child-breadcrumb mt-2">
                    <span class="text-muted small text-uppercase" style="letter-spacing: 1px;">
                        {{ $categoriasfilhas->subcategory->category->name ?? '' }}
                    </span>
                    <i class="fas fa-chevron-right mx-2 extra-small opacity-25"></i>
                    <span class="text-muted small text-uppercase" style="letter-spacing: 1px;">
                        {{ $categoriasfilhas->subcategory->name ?? '' }}
                    </span>
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
                                    alt="{{ $categoriasfilhas->name }} Promo" onerror="this.src='{{ $fallbackImg }}'">
                            </div>
                        @endif
                    </div>
                </div>

                {{-- Coluna de Produtos --}}
                <div class="col-12 col-lg-9">
                    @if (isset($products) && $products->count())
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
                                No se encontraron productos en esta categoría.
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
        .extra-small {
            font-size: 0.65rem;
        }

        .category-main-logo {
            max-height: 80px;
            width: auto;
            object-fit: contain;
        }

        .back-link-minimal {
            text-decoration: none;
            color: #000;
            font-size: 0.7rem;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: opacity 0.3s;
        }

        .back-link-minimal:hover {
            opacity: 0.6;
        }

        .jw-product-card {
            transition: transform 0.3s ease;
        }

        .jw-product-card:hover {
            transform: translateY(-5px);
        }

        .category-name-text {
            font-size: 1.5rem;
            margin-bottom: 0;
        }
    </style>
@endpush
