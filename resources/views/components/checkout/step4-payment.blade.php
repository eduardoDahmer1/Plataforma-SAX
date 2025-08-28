{{-- STEP 4: PAGAMENTO --}}
<div class="step" id="step4">
    <div class="checkout-box">
        <h4><i class="fa fa-credit-card"></i> Método de Pagamento</h4>
        <label><input type="radio" name="payment_method" value="deposito" checked
                onclick="togglePaymentMethod('deposito')"> Depósito bancário</label><br>
        <label><input type="radio" name="payment_method" value="bancard" onclick="togglePaymentMethod('bancard')">
            Bancard</label>
    </div>

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

    {{-- Modal Bancard --}}
    <div class="modal fade" id="bancardModal" tabindex="-1" aria-labelledby="bancardModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content" style="border-radius:12px; box-shadow:0 8px 25px rgba(0,0,0,0.2);">
                <div class="modal-header bg-primary text-white"
                    style="border-top-left-radius:12px; border-top-right-radius:12px;">
                    <h5 class="modal-title" id="bancardModalLabel"><i class="fa fa-credit-card"></i> Pagamento
                        Bancard</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                        aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted mb-3"><i class="fa fa-info-circle"></i> Preencha os dados do cartão
                        para continuar com o pagamento.</p>

                    <div class="mb-3">
                        <label><i class="fa fa-credit-card"></i> Número do Cartão</label>
                        <input type="text" class="form-control" name="card_number" placeholder="XXXX XXXX XXXX XXXX">
                    </div>

                    <div class="row">
                        <div class="col-6 mb-3">
                            <label><i class="fa fa-calendar-alt"></i> Validade</label>
                            <input type="text" class="form-control" name="card_expiry" placeholder="MM/AA">
                        </div>
                        <div class="col-6 mb-3">
                            <label><i class="fa fa-lock"></i> CVV</label>
                            <input type="text" class="form-control" name="card_cvv" placeholder="XXX">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal"><i
                            class="fa fa-times"></i> Cancelar</button>
                    <button type="submit" form="checkoutForm" class="btn btn-primary"><i
                            class="fa fa-check-circle"></i> Pagar com Bancard</button>
                </div>
            </div>
        </div>
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

    <button type="button" class="btn btn-secondary" onclick="prevStep(3)"><i class="fa fa-arrow-left"></i>
        Voltar</button>
    <button type="submit" class="btn btn-success" id="checkoutSubmit"><i class="fa fa-check"></i> Finalizar
        Compra</button>
</div>
