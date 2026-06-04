@extends('layout.layout')

@section('content')
    <div class="product-page-wrapper">
        <div class="container-fluid px-lg-5 py-4">
            {{-- Breadcrumb Dinâmico e Completo --}}
                <nav aria-label="breadcrumb" class="mb-4">
                    <ol class="breadcrumb x-small text-uppercase">
                        <li class="breadcrumb-item"><a href="/" class="text-muted text-decoration-none">{{ __('messages.home') }}</a></li>
                        @if($product->category)
                            <li class="breadcrumb-item">
                                <a href="{{ route('categories.show', $product->category->slug) }}" class="text-muted text-decoration-none">{{ $product->category->name }}</a>
                            </li>
                        @endif
                        @if($product->subcategory)
                            <li class="breadcrumb-item">
                                <a href="{{ route('subcategories.show', $product->subcategory->slug) }}" class="text-muted text-decoration-none">{{ $product->subcategory->name }}</a>
                            </li>
                        @endif
                        
                        @php
                            $currentLocale = app()->getLocale(); 
                            
                            $translation = $product->translations->where('locale', $currentLocale)->first();
                            
                            $displayName = ($translation && !empty($translation->name)) 
                                ? $translation->name 
                                : $product->name;
                        @endphp

                        <li class="breadcrumb-item active text-dark fw-bold" aria-current="page">{{ $displayName }}</li>
                    </ol>
                </nav>

            <div class="row g-5">
                {{-- COLUNA ESQUERDA: Galeria de Imagens --}}
                <div class="col-lg-6">
                    <div class="row g-2">
                        @php
                            $mainImage = $product->photo
                                ? Storage::url($product->photo)
                                : asset('storage/uploads/noimage.webp');
                            $gallery = is_string($product->gallery)
                                ? json_decode($product->gallery, true)
                                : ($product->gallery ?: []);
                        @endphp

                        <div class="col-12 mb-2">
                            <div class="gallery-frame position-relative">
                                @if($product->previous_price > $product->price)
                                    <span class="badge bg-danger position-absolute m-3 px-3 py-2 text-uppercase" style="z-index: 10; font-size: 10px; letter-spacing: 1px;">Sale</span>
                                @endif
                                <img src="{{ $mainImage }}" class="img-fluid w-100 main-product-image" alt="{{ $product->name }}">
                            </div>
                        </div>

                        @foreach ($gallery as $img)
                            <div class="col-6">
                                <div class="gallery-frame">
                                    <img src="{{ Storage::url($img) }}" class="img-fluid w-100" alt="Detail {{ $product->name }}">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- COLUNA DIREITA: Informações e Compra --}}
                <div class="col-lg-6">
                    <div class="product-sticky-info ps-lg-4">
                        {{-- Marca e Tag de Estoque --}}
                        <div class="d-flex justify-content-between align-items-center mb-2">
                            <div class="brand-name text-uppercase fw-bold text-muted" style="letter-spacing: 2px; font-size: 0.85rem;">
                                {{ $product->brand->name ?? 'Luxury Selection' }}
                            </div>
                            @if($product->stock > 0 && $product->stock <= 5)
                                <span class="text-danger extra-small fw-bold text-uppercase">
                                <i class="fas fa-exclamation-circle"></i>{{ __('messages.unicas_unidades', ['count' => $product->stock]) }}</span>
                            @elseif($product->stock > 5)
                                <span class="text-success extra-small fw-bold text-uppercase"><i class="fas fa-check"></i>{{ __('messages.em_estoque') }}</span>
                            @endif
                        </div>

                        <h1 class="product-title h3 text-uppercase mb-1 fw-light" style="letter-spacing: 1px;">{{ $product->name }}</h1>
                        <p class="text-muted extra-small mb-4">REF: {{ $product->sku }} | Vistas: {{ number_format($product->views) }}</p>

                        {{-- Preço com lógica de promoção --}}
                        <div class="product-price-wrapper mb-4">
                            @if($product->previous_price > $product->price)
                                <span class="text-muted text-decoration-line-through me-2 h6 fw-light">{{ currency_format($product->previous_price) }}</span>
                            @endif
                            <span class="product-price h4 fw-bold text-dark">
                                {{ currency_format($product->price) }}
                            </span>
                        </div>

                        {{-- Seleção de Cores --}}
                        <div class="color-selection-wrapper mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="section-label text-uppercase extra-small fw-bold">{{ __('messages.cor') }}: <span class="text-muted fw-normal">{{ $product->color }}</span></span>
                            </div>
                            <div class="color-grid d-flex flex-wrap gap-2">
                                @if (isset($colorSiblings) && $colorSiblings->count() > 0)
                                    @foreach ($colorSiblings as $colorSib)
                                        <a href="{{ route('produto.show', $colorSib->slug ?? $colorSib->id) }}" 
                                        class="color-box-link {{ $product->id == $colorSib->id ? 'active' : '' }}"
                                        title="{{ $colorSib->color }}">
                                            <div class="color-dot" style="background-color: {{ $colorSib->color ?? '#ccc' }};"></div>
                                        </a>
                                    @endforeach
                                @else
                                    <div class="color-box-link active">
                                        <div class="color-dot" style="background-color: {{ $product->color ?? '#ccc' }};"></div>
                                    </div>
                                @endif
                            </div>
                        </div>

                        {{-- Seleção de Tamanhos --}}
                        <div class="size-selection-wrapper mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="section-label text-uppercase extra-small fw-bold">Tamanho</span>
                                <a href="#" class="text-muted text-decoration-underline extra-small" data-bs-toggle="modal" data-bs-target="#sizeGuideModal">{{ __('messages.guia_de_medidas') }}</a>
                            </div>
                            <div class="size-grid d-flex flex-wrap gap-2">
                                @if (isset($siblings) && $siblings->count() > 0)
                                    @foreach ($siblings as $sib)
                                        <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                            class="size-box text-decoration-none {{ $product->id == $sib->id ? 'active' : '' }} {{ $sib->stock <= 0 ? 'disabled' : '' }}">
                                            {{ $sib->size ?? 'U' }}
                                        </a>
                                    @endforeach
                                @else
                                    <div class="size-box active">{{ $product->size ?? 'U' }}</div>
                                @endif
                            </div>
                        </div>

                        {{-- Ações de Compra --}}
                        <div class="actions-wrapper mb-5">
                            <div class="d-flex gap-2">
                                @if($isBridal)
                                    <a href="https://wa.me/{{ config('settings.whatsapp') }}?text=Olá, gostaria de agendar uma consulta para o produto {{ $product->name }}" target="_blank" class="btn btn-outline-dark w-100 py-3 text-uppercase fw-bold" style="font-size: 12px; letter-spacing: 1px;">
                                        <i class="fab fa-whatsapp me-2"></i> {{ __('messages.agendar_consulta_bridal') }}
                                    </a>
                                @else
                                    @if (Auth::check())
                                        <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <button type="submit" class="btn btn-dark btn-add-bag w-100 py-3 text-uppercase fw-bold" style="font-size: 12px; letter-spacing: 1px;"
                                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                {{ $product->stock > 0 ? __('messages.adicionar_ao_carrinho') : __('messages.esgotado') }}
                                            </button>
                                        </form>
                                        <button class="btn btn-outline-dark px-4 btn-wishlist">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}"
                                            class="btn btn-dark btn-add-bag flex-grow-1 text-center py-3 text-uppercase fw-bold js-requires-login"
                                            style="font-size: 12px; letter-spacing: 1px;"
                                            data-redirect-to="{{ url()->current() }}">
                                            {{ __('messages.login_para_comprar') }}
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>
                        
                            {{-- Accordion Detalhes --}}
                            <div class="product-details-accordion border-top">
                                <div class="accordion-item-sax">
                                    <div class="accordion-trigger">{{ __('messages.descricao_produto') }} <i class="fas fa-plus small"></i></div>
                                    <div class="accordion-content show">
                                        <div class="rich-text-content">
                                            @php

                                                $currentLocale = app()->getLocale(); 
                                                
                                                $translation = $product->translations->where('locale', $currentLocale)->first();
                                                
                                                $displayDescription = ($translation && !empty($translation->details)) 
                                                    ? $translation->details 
                                                    : $product->description;
                                            @endphp
                                            
                                            {!! $displayDescription !!}
                                        </div>
                                    </div>
                                </div>
                            </div>

                            @if ($product->attributes)
                                <div class="accordion-item-sax border-bottom py-3">
                                    <div class="accordion-trigger d-flex justify-content-between align-items-center fw-bold text-uppercase" style="cursor:pointer; font-size: 0.75rem; letter-spacing: 1px;">
                                        {{ __('messages.detalhes_tecnicos') }} <i class="fas fa-plus small text-muted"></i>
                                    </div>
                                    <div class="accordion-content pt-3" style="display: none;">
                                        <table class="table table-sm table-borderless m-0 x-small text-muted">
                                            @foreach (json_decode($product->attributes, true) as $key => $value)
                                                <tr>
                                                    <td class="ps-0 fw-bold text-uppercase">{{ $key }}:</td>
                                                    <td class="text-end pe-0">{{ $value }}</td>
                                                </tr>
                                            @endforeach
                                        </table>
                                    </div>
                                </div>
                            @endif
                        </div>

                        {{-- Disponibilidade em Lojas --}}
                        <div class="store-availability mt-5 p-4 bg-light">
                            <div class="section-label mb-3 text-uppercase fw-bold" style="font-size: 0.7rem; color: #1a1a1a; letter-spacing: 1px;">
                                <i class="fas fa-store me-2"></i> {{ __('messages.disponivel_retirada') }}
                            </div>

                            @php
                                $selectedStores = is_array($product->stores) ? $product->stores : json_decode($product->stores, true) ?? [];
                                $allStores = [
                                    'asuncion' => 'Sax Asunción',
                                    'cde' => 'Sax Ciudad Del Este',
                                    'pjc' => 'Sax Pedro Juan Caballero',
                                ];
                            @endphp

                            <div class="row g-2">
                                @foreach ($allStores as $key => $label)
                                    <div class="col-12 d-flex align-items-center" style="font-size: 0.8rem;">
                                        @if (in_array($key, $selectedStores))
                                            <span class="text-dark"><i class="fas fa-check-circle text-dark me-2"></i> {{ $label }}</span>
                                        @else
                                            <span class="text-muted" style="opacity: 0.5;"><i class="far fa-circle me-2"></i> {{ $label }}</span>
                                        @endif
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- PRODUTOS SIMILARES --}}
        @if (isset($similares) && $similares->isNotEmpty())
            <section class="sax-section-container py-5 border-top">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4 text-uppercase fw-light" style="letter-spacing: 2px;">{{ __('messages.artigos_similares') }}</h2>
                    <div class="swiper productSwiper">
                        <div class="swiper-wrapper">
                            @foreach ($similares as $item)
                                <div class="swiper-slide">
                                    @include('home-components.product-card', ['item' => $item])
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

                {{-- MAIS VISTOS --}}
        @if (isset($mostViewed) && $mostViewed->isNotEmpty())
            <section class="sax-section-container py-5 border-top bg-light">
                <div class="container-fluid px-lg-5">
                    <h2 class="sax-section-title mb-4">{{ __('messages.mais_vistos') }}</h2>
                    <div class="swiper productSwiper">
                        <div class="swiper-wrapper">
                            @foreach ($mostViewed as $item)
                                @include('home-components.product-card', ['item' => $item])
                            @endforeach
                        </div>
                    </div>
                </div>
            </section>
        @endif

        @include('home-components.form-home')
    </div>

    <style>
        .breadcrumb-item + .breadcrumb-item::before { content: "/"; font-size: 10px; color: #ccc; }
        .gallery-frame { overflow: hidden; background: #f9f9f9; transition: all 0.3s ease; }
        .gallery-frame img { transition: transform 0.5s ease; cursor: zoom-in; }
        .gallery-frame:hover img { transform: scale(1.05); }
        
        .color-box-link { width: 32px; height: 32px; border-radius: 50%; border: 1px solid #ddd; padding: 2px; transition: all 0.2s; }
        .color-box-link.active { border-color: #000; }
        .color-dot { width: 100%; height: 100%; border-radius: 50%; }
        
        .size-box { border: 1px solid #eee; padding: 8px 15px; min-width: 45px; text-align: center; font-size: 0.75rem; color: #555; transition: all 0.2s; cursor: pointer; }
        .size-box:hover { border-color: #000; color: #000; }
        .size-box.active { background: #000; border-color: #000; color: #fff; }
        .size-box.disabled { opacity: 0.3; cursor: not-allowed; text-decoration: line-through; }
        
        .extra-small { font-size: 0.65rem; letter-spacing: 0.5px; }
        .btn-add-bag { transition: background 0.3s ease; }
        .btn-add-bag:hover { background: #333; }
    </style>

    <script>
        // Script simples para o accordion manual
        document.querySelectorAll('.accordion-trigger').forEach(trigger => {
            trigger.addEventListener('click', () => {
                const content = trigger.nextElementSibling;
                const icon = trigger.querySelector('i');
                const isOpen = content.style.display === 'block';
                
                content.style.display = isOpen ? 'none' : 'block';
                icon.classList.toggle('fa-plus', isOpen);
                icon.classList.toggle('fa-minus', !isOpen);
            });
        });
    </script>
@endsection