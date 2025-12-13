@extends('layout.layout')

@section('content')
    <h1 class="mb-4"><i class="fas fa-shopping-cart me-2"></i>Seu Carrinho</h1>

    @if ($cart->count() > 0)
        @php
            $totalCarrinho = 0;
        @endphp

        <ul class="list-group mb-3">
            @foreach ($cart as $item)
                @php
                    $itemTotal = ($item->product->price ?? 0) * $item->quantity;
                    $totalCarrinho += $itemTotal;
                @endphp

                <li class="list-group-item d-flex flex-column flex-md-row justify-content-between align-items-start mb-2">

                    {{-- Imagem do Produto --}}
                    <div class="me-3 mb-2 mb-md-0">
                        <img src="{{ $item->product->photo_url ?? 'https://via.placeholder.com/80' }}"
                            alt="{{ $item->product->external_name }}" class="img-thumbnail rounded"
                            style="width: 80px; height: 80px; object-fit: contain;">
                    </div>

                    {{-- Detalhes do Produto --}}
                    <div class="flex-grow-1 mb-2 mb-md-0">
                        <strong><i
                                class="fas fa-box-open me-1"></i>{{ $item->product->external_name ?? 'Produto' }}</strong><br>
                        <small><i class="fas fa-link me-1"></i>Slug: {{ $item->product->slug ?? '-' }}</small><br>
                        <small><i class="fas fa-barcode me-1"></i>SKU: {{ $item->product->sku ?? '-' }}</small>
                    </div>

                    {{-- Quantidade e Preço --}}
                    <div class="d-flex align-items-center gap-3 flex-wrap">
                        <span><i class="fas fa-tag me-1"></i>{{ currency_format($item->product->price ?? 0) }}</span>
                        <span><i class="fas fa-sort-numeric-up me-1"></i>x {{ $item->quantity }}</span>
                        <span><i class="fas fa-equals me-1"></i>{{ currency_format($itemTotal) }}</span>

                        <div class="d-flex flex-column ms-2">
                            {{-- Botões de atualizar/excluir quantidade --}}
                            <form action="{{ route('cart.update', $item->product_id) }}" method="POST">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="quantity" value="{{ $item->quantity + 1 }}">
                                <button type="submit" class="btn btn-outline-secondary btn-sm mb-1"
                                    @if ($item->quantity >= ($item->product->stock ?? 1)) disabled @endif>
                                    <i class="fas fa-plus"></i>
                                </button>
                            </form>

                            <form action="{{ route('cart.update', $item->product_id) }}" method="POST" class="mb-1">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="quantity" value="{{ max($item->quantity - 1, 1) }}">
                                <button type="submit" class="btn btn-outline-secondary btn-sm">
                                    <i class="fas fa-minus"></i>
                                </button>
                            </form>

                            <form action="{{ route('cart.remove', $item->product_id) }}" method="POST">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-danger btn-sm"><i
                                        class="fas fa-trash me-1"></i>Excluir</button>
                            </form>
                        </div>
                    </div>
                </li>
            @endforeach
        </ul>
{{-- 
        Campo para aplicar cupom
        <div class="mb-3">
            <label for="cuponCodigo" class="form-label"><i class="fas fa-ticket-alt me-1"></i>Aplicar Cupom</label>
            <div class="d-flex gap-2">
                <input type="text" id="cuponCodigo" class="form-control" placeholder="Digite o código do cupom">
                <button type="button" id="aplicarCupon" class="btn btn-primary">Aplicar</button>
                <button type="button" id="removerCupon" class="btn btn-outline-danger">Remover</button>
            </div>
            <small id="mensagemCupon" class="mt-1 d-block"></small>
            <div id="cupomAplicado" class="mt-1 text-success"></div>
        </div> --}}

        <h5>Subtotal: <span id="subtotal">{{ currency_format($totalCarrinho) }}</span></h5>
        <h5>Desconto: <span id="desconto">{{ currency_format(0) }}</span></h5>
        <h4>Total: <span id="totalCarrinho">{{ currency_format($totalCarrinho) }}</span></h4>

        <div class="mt-3 d-flex gap-2 flex-wrap">
            <form action="{{ route('checkout.index') }}" method="GET">
                <button type="submit" class="btn btn-success"><i class="fas fa-credit-card me-1"></i>Finalizar
                    Compra</button>
            </form>

            <form action="{{ route('checkout.whatsapp') }}" method="GET">
                <button type="submit" class="btn btn-success"><i class="fab fa-whatsapp me-1"></i>Finalizar via
                    WhatsApp</button>
            </form>
        </div>
    @else
        <p><i class="fas fa-info-circle me-1"></i>Seu carrinho está vazio.</p>
    @endif
 @endsection
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
