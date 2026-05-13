@extends('layout.layout')

@section('content')
    <div class="category-detail-wrapper">
        @php
            $storagePath = 'uploads/';
            $bannerUrl = null;

            if ($subcategory->banner && Storage::disk('public')->exists($storagePath . $subcategory->banner)) {
                $bannerUrl = Storage::url($storagePath . $subcategory->banner);
            } elseif (isset($banner10) && $banner10 && Storage::disk('public')->exists($storagePath . $banner10)) {
                $bannerUrl = Storage::url($storagePath . $banner10);
            }

            if (!$bannerUrl && !empty($banner_horizontal)) {
                $bannerUrl = Storage::disk('public')->exists($storagePath . $banner_horizontal)
                    ? Storage::url($storagePath . $banner_horizontal)
                    : null;
            }

            $bannerLateralUrl = null;
            if (isset($subcategory->image) && Storage::disk('public')->exists($subcategory->image)) {
                $bannerLateralUrl = Storage::url($subcategory->image);
            } elseif (!empty($banner_horizontal)) {
                $bannerLateralUrl = Storage::disk('public')->exists($storagePath . $banner_horizontal)
                    ? Storage::url($storagePath . $banner_horizontal)
                    : null;
            }

            $fallbackImg = asset('storage/uploads/banner_horizontal.webp');
        @endphp

        @if ($bannerUrl)
            <div class="category-hero-fullwidth">
                <img src="{{ $bannerUrl }}" class="hero-img-render" alt="{{ $subcategory->name }}"
                    onerror="this.src='{{ $fallbackImg }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-3"></div>
        @endif

        <div class="category-identity-section py-4 border-bottom bg-white">
            <div class="container text-center">
                <a href="{{ route('subcategories.index') }}" class="back-link-minimal">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER A SUBCATEGORIAS
                </a>
                <div class="category-logo-container mt-3">
                    @if ($subcategory->photo && Storage::disk('public')->exists($storagePath . $subcategory->photo))
                        <img src="{{ Storage::url($storagePath . $subcategory->photo) }}" alt="{{ $subcategory->name }}"
                            class="category-main-logo">
                    @else
                        <h1 class="category-name-text">{{ $subcategory->name }}</h1>
                    @endif
                </div>
                <div class="child-breadcrumb mt-2">
                    <span class="opacity-50">{{ $subcategory->category->name ?? '' }}</span>
                </div>
            </div>
        </div>

        <div class="container-fluid px-1 px-md-4 py-4 bg-white">
            <div class="row g-1">
                @if ($bannerLateralUrl)
                    <div class="col-12 col-lg-3 d-none d-lg-block">
                        <div class="sticky-banner-lateral">
                            <img src="{{ $bannerLateralUrl }}" class="img-fluid banner-v-render"
                                alt="{{ $subcategory->name }} Promo" onerror="this.src='{{ $fallbackImg }}'">
                        </div>
                    </div>
                @endif

                <div class="col-12 {{ $bannerLateralUrl ? 'col-lg-9' : 'col-lg-12' }}">
                    @if ($products->count())
                        <div class="row g-1">
                            @foreach ($products as $item)
                                <div class="col-6 col-md-4 {{ $bannerLateralUrl ? 'col-xl-3' : 'col-xl-2' }}">
                                    <a href="{{ route('produto.show', $item->slug ?? $item->id) }}"
                                        class="text-decoration-none jw-product-link">
                                        <div class="card h-100 border-0 rounded-0 jw-product-card bg-transparent">
                                            <div class="jw-img-container position-relative bg-light">
                                                @if ($item->photo && Storage::disk('public')->exists($item->photo))
                                                    <img src="{{ Storage::url($item->photo) }}"
                                                        class="card-img-top img-fluid rounded-0" alt="{{ $item->name }}">
                                                @else
                                                    <img src="{{ asset('storage/uploads/noimage.webp') }}"
                                                        class="card-img-top img-fluid rounded-0" alt="No image">
                                                @endif
                                                <div class="position-absolute top-0 end-0 p-3 z-index-2">
                                                    @auth <x-product-favorite-button :item="$item" /> @endauth
                                                </div>
                                            </div>

                                            <div class="card-body px-2 py-3 d-flex flex-column">
                                                <div class="sax-brand fw-bold text-uppercase mb-1">
                                                    {{ $item->brand->name ?? 'BRAND NAME' }}
                                                </div>

                                                <div class="sax-product-name text-muted mb-3">
                                                    {{ $item->name ?? $item->external_name }}
                                                </div>

                                                <div class="d-flex justify-content-between align-items-center mt-auto">
                                                    <div class="sax-price fw-bold text-dark">
                                                        {{ isset($item->price) ? currency_format($item->price, 2, ',', '.') : '0,00' }}
                                                    </div>
                                                    <div class="sax-sku text-muted">
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
                            <p class="text-muted text-uppercase tracking-widest small">No se encontraron productos en esta
                                subcategoría.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
