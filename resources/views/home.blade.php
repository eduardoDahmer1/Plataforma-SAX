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
                    <div class="sax-product-grid">
                        @foreach ($productsLanc as $item)
                            @include('home-components.product-card', ['item' => $item])
                        @endforeach
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
                    <div class="sax-product-grid">
                        @foreach ($productsDest as $item)
                            @include('home-components.product-card', ['item' => $item])
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        {{-- 7. Slider de Marcas --}}
        @if(isset($brands) && $brands->count() > 0)
            <div class="sax-brands-promo-full my-5">
                <div class="py-5 bg-black text-white text-center">
                    <h2 class="sax-brands-title">TUS MARCAS RECOMENDADAS</h2>
                </div>
                @include('home-components.brands-grid')
            </div>
        @endif

        @include('home-components.form-home')
    </div>

    <style>
        .sax-home-wrapper { background-color: #fff; overflow-x: hidden; }
        
        /* GRADE DE PRODUTOS (Resolve o erro da imagem) */
        .sax-product-grid {
            display: grid;
            gap: 10px; /* Pequeno espaço entre cards */
            grid-template-columns: repeat(2, 1fr); /* Mobile: 2 cards */
        }

        @media (min-width: 768px) {
            .sax-product-grid { grid-template-columns: repeat(3, 1fr); }
        }

        @media (min-width: 992px) {
            .sax-product-grid { grid-template-columns: repeat(4, 1fr); }
        }

        @media (min-width: 1400px) {
            .sax-product-grid { grid-template-columns: repeat(5, 1fr); }
        }

        /* Títulos */
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

        .sax-brands-promo-full { background-color: #000; width: 100%; }
        .sax-brands-title {
            font-size: 2.2rem;
            font-weight: 400;
            letter-spacing: 4px;
            text-transform: uppercase;
        }

        /* Banners */
        .sax-triple-banners img {
            height: 450px;
            object-fit: cover;
            transition: transform 0.6s ease;
        }
        .sax-triple-banners img:hover { transform: scale(1.03); }

        @media (max-width: 768px) {
            .sax-section-title { font-size: 1.1rem; text-align: center; }
            .sax-brands-title { font-size: 1.4rem; }
            .sax-triple-banners img { height: auto; }
        }
    </style>
@endsection