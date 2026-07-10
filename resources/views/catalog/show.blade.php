@extends('layout.layout')

@section('content')
    <div class="category-detail-wrapper">
        @php
            $storagePath = 'uploads/';
            $fallbackImg = asset('storage/uploads/banner_horizontal.webp');

            $resolveStorageUrl = function ($rawPath) use ($storagePath) {
                if (empty($rawPath)) {
                    return null;
                }

                $candidates = [$rawPath, $storagePath . ltrim($rawPath, '/')];

                foreach ($candidates as $candidate) {
                    if (Storage::disk('public')->exists($candidate)) {
                        return Storage::url($candidate);
                    }
                }

                return null;
            };

            $heroBannerUrl = $resolveStorageUrl($entity->banner ?? null);

            if (!$heroBannerUrl && !empty($banner10 ?? null)) {
                $heroBannerUrl = $resolveStorageUrl($banner10);
            }

            if (!$heroBannerUrl && !empty($banner_horizontal ?? null)) {
                $heroBannerUrl = $resolveStorageUrl($banner_horizontal);
            }

            $sideBannerUrl = $resolveStorageUrl($entity->internal_banner ?? null);

            if (!$sideBannerUrl) {
                $sideBannerUrl = $resolveStorageUrl($entity->image ?? null);
            }

            if (!$sideBannerUrl && !empty($banner_horizontal ?? null)) {
                $sideBannerUrl = $resolveStorageUrl($banner_horizontal);
            }

            $entityName = $entity->name ?? '';
            $mobileFilterId = 'catalogMobileFilter_' . ($entity->id ?? uniqid());
            
            $isEditionPrivee = (isset($entity->slug) && $entity->slug === 'edition-privee') || (request()->is('*edition-privee*'));
        @endphp

        @if ($heroBannerUrl)
            <div class="category-hero-fullwidth">
                <img src="{{ $heroBannerUrl }}" class="hero-img-render" alt="{{ $entityName }}" onerror="this.src='{{ $fallbackImg }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-3"></div>
        @endif

        <div class="category-identity-section">
            <div class="container text-center">
                <div class="category-logo-container">
                    <h1 class="category-name-text {{ $isEditionPrivee ? 'text-lowercase font-edition-privee' : 'text-uppercase' }}">
                        {{ $entityName }}
                    </h1>
                    <span class="category-name-accent" aria-hidden="true"></span>
                </div>

                @if (!empty($breadcrumb ?? []))
                    <div class="child-breadcrumb mt-3">
                        @foreach ($breadcrumb as $index => $crumb)
                            @if (!$loop->last)
                                <a href="{{ $crumb['url'] }}" class="breadcrumb-crumb breadcrumb-crumb-link {{ $isEditionPrivee ? 'text-lowercase font-edition-privee' : 'text-uppercase' }}">
                                    {{ $crumb['label'] }}
                                </a>
                                <span class="breadcrumb-sep">/</span>
                            @else
                                <span class="breadcrumb-crumb {{ $isEditionPrivee ? 'text-lowercase font-edition-privee' : 'text-uppercase' }}">
                                    {{ $crumb['label'] }}
                                </span>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="container-fluid px-1 px-md-4 py-4 bg-white">
            <div class="row g-1">
                <div class="col-12 col-lg-3 d-none d-lg-block">
                    <div class="sticky-sidebar-content" style="position: sticky; top: 100px;">
                        <div class="catalog-filter-desktop mb-4">
                            <x-product-filters
                                :categories="$categories"
                                :brands="$brands"
                                :currentCategory="$currentCategory ?? null"
                                :currentSub="$currentSub ?? null"
                                :currentChild="$currentChild ?? null" />
                        </div>

                        @if ($sideBannerUrl)
                            <div class="sticky-banner-lateral">
                                <img src="{{ $sideBannerUrl }}" class="img-fluid banner-v-render" alt="{{ $entityName }} Promo" onerror="this.src='{{ $fallbackImg }}'">
                            </div>
                        @endif
                    </div>
                </div>

                <div class="col-12 col-lg-9">
                    <div class="catalog-toolbar d-flex align-items-center justify-content-between mb-3">
                        <p class="catalog-toolbar-label mb-0 {{ $isEditionPrivee ? 'text-lowercase font-edition-privee' : 'text-uppercase' }}">
                            {{ $entityName }}
                        </p>
                        <button
                            class="btn catalog-filter-toggle d-lg-none"
                            type="button"
                            data-bs-toggle="offcanvas"
                            data-bs-target="#{{ $mobileFilterId }}"
                            aria-expanded="false"
                            aria-controls="{{ $mobileFilterId }}">
                            <i class="fas fa-sliders-h me-2"></i> Filtrar
                        </button>
                    </div>

                    @if ($products->count())
                        <div class="row g-1">
                            @foreach ($products as $item)
                                <x-product-card :item="$item" :cartItems="$cartItems ?? []" gridClass="col-6 col-md-4 col-xl-3" />
                            @endforeach
                        </div>

                        <div class="d-flex justify-content-center mt-5 pagination-sax">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted text-uppercase tracking-widest small">
                                {{ $emptyMessage }}
                            </p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <div class="offcanvas offcanvas-end catalog-filter-offcanvas d-lg-none" tabindex="-1" id="{{ $mobileFilterId }}" aria-labelledby="{{ $mobileFilterId }}Label">
            <div class="offcanvas-header">
                <h5 class="offcanvas-title" id="{{ $mobileFilterId }}Label">{{ __('messages.filtrar_produtos') }}</h5>
                <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="{{ __('messages.fechar') }}"></button>
            </div>
            <div class="offcanvas-body">
                <div class="mobile-filter-shell">
                    <x-product-filters
                        :categories="$categories"
                        :brands="$brands"
                        :currentCategory="$currentCategory ?? null"
                        :currentSub="$currentSub ?? null"
                        :currentChild="$currentChild ?? null" />
                </div>

                @if ($sideBannerUrl)
                    <div class="mobile-filter-banner mt-3">
                        <img src="{{ $sideBannerUrl }}" class="img-fluid banner-v-render" alt="{{ $entityName }} Promo" onerror="this.src='{{ $fallbackImg }}'">
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection

