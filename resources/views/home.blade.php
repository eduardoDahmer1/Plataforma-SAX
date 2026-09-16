@extends('layout.layout')

@section('content')
    @php
        $settings = $settings ?? \App\Models\Generalsetting::first();
        $homeSections = $homeSections
            ?? ($settings?->resolvedHomeSections() ?? \App\Models\Generalsetting::defaultHomeSections());

        $productsEditados = $lancamentos ?? collect();
        $productsMaisVistos = $mostViewed ?? collect();
        $productsDest = $highlights['destaque'] ?? collect();

        $exclusiveCategories = collect($categories ?? [])->take(3);
        $stackedBanners = collect($homeEditorialBanners ?? [])->values()->map(fn ($banner, $index) => [
            'image' => $banner->image,
            'image_url' => $banner->image_url,
            'mobile_image_url' => $banner->mobile_image_url,
            'link' => $banner->link,
            'label' => [__('messages.selecao_curada'), __('messages.novidades_da_temporada'), __('messages.destaques_da_casa')][$index % 3],
        ]);
    @endphp

    <div class="sax-home-wrapper">
        <x-alert type="success" :message="session('success')" />
        @include('components.cart-feedback-access-modal')

        @foreach ($homeSections as $sectionKey => $section)
            @continue(!($section['enabled'] ?? false))
            @php $sectionContent = \App\Models\Generalsetting::contentForLocale($section); @endphp

            @switch($sectionKey)
                @case('main_slider')
                    @include('home-components.main-slider', ['limit' => 20, 'sectionContent' => $sectionContent])
                    @break

                @case('categories')
                    @if (isset($categories) && $categories->isNotEmpty())
                        @include('home-components.category-strip', ['sectionContent' => $sectionContent])
                    @endif
                    @break

                @case('exclusive_collection')
                    @include('home-components.exclusive-collection', ['sectionContent' => $sectionContent])
                    @break

                @case('recent_products')
                    @include('home-components.product-carousel', [
                        'products' => $productsEditados,
                        'sectionContent' => $sectionContent,
                    ])
                    @break

                @case('editorial_banners')
                    @include('home-components.editorial-banners', ['sectionContent' => $sectionContent])
                    @break

                @case('most_viewed')
                    @include('home-components.product-carousel', [
                        'products' => $productsMaisVistos,
                        'sectionContent' => $sectionContent,
                    ])
                    @break

                @case('featured_products')
                    @include('home-components.product-carousel', [
                        'products' => $productsDest,
                        'sectionContent' => $sectionContent,
                    ])
                    @break

                @case('brands')
                    @include('home-components.brands-grid', ['sectionContent' => $sectionContent])
                    @break

                @case('help')
                    @include('home-components.help-section', ['sectionContent' => $sectionContent])
                    @break

                @case('newsletter')
                    @include('home-components.newsletter-section', ['sectionContent' => $sectionContent])
                    @break
            @endswitch
        @endforeach
    </div>

    <style>
        .sax-home-section-heading { margin-bottom:1.5rem; }
        .sax-home-section-heading h2 { margin:0; color:#171717; font-size:clamp(1.35rem,2vw,2rem); font-weight:500; letter-spacing:.04em; }
        .sax-home-section-heading p,.sax-section-description,.sax-editorial-description,.sax-brand-description { color:#667085; font-size:.86rem; line-height:1.6; }
        .sax-home-section-heading p { margin:.4rem 0 0; }
        .sax-product-section-heading { margin-bottom:1.5rem; }
        .sax-product-section-heading .sax-section-title { margin-bottom:0; }
        .sax-product-section-heading .sax-section-description { margin:.45rem 0 0; }
        .sax-editorial-description { margin:.35rem 0 0; }
        .sax-slider-copy { display:flex; max-width:min(680px,70vw); flex-direction:column; align-items:flex-start; gap:.35rem; }
        .sax-luxury-slide__caption .sax-slider-copy em { font-size:clamp(.7rem,1vw,.88rem); font-style:normal; font-weight:400; letter-spacing:.02em; line-height:1.45; text-transform:none; }
        .sax-luxury-slide__caption > strong:only-child { margin-left:auto; }
        @media(max-width:767.98px){.sax-home-section-heading{text-align:center}.sax-slider-copy{max-width:88vw}.sax-luxury-slide__caption .sax-slider-copy em{display:none}}
    </style>
@endsection
