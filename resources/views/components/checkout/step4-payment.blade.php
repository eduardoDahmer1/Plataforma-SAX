{{-- STEP 4: PAGAMENTO --}}
<div class="step" id="step4">
    <div class="checkout-box">
        <h4><i class="fa fa-credit-card"></i> Método de Pagamento</h4>
        <label>
            <input type="radio" name="payment_method" value="deposito" checked
                   onclick="togglePaymentMethod('deposito')"> Depósito bancário
        </label><br>
        <label>
            <input type="radio" name="payment_method" value="bancard" onclick="togglePaymentMethod('bancard')">
            Bancard
        </label>
    </div>

    {{-- Depósito bancário --}}
    <div id="bankDetails" class="payment-details">
        <h5><i class="fa fa-university"></i> Escolha o Banco</h5>
        <div class="row">
            @foreach ($paymentMethods as $method)
                @if ($method->type === 'bank')
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="card">
                            <div class="card-body">
                                <h6 class="card-title"><i class="fa fa-building"></i> {{ $method->name }}</h6>
                                <p class="card-text">{{ $method->bank_details }}</p>
                            </div>
                        </div>
                    </div>
                @endif
            @endforeach
        </div>
        <div class="upload-btn">
            <label>Envie o Comprovante de Depósito (opcional)</label>
            <input type="file" name="deposit_receipt" class="form-control"><br>
        </div>
    </div>

    {{-- Bancard iFrame --}}
    <div id="bancardContainer" class="payment-details" style="display:none;">
        <h5><i class="fa fa-credit-card"></i> Pagamento com Bancard</h5>
        <p class="text-muted mb-3"><i class="fa fa-info-circle"></i> Preencha os dados do cartão no iframe abaixo.</p>
        <div id="iframe-container" style="height: 200px; width: 100%; margin: auto;"></div>
    </div>

    {{-- Resumo do Pedido --}}
    @php $totalPedido = 0; @endphp
    <div class="checkout-box">
        <h4><i class="fa fa-receipt"></i> Resumo do Pedido</h4>
        @foreach ($cart as $item)
            <p><strong>{{ $item->product->slug ?? 'Produto' }}</strong></p>
            <p><i class="fa fa-dollar-sign"></i> Preço: {{ currency_format($item->product->price ?? 0) }}</p>
            <p><i class="fa fa-sort-numeric-up"></i> Quantidade: {{ $item->quantity }}</p>
            <p><i class="fa fa-calculator"></i> Total: {{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</p>
            @php $totalPedido += ($item->product->price ?? 0) * $item->quantity; @endphp
        @endforeach
        <hr>
        <h5><i class="fa fa-money-bill-wave"></i> Total do Pedido: {{ currency_format($totalPedido) }}</h5>
    </div>

    <button type="button" class="btn btn-secondary" onclick="prevStep(3)">
        <i class="fa fa-arrow-left"></i> Voltar
    </button>
    <button type="submit" class="btn btn-success" id="checkoutSubmit">
        <i class="fa fa-check"></i> Finalizar Compra
    </button>
</div>

<!-- Script Bancard -->
<script src="{{ asset('js/bancard-checkout-5.0.1.js') }}"></script>
<script>
    const processId = '{{ $processId ?? '' }}';
    console.log("Bancard Process ID:", processId);
</script>
