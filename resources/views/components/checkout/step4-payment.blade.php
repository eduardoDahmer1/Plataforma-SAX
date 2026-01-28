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
        
        <div class="d-flex justify-content-center flex-wrap gap-3">
            {{-- Opção: Depósito --}}
            <button type="button" class="sax-payment-method active" id="btn-deposito" onclick="selectPayment('deposito')">
                <i class="fa fa-university mb-2 d-block"></i>
                DEPÓSITO / TRANSFERÊNCIA
            </button>

            {{-- Opção: Bancard --}}
            {{-- <button type="button" class="sax-payment-method" id="btn-bancard" onclick="selectPayment('bancard')">
                <i class="fa fa-credit-card mb-2 d-block"></i>
                CARTÃO / QR (BANCARD)
            </button> --}}
        </div>

        <p class="sax-payment-notice mt-4" id="payment-instruction">
            Após finalizar, você verá os dados bancários para transferência e envio do comprovante.
        </p>
    </div>

    {{-- Campo oculto essencial que envia o método escolhido para o CheckoutController --}}
    <input type="hidden" name="payment_method" id="payment_method" value="deposito">

    <div class="sax-checkout-box mt-4">
        <h4 class="sax-step-title">Resumo Final</h4>
        
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

<script>
    /**
     * Gerencia a seleção visual e lógica do método de pagamento
     */
    function selectPayment(method) {
        // 1. Atualiza o input que será enviado ao PHP
        document.getElementById('payment_method').value = method;

        // 2. Atualiza a interface visual (botões)
        document.querySelectorAll('.sax-payment-method').forEach(btn => {
            btn.classList.remove('active');
        });
        document.getElementById('btn-' + method).classList.add('active');

        // 3. Atualiza o texto de instrução para o usuário
        const instruction = document.getElementById('payment-instruction');
        if (method === 'bancard') {
            instruction.innerText = "Você será redirecionado para o checkout seguro do Bancard para pagar com Cartão ou QR Code.";
        } else {
            instruction.innerText = "Após finalizar, você verá os dados bancários para transferência e envio do comprovante.";
        }
    }
</script>