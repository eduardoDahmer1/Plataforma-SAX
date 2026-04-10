@extends('layout.dashboard')

@section('content')
<div class="sax-order-details-wrapper">
    @if (session('warning'))
    <div class="alert alert-warning mb-4" role="alert">
        {{ session('warning') }}
    </div>
    @endif

    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">{{ __('messages.resumo_do_pedido_titulo') }}</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">ID: #{{ $order->id }} • {{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar_dashboard') }}
        </a>
    </div>

    <div class="row g-4 mb-5">
        {{-- Card 1: Informações de Compra --}}
        <div class="col-md-6">
            <div class="sax-premium-card h-100">
                <h6 class="card-sax-header"><i class="fas fa-receipt me-2"></i> {{ __('messages.detalhes_de_pagamento') }}</h6>
                <div class="card-sax-body">
                    <div class="info-row">
                        <span class="label">{{ __('messages.estado') }}</span>
                        <span class="status-indicator-sax {{ $order->status }}">
                            <span class="dot"></span> {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">{{ __('messages.metodo') }}</span>
                        <span class="badge-payment-sax {{ $order->payment_method }}">{{ ucfirst($order->payment_method) }}</span>
                    </div>
                    <div class="info-row total-row">
                        <span class="label">{{ __('messages.total') }}:</span>
                        <span class="value fw-bold text-dark fs-5">{{ currency_format($order->items->sum(fn($i) => $i->price * $i->quantity)) }}</span>
                    </div>

                    {{-- --- LOGICA BANCARD --- --}}
                    @if (($order->payment_method ?? null) === 'bancard_v2')
                        @if (strtolower((string) $order->status) === 'paid' && !empty($order->shop_process_id))
                        <div class="mt-3">
                            <a href="{{ route('bancard.v2.success', ['shop_process_id' => $order->shop_process_id]) }}" class="btn btn-outline-success btn-sax-sm w-100">
                                <i class="fas fa-check-circle me-2"></i> {{ __('messages.ver_confirmacao') }}
                            </a>
                        </div>
                        @elseif (strtolower((string) $order->status) !== 'paid')
                        <div class="mt-3">
                            <a href="{{ route('checkout.bancard.v2', $order->id) }}" class="btn btn-outline-primary btn-sax-sm w-100">
                                <i class="fas fa-sync-alt me-2"></i> {{ __('messages.tentar_pagamento_novamente') }}
                            </a>
                        </div>
                        @endif
                    @endif

                    {{-- --- LOGICA DEPÓSITO --- --}}
                    @if ($order->payment_method === 'deposito' && strtolower((string) $order->status) !== 'paid')
                    <div class="mt-3">
                        <button type="button" class="btn btn-outline-dark btn-sax-sm w-100" data-bs-toggle="modal" data-bs-target="#modalContasBancarias">
                            <i class="fas fa-university me-2"></i> {{ __('messages.ver_dados_bancarios') }}
                        </button>
                    </div>
                    @endif

                    <div class="mt-4 pt-3 border-top">
                        @if ($order->deposit_receipt)
                        <label class="sax-label d-block mb-2">{{ __('messages.comprovante_enviado_cap') }}</label>
                        <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank" class="receipt-preview-link">
                            <img src="{{ asset('storage/' . $order->deposit_receipt) }}" class="img-fluid rounded border shadow-sm">
                            <div class="overlay"><i class="fas fa-search-plus"></i> {{ __('messages.ver_ampliado') }}</div>
                        </a>
                        @elseif (strtolower((string) $order->status) !== 'paid')
                        <div class="upload-sax-box">
                            <h6 class="x-small fw-bold text-success mb-2"><i class="fa fa-file-upload"></i> {{ __('messages.adjuntar_comprovante') }}</h6>
                            <form action="{{ route('orders.deposit.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                                @csrf
                                <input type="file" name="deposit_receipt" class="form-control sax-input-file mb-2" required>
                                <button type="submit" class="btn btn-dark btn-sax-sm w-100">{{ __('messages.enviar_agora') }}</button>
                            </form>
                        </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Card 2: Dados Pessoais e Entrega --}}
        <div class="col-md-6">
            <div class="sax-premium-card h-100">
                <h6 class="card-sax-header"><i class="fas fa-map-marker-alt me-2"></i> {{ __('messages.envio_e_cliente') }}</h6>
                <div class="card-sax-body">
                    <div class="mb-4">
                        <label class="sax-label">{{ __('messages.destinatario') }}</label>
                        <p class="m-0 fw-semibold">{{ $order->name ?? ($order->user->name ?? '-') }}</p>
                        <p class="text-muted small m-0">{{ $order->email ?? ($order->user->email ?? '-') }}</p>
                        <p class="text-muted small">{{ $order->phone ?? ($order->user->phone_number ?? '-') }}</p>
                    </div>

                    <div>
                        <label class="sax-label">{{ __('messages.endereco_de_entrega') }}</label>
                        <p class="small text-dark mb-1">
                            @if($order->shipping == 3)
                            <span class="badge bg-dark rounded-0">{{ __('messages.recolha_na_loja') }}
                                {{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Assunción' }}</span>
                            @else
                            {{ $order->street ?? ($order->user->street ?? '-') }},
                            {{ $order->number ?? ($order->user->number ?? '-') }}<br>
                            {{ $order->city ?? ($order->user->city ?? '-') }},
                            {{ $order->state ?? ($order->user->state ?? '-') }}<br>
                            CP: {{ $order->cep ?? ($order->user->cep ?? '-') }}
                            @endif
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Seção de Itens --}}
    <h5 class="sax-title-sub text-uppercase letter-spacing-2 mb-4">{{ __('messages.produtos_do_pedido') }}</h5>
    <div class="order-items-list">
        @foreach ($order->items as $item)
        <div class="item-sax-row shadow-sm border rounded-4 bg-white p-3 mb-3">
            <div class="row align-items-center">
                <div class="col-3 col-md-2 text-center">
                    <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" class="img-fluid rounded object-fit-contain" style="max-height: 80px;">
                </div>
                <div class="col-9 col-md-4">
                    <h6 class="mb-1 text-uppercase fw-bold small">{{ $item->product->external_name ?? 'Producto' }}</h6>
                    <span class="text-muted x-small">SKU: {{ $item->product->sku ?? '-' }}</span>
                </div>
                <div class="col-4 col-md-2 mt-3 mt-md-0 text-center">
                    <label class="sax-label d-block">{{ __('messages.cant_abrev') }}</label>
                    <span class="fw-bold">{{ $item->quantity }}</span>
                </div>
                <div class="col-4 col-md-2 mt-3 mt-md-0 text-center">
                    <label class="sax-label d-block">{{ __('messages.unitario') }}</label>
                    <span class="text-muted">{{ currency_format($item->price) }}</span>
                </div>
                <div class="col-4 col-md-2 mt-3 mt-md-0 text-end pe-4">
                    <label class="sax-label d-block">SUBTOTAL</label>
                    <span class="fw-bold text-dark">{{ currency_format($item->price * $item->quantity) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- MODAL DE CONTAS BANCÁRIAS --}}
    @if ($order->payment_method === 'deposito')
    <div class="modal fade" id="modalContasBancarias" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white">
                    <h5 class="modal-title text-uppercase letter-spacing-1 small">{{ __('messages.dados_bancarios') }}</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <p class="text-muted small mb-4 text-center">{{ __('messages.escolha_conta_deposito') }}</p>
                    <div class="row g-3">
                        @foreach ($bankAccounts as $bank)
                        <div class="col-12 col-md-6">
                            <div class="p-3 border rounded bg-light h-100">
                                <h6 class="fw-bold mb-2 text-uppercase small text-dark" style="letter-spacing: 1px;">{{ $bank->name }}</h6>
                                <div class="sax-bank-details small text-muted">
                                    {!! nl2br(e($bank->bank_details)) !!}
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">{{ __('messages.entendido') }}</button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Suporte --}}
    <div class="help-footer-sax text-center mt-5 py-4 border-top">
        <p class="text-muted small mb-3 text-uppercase letter-spacing-1">{{ __('messages.necesitas_ayuda') }}</p>
        <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode('Hola, necesito ayuda con mi pedido #' . $order->id) }}" target="_blank" class="btn btn-outline-success rounded-pill px-4 btn-sm fw-bold">
            <i class="fab fa-whatsapp me-2"></i> {{ __('messages.contactar_suporte') }}
        </a>
    </div>
