@extends('layout.layout')

@section('content')
<div class="container mt-5 mb-5 sax-cart-page">
    <div class="row g-4">
        <div class="col-12">
            <div class="sax-cart-header">
                <h1 class="sax-cart-title">{{ __('messages.sua_sacola') }}</h1>
                <p class="sax-cart-subtitle">{{ $cart->count() }} {{ __('messages.itens_selecionados') }}</p>
            </div>
        </div>

        @if ($cart->count() > 0)
            @php
                $totalCarrinho = 0;
                $totalItens = 0;
            @endphp

            <div class="col-lg-8">
                <div class="sax-cart-list-panel">
                    @foreach ($cart as $item)
                        @php
                            $unitPrice = $item->product->price ?? 0;
                            $itemTotal = $unitPrice * $item->quantity;
                            $totalCarrinho += $itemTotal;
                            $totalItens += $item->quantity;
                        @endphp

                        <article class="sax-cart-item-row">
                            <div class="sax-cart-item-media">
                                <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}"
                                     alt="{{ $item->product->external_name ?? 'Produto' }}"
                                     class="sax-cart-item-image">
                            </div>

                            <div class="sax-cart-item-content">
                                <p class="sax-cart-item-brand mb-1">{{ $item->product->brand->name ?? 'SAX EXCLUSIVE' }}</p>
                                <h2 class="sax-cart-item-name mb-2">{{ $item->product->external_name ?? 'Produto' }}</h2>

                                <div class="sax-cart-item-meta">
                                    <span>SKU: {{ $item->product->sku ?? '-' }}</span>
                                    <span>{{ __('messages.preco') }}: {{ currency_format($unitPrice) }}</span>
                                    <span>{{ __('messages.quantidade_abreviada') }}: {{ $item->quantity }}</span>
                                </div>

                                <div class="sax-cart-qty-area mt-3">
                                    <div class="quantity-control-sax">
                                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ max($item->quantity - 1, 1) }}">
                                            <button type="submit" class="qty-btn-sax" {{ $item->quantity <= 1 ? 'disabled' : '' }} aria-label="Diminuir quantidade">-</button>
                                        </form>

                                        <span class="qty-val-sax">{{ $item->quantity }}</span>

                                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                            @csrf
                                            @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" class="qty-btn-sax" {{ $item->quantity >= ($item->product->stock ?? 1) ? 'disabled' : '' }} aria-label="Aumentar quantidade">+</button>
                                        </form>
                                    </div>

                                    <form action="{{ route('cart.remove', $item->product_id) }}" method="POST" class="ms-2">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn-remove-sax">
                                            <i class="far fa-trash-alt me-1"></i>{{ __('messages.remover') }}
                                        </button>
                                    </form>
                                </div>
                            </div>

                            <div class="sax-cart-item-price-wrap">
                                <span class="sax-cart-item-price">{{ currency_format($itemTotal) }}</span>
                            </div>
                        </article>
                    @endforeach
                </div>

                <a href="{{ route('home') }}" class="sax-cart-continue-link">
                    <i class="fas fa-arrow-left me-2"></i>{{ __('messages.continuar_comprando') }}
                </a>
            </div>

            <div class="col-lg-4">
                <aside class="checkout-summary-sax sax-summary-sticky">
                    <h5>{{ __('messages.resumo_pedido') }}</h5>

                    <div class="sax-summary-line">
                        <span>{{ __('messages.itens_selecionados') }}</span>
                        <strong>{{ $totalItens }}</strong>
                    </div>

                    <div class="sax-summary-line">
                        <span>{{ __('messages.subtotal') }}</span>
                        <strong>{{ currency_format($totalCarrinho) }}</strong>
                    </div>

                    <div class="sax-summary-line">
                        <span>{{ __('messages.desconto') }}</span>
                        <strong class="text-success">- {{ currency_format(0) }}</strong>
                    </div>

                    <div class="sax-summary-total-line">
                        <span>{{ __('messages.total') }}</span>
                        <strong>{{ currency_format($totalCarrinho) }}</strong>
                    </div>

                    <div class="d-grid gap-2 mt-4">
                        <form action="{{ route('checkout.index') }}" method="GET">
                            <button type="submit" class="sax-btn-next w-100">
                                <i class="fas fa-lock me-2"></i>{{ __('messages.finalizar_compra') }}
                            </button>
                        </form>

                        <form action="{{ route('checkout.whatsapp') }}" method="GET">
                            <button type="submit" class="sax-btn-wa w-100">
                                <i class="fab fa-whatsapp me-2"></i>{{ __('messages.checkout_whatsapp') }}
                            </button>
                        </form>
                    </div>

                    <div class="sax-summary-security mt-4">
                        <img src="https://vignette.wikia.nocookie.net/logopedia/images/e/e3/Visa_2014.svg" alt="Visa" height="20">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" alt="Mastercard" height="20">
                        <span><i class="fas fa-shield-alt me-1"></i>{{ __('messages.compra_segura') }}</span>
                    </div>
                </aside>
            </div>
        @else
            <div class="col-12 text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-bag fa-4x text-secondary"></i>
                </div>
                <h2 class="fw-bold">{{ __('messages.sacola_vazia_titulo') }}</h2>
                <p class="text-muted">{{ __('messages.sacola_vazia_desc') }}</p>
                <a href="{{ route('home') }}" class="sax-btn-next mt-3 d-inline-flex align-items-center justify-content-center px-4">
                    {{ __('messages.voltar_loja') }}
                </a>
            </div>
        @endif
    </div>
</div>
@endsection

