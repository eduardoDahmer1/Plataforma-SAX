@extends('layout.dashboard')

@section('content')
    <div class="sax-order-details-wrapper">
        @php
            $subtotalPedido = $order->items->sum(fn ($item) => $item->price * $item->quantity);
            $descontoPedido = (float) ($order->discount ?? 0);
            $isPaid = $order->payment_status === 'paid';
            $isCanceled = in_array($order->status, ['canceled', 'cancelled', 'failed'], true);
            $progress = $isCanceled ? 0 : ($isPaid ? 2 : 1);
            if (in_array($order->status, ['processing', 'shipped'], true)) $progress = 3;
            if (in_array($order->status, ['completed', 'delivered'], true)) $progress = 4;
            $isParaguay = $order->shipping == 3 || strtoupper((string) $order->country) === 'PY';
            $catalogAvailable = (bool) ($catalogIntegrationStatus['available'] ?? true);
            $controlledPaymentMethods = ['deposito', 'bancard_v2', 'rendix_pix'];
            $paymentMethodManuallyEnabled = ! in_array((string) $order->payment_method, $controlledPaymentMethods, true)
                || app(\App\Services\StoreControlService::class)->paymentEnabled((string) $order->payment_method);
            $paymentMethodEnabled = $catalogAvailable && $paymentMethodManuallyEnabled;
        @endphp
        @if (session('warning'))
            <div class="alert alert-warning mb-4 shadow-sm border-0" role="alert"><i
                    class="fas fa-exclamation-triangle me-2"></i> {{ session('warning') }}</div>
        @endif
        @if (session('success'))
            <div class="alert alert-success mb-4 shadow-sm border-0" role="alert"><i class="fas fa-check-circle me-2"></i>
                {{ session('success') }}</div>
        @endif
        @if (session('info'))
            <div class="alert alert-info mb-4 shadow-sm border-0" role="alert"><i class="fas fa-info-circle me-2"></i>
                {{ session('info') }}</div>
        @endif

        <div class="sax-order-hero mb-4">
            <div class="sax-order-hero__main">
                <a href="{{ route('user.orders') }}" class="sax-order-back"><i class="fas fa-arrow-left"></i> Meus pedidos</a>
                <span class="sax-order-eyebrow">Pedido realizado em {{ $order->created_at->format('d/m/Y \à\s H:i') }}</span>
                <h1>Pedido <strong>#{{ $order->order_number ?: $order->id }}</strong></h1>
                <p>Acompanhe o pagamento, a preparação e a entrega em um só lugar.</p>
            </div>
            <div class="sax-order-hero__total">
                <span>Total do pedido</span>
                <strong>{{ order_money($order, $order->total) }}</strong>
                <span class="status-badge-custom status-payment {{ $order->payment_status }}">
                    <i class="fas {{ $isPaid ? 'fa-check-circle' : 'fa-clock' }}"></i>
                    {{ __('messages.payment_status_' . $order->payment_status) }}
                </span>
            </div>
        </div>

        <div class="sax-order-progress mb-4 {{ $isCanceled ? 'is-canceled' : '' }}">
            @foreach ([
                1 => ['fa-receipt', 'Pedido recebido'],
                2 => ['fa-credit-card', 'Pagamento'],
                3 => ['fa-box-open', 'Preparação / envio'],
                4 => ['fa-check', 'Concluído'],
            ] as $step => [$icon, $label])
                <div class="sax-order-progress__step {{ $progress >= $step ? 'is-active' : '' }}">
                    <span><i class="fas {{ $icon }}"></i></span>
                    <strong>{{ $label }}</strong>
                </div>
            @endforeach
        </div>

        @if ($isCanceled)
            <div class="sax-order-message is-danger mb-4">
                <i class="fas fa-circle-exclamation"></i>
                <div><strong>Este pedido não foi concluído.</strong><span>Confira o status do pagamento abaixo ou fale com nossa equipe para receber ajuda.</span></div>
            </div>
        @elseif (! $isPaid && ! $catalogAvailable)
            <div class="sax-order-message is-warning mb-4">
                <i class="fas fa-sync-alt"></i>
                <div>
                    <strong>{{ __('messages.catalog_purchase_paused_title') }}</strong>
                    <span>{{ __('messages.catalog_purchase_paused_message') }}</span>
                </div>
            </div>
        @elseif (! $isPaid && ! $paymentMethodManuallyEnabled)
            <div class="sax-order-message is-warning mb-4">
                <i class="fas fa-lock"></i>
                <div>
                    <strong>{{ __('messages.store_payment_disabled_title') }}</strong>
                    <span>{{ __('messages.store_payment_disabled_message') }}</span>
                </div>
            </div>
        @elseif (! $isPaid && $order->payment_method === 'bancard_v2')
            <div class="sax-order-message is-warning mb-4">
                <i class="fas fa-credit-card"></i>
                <div>
                    <strong>Seu pagamento ainda está pendente.</strong>
                    <span>Você pode tentar novamente. O Bancard processa a cobrança em guaranis (PYG).
                        @if (! $isParaguay)
                            Cartões do Brasil e de outros países podem ter conversão, IOF, spread e encargos definidos pelo banco emissor.
                        @endif
                    </span>
                    <a href="{{ route('checkout.bancard.v2', $order->id) }}" class="sax-order-message__action">
                        <i class="fas fa-sync-alt"></i> Tentar pagamento novamente
                    </a>
                </div>
            </div>
        @elseif (! $isPaid && $order->payment_method === 'rendix_pix')
            <div class="sax-order-message is-warning mb-4">
                <i class="fa-brands fa-pix"></i>
                <div>
                    <strong>{{ __('messages.order_pix_pending_title') }}</strong>
                    <span>{{ __('messages.order_pix_pending_body') }}</span>
                    <a href="{{ route('checkout.rendix.pix', $order->id) }}" class="sax-order-message__action">
                        <i class="fas fa-qrcode"></i> {{ __('messages.order_pix_open_payment') }}
                    </a>
                </div>
            </div>
        @elseif ($isPaid)
            <div class="sax-order-message is-success mb-4">
                <i class="fas fa-shield-alt"></i>
                <div><strong>Pagamento confirmado.</strong><span>Agora você pode acompanhar a preparação e a entrega do pedido por esta página.</span></div>
            </div>
        @endif

        <div class="row g-4 mb-5">
            <div class="col-lg-5">
                <div class="sax-premium-card h-100 shadow-sm border-0">
                    <h6 class="card-sax-header bg-white border-bottom"><i class="fas fa-receipt me-2"></i>
                        {{ __('messages.detalhes_de_pagamento') }}</h6>
                    <div class="card-sax-body">
                        <div class="info-row">
                            <span class="label">{{ __('messages.estado_pedido') }}</span>
                            <span class="status-badge-custom status-order"><i class="fas fa-box"></i>
                                {{ __('messages.status_' . $order->status) }}</span>
                        </div>
                        <div class="info-row">
                            <span class="label">{{ __('messages.estado_pagamento') }}</span>
                            <span class="status-badge-custom status-payment {{ $order->payment_status }}"><i
                                    class="fas fa-wallet"></i>
                                {{ __('messages.payment_status_' . $order->payment_status) }}</span>
                        </div>
                        <div class="info-row border-0">
                            <span class="label">{{ __('messages.metodo') }}</span>
                            <span
                                class="badge-payment-sax {{ $order->payment_method }} shadow-sm">{{ ucfirst($order->payment_method) }}</span>
                        </div>
                        <div class="mt-4 p-3 bg-light rounded-3">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="label m-0">{{ __('messages.subtotal') }}:</span>
                                <span class="value fw-bold text-dark">
                                    {{ order_money($order, $subtotalPedido) }}
                                </span>
                            </div>

                            @if ($descontoPedido > 0)
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="label m-0">
                                        {{ __('messages.desconto') }}
                                        @if ($order->cupon)
                                            <span class="sax-cupon-produto__codigo ms-1">{{ $order->cupon->codigo }}</span>
                                        @endif
                                    </span>
                                    <span class="value fw-bold text-success">
                                        - {{ order_money($order, $descontoPedido) }}
                                    </span>
                                </div>
                            @endif

                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <span class="label m-0">{{ __('messages.frete') }}:</span>
                                <span class="value fw-bold text-dark">
                                    {{ order_money($order, $order->shipping_cost ?? 0) }}
                                </span>
                            </div>

                            <hr class="my-2">

                            <div class="d-flex justify-content-between align-items-center">
                                <span class="label m-0 fw-bold">{{ __('messages.total_geral') }}:</span>
                                <span class="value fw-bold text-dark fs-4">
                                    {{ order_money($order, $order->total) }}
                                </span>
                            </div>
                        </div>

                        @if ($order->payment_currency === 'PYG' && $order->payment_amount)
                            <div class="sax-order-pyg-summary mt-3">
                                <span>Valor processado pelo Bancard</span>
                                <strong>G$ {{ number_format((float) $order->payment_amount, 0, ',', '.') }}</strong>
                                <small>Cotação registrada: G$ {{ number_format((float) $order->payment_exchange_rate, 2, ',', '.') }} por US$ 1</small>
                            </div>
                        @endif

                        @if ($order->payment_method === 'rendix_pix' && $order->payment_currency === 'BRL' && $order->payment_amount)
                            <div class="sax-order-pyg-summary mt-3">
                                <span>{{ __('messages.order_pix_processed_value') }}</span>
                                <strong>R$ {{ number_format((float) $order->payment_amount, 2, ',', '.') }}</strong>
                                @if ($order->payment_exchange_rate)
                                    <small>Taxa informada pela Rendix: {{ number_format((float) $order->payment_exchange_rate, 4, ',', '.') }}</small>
                                @endif
                            </div>
                        @endif

                        @if ($order->receipt && $order->payment_status === 'paid')
                            <div class="mt-3 pt-3 border-top">
                                <label class="sax-label d-block mb-2 text-uppercase"
                                    style="font-size:9px">{{ __('messages.recibo_de_compra') }}</label>
                                <a href="{{ route('receipts.show', $order->receipt) }}"
                                    class="btn btn-dark btn-sax-sm w-100 py-2 mb-2">
                                    <i class="fas fa-receipt me-2"></i> {{ __('messages.ver_recibo') }}
                                </a>
                                @if ($order->receipt->pdf_path && \Storage::exists($order->receipt->pdf_path))
                                    <a href="{{ route('receipts.download', $order->receipt) }}"
                                        class="btn btn-outline-dark btn-sax-sm w-100 py-2">
                                        <i class="fas fa-download me-2"></i> {{ __('messages.descargar_pdf') }}
                                    </a>
                                @endif
                            </div>
                        @endif

                        @if (($order->payment_method ?? null) === 'bancard_v2')
                            @if ($order->payment_status === 'paid' && !empty($order->shop_process_id))
                                <div class="mt-3">
                                    <a href="{{ route('bancard.v2.success', ['shop_process_id' => $order->shop_process_id]) }}"
                                        class="btn btn-outline-success btn-sax-sm w-100 py-2">
                                        <i class="fas fa-check-circle me-2"></i> {{ __('messages.ver_confirmacao') }}
                                    </a>
                                </div>
                            @elseif ($order->payment_status !== 'paid' && $paymentMethodEnabled)
                                <div class="mt-3">
                                    <a href="{{ route('checkout.bancard.v2', $order->id) }}"
                                        class="btn btn-outline-primary btn-sax-sm w-100 py-2">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        {{ __('messages.tentar_pagamento_novamente') }}
                                    </a>
                                </div>
                            @endif
                        @endif
                        @if (($order->payment_method ?? null) === 'rendix_pix' && $order->payment_status !== 'paid' && $paymentMethodEnabled)
                            <div class="mt-3">
                                <a href="{{ route('checkout.rendix.pix', $order->id) }}"
                                   class="btn btn-outline-success btn-sax-sm w-100 py-2">
                                    <i class="fa-brands fa-pix me-2"></i> {{ __('messages.order_pix_open_or_generate') }}
                                </a>
                            </div>
                        @endif
                        @if ($order->payment_method === 'deposito' && $order->payment_status !== 'paid' && $paymentMethodEnabled)
                            <div class="mt-3">
                                <button type="button" class="btn btn-outline-dark btn-sax-sm w-100 py-2"
                                    data-bs-toggle="modal" data-bs-target="#modalContasBancarias">
                                    <i class="fas fa-university me-2"></i> {{ __('messages.ver_dados_bancarios') }}
                                </button>
                            </div>
                        @endif

                        <div class="mt-4 pt-3 border-top">
                            @if ($order->deposit_receipt)
                                <label class="sax-label d-block mb-2 text-center text-uppercase"
                                    style="font-size: 9px">{{ __('messages.comprovante_enviado_cap') }}</label>
                                <div class="receipt-preview-link rounded border shadow-sm"
                                    data-receipt-preview="{{ asset('storage/' . $order->depositReceiptPath()) }}">
                                    <img src="{{ asset('storage/' . $order->depositReceiptPath()) }}"
                                        class="img-fluid d-block mx-auto">
                                    <div class="overlay"><i class="fas fa-search-plus"></i>
                                        {{ __('messages.ver_ampliado') }}</div>
                                </div>
                            @elseif ($order->payment_method === 'deposito' && $order->payment_status !== 'paid' && $order->status !== 'canceled' && $paymentMethodEnabled)
                                <div class="upload-sax-box border-dashed p-3 text-center">
                                    <h6 class="x-small fw-bold text-success mb-3"><i class="fa fa-file-upload me-1"></i>
                                        {{ __('messages.adjuntar_comprovante') }}</h6>
                                    <form action="{{ route('orders.deposit.submit', $order->id) }}" method="POST"
                                        enctype="multipart/form-data">
                                        @csrf
                                        <input type="file" name="deposit_receipt"
                                            class="form-control form-control-sm mb-2" required>
                                        <button type="submit"
                                            class="btn btn-dark btn-sax-sm w-100 fw-bold">{{ __('messages.enviar_agora') }}</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            <div class="col-lg-7">
                <div class="sax-premium-card h-100 shadow-sm border-0">
                    <h6 class="card-sax-header bg-white border-bottom"><i class="fas fa-map-marker-alt me-2"></i>
                        {{ __('messages.envio_e_cliente') }}</h6>
                    <div class="card-sax-body">
                        <div class="mb-4">
                            <label class="sax-label mb-1">{{ __('messages.destinatario') }}</label>
                            <p class="m-0 fw-bold text-dark">{{ $order->name }} {{ $order->surname }}</p>
                            <p class="text-muted small m-0"><i class="far fa-envelope me-1"></i> {{ $order->email }}</p>
                            <p class="text-muted small m-0"><i class="fas fa-phone-alt me-1"></i> {{ $order->phone }}</p>
                            <p class="text-muted small m-0">Doc: {{ $order->document }}</p>
                        </div>
                        <div class="p-3 bg-light rounded-3">
                            <label class="sax-label mb-2">{{ __('messages.endereco_de_entrega') }}</label>
                            <p class="small text-dark mb-0">
                                @if ($order->shipping == 3)
                                    <span class="badge bg-dark rounded-pill px-3 py-2"><i class="fas fa-store me-1"></i>
                                        {{ __('messages.recolha_na_loja') }}:
                                        {{ match ((int) $order->store) {
                                            1 => 'SAX Ciudad del Este',
                                            2 => 'SAX Assunção',
                                            3 => 'SAX Pedro Juan Caballero',
                                            default => 'Unidade SAX selecionada',
                                        } }}</span>
                                @else
                                    <span class="d-block fw-semibold">{{ $order->street }}, {{ $order->number }}</span>
                                    @if ($order->district)
                                        <span class="d-block text-secondary">{{ $order->district }}</span>
                                    @endif
                                    @if ($order->complement)
                                        <span
                                            class="text-muted italic d-block my-1 border-start ps-2 border-secondary">{{ $order->complement }}</span>
                                    @endif
                                    @if ($order->observations)
                                        <span
                                            class="text-muted italic d-block my-1 border-start ps-2 border-secondary">{{ $order->observations }}</span>
                                    @endif
                                    <span class="d-block">{{ $order->city }}, {{ $order->state }}</span>
                                    <span
                                        class="x-small text-uppercase fw-bold text-secondary">{{ strtoupper($order->country) }}
                                        • CP: {{ $order->cep }}</span>
                                @endif
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <h5 class="sax-title-sub text-uppercase letter-spacing-2 mb-4 d-flex align-items-center">
            <i class="fas fa-shopping-bag me-2"></i> {{ __('messages.produtos_do_pedido') }}
        </h5>
        <div class="order-items-list">
            @foreach ($order->items as $item)
                <div class="item-sax-row shadow-sm border-0 rounded-4 bg-white p-3 mb-3">
                    <div class="row align-items-center">
                        <div class="col-3 col-md-2 text-center"><img
                                src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}"
                                class="img-fluid rounded-3 object-fit-contain shadow-sm" style="max-height: 80px;"></div>
                        <div class="col-9 col-md-4">
                            <h6 class="mb-1 text-uppercase fw-bold small text-dark">
                                {{ $item->name ?? ($item->product->external_name ?? 'Producto') }}</h6>
                            <span class="badge bg-light text-secondary border x-small fw-normal">SKU:
                                {{ $item->sku ?? '-' }}</span>
                        </div>
                        <div class="col-4 col-md-2 mt-3 mt-md-0 text-center"><label
                                class="sax-label d-block text-muted">{{ __('messages.cant_abrev') }}</label><span
                                class="fw-bold fs-6">{{ $item->quantity }}</span></div>
                        <div class="col-4 col-md-2 mt-3 mt-md-0 text-center"><label
                                class="sax-label d-block text-muted">{{ __('messages.unitario') }}</label><span
                                class="text-muted small">{{ order_money($order, $item->price) }}</span></div>
                        <div class="col-4 col-md-2 mt-3 mt-md-0 text-end pe-4"><label
                            class="sax-label d-block text-muted">{{ __('messages.subtotal') }}</label><span
                                class="fw-bold text-dark fs-6">{{ order_money($order, $item->price * $item->quantity) }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if ($order->payment_method === 'deposito')
            <div class="modal fade" id="modalContasBancarias" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog modal-lg modal-dialog-centered">
                    <div class="modal-content border-0 shadow-lg">
                        <div class="modal-header bg-dark text-white p-4">
                            <h5 class="modal-title text-uppercase letter-spacing-1 small fw-bold">
                                <i class="fas fa-university me-2"></i> {{ __('messages.dados_bancarios') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"
                                aria-label="{{ __('messages.fechar') }}"></button>
                        </div>
                        <div class="modal-body p-4 bg-light">
                            <div class="alert alert-info border-0 rounded-0 x-small text-uppercase fw-bold mb-4">
                                {{ __('messages.escolha_conta_deposito') }}
                            </div>
                            <div class="row g-3">
                                @foreach ($bankAccounts as $bank)
                                    <div class="col-12 col-md-6">
                                        <div class="p-4 border-0 shadow-sm rounded-4 bg-white h-100">
                                            <h6 class="fw-bold mb-3 text-uppercase small text-primary"
                                                style="letter-spacing: 1px;">{{ $bank->name }}</h6>
                                            <div class="sax-bank-details small text-dark opacity-75">
                                                {!! nl2br(e($bank->bank_details)) !!}
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                        <div class="modal-footer border-0 p-4">
                            <button type="button" class="btn btn-dark w-100 py-2 text-uppercase fw-bold tracking-wider"
                                data-bs-dismiss="modal">{{ __('messages.entendido') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif

        <div class="help-footer-sax text-center mt-5 py-5 border-top">
            <p class="text-muted small mb-3 text-uppercase letter-spacing-1">{{ __('messages.necesitas_ayuda') }}</p>
            <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode(__('messages.whatsapp_help_order_prefix') . $order->id) }}"
                target="_blank" class="btn btn-outline-success rounded-pill px-5 py-2 btn-sm fw-bold shadow-sm">
                <i class="fab fa-whatsapp me-2"></i> {{ __('messages.contactar_suporte') }}
            </a>

            <div class="img-modal-backdrop" id="imgModal" role="dialog" aria-modal="true" aria-label="Visualização do comprovante">
                <img src="" id="modalImg" alt="Comprovante de pagamento">
            </div>
        </div>
    @endsection

@push('scripts')
    <script src="{{ asset('js/user-order.js') }}?v={{ filemtime(public_path('js/user-order.js')) }}"></script>
@endpush
