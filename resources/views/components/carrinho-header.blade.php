@php
$cart = session('cart', []);
$cartCount = count($cart);
@endphp

<div class="position-relative d-inline-block ms-3" id="cart-hover-area" style="cursor:pointer;">
    <button id="cart-button" class="btn btn-light position-relative">
        <i class="fas fa-shopping-cart me-1"></i> Carrinho
        @if($cartCount > 0)
        <span class="badge bg-danger position-absolute top-0 start-100 translate-middle">{{ $cartCount }}</span>
        @endif
    </button>

    @if($cartCount > 0)
    <div id="cart-modal" class="card shadow-lg p-3 position-absolute bg-white text-dark"
        style="top: 50px; right: 0; display: none; width: 320px; max-width: 90vw; z-index:999;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <h6 class="mb-0"><i class="fas fa-boxes me-1"></i>Itens no carrinho:</h6>
            <button id="cart-close" class="btn btn-sm btn-outline-danger"><i class="fas fa-times"></i></button>
        </div>

        <ul class="list-group list-group-flush">
            @foreach($cart as $productId => $item)
            <li class="list-group-item">
                <div class="d-flex justify-content-between align-items-start mb-2">
                    <div>
                        <strong><i class="fas fa-box-open me-1"></i>{{ $item['title'] ?? $item['slug'] ?? 'Produto' }}</strong><br>
                        <small><i class="fas fa-tag me-1"></i>R$ {{ number_format($item['price'] ?? 0, 2, ',', '.') }}</small>
                    </div>

                    {{-- Excluir item --}}
                    <form action="{{ route('cart.remove', $productId) }}" method="POST"
                        onsubmit="return confirm('Remover este item do carrinho?')" class="ms-2">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Remover">
                            <i class="fas fa-trash"></i>
                        </button>
                    </form>
                </div>

                {{-- Controles de quantidade --}}
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    {{-- Diminuir --}}
                    @if (($item['quantity'] ?? 1) > 1)
                    <form action="{{ route('cart.update', $productId) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="quantity" value="{{ $item['quantity'] - 1 }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Diminuir">
                            <i class="fas fa-minus"></i>
                        </button>
                    </form>
                    @endif

                    {{-- Quantidade --}}
                    <span class="fw-bold">{{ $item['quantity'] ?? 1 }}</span>

                    {{-- Aumentar --}}
                    <form action="{{ route('cart.update', $productId) }}" method="POST" class="d-inline">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="quantity" value="{{ ($item['quantity'] ?? 1) + 1 }}">
                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Aumentar"
                            @if(($item['quantity'] ?? 1) >= ($item['stock'] ?? 1)) disabled @endif>
                            <i class="fas fa-plus"></i>
                        </button>
                    </form>
                </div>
            </li>
            @endforeach
        </ul>

        <a href="{{ route('cart.view') }}" class="btn btn-primary btn-sm mt-3 w-100">
            <i class="fas fa-shopping-cart me-1"></i> Ir para o Carrinho
        </a>
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
