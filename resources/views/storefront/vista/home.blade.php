@extends('layout.layout')

@section('title', 'Vista & Co — Óptica')

@section('content')
@php
    $settings = $settings ?? \App\Models\Generalsetting::first();
    $homeSections = $homeSections ?? ($settings?->resolvedHomeSections() ?? \App\Models\Generalsetting::defaultHomeSections());
    $productsEditados = $lancamentos ?? collect();
    $productsMaisVistos = $mostViewed ?? collect();
    $productsDest = $highlights['destaque'] ?? collect();
    $vistaEditorialBanners = collect($homeEditorialBanners ?? [])->values()->map(fn ($banner, $index) => [
        'image' => $banner->image,
        'image_url' => $banner->image_url,
        'mobile_image_url' => $banner->mobile_image_url,
        'link' => match ($index) {
            0 => route('collections.show', ['collection' => 'new-arrivals']),
            1 => route('collections.show', ['collection' => 'trending']),
            default => $banner->link,
        },
        'title' => $banner->translated('title'),
        'label' => ['Recién llegados', 'Tendencias', 'Para mujer', 'Para hombre', 'Para niños'][$index % 5],
    ]);
@endphp

<div class="vista-home">
    <x-alert type="success" :message="session('success')" />
    @include('components.cart-feedback-access-modal')

    @foreach($homeSections as $sectionKey => $section)
        @continue(!($section['enabled'] ?? false))
        @php($sectionContent = \App\Models\Generalsetting::contentForLocale($section))
        @switch($sectionKey)
            @case('main_slider')
                @include('home-components.main-slider', ['limit' => 10, 'sectionContent' => $sectionContent])
                @break
            @case('categories')
                @include('storefront.vista.categories', ['sectionContent' => $sectionContent, 'section' => $section])
                @break
            @case('exclusive_collection')
                @include('storefront.vista.audience-grid', ['section' => $section])
                @break
            @case('recent_products')
                @include('storefront.vista.product-carousel', ['products' => $productsEditados, 'sectionContent' => $sectionContent])
                @break
            @case('editorial_banners')
                @include('storefront.vista.editorial-banners', ['banners' => $vistaEditorialBanners->take(2)])
                @break
            @case('most_viewed')
                @include('storefront.vista.product-carousel', ['products' => $productsMaisVistos, 'sectionContent' => $sectionContent])
                @break
            @case('featured_products')
                @include('storefront.vista.product-carousel', ['products' => $productsDest, 'sectionContent' => $sectionContent])
                @break
            @case('brands')
                @include('home-components.brands-grid', ['sectionContent' => $sectionContent])
                @break
            @case('help')
                @include('storefront.vista.help', ['sectionContent' => $sectionContent, 'section' => $section])
                @break
            @case('newsletter')
                @include('storefront.vista.newsletter', ['sectionContent' => $sectionContent])
                @break
        @endswitch
    @endforeach
</div>
@endsection
