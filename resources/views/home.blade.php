@extends('layout.layout')

@section('content')
    @php
        $settings = $settings ?? \App\Models\Generalsetting::first();
        $highlightTitles = [
            'lancamentos' => __('messages.lancamentos'),
            'destaque' => __('messages.destacados'),
            'mais_vistos' => __('messages.mais_vistos'),
        ];

        $productsEditados = $lancamentos ?? collect();
        $productsMaisVistos = $mostViewed ?? collect();
        $productsDest = $highlights['destaque'] ?? collect();

        $showEditados = $settings->show_highlight_lancamentos ?? 0;
        $showMaisVistos = $settings->show_highlight_famosos ?? 0;
        $showDest = $settings->show_highlight_destaque ?? 0;

        $exclusiveCategories = collect($categories ?? [])->take(3);
        $exclusiveDescription = __('messages.exclusive_description');
        $stackedBanners = collect($homeEditorialBanners ?? [])->values()->map(fn ($banner, $index) => [
            'image' => $banner->image,
            'image_url' => $banner->image_url,
            'link' => $banner->link,
            'label' => [__('messages.selecao_curada'), __('messages.novidades_da_temporada'), __('messages.destaques_da_casa')][$index % 3],
        ]);
    @endphp

    <div class="sax-home-wrapper">
        @include('home-components.main-slider', ['limit' => 20])

        <x-alert type="success" :message="session('success')" />

        @include('components.cart-feedback-access-modal')

        @if (isset($categories) && $categories->count() > 0)
            @include('home-components.category-strip')
        @endif

        <section class="sax-exclusive-section py-5">
            <div class="container-fluid px-lg-5">
                <div class="exclusive-shell row g-4 align-items-stretch">
                    <div class="col-lg-5">
                        <div class="exclusive-panel h-100">
                            <span class="exclusive-eyebrow">{{ __('messages.curadoria_sax') }}</span>
                            <h2 class="exclusive-title">{{ __('messages.colecao_exclusiva') }}</h2>
                            <p class="exclusive-copy">{{ $exclusiveDescription }}</p>

                            @if ($exclusiveCategories->isNotEmpty())
                                <div class="exclusive-tags">
                                    @foreach ($exclusiveCategories as $category)
                                        <a href="{{ route('categories.show', $category->slug ?? $category->id) }}" class="exclusive-tag">
                                            {{ $category->name }}
                                        </a>
                                    @endforeach
                                </div>
                            @endif

                            <div class="exclusive-actions">
                                <a href="{{ route('categories.index') }}" class="exclusive-btn exclusive-btn--dark">{{ __('messages.explorar_colecao') }}</a>
                                <a href="{{ route('blogs.index') }}" class="exclusive-btn exclusive-btn--ghost">{{ __('messages.ver_editorial') }}</a>
                            </div>

                            <div class="exclusive-note">
                                <strong>{{ __('messages.selecao_com_intencao') }}</strong>
                                {{ __('messages.exclusive_note_text') }}
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-7">
                        <div class="exclusive-media-wrap h-100">
                            @if (isset($banner9) && $banner9)
                                @if (!empty($banner9_link))
                                    <a href="{{ $banner9_link }}" aria-label="{{ __('messages.abrir_banner_principal') }}">
                                        <img
                                            src="{{ asset('storage/uploads/' . $banner9) }}"
                                            class="img-fluid w-100 exclusive-media"
                                            alt="{{ __('messages.colecao_exclusiva_sax') }}"
                                            loading="lazy"
                                            decoding="async"
                                            onerror="this.style.display='none'"
                                        >
                                    </a>
                                @else
                                    <img
                                        src="{{ asset('storage/uploads/' . $banner9) }}"
                                        class="img-fluid w-100 exclusive-media"
                                        alt="{{ __('messages.colecao_exclusiva_sax') }}"
                                        loading="lazy"
                                        decoding="async"
                                        onerror="this.style.display='none'"
                                    >
                                @endif
                            @else
                                <div class="exclusive-media exclusive-media--placeholder">
                                    <div>
                                        <span class="exclusive-eyebrow">{{ __('messages.colecao_exclusiva') }}</span>
                                        <p class="mb-0">{{ __('messages.adicione_banner_destaque') }}</p>
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </section>

        @if ($showEditados && $productsEditados->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ __('messages.recentemente_atualizados') }}</h2>
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($productsEditados as $item)
                                @include('home-components.product-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <section class="sax-stacked-banners py-5" aria-label="Destaques editoriais SAX">
            <div class="container-fluid px-lg-5">
                @if ($stackedBanners->isNotEmpty())
                    <div class="stacked-banners-heading">
                        <div><span>{{ __('messages.curadoria_sax') }}</span><h2>{{ __('messages.destaques_da_casa') }}</h2></div>
                        @if($stackedBanners->count() > 1)
                            <div class="stacked-banner-controls">
                                <button type="button" class="editorial-prev" aria-label="Destaque anterior"><i class="fa-solid fa-arrow-left"></i></button>
                                <button type="button" class="editorial-next" aria-label="Próximo destaque"><i class="fa-solid fa-arrow-right"></i></button>
                            </div>
                        @endif
                    </div>
                    <div class="swiper editorialBannerSwiper">
                        <div class="swiper-wrapper">
                            @foreach ($stackedBanners as $banner)
                                @php $isExternal = filled($banner['link']) && !str_starts_with($banner['link'], url('/')) && !str_starts_with($banner['link'], '/'); @endphp
                                <div class="swiper-slide">
                                    <article class="stacked-banner-card">
                                        @if (!empty($banner['link']))
                                            <a href="{{ $banner['link'] }}" @if($isExternal) target="_blank" rel="noopener noreferrer" @endif aria-label="Abrir {{ $banner['label'] }}">
                                        @endif
                                            <img
                                                src="{{ $banner['image_url'] }}"
                                                alt="{{ $banner['label'] }}"
                                                width="1600" height="760" loading="lazy" decoding="async">
                                            <span class="stacked-banner-card__index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                            <span class="stacked-banner-card__overlay"><small>{{ __('messages.curadoria_sax') }}</small><strong>{{ $banner['label'] }}</strong>@if (!empty($banner['link']))<b>{{ __('messages.descobrir_btn') }} <i class="fa-solid fa-arrow-right"></i></b>@endif</span>
                                        @if (!empty($banner['link']))</a>@endif
                                    </article>
                                </div>
                            @endforeach
                        </div>
                        <div class="editorial-pagination"></div>
                    </div>
                @endif
            </div>
        </section>

        @if ($showMaisVistos && $productsMaisVistos->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ $highlightTitles['mais_vistos'] ?? __('messages.mais_vistos') }}</h2>
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($productsMaisVistos as $item)
                                @include('home-components.product-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @if ($showDest && $productsDest->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ $highlightTitles['destaque'] }}</h2>
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($productsDest as $item)
                                @include('home-components.product-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @include('home-components.brands-grid')
        @include('home-components.form-home')
    </div>

@endsection
