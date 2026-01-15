@php
    use App\Models\Cart;
    use App\Models\Currency;

    $user = auth()->user();
    $cart = $user ? Cart::with('product')->where('user_id', $user->id)->get() : collect();
    $cartCount = $cart->sum('quantity');

    // Pega a moeda da sessão
    $currencySession = session('currency');
    $currencyId = null;

    // Força pegar só o ID
    if (is_object($currencySession)) {
        $currencyId = $currencySession->id ?? null;
    } elseif (is_array($currencySession)) {
        $currencyId = $currencySession['id'] ?? ($currencySession[0] ?? null);
    } else {
        $currencyId = $currencySession;
    }

    // Busca a moeda
    $currency = Currency::find($currencyId);
    if (!$currency) {
        $currency = Currency::where('is_default', 1)->first();
    }

    // Valores fallback
    $symbol = $currency->sign ?? 'R$';
    $decimal = $currency->decimal_separator ?? ',';
    $thousand = $currency->thousands_separator ?? '.';
    $rate = $currency->value ?? 1;
@endphp


<div class="cart-wrapper">
    <button id="cart-button" class="cart-toggle-btn">
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
        <span class="cart-label">Carrinho</span>
    </button>

    <div id="cart-overlay" class="cart-overlay"></div>

    <div id="cart-sidebar" class="cart-sidebar">
        <div class="cart-header">
            <div class="header-title">
                <span class="fw-bold">ITENS NO CARRINHO</span>
                <span class="items-count">{{ $cartCount }} Itens</span>
            </div>
            <button id="cart-close" class="close-drawer">&times;</button>
        </div>

        <div class="cart-body">
            @if ($cartCount > 0)
                <div class="cart-items-list">
                    @foreach ($cart as $item)
                        @php
                            $basePrice = $item->product->price ?? 0;
                            $convertedPrice = $basePrice * $rate;
                            $formattedPrice = $symbol . ' ' . number_format($convertedPrice, 2, $decimal, $thousand);
                        @endphp
                        <div class="cart-item">
                            <div class="item-image">
                                <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" alt="{{ $item->product->name }}">
                            </div>
                            <div class="item-details">
                                <a href="#" class="item-name">{{ $item->product->external_name ?? $item->product->name }}</a>
                                <span class="item-price">{{ $formattedPrice }}</span>
                                
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
                                            <button type="submit" class="qty-btn" @if($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>+</button>
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
                <div class="empty-cart-msg">
                    <p>Sua sacola está vazia.</p>
                </div>
            @endif
        </div>

        @if ($cartCount > 0)
            <div class="cart-footer">
                <a href="{{ route('cart.view') }}" class="btn-go-to-cart">IR PARA O CARRINHO</a>
            </div>
        @endif
    </div>
</div>

{{-- CSS custom --}}
<style>
/* Container Principal */
.cart-wrapper { font-family: 'Inter', sans-serif; }

/* Botão de abrir (Estilo Header) */
.cart-toggle-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 8px 12px;
    cursor: pointer;
    transition: 0.3s;
}

.cart-toggle-btn .icon-container { position: relative; }

.cart-badge {
    position: absolute;
    top: -5px;
    right: -8px;
    background: #000;
    color: #fff;
    font-size: 10px;
    padding: 2px 6px;
    border-radius: 10px;
    font-weight: bold;
}

.cart-label { font-size: 13px; text-transform: uppercase; letter-spacing: 1px; }

/* Sidebar (Side Drawer) */
.cart-sidebar {
    position: fixed;
    top: 0;
    right: -100%; /* Começa fora da tela */
    width: 400px;
    height: 100vh;
    background: #fff;
    z-index: 9999;
    transition: right 0.4s cubic-bezier(0.25, 0.46, 0.45, 0.94);
    display: flex;
    flex-direction: column;
    box-shadow: -10px 0 30px rgba(0,0,0,0.05);
}

.cart-sidebar.open { right: 0; }

/* Mobile full width */
@media (max-width: 576px) {
    .cart-sidebar { width: 100%; }
}

/* Overlay */
.cart-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.4);
    z-index: 9998;
    display: none;
    backdrop-filter: blur(2px);
}

.cart-overlay.open { display: block; }

/* Header do Carrinho */
.cart-header {
    padding: 25px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    border-bottom: 1px solid #eee;
}

.header-title .items-count { display: block; font-size: 11px; color: #888; margin-top: 4px; }

.close-drawer {
    background: none;
    border: none;
    font-size: 30px;
    font-weight: 200;
    cursor: pointer;
}

/* Corpo e Itens */
.cart-body { flex: 1; overflow-y: auto; padding: 20px; }

.cart-item {
    display: flex;
    gap: 15px;
    padding-bottom: 20px;
    margin-bottom: 20px;
    border-bottom: 1px solid #f9f9f9;
}

.item-image img {
    width: 80px;
    height: 100px;
    object-fit: contain;
    background: #f8f8f8;
}

.item-details { flex: 1; display: flex; flex-direction: column; }

.item-name {
    font-size: 13px;
    color: #000;
    text-decoration: none;
    font-weight: 500;
    text-transform: uppercase;
    margin-bottom: 5px;
}

.item-price { font-size: 14px; font-weight: 600; margin-bottom: 15px; }

/* Controles de Qtd */
.item-controls { display: flex; align-items: center; }

.quantity-selector {
    display: flex;
    align-items: center;
    border: 1px solid #eee;
}

.qty-btn {
    background: none;
    border: none;
    width: 30px;
    height: 30px;
    cursor: pointer;
}

.qty-number { padding: 0 10px; font-size: 12px; }

.remove-item-btn {
    background: none;
    border: none;
    color: #ccc;
    cursor: pointer;
    transition: 0.3s;
}

.remove-item-btn:hover { color: #d9534f; }

/* Footer */
.cart-footer { padding: 25px; border-top: 1px solid #eee; }

.btn-go-to-cart {
    display: block;
    width: 100%;
    background: #000;
    color: #fff;
    text-align: center;
    padding: 15px;
    text-decoration: none;
    font-size: 12px;
    letter-spacing: 2px;
    font-weight: bold;
    transition: 0.3s;
}

.btn-go-to-cart:hover { background: #333; }
</style>

{{-- Script Carrinho --}}
<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartBtn = document.getElementById('cart-button');
    const cartSidebar = document.getElementById('cart-sidebar');
    const cartOverlay = document.getElementById('cart-overlay');
    const cartClose = document.getElementById('cart-close');

    function toggleCart() {
        cartSidebar.classList.toggle('open');
        cartOverlay.classList.toggle('open');
        // Previne scroll no body quando aberto
        document.body.style.overflow = cartSidebar.classList.contains('open') ? 'hidden' : '';
    }

    cartBtn?.addEventListener('click', (e) => {
        e.preventDefault();
        toggleCart();
    });

    cartClose?.addEventListener('click', toggleCart);
    cartOverlay?.addEventListener('click', toggleCart);
});
</script>
