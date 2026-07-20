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

            $isBrand = $isBrand ?? false;

            $heroBannerUrl = $resolveStorageUrl($isBrand
                ? ($entity->internal_banner ?? null)
                : ($entity->banner ?? null));

            if (!$heroBannerUrl && !empty($banner10 ?? null)) {
                $heroBannerUrl = $resolveStorageUrl($banner10);
            }

            if (!$heroBannerUrl && !empty($banner_horizontal ?? null)) {
                $heroBannerUrl = $resolveStorageUrl($banner_horizontal);
            }

            $sideBannerUrl = $resolveStorageUrl($isBrand
                ? ($entity->banner ?? null)
                : ($entity->internal_banner ?? null));

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

        <div class="container-fluid px-3 px-lg-5 py-4 bg-white catalog-products-section">
            <div class="toolbar-container search-toolbar catalog-toolbar d-flex d-lg-none justify-content-between align-items-center mb-4">
                <button
                    class="btn-filter-trigger search-filter-button d-flex align-items-center gap-2"
                    type="button"
                    data-bs-toggle="offcanvas"
                    data-bs-target="#{{ $mobileFilterId }}"
                    aria-controls="{{ $mobileFilterId }}">
                    <svg width="18" height="12" viewBox="0 0 18 12" fill="none" aria-hidden="true">
                        <path d="M0 1H18M0 6H12M0 11H18" stroke="currentColor" stroke-width="1.5"/>
                    </svg>
                    <span class="x-small fw-bold text-uppercase tracking-widest">{{ __('messages.todos_filtros') }}</span>
                </button>

                <div class="catalog-result-summary">
                    <strong>{{ number_format($products->total(), 0, ',', '.') }}</strong>
                    <span>produtos encontrados</span>
                </div>
            </div>

            <div class="row g-3 g-xl-4 align-items-start">
                <aside class="col-lg-3 d-none d-lg-block">
                    <div class="catalog-desktop-filter catalog-standard-drawer">
                        <div class="search-filter-header catalog-desktop-filter-header">
                            <div>
                                <span class="search-filter-eyebrow">Catálogo</span>
                                <h2 class="offcanvas-title mb-0">{{ __('messages.filtrar_por') }}</h2>
                            </div>
                        </div>
                        <div class="search-filter-body catalog-desktop-filter-body">
                            <div class="catalog-drawer-filter-shell">
                                <x-product-filters
                                    :categories="$categories"
                                    :brands="$brands"
                                    :currentCategory="$currentCategory ?? null"
                                    :currentSub="$currentSub ?? null"
                                    :currentChild="$currentChild ?? null" />
                            </div>
                        </div>
                    </div>

                    @if ($sideBannerUrl)
                        <div class="sticky-banner-lateral catalog-sidebar-banner mt-3">
                            <img
                                src="{{ $sideBannerUrl }}"
                                class="img-fluid banner-v-render"
                                alt="{{ $entityName }} Promo"
                                onerror="this.src='{{ $fallbackImg }}'">
                        </div>
                    @endif
                </aside>

                <div class="col-12 col-lg-9">
                    <x-catalog-sort-toolbar />

                    <div class="catalog-result-summary catalog-result-summary-desktop d-none d-lg-flex">
                        <strong>{{ number_format($products->total(), 0, ',', '.') }}</strong>
                        <span>produtos encontrados</span>
                    </div>

                    @if ($products->count())
                        @php
                            $productLocale = translation_locale();
                            $products->getCollection()->load([
                                'translations' => fn ($query) => $query->where('locale', $productLocale),
                            ]);
                        @endphp
                        <div class="row g-2 g-md-3">
                            @foreach ($products as $item)
                                @php
                                    $translation = $item->translations->first();
                                    $displayName = filled($translation?->name) ? $translation->name : $item->name;
                                @endphp
                                <x-product-card :item="$item" :cartItems="$cartItems ?? []" :displayName="$displayName" gridClass="col-6 col-md-4 col-xl-3" />
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

        <div class="offcanvas offcanvas-end search-filter-drawer catalog-standard-drawer" tabindex="-1" id="{{ $mobileFilterId }}" aria-labelledby="{{ $mobileFilterId }}Label">
            <div class="offcanvas-header search-filter-header">
                <div>
                    <span class="search-filter-eyebrow">Catálogo</span>
                    <h5 class="offcanvas-title mb-0" id="{{ $mobileFilterId }}Label">{{ __('messages.filtrar_por') }}</h5>
                </div>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="{{ __('messages.fechar') }}"></button>
            </div>
            <div class="offcanvas-body search-filter-body">
                <div class="catalog-drawer-filter-shell">
                    <x-product-filters
                        :categories="$categories"
                        :brands="$brands"
                        :currentCategory="$currentCategory ?? null"
                        :currentSub="$currentSub ?? null"
                        :currentChild="$currentChild ?? null" />
                </div>

                @if ($sideBannerUrl)
                    <div class="sticky-banner-lateral catalog-sidebar-banner mt-3">
                        <img
                            src="{{ $sideBannerUrl }}"
                            class="img-fluid banner-v-render"
                            alt="{{ $entityName }} Promo"
                            onerror="this.src='{{ $fallbackImg }}'">
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
            border-bottom: 0;
        }

        .catalog-sort-toolbar {
            display: flex;
            justify-content: flex-end;
            align-items: center;
            gap: 10px;
            padding: 12px 14px;
            border: 1px solid #e0e5ec;
            border-radius: 14px;
            background: #fff;
            box-shadow: 0 7px 22px rgba(16, 24, 40, 0.045);
        }

        .catalog-sort-toolbar .toolbar-control {
            padding: 6px 9px 6px 12px;
            border: 1px solid #e0e5ec;
            border-radius: 11px;
            background: #f9fafb;
        }

        .catalog-sort-toolbar .toolbar-label {
            color: #667085;
            font-size: .67rem;
            font-weight: 800;
            letter-spacing: .03em;
            text-transform: uppercase;
            white-space: nowrap;
        }

        .catalog-sort-toolbar .toolbar-select {
            min-width: 145px;
            border: 0;
            padding-top: 4px;
            padding-bottom: 4px;
            background-color: transparent;
            box-shadow: none;
            color: #101828;
            font-size: .74rem;
            font-weight: 700;
        }

        .catalog-sort-toolbar .catalog-per-page-select {
            min-width: 68px;
        }

        .catalog-result-summary {
            display: flex;
            align-items: baseline;
            gap: 5px;
            color: #667085;
            font-size: 0.73rem;
        }

        .catalog-result-summary strong {
            color: #101828;
            font-size: 0.9rem;
            font-weight: 750;
        }

        .catalog-result-summary-desktop {
            justify-content: flex-end;
            min-height: 28px;
            margin-bottom: 12px;
            padding: 0 3px;
        }

        .catalog-desktop-filter {
            position: sticky;
            top: 96px;
            overflow: hidden;
            max-height: calc(100vh - 116px);
            background: #fff;
            border: 1px solid #dfe3ea;
            border-radius: 14px;
            box-shadow: 0 8px 28px rgba(16, 24, 40, 0.07);
        }

        .catalog-desktop-filter-header {
            display: flex;
            align-items: center;
            min-height: 82px;
            padding: 17px 18px;
        }

        .catalog-desktop-filter-body {
            max-height: calc(100vh - 198px);
            padding: 18px;
            overflow-y: auto;
            scrollbar-width: thin;
            scrollbar-color: #d5dae3 transparent;
        }

        .catalog-desktop-filter-body::-webkit-scrollbar {
            width: 4px;
        }

        .catalog-desktop-filter-body::-webkit-scrollbar-thumb {
            background: #d5dae3;
            border-radius: 999px;
        }

        .catalog-standard-drawer .catalog-drawer-filter-shell {
            padding: 0;
        }

        .catalog-standard-drawer .product-filters-wrapper {
            padding: 0;
        }

        .catalog-standard-drawer .filter-title {
            display: none;
        }

        .catalog-standard-drawer .filter-group {
            margin-bottom: 24px !important;
        }

        .catalog-standard-drawer .filter-label {
            margin-bottom: 9px;
            color: #344054;
            font-size: 0.69rem;
            letter-spacing: 0.07em;
        }

        .catalog-standard-drawer .filter-search-input {
            height: 54px;
            padding: 0 44px 0 15px;
            border: 1px solid #d7dee9;
            border-radius: 12px;
            background: #fff;
            color: #344054;
            font-size: 0.82rem;
            letter-spacing: 0;
        }

        .catalog-standard-drawer .filter-search-input:focus {
            border-color: #98a2b3;
            box-shadow: 0 0 0 3px rgba(41, 112, 255, 0.08);
        }

        .catalog-standard-drawer .filter-search-icon {
            top: 50%;
            right: 16px;
            color: #98a2b3;
            font-size: 0.8rem;
            transform: translateY(-50%);
        }

        .catalog-standard-drawer .filter-link {
            color: #667085;
            font-size: 0.72rem;
            letter-spacing: 0.02em;
            line-height: 1.55;
        }

        .catalog-standard-drawer .filter-link-sub,
        .catalog-standard-drawer .filter-link-child,
        .catalog-standard-drawer .filter-link-brand {
            font-size: 0.69rem;
        }

        .catalog-standard-drawer .filter-link:hover,
        .catalog-standard-drawer .filter-link.active {
            color: #101828;
        }

        .catalog-standard-drawer .brand-filter-scroll {
            max-height: 290px;
        }

        @media (max-width: 575.98px) {
            .catalog-products-section {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            .catalog-result-summary span {
                display: inline;
            }

            .catalog-sort-toolbar {
                justify-content: stretch;
                padding: 9px;
            }

            .catalog-sort-toolbar .toolbar-control:first-of-type {
                flex: 1 1 auto;
            }

            .catalog-sort-toolbar .toolbar-select {
                width: 100%;
                min-width: 0;
                font-size: .7rem;
            }

            .catalog-sort-toolbar .toolbar-control:last-of-type {
                flex: 0 0 78px;
            }
        }

        @media (max-width: 767.98px) {
            .catalog-toolbar.search-toolbar {
                display: flex !important;
                flex-wrap: nowrap;
                gap: 0.75rem;
            }

            .catalog-toolbar .search-filter-button {
                width: auto;
                flex: 1 1 auto;
                justify-content: center;
            }

            .catalog-toolbar .catalog-result-summary {
                flex: 0 0 auto;
                white-space: nowrap;
            }
        }

        @media (max-width: 380px) {
            .catalog-toolbar.search-toolbar {
                flex-wrap: wrap;
            }

            .catalog-toolbar .search-filter-button {
                width: 100%;
                flex-basis: 100%;
            }

            .catalog-toolbar .catalog-result-summary {
                width: 100%;
                justify-content: flex-end;
            }
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
