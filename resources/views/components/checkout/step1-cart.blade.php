{{-- STEP 1: CARRINHO --}}
<div class="step active" id="step1">
    <div class="checkout-box">
        <h4><i class="fa fa-shopping-cart"></i> Itens no Carrinho</h4>
        @php $totalCarrinho = 0; @endphp
        @foreach ($cart as $item)
            <div class="cart-item">
                <div>
                    <p><strong>{{ $item->product->external_name ?? 'Produto' }}</strong></p>
                    <p><i class="fa fa-dollar-sign"></i> 
                        {{ currency_format($item->product->price ?? 0) }}</p>
                </div>
                <div>
                    <p><i class="fa fa-sort-numeric-up"></i> Quantidade: {{ $item->quantity }}</p>
                    <p><i class="fa fa-calculator"></i> Total: 
                        {{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</p>
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
