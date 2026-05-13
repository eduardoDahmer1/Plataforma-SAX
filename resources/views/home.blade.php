@extends('layout.layout')

@section('content')
    @php
        $settings = $settings ?? \App\Models\Generalsetting::first();
        $highlightTitles = [
            'lancamentos' => __('messages.lancamentos'),
            'destaque' => __('messages.destacados'),
            'mais_vistos' => __('messages.mais_vistos'),
        ];
    @endphp

    <div class="sax-home-wrapper">
        
        {{-- 1. Slider Superior --}}
        @include('home-components.main-slider', ['limit' => 5])

        <x-alert type="success" :message="session('success')" />

        {{-- 2. Categorias --}}
        @if(isset($categories) && $categories->count() > 0)
            @include('home-components.category-strip')
        @endif

        {{-- 3. Seção Exclusiva --}}
        <section class="sax-exclusive-banner py-5">
            <div class="container-fluid px-lg-5">
                <div class="exclusive-content text-center">
                    <h2 class="sax-brands-title text-dark">{{ __('messages.colecao_exclusiva') }}</h2>
                    @if(isset($banner1) && $banner1)
                        <div class="mt-4">
                            <img src="{{ asset('storage/uploads/' . $banner1) }}" 
                                 class="img-fluid w-100" 
                                 alt="Exclusivo"
                                 onerror="this.style.display='none'">
                        </div>
                    @endif
                </div>
            </div>
        </section>

        {{-- 4. Seção: Recentemente Editados (Usando a variável do Controller) --}}
        @php 
            $showEditados = $settings->show_highlight_lancamentos ?? 0;
            // AJUSTE: O Controller envia como $lancamentos
            $productsEditados = $lancamentos ?? collect(); 
        @endphp

        @if ($showEditados && $productsEditados->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">RECENTEMENTE ATUALIZADOS</h2>
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

        {{-- 5. Seção de 3 Banners --}}
        <section class="sax-triple-banners py-5">
            <div class="container-fluid px-lg-5">
                <div class="row g-2">
                    <div class="col-md-4">
                        <img src="{{ asset('storage/uploads/' . ($banner2 ?? 'default.jpg')) }}" class="img-fluid w-100" onerror="this.src='https://placehold.co/600x800?text=Banner+2'">
                    </div>
                    <div class="col-md-4">
                        <img src="{{ asset('storage/uploads/' . ($banner3 ?? 'default.jpg')) }}" class="img-fluid w-100" onerror="this.src='https://placehold.co/600x800?text=Banner+3'">
                    </div>
                    <div class="col-md-4">
                        <img src="{{ asset('storage/uploads/' . ($banner4 ?? 'default.jpg')) }}" class="img-fluid w-100" onerror="this.src='https://placehold.co/600x800?text=Banner+4'">
                    </div>
                </div>
            </div>
        </section>

        {{-- 6. Seção MAIS VISTOS (Ajustado com swiper-slide) --}}
        @php 
            $keyMaisVistos = 'famosos'; 
            $showMaisVistos = $settings->{'show_highlight_' . $keyMaisVistos} ?? 0;
            $productsMaisVistos = $mostViewed ?? collect();
        @endphp

        @if ($showMaisVistos && $productsMaisVistos->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ $highlightTitles['mais_vistos'] ?? 'MAIS VISTOS' }}</h2>
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($productsMaisVistos as $item)
                                <div class="swiper-slide"> {{-- ADICIONADO --}}
                                    @include('home-components.product-card', ['item' => $item])
                                </div> {{-- ADICIONADO --}}
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        {{-- 7. Seção Destaques (Ajustado com swiper-slide) --}}
        @php 
            $keyDest = 'destaque';
            $productsDest = $highlights[$keyDest] ?? collect();
            $showDest = $settings->{'show_highlight_' . $keyDest} ?? 0;
        @endphp

        @if ($showDest && $productsDest->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ $highlightTitles[$keyDest] }}</h2>
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($productsDest as $item)
                                <div class="swiper-slide"> {{-- ADICIONADO --}}
                                    @include('home-components.product-card', ['item' => $item])
                                </div> {{-- ADICIONADO --}}
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        @endif

        @include('home-components.brands-grid')
        @include('home-components.form-home')
    </div>

    {{-- JS migrado a home.js --}}

@endsection