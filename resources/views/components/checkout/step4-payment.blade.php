{{-- STEP 4: PAGAMENTO --}}
<div class="step" id="step4">
    @php 
        $totalPedido = 0;
        foreach ($cart as $item) {
            $totalPedido += ($item->product->price ?? 0) * $item->quantity;
        }

        $hasBancardV2 = false;
        foreach (($paymentMethods ?? collect()) as $method) {
            if (($method->type ?? null) === 'gateway' && str_contains(mb_strtolower((string) ($method->name ?? '')), 'bancard v2')) {
                $hasBancardV2 = true;
                break;
            }
        }
    @endphp

    <div class="sax-checkout-box text-center py-5">
        <h5 class="mb-4 text-uppercase tracking-wider">{{ __('messages.forma_pagamento') }}</h5>
        
        <div class="d-flex justify-content-center flex-wrap gap-3">
            {{-- Opção: Depósito --}}
            <button type="button" class="sax-payment-method" id="btn-deposito" data-payment-method="deposito" aria-pressed="false">
                <i class="fa fa-university mb-2 d-block"></i>
                {{ __('messages.deposito_transferencia') }}
            </button>

            @if ($hasBancardV2)
                <button type="button" class="sax-payment-method" id="btn-bancard_v2" data-payment-method="bancard_v2" aria-pressed="false">
                    <i class="fa fa-credit-card mb-2 d-block"></i>
                    {{ __('messages.cartao_qr_bancard') }}
                </button>
            @endif
        </div>

        <p class="sax-payment-notice mt-4" id="payment-instruction">
            {{ __('messages.instrucao_pagamento_deposito') }}
        </p>
    </div>

    {{-- Campo oculto essencial para o Controller --}}
    <input type="hidden" name="payment_method" id="payment_method" value="{{ old('payment_method', 'deposito') }}">

    <div class="sax-checkout-box mt-4">
        <h4 class="sax-step-title">{{ __('messages.resumo_final') }}</h4>
        
        <div class="sax-cart-list mb-4">
            @foreach ($cart as $item)
                <div class="d-flex align-items-center gap-3 mb-2 border-bottom pb-2">
                    <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" 
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
                <span>{{ __('messages.subtotal') }}</span>
                <span>{{ currency_format($totalPedido) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2">
                <span>{{ __('messages.envio') }}</span>
                <span class="text-success fw-bold">{{ __('messages.a_combinar') }}</span>
            </div>
            <hr>
            <div class="d-flex justify-content-between align-items-center total-row">
                <strong>{{ __('messages.total') }}</strong>
                <strong style="font-size: 1.5rem;">{{ currency_format($totalPedido) }}</strong>
            </div>
        </div>
    </div>

    <div class="d-flex justify-content-between mt-4">
        <button type="button" class="sax-btn-prev" onclick="prevStep(3)">
            <i class="fa fa-arrow-left me-2"></i> {{ __('messages.voltar') }}
        </button>
        <button type="submit" class="sax-btn-finish" id="checkoutSubmit">
            <i class="fa fa-check me-2"></i> {{ __('messages.finalizar_compra') }}
        </button>
    </div>
</div>
<script>
    window.translations = {
        payment_bancard: "{{ __('messages.instrucao_pagamento_bancard') }}",
        payment_deposito: "{{ __('messages.instrucao_pagamento_deposito') }}"
    };
</script>