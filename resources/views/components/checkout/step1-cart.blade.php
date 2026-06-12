<div class="step active" id="step1">
    <div class="sax-checkout-box">
        <h4 class="sax-step-title">
            <span class="step-number">01</span> {{ __('messages.passo_itens_carrinho') }}
        </h4>
        
        <div class="sax-cart-list">
            @php $totalCarrinho = 0; @endphp
            @foreach ($cart as $item)
                @php 
                    $subtotalItem = ($item->product->price ?? 0) * $item->quantity;
                    $totalCarrinho += $subtotalItem; 
                @endphp
                <div class="sax-cart-item d-flex align-items-center gap-4 py-3 border-bottom">
                    <div class="sax-cart-img-wrapper">
                        <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" 
                             alt="{{ $item->product->external_name }}" 
                             class="img-fluid" style="width: 80px;">
                    </div>

                    <div class="flex-grow-1">
                        <span class="sax-item-brand">{{ $item->product->brand->name ?? 'SAX EXCLUSIVE' }}</span>
                        <h5 class="sax-item-name">{{ $item->product->external_name ?? 'Produto' }}</h5>
                        <div class="sax-item-meta">
                            <span>{{ __('messages.quantidade_abreviada') }}: <strong>{{ $item->quantity }}</strong></span>
                            <span class="ms-3">{{ __('messages.preco') }}: <strong>{{ currency_format($item->product->price ?? 0) }}</strong></span>
                        </div>
                    </div>

                    <div class="text-end">
                        <span class="sax-item-subtotal">{{ currency_format($subtotalItem) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sax-total-section d-flex justify-content-between align-items-center mt-4">
            <span class="total-label">{{ __('messages.subtotal') }}</span>
            {{-- ID adicionado para o JS conseguir ler o valor base --}}
            <span class="total-value" id="subtotal-valor" data-valor="{{ $totalCarrinho }}">
                {{ currency_format($totalCarrinho) }}
            </span>
        </div>

        <button type="button" class="sax-btn-next mt-4" onclick="nextStep(1)">
            {{ __('messages.seguir_identificacao') }} <i class="fa fa-chevron-right ms-2"></i>
        </button>
    </div>
</div>