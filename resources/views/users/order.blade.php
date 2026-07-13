@extends('layout.dashboard')

@section('content')
    <style>
        .sax-order-details-wrapper {
            animation: fadeIn 0.5s ease-in-out;
        }

        .status-group {
            display: flex;
            flex-direction: column;
            gap: 10px;
        }

        .info-row {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px dashed #eee;
        }

        .info-row:last-child {
            border-bottom: none;
        }

        .label {
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            color: #888;
            font-weight: 700;
        }

        .status-badge-custom {
            padding: 4px 12px;
            border-radius: 50px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .status-order {
            background: #f0f0f0;
            color: #444;
            border: 1px solid #ddd;
        }

        .status-payment {
            background: #eef2ff;
            color: #4f46e5;
            border: 1px solid #c7d2fe;
        }

        .status-payment.paid {
            background: #ecfdf5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .status-payment.pending {
            background: #fffbeb;
            color: #d97706;
            border: 1px solid #fde68a;
        }

        .receipt-preview-link {
            position: relative;
            display: block;
            overflow: hidden;
            transition: 0.3s;
            cursor: pointer;
        }

        .receipt-preview-link:hover .overlay {
            opacity: 1;
        }

        .receipt-preview-link .overlay {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.4);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: 0.3s;
        }

        .img-modal-backdrop {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0, 0, 0, 0.9);
            z-index: 9999;
            display: none;
            align-items: center;
            justify-content: center;
            cursor: zoom-out;
        }

        .img-modal-backdrop img {
            max-width: 90%;
            max-height: 90%;
            border-radius: 8px;
        }

        @keyframes fadeIn {
            from {
                opacity: 0;
                transform: translateY(10px);
            }

            to {
                opacity: 1;
                transform: translateY(0);
            }
        }
    </style>

    <div class="sax-order-details-wrapper">
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

        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">{{ __('messages.resumo_do_pedido_titulo') }}</h2>
                <div class="sax-divider-dark"></div>
                <span class="text-muted x-small">
                    <i class="far fa-calendar-alt me-1"></i> {{ $order->created_at->format('d/m/Y H:i') }} •
                    <span class="text-dark fw-bold">#{{ $order->id }}</span>
                </span>
            </div>
            <a href="{{ route('user.dashboard') }}" class="btn-back-minimal"><i class="fas fa-chevron-left me-1"></i>
                {{ __('messages.voltar_dashboard') }}</a>
        </div>

        <div class="row g-4 mb-5">
            <div class="col-md-6">
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
                        @php
                            // Subtotal a partir dos itens: o total do pedido já vem com
                            // desconto e frete aplicados.
                            $subtotalPedido = $order->items->sum(fn ($item) => $item->price * $item->quantity);
                            $descontoPedido = (float) ($order->discount ?? 0);
                        @endphp

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
                            @elseif ($order->payment_status !== 'paid' && $order->status !== 'canceled')
                                <div class="mt-3">
                                    <a href="{{ route('checkout.bancard.v2', $order->id) }}"
                                        class="btn btn-outline-primary btn-sax-sm w-100 py-2">
                                        <i class="fas fa-sync-alt me-2"></i>
                                        {{ __('messages.tentar_pagamento_novamente') }}
                                    </a>
                                </div>
                            @endif
                        @endif
                        @if ($order->payment_method === 'deposito' && $order->payment_status !== 'paid')
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
                                    onclick="openModal('{{ asset('storage/' . $order->deposit_receipt) }}')">
                                    <img src="{{ asset('storage/' . $order->deposit_receipt) }}"
                                        class="img-fluid d-block mx-auto">
                                    <div class="overlay"><i class="fas fa-search-plus"></i>
                                        {{ __('messages.ver_ampliado') }}</div>
                                </div>
                            @elseif ($order->payment_status !== 'paid' && $order->status !== 'canceled')
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

            <div class="col-md-6">
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
                                        {{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Assunción' }}</span>
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

            <div class="img-modal-backdrop" id="imgModal" onclick="this.style.display='none'">
                <img src="" id="modalImg">
            </div>
        </div>

        <script>
            function openModal(src) {
                document.getElementById('modalImg').src = src;
                document.getElementById('imgModal').style.display = 'flex';
            }
        </script>
    @endsection
