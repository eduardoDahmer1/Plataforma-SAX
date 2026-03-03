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
                <div class="col-lg-7">
                    <div class="row g-2">
                        @php
                            $mainImage = $product->photo
                                ? Storage::url($product->photo)
                                : asset('storage/uploads/noimage.webp');
                            $gallery = is_string($product->gallery)
                                ? json_decode($product->gallery, true)
                                : ($product->gallery ?:
                                []);
                        @endphp

                        {{-- Imagem Principal --}}
                        <div class="col-12 mb-2">
                            <div class="gallery-frame">
                                <img src="{{ $mainImage }}" class="img-fluid w-100" alt="{{ $product->external_name }}">
                            </div>
                        </div>

                        {{-- Grid de Galeria (2 colunas) --}}
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
                <div class="col-lg-5">
                    <div class="product-sticky-info">
                        <div class="brand-name">{{ $product->brand->name ?? 'Luxury Brand' }}</div>
                        <h1 class="product-title text-uppercase">{{ $product->external_name }}</h1>

                        <div class="product-price mb-4">
                            {{ currency_format($product->price) }}
                        </div>

                        {{-- Seleção de Tamanhos Dinâmica --}}
                        <div class="size-selection-wrapper mb-4">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="section-label">GUIA TALLAS</span>
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

                        {{-- Ações de Compra --}}
                        <div class="actions-wrapper mb-5">
                            <div class="d-flex gap-2">
                                @if (Auth::check())
                                    <form action="{{ route('cart.add') }}" method="POST" class="flex-grow-1">
                                        @csrf
                                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                                        <button type="submit" class="btn btn-dark btn-add-bag w-100"
                                            {{ $product->stock <= 0 ? 'disabled' : '' }}>
                                            {{ $product->stock > 0 ? 'AÑADIR A LA BOLSA' : 'AGOTADO' }}
                                        </button>
                                    </form>
                                                                    <button class="btn btn-outline-dark btn-wishlist">
                                    <i class="far fa-heart"></i>
                                </button>
                                @else
                                    <a href="{{ route('login') }}"
                                        class="btn btn-dark btn-add-bag flex-grow-1 text-center">LOGIN PARA COMPRAR</a>
                                @endif


                            </div>
                        </div>

                        {{-- Accordion de Informações --}}
                        <div class="product-details-accordion">
                            <div class="accordion-item-sax">
                                <div class="accordion-trigger">DESCRIPCIÓN DE PRODUCTO <i class="fas fa-plus small"></i>
                                </div>
                                <div class="accordion-content show">
                                    <div class="rich-text-content">
                                        {!! $product->description !!}
                                    </div>
                                </div>
                            </div>

                            @if ($product->attributes)
                                <div class="accordion-item-sax">
                                    <div class="accordion-trigger">DETALLES TÉCNICOS <i class="fas fa-plus small"></i></div>
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
                                <div class="accordion-trigger">ENVÍOS Y DEVOLUCIONES <i class="fas fa-plus small"></i></div>
                                <div class="accordion-content">
                                    <p>Consulte nuestros plazos de entrega y políticas de devolución en el checkout.</p>
                                </div>
                            </div>
                        </div>

                        {{-- Disponibilidade em Loja --}}
                        <div class="store-availability mt-5 pt-4 border-top">
                            <div class="section-label mb-3 text-uppercase fw-bold tracking-wider"
                                style="font-size: 0.75rem; color: #1a1a1a;">
                                <i class="fas fa-map-marker-alt me-2"></i> Disponible para retirar en tienda
                            </div>

                            @php
                                // Garante que stores seja um array, mesmo que venha nulo do banco
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

        {{-- SEÇÕES DE DESTAQUE DINÂMICAS --}}
        @php
            $highlightTitles = [
                'lancamentos' => 'RECIÉN LLEGADOS',
                'destaque' => 'ARTÍCULOS SIMILARES',
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
        <section class="help-section">
            <div class="help-grid">
                {{-- CARD: GUÍA DE COMPRA (Ícone Cabide) --}}
                <div class="help-card">
                    <div class="icon">
                        @if ($attribute && $attribute->icon_cabide)
                            <img src="{{ asset('storage/uploads/' . $attribute->icon_cabide) }}" alt="Compra"
                                width="30">
                        @else
                            👕
                        @endif
                    </div>
                    <h3>CÓMO REALIZAR UNA COMPRA</h3>
                    <p>Tu guía para hacer pedidos</p>
                </div>

                {{-- CARD: PREGUNTAS FRECUENTES (Ícone Ajuda/Dúvida) --}}
                <div class="help-card">
                    <div class="icon">
                        @if ($attribute && $attribute->icon_help)
                            <img src="{{ asset('storage/uploads/' . $attribute->icon_help) }}" alt="FAQ"
                                width="30">
                        @else
                            <span class="red-icon">?</span>
                        @endif
                    </div>
                    <h3>PREGUNTAS FRECUENTES</h3>
                    <p>¡Respondemos tus preguntas!</p>
                </div>

                {{-- CARD: NECESITAS AYUDA (Ícone Info/Relógio) --}}
                <div class="help-card">
                    <div class="icon">
                        @if ($attribute && $attribute->icon_info)
                            <img src="{{ asset('storage/uploads/' . $attribute->icon_info) }}" alt="Ayuda"
                                width="30">
                        @else
                            ⓘ
                        @endif
                    </div>
                    <h3>¿NECESITAS AYUDA?</h3>
                    <p>Contacta a nuestro equipo de Atención al Cliente</p>
                </div>
            </div>
        </section>
    </div>

    {{-- Swiper JS e Lógica Accordion --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Inicializa Swiper para os destaques
            new Swiper(".productSwiper", {
                slidesPerView: 2,
                spaceBetween: 20,
                navigation: {
                    nextEl: ".swiper-button-next",
                    prevEl: ".swiper-button-prev",
                },
                breakpoints: {
                    768: {
                        slidesPerView: 3
                    },
                    1024: {
                        slidesPerView: 5
                    }
                }
            });

            // Toggle Accordion SAX Style
            document.querySelectorAll('.accordion-trigger').forEach(trigger => {
                trigger.addEventListener('click', function() {
                    const content = this.nextElementSibling;
                    const icon = this.querySelector('i');

                    content.classList.toggle('show');

                    // Muda o ícone de + para -
                    if (content.classList.contains('show')) {
                        icon.classList.replace('fa-plus', 'fa-minus');
                    } else {
                        icon.classList.replace('fa-minus', 'fa-plus');
                    }
                });
            });
        });
    </script>

    <style>
        .store-availability .section-label {
            letter-spacing: 0.1em;
            font-family: 'Montserrat', sans-serif;
            /* Ou a fonte principal do seu site */
        }

        .store-list li {
            transition: all 0.2s ease;
        }

        /* Estilo para o ícone de "X" (Indisponível) */
        .text-muted {
            color: #999 !important;
        }

        /* Estilo para o ícone de "Check" (Disponível) */
        .text-success {
            color: #28a745 !important;
        }

        /* Estrutura Geral */
        .product-page-wrapper {
            background-color: #fff;
            font-family: 'Inter', sans-serif;
            color: #1a1a1a;
            overflow-x: hidden;
        }

        .x-small {
            font-size: 11px;
            letter-spacing: 1px;
        }

        .help-section {
            max-width: 1200px;
            margin: 40px auto;
            padding: 0 20px;
        }

        .help-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 20px;
        }

        /* Galeria */
        .gallery-frame {
            background-color: #f9f9f9;
            transition: 0.3s;
        }

        .gallery-frame img {
            mix-blend-mode: multiply;
        }

        /* Info do Produto */
        .product-sticky-info {
            position: sticky;
            top: 100px;
        }

        .brand-name {
            font-size: 24px;
            font-weight: 600;
            letter-spacing: 2px;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .product-title {
            font-size: 20px;
            font-weight: 300;
            color: #666;
            margin-bottom: 20px;
            letter-spacing: 1px;
        }

        .product-price {
            font-size: 22px;
            font-weight: 700;
        }

        .currency {
            font-size: 14px;
            color: #999;
            margin-left: 5px;
        }

        /* Seleção de Tamanhos */
        .section-label {
            font-size: 11px;
            font-weight: 700;
            color: #1a1a1a;
            letter-spacing: 1.5px;
        }

        .size-grid {
            display: flex;
            flex-wrap: wrap;
            border: 1px solid #eee;
            margin-top: 10px;
        }

        .size-box {
            flex: 1;
            min-width: 60px;
            text-align: center;
            padding: 12px 5px;
            border-right: 1px solid #eee;
            cursor: pointer;
            color: #1a1a1a;
            font-size: 12px;
            transition: 0.2s;
            border-bottom: 1px solid #eee;
        }

        .size-box:hover {
            background: #f8f8f8;
        }

        .size-box.active {
            background: #999;
            color: #fff;
            border-color: #999;
        }

        /* Botões */
        .btn-add-bag {
            background: #1a1a1a;
            border: none;
            border-radius: 0;
            padding: 18px;
            font-weight: 600;
            letter-spacing: 2px;
            font-size: 13px;
        }

        .btn-wishlist {
            border-radius: 0;
            width: 60px;
            border-color: #eee;
            color: #333;
        }

        .btn-wishlist:hover {
            background: #f8f8f8;
            border-color: #333;
        }

        .btn-guia-tallas {
            font-size: 10px;
            text-decoration: underline;
            color: #1a1a1a;
            font-weight: 700;
            letter-spacing: 1px;
        }

        /* Accordion Custom */
        .accordion-item-sax {
            border-top: 1px solid #eee;
        }

        .accordion-trigger {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 18px 0;
            font-size: 12px;
            font-weight: 700;
            cursor: pointer;
            color: #1a1a1a;
            letter-spacing: 1px;
        }

        .accordion-content {
            max-height: 0;
            overflow: hidden;
            transition: 0.4s ease;
            color: #666;
            font-size: 13px;
            line-height: 1.6;
        }

        .accordion-content.show {
            max-height: 1000px;
            padding-bottom: 20px;
        }

        .help-card {
            border: 1px solid #e0e0e0;
            border-radius: 8px;
            padding: 30px;
            text-align: left;
            background: #fff;
            transition: transform 0.3s ease;
        }

        .help-card:hover {
            transform: translateY(-5px);
        }

        .help-card .icon {
            font-size: 24px;
            margin-bottom: 15px;
        }

        .help-card .red-icon {
            color: #d9534f;
            font-weight: bold;
        }

        .help-card h3 {
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 8px;
            color: #000;
            letter-spacing: 0.5px;
        }

        .help-card p {
            font-size: 13px;
            color: #666;
            margin: 0;
        }

        /* Loja e WhatsApp */
        .store-list li {
            font-size: 12px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 12px;
            font-weight: 500;
        }

        .whatsapp-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            color: #1a1a1a;
            text-decoration: underline;
            font-weight: 700;
            font-size: 13px;
            letter-spacing: 0.5px;
        }

        /* Seções Inferiores */
        .sax-section-title {
            font-size: 16px;
            font-weight: 600;
            letter-spacing: 3px;
            border-bottom: 1px solid #eee;
            padding-bottom: 15px;
            text-transform: uppercase;
        }

        @media (max-width: 991px) {
            .product-sticky-info {
                position: static;
                margin-top: 30px;
            }

            .brand-name {
                font-size: 20px;
            }
        }

        /* --- RESPONSIVIDADE --- */
        @media (max-width: 992px) {
            .help-grid {
                grid-template-columns: 1fr 1fr;
            }
        }

        @media (max-width: 768px) {
            .help-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
@endsection
