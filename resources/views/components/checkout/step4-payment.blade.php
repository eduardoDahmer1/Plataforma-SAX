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

    <input type="hidden" name="payment_method" id="payment_method" value="{{ old('payment_method', 'deposito') }}">
    <input type="hidden" name="total_final" id="total_final" value="{{ $totalPedido }}">

    <div class="sax-checkout-box mt-4">
        <h4 class="sax-step-title">{{ __('messages.resumo_final') }}</h4>
        
        <div class="sax-cart-list mb-4">
            @foreach ($cart as $item)
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                    <div class="sax-cart-img-wrapper" style="width: 50px; height: 50px;">
                        <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" 
                             alt="{{ $item->product->external_name ?? 'Produto' }}" class="img-fluid">
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 sax-item-name text-truncate" style="max-width: 200px;">
                            {{ $item->product->external_name ?? 'Produto' }}
                        </p>
                        <small class="text-muted">{{ __('messages.quantidade') }}: {{ $item->quantity }}</small>
                    </div>
                    <div class="text-end">
                        <span class="d-block fw-bold">{{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="sax-summary-total pt-3">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">{{ __('messages.subtotal') }}</span>
                <span id="subtotal-display">{{ currency_format($totalPedido) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3">
                <span class="text-muted">{{ __('messages.envio') }}</span>
                <span id="frete-display" class="text-success small fw-bold text-uppercase">{{ __('messages.selecione_entrega') }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <span class="fw-bold h5 mb-0">{{ __('messages.total') }}</span>
                <span id="total-geral-display" class="fw-bold h4 mb-0">{{ currency_format($totalPedido) }}</span>
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

<style>
    .sax-payment-method { border: 2px solid #eee; padding: 15px; border-radius: 8px; background: white; transition: 0.3s; width: 150px; }
    .sax-payment-method.active { background: #000000; color: white; border-color: #000; }
    .sax-payment-method:hover { border-color: #000; }
</style>