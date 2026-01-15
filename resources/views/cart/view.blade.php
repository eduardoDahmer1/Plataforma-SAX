@extends('layout.layout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="row g-4">
        {{-- Título da Página --}}
        <div class="col-12 border-bottom pb-3 mb-4">
            <h1 class="display-6 fw-bold text-uppercase letter-spacing-2 m-0">
                <i class="fas fa-shopping-bag me-2 small"></i>Sua Sacola
            </h1>
            <p class="text-muted small mt-2">{{ $cart->count() }} itens selecionados</p>
        </div>

        @if ($cart->count() > 0)
            @php $totalCarrinho = 0; @endphp

            {{-- Lista de Itens --}}
            <div class="col-lg-8">
                <div class="cart-items-container">
                    @foreach ($cart as $item)
                        @php
                            $itemTotal = ($item->product->price ?? 0) * $item->quantity;
                            $totalCarrinho += $itemTotal;
                        @endphp

                        <div class="cart-item-row shadow-sm rounded-4 mb-3 p-3 bg-white border">
                            <div class="row align-items-center g-3">
                                {{-- Imagem --}}
                                <div class="col-4 col-md-2 text-center">
                                    <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}"
                                        alt="{{ $item->product->external_name }}" 
                                        class="img-fluid rounded-3 item-img-sax">
                                </div>

                                {{-- Detalhes --}}
                                <div class="col-8 col-md-5">
                                    <h6 class="fw-bold text-uppercase mb-1 item-title-sax">
                                        {{ $item->product->external_name ?? 'Produto' }}
                                    </h6>
                                    <p class="text-muted x-small mb-1">SKU: {{ $item->product->sku ?? '-' }}</p>
                                    <p class="text-dark fw-bold m-0 d-md-none">{{ currency_format($item->product->price ?? 0) }}</p>
                                </div>

                                {{-- Quantidade --}}
                                <div class="col-6 col-md-3">
                                    <div class="quantity-control-sax">
                                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ max($item->quantity - 1, 1) }}">
                                            <button type="submit" class="qty-btn-sax" {{ $item->quantity <= 1 ? 'disabled' : '' }}>-</button>
                                        </form>
                                        
                                        <span class="qty-val-sax">{{ $item->quantity }}</span>

                                        <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                            @csrf @method('PUT')
                                            <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                            <button type="submit" class="qty-btn-sax" 
                                                @if ($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>+</button>
                                        </form>
                                    </div>
                                </div>

                                {{-- Preço Total e Excluir --}}
                                <div class="col-6 col-md-2 text-end">
                                    <p class="fw-bold m-0 item-price-sax">{{ currency_format($itemTotal) }}</p>
                                    <form action="{{ route('cart.remove', $item->product_id) }}" method="POST" class="mt-2">
                                        @csrf @method('DELETE')
                                        <button type="submit" class="btn-remove-sax">
                                            <i class="far fa-trash-alt me-1"></i>Remover
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                
                <a href="{{ route('home') }}" class="btn btn-link text-dark text-decoration-none x-small fw-bold mt-3">
                    <i class="fas fa-arrow-left me-2"></i> CONTINUAR COMPRANDO
                </a>
            </div>

            {{-- Resumo do Pedido (Sidebar) --}}
            <div class="col-lg-4">
                <div class="checkout-summary-sax p-4 rounded-4 shadow-sm border bg-light sticky-top" style="top: 20px;">
                    <h5 class="fw-bold text-uppercase mb-4">Resumo do Pedido</h5>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Subtotal</span>
                        <span class="fw-bold">{{ currency_format($totalCarrinho) }}</span>
                    </div>
                    
                    <div class="d-flex justify-content-between mb-2">
                        <span class="text-muted">Desconto</span>
                        <span class="text-success fw-bold">- {{ currency_format(0) }}</span>
                    </div>

                    <hr class="my-3">

                    <div class="d-flex justify-content-between mb-4">
                        <span class="h5 fw-bold">TOTAL</span>
                        <span class="h5 fw-bold text-dark">{{ currency_format($totalCarrinho) }}</span>
                    </div>

                    <div class="d-grid gap-2">
                        <form action="{{ route('checkout.index') }}" method="GET">
                            <button type="submit" class="btn btn-dark btn-lg w-100 rounded-pill py-3 fw-bold text-uppercase x-small">
                                <i class="fas fa-lock me-2"></i> Finalizar Compra
                            </button>
                        </form>

                        <form action="{{ route('checkout.whatsapp') }}" method="GET">
                            <button type="submit" class="btn btn-success btn-lg w-100 rounded-pill py-3 fw-bold text-uppercase x-small">
                                <i class="fab fa-whatsapp me-2"></i> Checkout WhatsApp
                            </button>
                        </form>
                    </div>

                    <div class="text-center mt-4">
                        <img src="https://vignette.wikia.nocookie.net/logopedia/images/e/e3/Visa_2014.svg" height="20" class="me-2 opacity-50">
                        <img src="https://upload.wikimedia.org/wikipedia/commons/2/2a/Mastercard-logo.svg" height="20" class="me-2 opacity-50">
                        <i class="fas fa-shield-alt text-muted me-1"></i> <span class="x-small text-muted">Compra 100% Segura</span>
                    </div>
                </div>
            </div>
        @else
            {{-- Carrinho Vazio --}}
            <div class="col-12 text-center py-5">
                <div class="mb-4">
                    <i class="fas fa-shopping-bag fa-4x text-light"></i>
                </div>
                <h2 class="fw-bold">Sua sacola está vazia</h2>
                <p class="text-muted">Parece que você ainda não escolheu seus produtos.</p>
                <a href="{{ route('home') }}" class="btn btn-dark rounded-pill px-5 py-3 mt-3">VOLTAR PARA A LOJA</a>
            </div>
        @endif
    </div>
</div>
@endsection
<style>
/* Layout Geral */
.letter-spacing-2 { letter-spacing: 2px; }
.x-small { font-size: 0.75rem; letter-spacing: 1px; }

/* Linha do Item */
.cart-item-row {
    transition: transform 0.2s ease, box-shadow 0.2s ease;
}

.cart-item-row:hover {
    transform: translateY(-2px);
    box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important;
}

.item-img-sax {
    max-height: 100px;
    object-fit: contain;
    mix-blend-mode: multiply; /* Ótimo se o fundo da imagem for branco puro */
}

.item-title-sax {
    font-size: 0.9rem;
    letter-spacing: 0.5px;
    color: #1a1a1a;
}

.item-price-sax {
    font-size: 1.1rem;
    color: #000;
}

/* Controle de Quantidade Minimalista */
.quantity-control-sax {
    display: inline-flex;
    align-items: center;
    border: 1px solid #e0e0e0;
    border-radius: 50px;
    padding: 2px;
    background: #fff;
}

.qty-btn-sax {
    width: 32px;
    height: 32px;
    border-radius: 50%;
    border: none;
    background: transparent;
    color: #000;
    font-weight: bold;
    transition: 0.2s;
}

.qty-btn-sax:hover:not(:disabled) {
    background: #f0f0f0;
}

.qty-val-sax {
    padding: 0 15px;
    font-weight: bold;
    font-size: 0.85rem;
}

/* Botão Remover */
.btn-remove-sax {
    background: none;
    border: none;
    color: #bbb;
    font-size: 0.7rem;
    text-transform: uppercase;
    font-weight: bold;
    letter-spacing: 1px;
    transition: 0.3s;
}

.btn-remove-sax:hover {
    color: #dc3545;
}

/* Sidebar de Resumo */
.checkout-summary-sax {
    border: none !important;
    background-color: #f8f9fa !important;
}

.checkout-summary-sax h5 {
    letter-spacing: 1.5px;
    border-bottom: 2px solid #000;
    display: inline-block;
    padding-bottom: 5px;
}

/* Responsividade Mobile */
@media (max-width: 768px) {
    .item-img-sax {
        max-height: 80px;
    }
    .item-title-sax {
        font-size: 0.8rem;
    }
    .quantity-control-sax {
        margin-top: 10px;
    }
    .checkout-summary-sax {
        margin-top: 20px;
    }
}
</style>
{{-- @push('scripts')
    <script>
        const cart = @json($cart->mapWithKeys(fn($item) => [$item->product_id => $item->quantity]));
        const currencyCode = '{{ session('currency')['code'] ?? 'BRL' }}';
        const formatter = new Intl.NumberFormat('pt-BR', {
            style: 'currency',
            currency: currencyCode
        });

        function atualizarValores(subtotal, desconto, total) {
            document.getElementById('subtotal').textContent = formatter.format(subtotal);
            document.getElementById('desconto').textContent = formatter.format(desconto);
            document.getElementById('totalCarrinho').textContent = formatter.format(total);
        }

        document.getElementById('aplicarCupon').addEventListener('click', function() {
            const codigo = document.getElementById('cuponCodigo').value.trim();
            if (!codigo) return alert('Digite o código do cupom');

            fetch('{{ route('user.cupons.apply') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        codigo,
                        cart
                    })
                })
                .then(res => res.json())
                .then(data => {
                    const msg = document.getElementById('mensagemCupon');
                    const cupomAplicado = document.getElementById('cupomAplicado');
                    if (data.success) {
                        atualizarValores(data.subtotal, data.desconto, data.total);
                        msg.textContent = 'Cupom aplicado com sucesso!';
                        msg.classList.remove('text-danger');
                        msg.classList.add('text-success');
                        cupomAplicado.textContent = 'Cupom: ' + codigo;
                    } else {
                        msg.textContent = data.message;
                        msg.classList.remove('text-success');
                        msg.classList.add('text-danger');
                        cupomAplicado.textContent = '';
                    }
                })
                .catch(err => console.error(err));
        });

        document.getElementById('removerCupon').addEventListener('click', function() {
            fetch('{{ route('user.cupons.remove') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    }
                })
                .then(res => res.json())
                .then(data => {
                    atualizarValores(data.subtotal, data.desconto, data.total);
                    document.getElementById('cuponCodigo').value = '';
                    document.getElementById('mensagemCupon').textContent = 'Cupom removido!';
                    document.getElementById('mensagemCupon').classList.remove('text-danger');
                    document.getElementById('mensagemCupon').classList.add('text-success');
                    document.getElementById('cupomAplicado').textContent = '';
                })
                .catch(err => console.error(err));
        });
    </script>
@endpush --}}
