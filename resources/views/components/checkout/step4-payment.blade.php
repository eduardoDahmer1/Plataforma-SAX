{{-- STEP 4: PAGAMENTO --}}
<div class="step" id="step4">
    <div class="checkout-box text-center mb-4">
        <h4><i class="fa fa-credit-card"></i> Escolha o Método de Pagamento</h4>

        <div class="d-flex justify-content-center gap-3 mt-3">
            <button type="button" class="btn btn-outline-primary btn-lg" id="btn-deposito"
                onclick="selectPayment('deposito')">
                <i class="fa fa-university"></i> Depósito Bancário
            </button>
            {{-- <button type="button" class="btn btn-outline-success btn-lg" id="btn-bancard"
                onclick="selectPayment('bancard')">
                <i class="fa fa-credit-card"></i> Bancard
            </button> --}}
        </div>
        <p class="mt-1">Cartão ou QR Code? Chame no WhatsApp e concluímos seu pedido.</p>
    </div>

    {{-- Campo oculto que guarda o método de pagamento --}}
    <input type="hidden" name="payment_method" id="payment_method" value="deposito">

    {{-- Resumo do Pedido --}}
    @php $totalPedido = 0; @endphp
    <div class="checkout-box mt-4">
        <h4><i class="fa fa-receipt"></i> Resumo do Pedido</h4>
        @foreach ($cart as $item)
            <div class="border-bottom py-2">
                <p><strong>{{ $item->product->slug ?? 'Produto' }}</strong></p>
                <p><i class="fa fa-dollar-sign"></i> Preço: {{ currency_format($item->product->price ?? 0) }}</p>
                <p><i class="fa fa-sort-numeric-up"></i> Quantidade: {{ $item->quantity }}</p>
                <p><i class="fa fa-calculator"></i> Total:
                    {{ currency_format(($item->product->price ?? 0) * $item->quantity) }}
                </p>
            </div>
            @php $totalPedido += ($item->product->price ?? 0) * $item->quantity; @endphp
        @endforeach
        <hr>
        <h5><i class="fa fa-money-bill-wave"></i> Total do Pedido: {{ currency_format($totalPedido) }}</h5>
    </div>

    <div class="d-flex justify-content-between mt-3">
        <button type="button" class="btn btn-secondary" onclick="prevStep(3)">
            <i class="fa fa-arrow-left"></i> Voltar
        </button>
        <button type="submit" class="btn btn-success" id="checkoutSubmit">
            <i class="fa fa-check"></i> Finalizar Compra
        </button>
    </div>
</div>