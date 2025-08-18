@php
$cart = session('cart', []);
$cartCount = count($cart);
@endphp

<div class="position-relative d-inline-block ms-3" id="cart-hover-area" style="cursor:pointer;">
    <button id="cart-button" class="btn btn-light">
        🛒 Carrinho ({{ $cartCount }})
    </button>

    @if($cartCount > 0)
    <div id="cart-modal" class="card shadow-lg p-3 position-absolute bg-white text-dark"
        style="top: 50px; right: 0; display: none; width: 300px; z-index:999;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0">Itens no carrinho:</h6>
            <button id="cart-close" class="btn btn-sm btn-outline-danger">&times;</button>
        </div>
        <ul class="list-group list-group-flush">
            @foreach($cart as $productId => $item)
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <strong>{{ $item['title'] ?? $item['slug'] ?? 'Produto' }}</strong><br>
                        <small>R$ {{ number_format($item['price'] ?? 0, 2, ',', '.') }}</small>
                    </div>

                    {{-- Excluir item --}}
                    <form action="{{ route('cart.remove', $productId) }}" method="POST"
                        onsubmit="return confirm('Remover este item do carrinho?')" class="ms-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">🗑</button>
                    </form>
                </div>

                {{-- Controles de quantidade --}}
                <div class="d-flex align-items-center mt-2">
                    @if (($item['quantity'] ?? 1) > 1)
                    <form action="{{ route('cart.update', $productId) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Diminuir">-</button>
                    </form>
                    @endif

                    <span class="mx-2">{{ $item['quantity'] ?? 1 }}</span>

                    {{-- Aumentar quantidade --}}
                    <form action="{{ route('cart.update', $productId) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="quantity" value="{{ ($item['quantity'] ?? 1) + 1 }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Aumentar"
                            @if(($item['quantity'] ?? 1) >= ($item['stock'] ?? 1)) disabled @endif>+</button>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>
        <a href="{{ route('cart.view') }}" class="btn btn-primary btn-sm mt-3 w-100">Ir para o Carrinho</a>
    </div>
    @endif
</div>


<script>
document.addEventListener('DOMContentLoaded', function() {
    const cartButton = document.getElementById('cart-button');
    const cartModal = document.getElementById('cart-modal');
    const cartClose = document.getElementById('cart-close');

    function toggleCartModal() {
        if (cartModal && cartModal.style.display === 'block') {
            cartModal.style.display = 'none';
        } else if (cartModal) {
            cartModal.style.display = 'block';
        }
    }

    if (cartButton) {
        cartButton.addEventListener('click', function(e) {
            e.stopPropagation();
            toggleCartModal();
        });
    }

    if (cartClose) {
        cartClose.addEventListener('click', function() {
            cartModal.style.display = 'none';
        });
    }

    document.addEventListener('click', function(event) {
        if (cartModal && !cartModal.contains(event.target) && event.target !== cartButton) {
            cartModal.style.display = 'none';
        }
    });
});
</script>