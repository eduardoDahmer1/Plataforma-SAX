@php
    use App\Models\Cart;
    use App\Models\Currency;

    $user = auth()->user();
    $cart = $user ? Cart::with(['product.brand'])->where('user_id', $user->id)->get() : collect();
    $cartCount = $cart->sum('quantity');

    $totalGeral = 0;
    $currencySession = session('currency');
    $currencyId = null;

    if (is_object($currencySession)) {
        $currencyId = $currencySession->id ?? null;
    } elseif (is_array($currencySession)) {
        $currencyId = $currencySession['id'] ?? ($currencySession[0] ?? null);
    } else {
        $currencyId = $currencySession;
    }

    $currency = Currency::find($currencyId) ?: Currency::where('is_default', 1)->first();

    $symbol = $currency->sign ?? 'R$';
    $decimal = $currency->decimal_separator ?? ',';
    $thousand = $currency->thousands_separator ?? '.';
    $decimals = $currency->decimal_digits ?? 2;
    $rate = $currency->value ?? 1;
@endphp

<div class="cart-wrapper">
    {{-- Botão do Carrinho (Badge) --}}
    <button id="cart-button" class="cart-toggle-btn" aria-label="Abrir carrinho">
        <div class="icon-container">
            <svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                <path d="M6 2L3 6v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2V6l-3-4H6z"></path>
                <line x1="3" y1="6" x2="21" y2="6"></line>
                <path d="M16 10a4 4 0 0 1-8 0"></path>
            </svg>
            @if ($cartCount > 0)
                <span class="cart-badge">{{ $cartCount }}</span>
            @endif
        </div>
        <span class="cart-label">{{ __('messages.carrinho') }}</span>
    </button>

    <div id="cart-overlay" class="cart-overlay"></div>

    <div id="cart-sidebar" class="cart-sidebar">
        <div class="cart-header">
            <div class="header-title">
                <span class="fw-bold">{{ __('messages.itens_no_carrinho') }}</span>
                <span class="items-count">{{ $cartCount }} {{ __('messages.unidade_itens') }}</span>
            </div>
            <button id="cart-close" class="close-drawer">&times;</button>
        </div>

        <div class="cart-body">
            @if ($cartCount > 0)
                <div class="cart-items-list">
                    @foreach ($cart as $item)
                        @php
                            $productName = $item->product->external_name ?? $item->product->name ?? 'Produto';
                            $productBrand = $item->product->brand->name ?? 'SAX';
                            $productSize = trim((string) ($item->product->size ?? $item->product->product_size ?? ''));
                            $productColor = trim((string) ($item->product->color ?? ''));
                            $isHexColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $productColor) === 1;
                            $normalizedColor = $isHexColor ? ('#' . ltrim($productColor, '#')) : null;
                            $basePrice = $item->product->price ?? 0;
                            $convertedPrice = $basePrice * $rate;
                            $subtotalItem = $convertedPrice * $item->quantity;
                            $totalGeral += $subtotalItem;
                            $formattedPrice = $symbol . ' ' . number_format($convertedPrice, $decimals, $decimal, $thousand);
                            $formattedSubtotal = $symbol . ' ' . number_format($subtotalItem, $decimals, $decimal, $thousand);
                        @endphp
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}"
                                    alt="{{ $productName }}">
                            </div>
                            <div class="item-details">
                                <p class="item-brand">{{ $productBrand }}</p>
                                <a href="#" class="item-name text-decoration-none">{{ $productName }}</a>

                                <div class="item-meta-row">
                                    <span>SKU: {{ $item->product->sku ?? '-' }}</span>
                                    @if ($productSize !== '')
                                        <span>Tamanho: {{ $productSize }}</span>
                                    @endif
                                    @if ($productColor !== '')
                                        <span class="item-color-wrap">
                                            Cor:
                                            @if ($isHexColor)
                                                <i class="item-color-dot" style="--item-color: {{ $normalizedColor }};"></i>
                                                {{ $normalizedColor }}
                                            @else
                                                {{ $productColor }}
                                            @endif
                                        </span>
                                    @endif
                                </div>

                                <div class="item-prices-row">
                                    <span class="item-price">{{ $formattedPrice }}</span>
                                    <span class="item-subtotal">Subtotal: {{ $formattedSubtotal }}</span>
                                </div>

                                <div class="item-controls">
                                    <div class="quantity-selector">
                                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                            <button type="submit" class="qty-btn" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                        </form>
                                        <span class="qty-number">{{ $item->quantity }}</span>
                                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" class="qty-btn"
                                                @if ($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>+</button>
                                        </form>
                                    </div>
                                    <form action="{{ route('cart.remove', $item->product_id) }}" method="POST" class="ms-auto">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="remove-item-btn"><i class="fa fa-trash-o"></i></button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-cart-msg text-center mt-5">
                    <p>{{ __('messages.sacola_vazia') }}</p>
                </div>
            @endif
        </div>

        @if ($cartCount > 0)
            <div class="cart-footer border-top p-3 bg-white">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <span class="fw-bold">{{ __('messages.subtotal') }}:</span>
                    <span class="fw-bold h5 mb-0 text-dark cart-subtotal-value">
                        {{ $symbol . ' ' . number_format($totalGeral, $decimals, $decimal, $thousand) }}
                    </span>
                </div>
                <a href="{{ route('cart.view') }}" class="btn-go-to-cart w-100 py-3 text-uppercase">
                    {{ __('messages.ir_para_carrinho') }}
                </a>
            </div>
        @endif
    </div>
</div>