@push('styles')
    <style>
        .font-edition-privee {
            text-transform: none !important;
        }

        .extra-small {
            font-size: 0.65rem;
        }

        .category-detail-wrapper {
            background: #fff;
        }

        .category-identity-section {
            background: linear-gradient(180deg, #ffffff 0%, #faf9f7 100%) !important;
            padding: 2.75rem 0 2.5rem;
            border-bottom: 1px solid #efede8;
        }

        .category-logo-container {
            min-height: 46px;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        .category-main-logo {
            max-height: 80px;
            width: auto;
            object-fit: contain;
        }

        .category-name-accent {
            display: block;
            width: 42px;
            height: 2px;
            margin: 0.9rem auto 0;
            background: #b2945e;
        }

        .child-breadcrumb {
            display: flex;
            align-items: center;
            justify-content: center;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .breadcrumb-crumb {
            font-size: 0.68rem;
            font-weight: 600;
            letter-spacing: 1.2px;
            color: #928c7e;
        }

        .breadcrumb-crumb:last-child {
            color: #29251f;
            font-weight: 700;
        }

        a.breadcrumb-crumb-link {
            text-decoration: none;
            transition: color 0.2s ease;
        }

        a.breadcrumb-crumb-link:hover {
            color: #29251f;
            text-decoration: underline;
        }

        .breadcrumb-sep {
            color: #cbc3b6;
            font-size: 0.7rem;
        }

        .catalog-toolbar {
            border-bottom: 1px solid #efede8;
            padding-bottom: 10px;
        }

        .catalog-toolbar-label {
            font-size: 10px;
            letter-spacing: 1.8px;
            color: #696255;
            font-weight: 700;
        }

        .catalog-filter-toggle {
            border: 1px solid #d9d3c8;
            border-radius: 999px;
            background: #f8f6f2;
            color: #221f19;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 700;
            padding: 7px 14px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.05);
        }

        .catalog-filter-toggle:hover,
        .catalog-filter-toggle:focus {
            background: #f0ece4;
            color: #13110f;
            border-color: #c7beaf;
        }

        .catalog-filter-desktop {
            border: 1px solid #ece7de;
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
        }

        .mobile-filter-shell {
            border: 1px solid #ece7de;
            background: #fff;
            border-radius: 12px;
            padding: 14px;
            box-shadow: 0 6px 20px rgba(0, 0, 0, 0.04);
        }

        .mobile-filter-banner {
            border-radius: 10px;
            overflow: hidden;
            border: 1px solid #efede8;
        }

        .catalog-filter-offcanvas {
            width: min(420px, 92vw);
            background: #faf9f7;
        }

        .catalog-filter-offcanvas .offcanvas-header {
            border-bottom: 1px solid #e8e4dc;
            padding: 14px 16px;
        }

        .catalog-filter-offcanvas .offcanvas-title {
            font-size: 12px;
            font-weight: 700;
            letter-spacing: 1.4px;
            text-transform: uppercase;
            margin: 0;
        }

        .catalog-filter-offcanvas .offcanvas-body {
            padding: 14px;
        }

        .jw-product-card {
            transition: transform 0.22s ease, box-shadow 0.22s ease;
        }

        .jw-product-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        }

        .category-name-text {
            font-family: 'Playfair Display', Georgia, serif;
            font-weight: 700;
            font-size: clamp(1.6rem, 4vw, 2.6rem);
            margin-bottom: 0;
            letter-spacing: 1px;
            color: #1a1a1a;
        }

        .category-name-text.font-edition-privee {
            font-weight: 400;
            font-style: italic;
        }

        @media (max-width: 991px) {
            .category-identity-section {
                padding-top: 1.1rem !important;
                padding-bottom: 1.1rem !important;
            }

            .catalog-toolbar {
                margin-top: 2px;
            }
        }
    </style>
@endpush