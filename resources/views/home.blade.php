@extends('layout.layout')

@section('content')
    @php
        // 1. Definições Iniciais
        $settings = $settings ?? \App\Models\Generalsetting::first();
        $highlightTitles = [
            'lancamentos' => 'RECIÉN LLEGADOS',
            'destaque' => 'DESTACADOS',
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
                    <h2 class="sax-brands-title text-dark">Colección Exclusiva</h2>
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

        {{-- 4. Seção Lançamentos --}}
        @php 
            $keyLanc = 'lancamentos';
            $productsLanc = $highlights[$keyLanc] ?? collect(); 
            $showLanc = $settings->{'show_highlight_' . $keyLanc} ?? 0;
        @endphp

        @if ($showLanc && $productsLanc->isNotEmpty())
            <div class="sax-section-container py-4">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ $highlightTitles[$keyLanc] }}</h2>
                    
                    <div class="swiper mySwiper">
                        <div class="swiper-wrapper">
                            @foreach ($productsLanc as $item)
                                @include('home-components.product-card', ['item' => $item])
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
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

        {{-- 6. Seção Destaques --}}
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
                                @include('home-components.product-card', ['item' => $item])
                            @endforeach
                        </div>
                        <div class="swiper-button-next"></div>
                        <div class="swiper-button-prev"></div>
                    </div>
                </div>
            </div>
        @endif

        @include('home-components.brands-grid')
        @include('home-components.form-home')
    </div>

    {{-- SCRIPTS DE INICIALIZAÇÃO DO SLIDER --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const swiper = new Swiper(".mySwiper", {
                slidesPerView: 2,      // Padrão Mobile
                spaceBetween: 10,
                grabCursor: true,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    // Quando a tela for >= 768px (Tablet): 3 cards
                    768: {
                        slidesPerView: 3,
                        spaceBetween: 15
                    },
                    // Quando a tela for >= 1024px (Notebook): 4 cards
                    1024: {
                        slidesPerView: 4,
                        spaceBetween: 20
                    },
                    // Quando a tela for >= 1400px (Desktop Grande): 5 cards
                    1400: {
                        slidesPerView: 5,
                        spaceBetween: 20
                    }
                }
            });
        });
    </script>

    <style>
        .sax-home-wrapper { background-color: #fff; overflow-x: hidden; }
        
        /* Ajustes Swiper */
        .mySwiper {
            padding-bottom: 50px !important;
            width: 100%;
        }

        .swiper-button-next, .swiper-button-prev {
            color: #000 !important;
            background: rgba(255,255,255,0.9);
            width: 40px;
            height: 40px;
            border-radius: 50%;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            top: 40%; /* Centraliza melhor verticalmente na imagem */
        }
        
        .swiper-button-next:after, .swiper-button-prev:after {
            font-size: 18px;
            font-weight: bold;
        }

        /* Títulos das Seções */
        .sax-section-title {
            font-size: 1.4rem;
            font-weight: 500;
            color: #1a1a1a;
            text-transform: uppercase;
            letter-spacing: 2px;
            border-bottom: 1px solid #f0f0f0;
            padding-bottom: 15px;
            margin-bottom: 30px;
        }

        .sax-brands-title {
            font-size: 2.2rem;
            font-weight: 400;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        /* Banners Triplos */
        .sax-triple-banners img {
            height: 450px;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .sax-triple-banners img:hover { transform: scale(1.02); }

        @media (max-width: 768px) {
            .sax-section-title { font-size: 1.1rem; text-align: center; }
            .sax-brands-title { font-size: 1.4rem; }
            .sax-triple-banners img { height: auto; }
            .swiper-button-next, .swiper-button-prev { display: none; } /* Esconde setas no mobile para focar no touch */
        }
    </style>
@endsection