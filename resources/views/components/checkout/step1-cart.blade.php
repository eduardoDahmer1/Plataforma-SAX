<div class="step active" id="step1">
    <div class="sax-checkout-box">
        <h4 class="sax-step-title">
            <span class="step-number">01</span> {{ __('messages.passo_itens_carrinho') }}
        </h4>

        <div class="sax-cart-list">
            @php
                $totalCarrinho = 0;
                $totalItens = 0;
            @endphp

            @foreach ($cart as $item)
                @php
                    $productSize = trim((string) ($item->product->size ?? $item->product->product_size ?? ''));
                    $productColor = trim((string) ($item->product->color ?? ''));
                    $isHexColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $productColor) === 1;
                    $normalizedColor = $isHexColor ? ('#' . ltrim($productColor, '#')) : null;
                    $subtotalItem = ($item->product->price ?? 0) * $item->quantity;
                    $totalCarrinho += $subtotalItem;
                    $totalItens += $item->quantity;
                @endphp

                <div class="sax-cart-item">
                    <div class="sax-cart-img-wrapper">
                        <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}"
                            alt="{{ $item->product->external_name ?? 'Produto' }}"
                            class="img-fluid">
                    </div>

                    <div class="sax-cart-item-main">
                        <span class="sax-item-brand">{{ $item->product->brand->name ?? 'SAX EXCLUSIVE' }}</span>
                        <h5 class="sax-item-name">{{ $item->product->external_name ?? 'Produto' }}</h5>

                        <div class="sax-item-meta">
                            <span>SKU: <strong>{{ $item->product->sku ?? '-' }}</strong></span>
                            <span>{{ __('messages.preco') }}: <strong>{{ currency_format($item->product->price ?? 0) }}</strong></span>
                            <span>{{ __('messages.quantidade_abreviada') }}: <strong>{{ $item->quantity }}</strong></span>
                            @if ($productSize !== '')
                                <span>Tamanho: <strong>{{ $productSize }}</strong></span>
                            @endif
                            @if ($productColor !== '')
                                <span class="sax-item-color-wrap">
                                    Cor:
                                    @if ($isHexColor)
                                        <i class="sax-item-color-dot" style="--item-color: {{ $normalizedColor }};"></i>
                                        <strong>{{ $normalizedColor }}</strong>
                                    @else
                                        <strong>{{ $productColor }}</strong>
                                    @endif
                                </span>
                            @endif
                        </div>
                    </div>

                    <div class="sax-cart-item-end">
                        <span class="sax-item-subtotal">{{ currency_format($subtotalItem) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sax-total-section mt-4">
            <span class="total-count">{{ $totalItens }} {{ __('messages.itens_selecionados') }}</span>
            <span class="total-label">{{ __('messages.subtotal') }}</span>
            <span class="total-value" id="subtotal-valor" data-valor="{{ $totalCarrinho }}">
                {{ currency_format($totalCarrinho) }}
            </span>
        </div>

        <button type="button" class="sax-btn-next mt-4" onclick="nextStep(1)">
            {{ __('messages.seguir_identificacao') }} <i class="fa fa-chevron-right ms-2"></i>
        </button>
    </div>
</div>