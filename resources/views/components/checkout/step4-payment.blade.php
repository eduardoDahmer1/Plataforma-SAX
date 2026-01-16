{{-- STEP 4: PAGAMENTO --}}
<div class="step" id="step4">
    {{-- Cálculo do Total dentro do componente --}}
    @php 
        $totalPedido = 0;
        foreach ($cart as $item) {
            $totalPedido += ($item->product->price ?? 0) * $item->quantity;
        }
    @endphp

    <div class="sax-checkout-box text-center py-5">
        <h5 class="mb-4 text-uppercase tracking-wider">Forma de Pagamento</h5>
        <div class="d-flex justify-content-center gap-3">
            <button type="button" class="sax-payment-method active" id="btn-deposito" onclick="selectPayment('deposito')">
                <i class="fa fa-university mb-2 d-block"></i>
                DEPÓSITO / TRANSFERÊNCIA
            </button>
        </div>
        <p class="sax-payment-notice mt-4">
            Estamos finalizando a implementação do nosso novo sistema de pagamento. Quer pagar com cartão ou QR Code? Chame no WhatsApp que concluímos seu pedido rapidinho!
        </p>
    </div>

    {{-- Campo oculto que guarda o método de pagamento --}}
    <input type="hidden" name="payment_method" id="payment_method" value="deposito">

    <div class="sax-checkout-box mt-4">
        <h4 class="sax-step-title">Resumo Final</h4>
        
        {{-- Listagem de itens no resumo final --}}
        <div class="sax-cart-list mb-4">
            @foreach ($cart as $item)
                <div class="d-flex align-items-center gap-3 mb-2 border-bottom pb-2">
                    <img src="{{ $item->product->photo_url ?? 'https://via.placeholder.com/60' }}" 
                         style="width: 50px; height: 50px; object-fit: contain; background: #f5f5f5;">
                    <div class="flex-grow-1">
                        <small class="d-block fw-bold">{{ $item->product->external_name ?? 'Produto' }}</small>
                        <small class="text-muted">{{ $item->quantity }}x {{ currency_format($item->product->price ?? 0) }}</small>
                    </div>
                    <div>
                        <small class="fw-bold">{{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</small>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sax-summary-total">
            <div class="d-flex justify-content-between mb-2">
                <span>Subtotal</span>
                <span>{{ currency_format($totalPedido) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>Envio</span>
                <span class="text-success fw-bold">A COMBINAR</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center total-row">
                <strong>TOTAL</strong>
                <strong style="font-size: 1.5rem;">{{ currency_format($totalPedido) }}</strong>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="sax-btn-prev" onclick="prevStep(3)">
            <i class="fa fa-arrow-left me-2"></i> Voltar
        </button>
        <button type="submit" class="sax-btn-finish" id="checkoutSubmit">
            <i class="fa fa-check me-2"></i> FINALIZAR COMPRA
        </button>
    </div>
</div>