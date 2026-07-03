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
        $stackedBanners = collect([
            ['image' => $banner6 ?? null, 'link' => $banner6_link ?? null, 'label' => __('messages.selecao_curada')],
            ['image' => $banner7 ?? null, 'link' => $banner7_link ?? null, 'label' => __('messages.novidades_da_temporada')],
            ['image' => $banner8 ?? null, 'link' => $banner8_link ?? null, 'label' => __('messages.destaques_da_casa')],
        ])->filter(fn ($banner) => filled($banner['image']));
    @endphp

    <div class="sax-home-wrapper">
        @include('home-components.main-slider', ['limit' => 5])

        <x-alert type="success" :message="session('success')" />

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
                            @if (isset($banner1) && $banner1)
                                @if (!empty($banner1_link))
                                    <a href="{{ $banner1_link }}" target="_blank" rel="noopener noreferrer" aria-label="{{ __('messages.abrir_banner_principal') }}">
                                        <img
                                            src="{{ asset('storage/uploads/' . $banner1) }}"
                                            class="img-fluid w-100 exclusive-media"
                                            alt="{{ __('messages.colecao_exclusiva_sax') }}"
                                            onerror="this.style.display='none'"
                                        >
                                    </a>
                                @else
                                    <img
                                        src="{{ asset('storage/uploads/' . $banner1) }}"
                                        class="img-fluid w-100 exclusive-media"
                                        alt="{{ __('messages.colecao_exclusiva_sax') }}"
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
                                <div class="swiper-slide">
                                    @include('home-components.product-card', ['item' => $item])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <section class="sax-stacked-banners py-5">
            <div class="container-fluid px-lg-5">
                @if ($stackedBanners->isNotEmpty())
                    <div class="stacked-banners-grid">
                        @foreach ($stackedBanners as $banner)
                            <article class="stacked-banner-card">
                                <div class="stacked-banner-card__media">
                                    @if (!empty($banner['link']))
                                        <a href="{{ $banner['link'] }}" target="_blank" rel="noopener noreferrer" aria-label="Abrir {{ $banner['label'] }}">
                                            <img
                                                src="{{ asset('storage/uploads/' . $banner['image']) }}"
                                                class="img-fluid w-100"
                                                alt="{{ $banner['label'] }}"
                                                onerror="this.src='https://placehold.co/1400x680?text=SAX+Banner'"
                                            >
                                        </a>
                                    @else
                                        <img
                                            src="{{ asset('storage/uploads/' . $banner['image']) }}"
                                            class="img-fluid w-100"
                                            alt="{{ $banner['label'] }}"
                                            onerror="this.src='https://placehold.co/1400x680?text=SAX+Banner'"
                                        >
                                    @endif
                                </div>
                                <div class="stacked-banner-card__overlay">
                                    <span>{{ $banner['label'] }}</span>
                                </div>
                            </article>
                        @endforeach
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
                                <div class="swiper-slide">
                                    @include('home-components.product-card', ['item' => $item])
                                </div>
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
                                <div class="swiper-slide">
                                    @include('home-components.product-card', ['item' => $item])
                                </div>
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