<div class="step" id="step4">
    @php
        // Os valores vêm do CuponService (via $resumo); a view não recalcula desconto.
        $subtotalPedido = $resumo['subtotal'] ?? 0;
        $descontoPedido = $resumo['desconto'] ?? 0;
        $cuponAplicado = $resumo['cupon'] ?? null;
        $totalPedido = $resumo['total'] ?? $subtotalPedido;
        $pygRate = (float) ($pygCurrency?->value ?: 1);
        $pygSign = $pygCurrency?->sign ?: 'G$';
        $selectedCurrencySign = session('currency_sign', 'US$');

        $hasBancardV2 = false;
        $hasRendixPix = false;
        foreach (($paymentMethods ?? collect()) as $method) {
            $methodName = mb_strtolower(trim((string) ($method->name ?? '')));
            if (($method->type ?? null) === 'gateway' && $methodName === 'bancard v2') {
                $hasBancardV2 = true;
            }
            if (($method->type ?? null) === 'gateway' && in_array($methodName, ['rendix pix', 'pix rendix', 'pix'], true)) {
                $hasRendixPix = true;
            }
        }
    @endphp

    <div class="sax-checkout-box text-center py-5">
        <h5 class="mb-4 text-uppercase tracking-wider">{{ __('messages.forma_pagamento') }}</h5>

        <div class="d-flex justify-content-center flex-wrap gap-3 sax-payment-grid">
            <button type="button" class="sax-payment-method" id="btn-deposito" data-payment-method="deposito" aria-pressed="false">
                <i class="fa fa-university mb-2 d-block"></i>
                {{ __('messages.deposito_transferencia') }}
                <span class="sax-payment-caption">{{ __('messages.checkout_deposit_caption') }}</span>
            </button>

            @if ($hasBancardV2)
                <button type="button" class="sax-payment-method" id="btn-bancard_v2" data-payment-method="bancard_v2" aria-pressed="false">
                    <i class="fa fa-credit-card mb-2 d-block"></i>
                    {{ __('messages.checkout_bancard_label') }}
                    <span class="sax-payment-caption">{{ __('messages.checkout_bancard_caption') }}</span>
                </button>
            @endif

            @if ($hasRendixPix)
                <button type="button" class="sax-payment-method" id="btn-rendix_pix" data-payment-method="rendix_pix" aria-pressed="false">
                    <i class="fa-brands fa-pix mb-2 d-block"></i>
                    {{ __('messages.checkout_pix_label') }}
                    <span class="sax-payment-caption">{{ __('messages.checkout_pix_caption') }}</span>
                </button>
            @endif
        </div>

        @if ($hasRendixPix)
            <div class="alert alert-success border-0 rounded-3 mt-3 mb-0 text-start d-none" id="rendix-pix-notice">
                <div class="d-flex gap-3 align-items-start">
                    <i class="fa-brands fa-pix fs-4 mt-1"></i>
                    <div>
                        <strong>{{ __('messages.checkout_pix_notice_title') }}</strong>
                        <span class="d-block small mt-1">
                            {{ __('messages.checkout_pix_notice_body') }}
                        </span>
                    </div>
                </div>
                <div class="form-check mt-3 pt-3 border-top border-success-subtle">
                    <input class="form-check-input" type="checkbox" name="accept_pix_terms" value="1"
                           id="accept_pix_terms" @checked(old('accept_pix_terms'))>
                    <label class="form-check-label small" for="accept_pix_terms">
                        {{ __('messages.checkout_pix_terms_prefix') }}
                        <a href="{{ route('checkout.rendix.pix.terms') }}" target="_blank" rel="noopener"
                           class="fw-bold text-success">{{ __('messages.checkout_pix_terms_link') }}</a>
                        {{ __('messages.checkout_pix_terms_suffix') }}
                    </label>
                </div>
                @error('accept_pix_terms')
                    <div class="text-danger small mt-2">{{ $message }}</div>
                @enderror
            </div>
        @endif

        <p class="sax-payment-notice mt-4" id="payment-instruction">
            {{ __('messages.instrucao_pagamento_deposito') }}
        </p>

        <div class="sax-bancard-currency-notice d-none mt-4 text-start" id="bancard-currency-notice"
             data-pyg-rate="{{ $pygRate }}" data-pyg-sign="{{ $pygSign }}"
             data-selected-currency="{{ $selectedCurrencySign }}">
            <div class="sax-bancard-currency-notice__head">
                <i class="fa-solid fa-money-bill-transfer"></i>
                <div>
                    <span>{{ __('messages.checkout_bancard_processed_currency') }}</span>
                    <strong>{{ __('messages.checkout_pyg_currency') }}</strong>
                </div>
                <strong class="sax-bancard-currency-notice__amount" id="bancard-pyg-total">
                    {{ $pygSign }} {{ number_format($totalPedido * $pygRate, 0, ',', '.') }}
                </strong>
            </div>
            <p class="mb-0" id="bancard-country-warning">
                {{ __('messages.checkout_bancard_currency_reference') }}
            </p>
        </div>
    </div>

    <input type="hidden" name="payment_method" id="payment_method" value="{{ old('payment_method', 'deposito') }}">
    <input type="hidden" name="total_final" id="total_final" value="{{ $totalPedido }}">

    <div class="sax-checkout-box mt-4">
        <h4 class="sax-step-title">{{ __('messages.resumo_final') }}</h4>

        <div class="sax-cart-list mb-4">
            @foreach ($cart as $item)
                @php
                    $productSize = trim((string) ($item->product->size ?? $item->product->product_size ?? ''));
                    $productColor = trim((string) ($item->product->color ?? ''));
                    $isHexColor = preg_match('/^#?[0-9A-Fa-f]{6}$/', $productColor) === 1;
                    $normalizedColor = $isHexColor ? ('#' . ltrim($productColor, '#')) : null;
                @endphp
                <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light sax-order-item-row">
                    <div class="sax-cart-img-wrapper sax-order-item-image-wrap">
                        <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" 
                             alt="{{ $item->product->external_name ?? __('messages.table_product') }}" class="img-fluid">
                    </div>
                    <div class="flex-grow-1">
                        <p class="mb-0 sax-item-name text-truncate sax-order-item-name">
                            {{ $item->product->external_name ?? __('messages.table_product') }}
                        </p>
                        <small class="text-muted d-block">{{ __('messages.quantidade') }}: {{ $item->quantity }}</small>
                        <small class="text-muted d-block">SKU: {{ $item->product->sku ?? '-' }}</small>

                        @if ($productSize !== '')
                            <small class="text-muted d-block">{{ __('messages.tamanho') }}: {{ $productSize }}</small>
                        @endif

                        @if ($productColor !== '')
                            <small class="text-muted d-block sax-item-color-wrap">
                                {{ __('messages.cor') }}:
                                @if ($isHexColor)
                                    <i class="sax-item-color-dot" style="--item-color: {{ $normalizedColor }};"></i>
                                    {{ $normalizedColor }}
                                @else
                                    {{ $productColor }}
                                @endif
                            </small>
                        @endif
                    </div>
                    <div class="text-end">
                        <span class="d-block fw-bold">{{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</span>
                    </div>
                </div>
            @endforeach
        </div>

        {{-- Cupom de desconto --}}
        <div class="sax-cupon-box mb-4" id="cupon-box"
             data-apply-url="{{ route('user.cupons.apply') }}"
             data-remove-url="{{ route('user.cupons.remove') }}">
            @if ($cuponAplicado)
                <div class="sax-cupon-applied">
                    <div>
                        <span class="sax-cupon-applied-label">{{ __('messages.cupon_aplicado_label') }}</span>
                        <strong class="sax-cupon-applied-code">{{ $cuponAplicado->codigo }}</strong>
                        <span class="sax-cupon-applied-rule">{{ $cuponAplicado->rotuloDesconto() }} · {{ $cuponAplicado->rotuloEscopo() }}</span>
                    </div>
                    <button type="button" class="sax-cupon-remove" id="cupon-remove-btn" aria-label="{{ __('messages.cupon_remover_btn') }}">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            @else
                <label for="cupon-codigo-checkout" class="sax-cupon-label">
                    <i class="fas fa-tag me-1"></i>{{ __('messages.cupon_tem_codigo') }}
                </label>
                <div class="sax-cupon-input-group">
                    {{-- input sem 'name': o cupom vive na sessão, o form do pedido não o envia --}}
                    <input type="text" id="cupon-codigo-checkout" maxlength="60"
                           class="sax-cupon-input text-uppercase"
                           placeholder="{{ __('messages.cupon_placeholder') }}">
                    <button type="button" class="sax-cupon-btn" id="cupon-apply-btn">{{ __('messages.cupon_aplicar_btn') }}</button>
                </div>
            @endif
            <div class="sax-cupon-feedback" id="cupon-feedback" role="status"></div>
        </div>

        <div class="sax-summary-total pt-3">
            <div class="d-flex justify-content-between mb-2">
                <span class="text-muted">{{ __('messages.subtotal') }}</span>
                <span id="subtotal-display">{{ currency_format($subtotalPedido) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-2 {{ $descontoPedido > 0 ? '' : 'd-none' }}" id="desconto-row">
                <span class="text-muted">{{ __('messages.desconto') }}</span>
                <span id="desconto-display" class="text-success">- {{ currency_format($descontoPedido) }}</span>
            </div>
            <div class="d-flex justify-content-between mb-3" id="shipping-summary-row">
                <span class="text-muted" id="shipping-summary-label">{{ __('messages.envio') }}</span>
                <span id="frete-display" class="text-success small fw-bold text-uppercase">{{ __('messages.selecione_entrega') }}</span>
            </div>
            <div class="d-flex justify-content-between align-items-center border-top pt-3">
                <span class="fw-bold h5 mb-0">{{ __('messages.total') }}</span>
                <span id="total-geral-display" class="fw-bold h4 mb-0">{{ currency_format($totalPedido) }}</span>
            </div>

            {{-- Total sem frete (subtotal - desconto), usado pelo JS ao trocar a entrega --}}
            <span id="total-sem-frete" class="d-none" data-valor="{{ $totalPedido }}">{{ currency_format($totalPedido) }}</span>
        </div>
    </div>

    <div class="sax-checkout-box mt-4">
        <div id="terms-validation-message" class="alert alert-warning border-0 rounded-3 mb-3 {{ $errors->has('accept_terms') ? '' : 'd-none' }}" role="alert">
            <i class="fa fa-info-circle me-2"></i>
            <strong>{{ __('messages.checkout_terms_confirmation_title') }}</strong> {{ __('messages.checkout_terms_confirmation_body') }}
        </div>
        <div class="form-check d-flex align-items-start gap-2 m-0">
            <input class="form-check-input mt-1" type="checkbox" name="accept_terms" value="1" id="accept_terms" @checked(old('accept_terms'))>
            <label class="form-check-label text-start" for="accept_terms">
                {{ __('messages.checkout_terms_accept_prefix') }}
                <button type="button" class="btn btn-link d-inline p-0 align-baseline fw-bold text-decoration-underline checkout-policy-link"
                        data-bs-toggle="modal" data-bs-target="#checkoutPoliciesModal">{{ __('messages.checkout_terms_policies_link') }}</button>
                <span class="d-block small text-muted mt-1">{{ __('messages.checkout_terms_required_note') }}</span>
            </label>
        </div>
        @error('accept_terms')
            <div class="text-danger small mt-2">{{ __('messages.checkout_terms_required_error') }}</div>
        @enderror
    </div>

    <div class="modal fade" id="checkoutPoliciesModal" tabindex="-1" aria-labelledby="checkoutPoliciesModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 rounded-3 shadow">
                <div class="modal-header border-bottom px-4 py-3">
                    <div>
                        <span class="d-block small text-muted text-uppercase fw-bold tracking-wider">SAX Department</span>
                        <h2 class="modal-title h4 fw-bold mb-0" id="checkoutPoliciesModalLabel">{{ __('messages.checkout_policies_title') }}</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{{ __('messages.fechar') }}"></button>
                </div>

                <div class="modal-body p-4 p-lg-5">
                    @forelse(($policies ?? collect()) as $policy)
                        <article class="checkout-policy-content {{ !$loop->last ? 'border-bottom pb-4 mb-4' : '' }}">
                            <h3 class="h5 fw-bold mb-3">{{ $policy->title }}</h3>
                            {!! $policy->content !!}
                            <p class="small text-muted mb-0 mt-3">{{ __('messages.checkout_policy_last_update', ['date' => $policy->updated_at->format('d/m/Y')]) }}</p>
                        </article>
                    @empty
                        <div class="alert alert-light border mb-0">{{ __('messages.checkout_policies_unavailable') }}</div>
                    @endforelse
                </div>

                <div class="modal-footer border-top px-4 py-3">
                    <button type="button" class="btn btn-dark px-4" data-bs-dismiss="modal">{{ __('messages.checkout_policies_understood') }}</button>
                </div>
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
        payment_pix: @json(__('messages.checkout_pix_instruction')),
        payment_deposito: "{{ __('messages.instrucao_pagamento_deposito') }}",
        document_invalid: @json(__('messages.document_invalid_generic'))
    };

    window.cuponTexts = {
        aplicado: @json(__('messages.cupon_aplicado_label')),
        remover: @json(__('messages.cupon_remover_btn')),
        aplicar: @json(__('messages.cupon_aplicar_btn')),
        tem_codigo: @json(__('messages.cupon_tem_codigo')),
        placeholder: @json(__('messages.cupon_placeholder')),
        digite_codigo: @json(__('messages.cupon_digite_codigo')),
        erro_generico: @json(__('messages.cupon_invalido')),
        erro_conexao: @json(__('messages.cupon_erro_conexao'))
    };
</script>
