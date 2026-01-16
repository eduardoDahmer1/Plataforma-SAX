<div class="step active" id="step1">
    <div class="sax-checkout-box">
        <h4 class="sax-step-title"><span class="step-number">01</span> Itens no Carrinho</h4>
        
        <div class="sax-cart-list">
            @php $totalCarrinho = 0; @endphp
            @foreach ($cart as $item)
                <div class="sax-cart-item d-flex align-items-center gap-4 py-3 border-bottom">
                    <div class="sax-cart-img-wrapper">
                        <img src="{{ $item->product->photo_url ?? 'https://via.placeholder.com/100' }}" 
                             alt="{{ $item->product->external_name }}" 
                             class="img-fluid">
                    </div>

                    <div class="flex-grow-1">
                        <span class="sax-item-brand">{{ $item->product->brand->name ?? 'SAX EXCLUSIVE' }}</span>
                        <h5 class="sax-item-name">{{ $item->product->external_name ?? 'Produto' }}</h5>
                        <div class="sax-item-meta">
                            <span>Qtd: <strong>{{ $item->quantity }}</strong></span>
                            <span class="ms-3">Preço: <strong>{{ currency_format($item->product->price ?? 0) }}</strong></span>
                        </div>
                    </div>

                    <div class="text-end">
                        <span class="sax-item-subtotal">{{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</span>
                    </div>
                </div>
                @php $totalCarrinho += ($item->product->price ?? 0) * $item->quantity; @endphp
            @endforeach
        </div>

        <div class="sax-total-section d-flex justify-content-between align-items-center mt-4">
            <span class="total-label">Subtotal</span>
            <span class="total-value">{{ currency_format($totalCarrinho) }}</span>
        </div>

        <button type="button" class="sax-btn-next mt-4" onclick="nextStep(1)">
            Seguir para Identificação <i class="fa fa-chevron-right ms-2"></i>
        </button>
    </div>
</div>