</div>
@endsection

<style>
/* CSS Adicional para o Modal e Detalhes */
.sax-bank-details {
    line-height: 1.5;
    word-break: break-word;
}

.modal-content {
    border-radius: 15px;
}

/* Reaproveitando os seus estilos existentes */
.sax-premium-card {
    background: #fff;
    border: 1px solid #f0f0f0;
    border-radius: 12px;
    overflow: hidden;
}

.card-sax-header {
    background: #fafafa;
    padding: 15px 20px;
    font-size: 0.75rem;
    font-weight: 800;
    letter-spacing: 1px;
    border-bottom: 1px solid #f0f0f0;
    margin: 0;
}

.card-sax-body {
    padding: 25px;
}

.info-row {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-bottom: 15px;
}

.info-row .label {
    font-size: 0.7rem;
    font-weight: 700;
    color: #999;
}

.total-row {
    margin-top: 20px;
    padding-top: 15px;
    border-top: 1px dashed #eee;
}

.receipt-preview-link {
    position: relative;
    display: block;
    max-width: 200px;
}

.receipt-preview-link .overlay {
    position: absolute;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0, 0, 0, 0.4);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
    opacity: 0;
    transition: 0.3s;
}

.receipt-preview-link:hover .overlay {
    opacity: 1;
}

.upload-sax-box {
    background: #f8fff9;
    border: 1px dashed #28a745;
    padding: 15px;
    border-radius: 8px;
}

.sax-input-file {
    font-size: 0.75rem;
    border: 1px solid #eee;
}

.item-sax-row {
    transition: 0.3s;
}

.item-sax-row:hover {
    transform: scale(1.01);
    box-shadow: 0 10px 20px rgba(0, 0, 0, 0.05) !important;
}

.sax-title-sub {
    font-size: 0.9rem;
    font-weight: 700;
    border-left: 4px solid #000;
    padding-left: 15px;
}

.x-small {
    font-size: 0.65rem;
}
</style>