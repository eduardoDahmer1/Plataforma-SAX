@extends('layout.layout')

@section('content')
    <div class="category-detail-wrapper">

        @php
            $bannerUrl = null;

            // 1. Tenta o banner específico da subcategoria (Pasta uploads)
            if ($subcategory->banner && Storage::disk('public')->exists('uploads/' . $subcategory->banner)) {
                $bannerUrl = asset('storage/uploads/' . $subcategory->banner);
            }
            // 2. Se vazio, tenta o banner10 global (Atributos)
            elseif (isset($attribute->banner10) && $attribute->banner10) {
                if (Storage::disk('public')->exists('uploads/' . $attribute->banner10)) {
                    $bannerUrl = asset('storage/uploads/' . $attribute->banner10);
                }
            }
            
            // 3. Fallback final para o banner_horizontal global
            if (!$bannerUrl && !empty($banner_horizontal)) {
                $bannerUrl = asset('img/' . $banner_horizontal);
            }
        @endphp

        @if ($bannerUrl)
            <div class="category-hero-fullwidth">
                <img src="{{ $bannerUrl }}" class="hero-img-render" alt="{{ $subcategory->name }}" onerror="this.src='{{ asset('img/banner_horizontal.webp') }}'">
                <div class="hero-overlay-soft"></div>
            </div>
        @else
            <div class="py-3"></div>
        @endif

        {{-- 2. ILHA DE IDENTIDADE --}}
        <div class="category-identity-section py-4 border-bottom bg-white">
            <div class="container text-center">
                <a href="{{ route('subcategories.index') }}" class="back-link-minimal">
                    <i class="fas fa-chevron-left me-1"></i> VOLVER A SUBCATEGORIAS
                </a>
                <div class="category-logo-container mt-3">
                    @if ($subcategory->photo && Storage::disk('public')->exists('uploads/' . $subcategory->photo))
                        <img src="{{ asset('storage/uploads/' . $subcategory->photo) }}" alt="{{ $subcategory->name }}"
                            class="category-main-logo">
                    @else
                        <h1 class="category-name-text">{{ $subcategory->name }}</h1>
                    @endif
                </div>
                {{-- Breadcrumb para Subcategoria --}}
                <div class="child-breadcrumb mt-2">
                    <span class="opacity-50">{{ $subcategory->category->name ?? '' }}</span>
                </div>
            </div>
        </div>

        <div class="container-fluid px-1 px-md-4 py-4 bg-white">
            <div class="row g-1">

                {{-- BANNER LATERAL --}}
                @if (isset($subcategory->image) && Storage::disk('public')->exists($subcategory->image))
                    <div class="col-12 col-lg-3 d-none d-lg-block">
                        <div class="sticky-banner-lateral">
                            <img src="{{ Storage::url($subcategory->image) }}" class="img-fluid banner-v-render"
                                alt="{{ $subcategory->name }} Promo">
                        </div>
                    </div>
                @endif

                {{-- GRID DE PRODUTOS --}}
                <div class="col-12 {{ isset($subcategory->image) && Storage::disk('public')->exists($subcategory->image) ? 'col-lg-9' : '' }}">
                    @if ($products->count())
                        <div class="row g-1">
                            @foreach ($products as $item)
                                <div class="col-6 col-md-4 {{ isset($subcategory->image) && Storage::disk('public')->exists($subcategory->image) ? 'col-xl-3' : 'col-xl-2' }}">
                                    <a href="{{ route('produto.show', $item->slug ?? $item->id) }}"
                                        class="text-decoration-none jw-product-link">
                                        <div class="card h-100 border-0 rounded-0 jw-product-card bg-transparent">

                                            <div class="jw-img-container position-relative bg-light">
                                                @if($item->photo && Storage::disk('public')->exists($item->photo))
                                                    <img src="{{ Storage::url($item->photo) }}" class="card-img-top img-fluid rounded-0" alt="{{ $item->name }}">
                                                @else
                                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" class="card-img-top img-fluid rounded-0" alt="No image">
                                                @endif
                                                
                                                <div class="position-absolute top-0 end-0 p-3 z-index-2">
                                                    @auth <x-product-favorite-button :item="$item" /> @endauth
                                                </div>
                                            </div>

                                            <div class="card-body px-1 py-4 text-center">
                                                <div class="jw-brand fw-bold text-uppercase mb-1">
                                                    {{ $item->brand->name ?? 'EXCLUSIVO' }}
                                                </div>
                                                <div class="jw-product-name text-muted mb-2">
                                                    {{ Str::limit($item->name, 40) }}
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

                        {{-- PAGINAÇÃO --}}
                        <div class="d-flex justify-content-center mt-5 pagination-sax">
                            {{ $products->links() }}
                        </div>
                    @else
                        <div class="text-center py-5">
                            <p class="text-muted text-uppercase tracking-widest small">No se encontraron productos en esta subcategoría.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection

<style>
    .category-detail-wrapper { background-color: #fff; overflow-x: hidden; }
    .tracking-widest { letter-spacing: 0.2em; }
    .category-hero-fullwidth { width: 100vw; height: 45vh; min-height: 300px; position: relative; overflow: hidden; margin-left: calc(-50vw + 50%); }
    .hero-img-render { width: 100%; height: 100%; object-fit: cover; object-position: center; }
    .hero-overlay-soft { position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0, 0, 0, 0.03); }
    .category-main-logo { max-height: 60px; width: auto; object-fit: contain; }
    .category-name-text { font-weight: 300; text-transform: uppercase; letter-spacing: 5px; color: #000; }
    .back-link-minimal { color: #999; font-size: 0.65rem; letter-spacing: 2px; text-decoration: none; text-transform: uppercase; transition: color 0.2s; }
    .back-link-minimal:hover { color: #000; }
    .sticky-banner-lateral { position: sticky; top: 100px; height: fit-content; padding-right: 10px; }
    .banner-v-render { width: 100%; height: auto; object-fit: cover; border: 1px solid #f0f0f0; }
    .g-1 { margin-right: -4px; margin-left: -4px; }
    .g-1 > [class*="col-"] { padding-right: 4px; padding-left: 4px; margin-bottom: 10px; }
    .jw-product-card { transition: opacity 0.3s ease; }
    .jw-product-link:hover .jw-product-card { opacity: 0.8; }
    .jw-img-container { aspect-ratio: 3 / 4; background-color: #fcfcfc; overflow: hidden; }
    .jw-img-container img { width: 100%; height: 100%; object-fit: cover; }
    .jw-brand { font-size: 0.7rem; letter-spacing: 1px; color: #000; }
    .jw-product-name { font-size: 0.75rem; color: #777 !important; height: 2.2rem; overflow: hidden; }
    .jw-price { font-size: 0.85rem; color: #000; }
    
    .pagination-sax .page-link { color: #000; border: none; background: transparent; font-size: 0.8rem; padding: 5px 10px; }
    .pagination-sax .page-item.active .page-link { background: none; text-decoration: underline; font-weight: bold; color: #000; }
    .pagination-sax .page-item.disabled .page-link { color: #ccc; }

    @media (max-width: 991px) {
        .category-hero-fullwidth { height: 35vh; }
        .col-lg-3 { display: none; }
    }
</style>