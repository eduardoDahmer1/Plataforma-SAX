{{-- STEP 1: CARRINHO --}}
<div class="step active" id="step1">
    <div class="checkout-box">
        <h4><i class="fa fa-shopping-cart"></i> Itens no Carrinho</h4>
        @php $totalCarrinho = 0; @endphp
        @foreach ($cart as $item)
            <div class="cart-item d-flex align-items-center gap-3 mb-3">
                
                {{-- Imagem do Produto --}}
                <div>
                    <img src="{{ $item->product->photo_url ?? 'https://via.placeholder.com/80' }}" 
                         alt="{{ $item->product->external_name }}" 
                         class="img-thumbnail rounded" 
                         style="width: 80px; height: 80px; object-fit: contain;">
                </div>

                {{-- Detalhes do Produto --}}
                <div class="flex-grow-1">
                    <p><strong>{{ $item->product->external_name ?? 'Produto' }}</strong></p>
                    <p><i class="fa fa-dollar-sign"></i> {{ currency_format($item->product->price ?? 0) }}</p>
                    <p><i class="fa fa-sort-numeric-up"></i> Quantidade: {{ $item->quantity }}</p>
                    <p><i class="fa fa-calculator"></i> Total: {{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</p>
                </div>
            </div>
            @php $totalCarrinho += ($item->product->price ?? 0) * $item->quantity; @endphp
        @endforeach

        <div class="total-carrinho">
            <h5>Total do Carrinho: {{ currency_format($totalCarrinho) }}</h5>
        </div>

        <button type="button" class="btn btn-primary mt-3" onclick="nextStep(1)">
            <i class="fa fa-arrow-right"></i> Seguir
        </button>
    </div>
</div>
