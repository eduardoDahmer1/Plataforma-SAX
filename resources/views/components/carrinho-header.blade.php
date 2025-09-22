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

<div class="d-flex justify-content-center justify-content-md-end position-relative">
    <button id="cart-button" class="btn btn-outline-dark position-relative">
        <i class="fa fa-shopping-cart me-1"></i>
        <span>Carrinho</span>
        @if ($cartCount > 0)
            <span class="badge bg-danger rounded-pill position-absolute top-0 start-100 translate-middle">
                {{ $cartCount }}
            </span>
        @endif
    </button>

    @if ($cartCount > 0)
        <div id="cart-modal" class="cart-popup shadow-lg bg-white text-dark rounded-4"
            style="display: none; z-index: 1055;">

            <div class="d-flex justify-content-between align-items-center border-bottom pb-2 mb-3">
                <h6 class="fw-bold mb-0"><i class="fa fa-shopping-bag me-1"></i> Itens no carrinho</h6>
                <button id="cart-close" class="btn btn-sm btn-outline-danger rounded-circle">&times;</button>
            </div>

            <ul class="list-group list-group-flush small">
                @foreach ($cart as $item)
                    @php
                        $basePrice = $item->product->price ?? 0;
                        $convertedPrice = $basePrice * $rate;
                        $formattedPrice = $symbol . ' ' . number_format($convertedPrice, 2, $decimal, $thousand);

                        $convertedTotal = $basePrice * $item->quantity * $rate;
                        $formattedTotal = $symbol . ' ' . number_format($convertedTotal, 2, $decimal, $thousand);
                    @endphp
                    <li class="list-group-item">
                        <div class="d-flex justify-content-between align-items-start">

                            {{-- Imagem do Produto --}}
                            <div class="me-2">
                                <img src="{{ $item->product->photo_url ?? 'https://via.placeholder.com/60' }}"
                                    alt="{{ $item->product->title ?? $item->product->slug }}"
                                    class="img-thumbnail rounded"
                                    style="width: 60px; height: 60px; object-fit: contain;">
                            </div>

                            {{-- Detalhes do Produto --}}
                            <div class="flex-grow-1">
                                <strong
                                    class="d-block">{{ $item->product->title ?? ($item->product->slug ?? 'Produto') }}</strong>
                                <small class="text-muted">Preço: {{ $formattedPrice }}</small><br>
                                <small class="fw-semibold">Total: {{ $formattedTotal }}</small>
                            </div>

                            {{-- Botão Remover --}}
                            <form action="{{ route('cart.remove', $item->product_id) }}" method="POST"
                                onsubmit="return confirm('Remover este item?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-outline-danger">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </form>
                        </div>

                        {{-- Controle de Quantidade --}}
                        <div class="d-flex align-items-center mt-2 gap-2">
                            @if ($item->quantity > 1)
                                <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                    @csrf
                                    @method('PUT')
                                    <input type="hidden" name="quantity" value="{{ $item->quantity - 1 }}">
                                    <button type="submit" class="btn btn-sm btn-outline-secondary">-</button>
                                </form>
                            @endif

                            <span class="fw-bold">{{ $item->quantity }}</span>

                            <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                <button type="submit" class="btn btn-sm btn-outline-secondary"
                                    @if ($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>+</button>
                            </form>
                        </div>
                    </li>
                @endforeach
            </ul>

            <a href="{{ route('cart.view') }}" class="btn btn-primary btn-sm mt-3 w-100">
                <i class="fa fa-shopping-cart me-1"></i> Ir para o Carrinho
            </a>
        </div>
    @endif
</div>

{{-- CSS custom --}}
<style>
    .cart-popup {
        position: fixed;
        top: 50%;
        left: 50%;
        width: 90%;
        max-width: 400px;
        transform: translate(-50%, -50%);
        padding: 1rem;
        display: none;
    }

    @media (min-width: 768px) {
        .cart-popup {
            position: absolute;
            top: 50px;
            right: 0;
            left: auto;
            transform: none;
            width: 30em;
        }
    }
</style>

{{-- Script Carrinho --}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const cartButton = document.getElementById('cart-button');
        const cartModal = document.getElementById('cart-modal');
        const cartClose = document.getElementById('cart-close');

        cartButton?.addEventListener('click', e => {
            e.stopPropagation();
            cartModal.style.display = cartModal.style.display === 'block' ? 'none' : 'block';
        });
        cartClose?.addEventListener('click', () => cartModal.style.display = 'none');
        document.addEventListener('click', event => {
            if (cartModal && !cartModal.contains(event.target) && event.target !== cartButton) {
                cartModal.style.display = 'none';
            }
        });
    });
</script>
