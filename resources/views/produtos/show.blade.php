@extends('layout.layout')

@section('content')
    <div class="product-page-wrapper">
        <div class="container-fluid px-lg-5 py-4">
            {{-- Breadcrumb minimalista --}}
            <nav aria-label="breadcrumb" class="mb-4">
                <ol class="breadcrumb x-small text-uppercase">
                    <li class="breadcrumb-item"><a href="/" class="text-muted">Home</a></li>
                    <li class="breadcrumb-item active text-dark">{{ $product->external_name }}</li>
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
                            <div class="gallery-frame">
                                <img src="{{ $mainImage }}" class="img-fluid w-100" alt="{{ $product->external_name }}">
                            </div>
                        </div>

                        @foreach ($gallery as $img)
                            <div class="col-6">
                                <div class="gallery-frame">
                                    <img src="{{ Storage::url($img) }}" class="img-fluid w-100" alt="Detail">
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- COLUNA DIREITA: Informações e Compra --}}
                <div class="col-lg-6">
                    <div class="product-sticky-info">
                        <div class="brand-name">{{ $product->brand->name ?? 'Luxury Brand' }}</div>
                        <h1 class="product-title text-uppercase">{{ $product->external_name }}</h1>
                        <p class="product-title text-uppercase">SKU: {{ $product->sku }}</p>

                        <div class="product-price mb-4">
                            {{ currency_format($product->price) }}
                        </div>

                        <div class="size-selection-wrapper mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="section-label">{{ __('messages.guia_tamanhos') }}</span>
                            </div>
                            <div class="size-grid">
                                @if (isset($siblings) && $siblings->count() > 0)
                                    @foreach ($siblings as $sib)
                                        <a href="{{ route('produto.show', $sib->slug ?? $sib->id) }}"
                                            class="size-box text-decoration-none {{ $product->id == $sib->id ? 'active' : '' }}">
                                            {{ $sib->size ?? 'U' }}
                                        </a>
                                    @endforeach
                                @else
                                    <div class="size-box active">{{ $product->size ?? 'U' }}</div>
                                @endif
                            </div>
                        </div>

                        <div class="actions-wrapper mb-5">
                            <div class="d-flex gap-2">
                                @if($isBridal)
                                    <a href="https://wa.me/SEU_NUMERO" class="btn btn-outline-dark w-100 py-3">
                                        <i class="fab fa-whatsapp me-2"></i> {{ __('messages.agendar_consulta_bridal') }}
                                    </a>
                                @else
                                    @if (Auth::check())
                                        <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                                            @csrf
                                            <input type="hidden" name="product_id" value="{{ $product->id }}">
                                            <button type="submit" class="btn btn-dark btn-add-bag w-100"
                                                {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                                {{ $product->stock > 0 ? __('messages.adicionar_ao_carrinho') : __('messages.esgotado') }}
                                            </button>
                                        </form>
                                        <button class="btn btn-outline-dark btn-wishlist">
                                            <i class="far fa-heart"></i>
                                        </button>
                                    @else
                                        <a href="{{ route('login') }}"
                                            class="btn btn-dark btn-add-bag flex-grow-1 text-center">
                                            {{ __('messages.login_para_comprar') }}
                                        </a>
                                    @endif
                                @endif
                            </div>
                        </div>

                        <div class="product-details-accordion">
                            <div class="accordion-item-sax">
                                <div class="accordion-trigger">{{ __('messages.descricao_produto') }} <i class="fas fa-plus small"></i></div>
                                <div class="accordion-content show">
                                    <div class="rich-text-content">
                                        {!! $product->description !!}
                                    </div>
                                </div>
                            </div>

                            @if ($product->attributes)
                                <div class="accordion-item-sax">
                                    <div class="accordion-trigger">{{ __('messages.detalhes_tecnicos') }} <i class="fas fa-plus small"></i></div>
                                    <div class="accordion-content">
                                        <table class="table table-sm table-borderless m-0 x-small">
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

                            <div class="accordion-item-sax">
                                <div class="accordion-trigger">{{ __('messages.envios_devolucoes') }} <i class="fas fa-plus small"></i></div>
                                <div class="accordion-content">
                                    <p>{{ __('messages.politica_envio_texto') }}</p>
                                </div>
                            </div>
                        </div>

                        <div class="store-availability mt-5 pt-4 border-top">
                            <div class="section-label mb-3 text-uppercase fw-bold tracking-wider" style="font-size: 0.75rem; color: #1a1a1a;">
                                <i class="fas fa-map-marker-alt me-2"></i> {{ __('messages.disponivel_retirada') }}
                            </div>

                            @php
                                $selectedStores = is_array($product->stores) ? $product->stores : [];
                                $allStores = [
                                    'asuncion' => 'Asunción',
                                    'cde' => 'Ciudad Del Este',
                                    'pjc' => 'Pedro Juan Caballero',
                                ];
                            @endphp

                            <ul class="list-unstyled store-list">
                                @foreach ($allStores as $key => $label)
                                    <li class="mb-2 d-flex align-items-center" style="font-size: 0.9rem; color: #444;">
                                        @if (in_array($key, $selectedStores))
                                            <i class="far fa-check-circle text-success me-2"></i>
                                            <span>{{ $label }}</span>
                                        @else
                                            <i class="far fa-times-circle text-muted me-2" style="opacity: 0.5;"></i>
                                            <span class="text-muted">{{ $label }}</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEÇÕES DE DESTAQUE --}}
        @php
            $highlightTitles = [
                'lancamentos' => __('messages.recem_chegados'),
                'destaque' => __('messages.artigos_similares'),
            ];
        @endphp

        @foreach (['destaque', 'lancamentos'] as $key)
            @php
                $prods = $highlights[$key] ?? collect();
                $show = $settings->{'show_highlight_' . $key} ?? 1;
            @endphp

            @if ($show && $prods->isNotEmpty())
                <section class="sax-section-container py-5 border-top {{ $key == 'lancamentos' ? 'bg-light' : '' }}">
                    <div class="container-fluid px-lg-5">
                        <h2 class="sax-section-title mb-4">{{ $highlightTitles[$key] }}</h2>
                        <div class="swiper productSwiper">
                            <div class="swiper-wrapper">
                                @foreach ($prods as $item)
                                    <div class="swiper-slide">
                                        @include('home-components.product-card', ['item' => $item])
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </section>
            @endif
        @endforeach
        @include('home-components.form-home')
    </div>
@endsection