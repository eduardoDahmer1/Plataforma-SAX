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

        @if (session('success') || session('error') || ($resumo['aviso'] ?? null))
            <div class="col-12">
                <div class="alert {{ session('error') || ($resumo['aviso'] ?? null) ? 'alert-danger' : 'alert-success' }} border-0 rounded-3 mb-0">
                    {{ session('error') ?? ($resumo['aviso'] ?? session('success')) }}
                </div>
            </div>
        @endif

        @if ($cart->count() > 0)
            @php
                $totalCarrinho = 0;
                $totalItens = 0;
                $cupon = $resumo['cupon'] ?? null;
                $itensComDesconto = $resumo['itens_elegiveis'] ?? [];
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
                                @if ($cupon && in_array($item->product_id, $itensComDesconto))
                                    <span class="sax-cart-item-cupon-tag">
                                        <i class="fas fa-tag me-1"></i>{{ $cupon->codigo }}
                                    </span>
                                @endif
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
                        <strong>{{ currency_format($resumo['subtotal']) }}</strong>
                    </div>

                    <div class="sax-summary-line">
                        <span>{{ __('messages.desconto') }}</span>
                        <strong class="text-success">- {{ currency_format($resumo['desconto']) }}</strong>
                    </div>

                    <div class="sax-summary-total-line">
                        <span>{{ __('messages.total') }}</span>
                        <strong>{{ currency_format($resumo['total']) }}</strong>
                    </div>

                    {{-- Cupom de desconto --}}
                    <div class="sax-cupon-box mt-4">
                        @if ($cupon)
                            <div class="sax-cupon-applied">
                                <div>
                                    <span class="sax-cupon-applied-label">{{ __('messages.cupon_aplicado_label') }}</span>
                                    <strong class="sax-cupon-applied-code">{{ $cupon->codigo }}</strong>
                                    <span class="sax-cupon-applied-rule">{{ $cupon->rotuloDesconto() }} · {{ $cupon->rotuloEscopo() }}</span>
                                </div>
                                <form action="{{ route('user.cupons.remove') }}" method="POST" class="m-0">
                                    @csrf
                                    <button type="submit" class="sax-cupon-remove" aria-label="{{ __('messages.cupon_remover_btn') }}">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </form>
                            </div>
                        @else
                            <form action="{{ route('user.cupons.apply') }}" method="POST" class="sax-cupon-form">
                                @csrf
                                <label for="cupon-codigo" class="sax-cupon-label">
                                    <i class="fas fa-tag me-1"></i>{{ __('messages.cupon_tem_codigo') }}
                                </label>
                                <div class="sax-cupon-input-group">
                                    <input type="text" id="cupon-codigo" name="codigo" maxlength="60" required
                                           class="sax-cupon-input text-uppercase"
                                           placeholder="{{ __('messages.cupon_placeholder') }}">
                                    <button type="submit" class="sax-cupon-btn">{{ __('messages.cupon_aplicar_btn') }}</button>
                                </div>
                            </form>
                        @endif
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

