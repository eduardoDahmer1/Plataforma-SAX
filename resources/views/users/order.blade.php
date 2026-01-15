@extends('layout.dashboard')

@section('content')
<div class="sax-order-details-wrapper">
    {{-- Header --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Resumen del Pedido</h2>
            <div class="sax-divider-dark"></div>
            <span class="text-muted x-small">ID: #{{ $order->id }} • {{ $order->created_at->format('d/m/Y') }}</span>
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER
        </a>
    </div>

    <div class="row g-4 mb-5">
        {{-- Card 1: Informações de Compra --}}
        <div class="col-md-6">
            <div class="sax-premium-card h-100">
                <h6 class="card-sax-header"><i class="fas fa-receipt me-2"></i> DETALLES DE PAGO</h6>
                <div class="card-sax-body">
                    <div class="info-row">
                        <span class="label">ESTADO:</span>
                        <span class="status-indicator-sax {{ $order->status }}">
                            <span class="dot"></span> {{ ucfirst($order->status) }}
                        </span>
                    </div>
                    <div class="info-row">
                        <span class="label">MÉTODO:</span>
                        <span class="badge-payment-sax {{ $order->payment_method }}">{{ ucfirst($order->payment_method) }}</span>
                    </div>
                    <div class="info-row total-row">
                        <span class="label">TOTAL:</span>
                        <span class="value fw-bold text-dark fs-5">{{ currency_format($order->items->sum(fn($i) => $i->price * $i->quantity)) }}</span>
                    </div>

                    {{-- Seção de Comprovante --}}
                    <div class="mt-4 pt-3 border-top">
                        @if ($order->deposit_receipt)
                            <label class="sax-label d-block mb-2">COMPROBANTE ENVIADO</label>
                            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank" class="receipt-preview-link">
                                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" class="img-fluid rounded border shadow-sm">
                                <div class="overlay"><i class="fas fa-search-plus"></i> Ver Ampliado</div>
                            </a>
                        @else
                            <div class="upload-sax-box">
                                <h6 class="x-small fw-bold text-success mb-2"><i class="fa fa-file-upload"></i> ADJUNTAR COMPROBANTE</h6>
                                <form action="{{ route('orders.deposit.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                                    @csrf
                                    <input type="file" name="deposit_receipt" class="form-control sax-input-file mb-2" required>
                                    <button type="submit" class="btn btn-dark btn-sax-sm w-100">ENVIAR AHORA</button>
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
                <h6 class="card-sax-header"><i class="fas fa-map-marker-alt me-2"></i> ENVÍO Y CLIENTE</h6>
                <div class="card-sax-body">
                    <div class="mb-4">
                        <label class="sax-label">DESTINATARIO</label>
                        <p class="m-0 fw-semibold">{{ $order->name ?? ($order->user->name ?? '-') }}</p>
                        <p class="text-muted small m-0">{{ $order->email ?? ($order->user->email ?? '-') }}</p>
                        <p class="text-muted small">{{ $order->phone ?? ($order->user->phone_number ?? '-') }}</p>
                    </div>

                    <div>
                        <label class="sax-label">DIRECCIÓN DE ENTREGA</label>
                        <p class="small text-dark mb-1">
                            @if($order->shipping == 3)
                                <span class="badge bg-dark rounded-0">RECOJO EN TIENDA: {{ $order->store == 1 ? 'SAX Ciudad del Este' : 'SAX Asunción' }}</span>
                            @else
                                {{ $order->street ?? ($order->user->street ?? '-') }}, {{ $order->number ?? ($order->user->number ?? '-') }}<br>
                                {{ $order->city ?? ($order->user->city ?? '-') }}, {{ $order->state ?? ($order->user->state ?? '-') }}<br>
                                CP: {{ $order->cep ?? ($order->user->cep ?? '-') }}
                            @endif
                        </p>
                    </div>

                    @if ($order->observations)
                        <div class="mt-3 p-2 bg-light border-start border-dark">
                            <label class="sax-label mb-1">OBSERVACIONES</label>
                            <p class="x-small m-0 italic">{{ $order->observations }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- Seção de Itens --}}
    <h5 class="sax-title-sub text-uppercase letter-spacing-2 mb-4">Productos del Pedido</h5>
    <div class="order-items-list">
        @foreach ($order->items as $item)
            <div class="item-sax-row shadow-sm border rounded-4 bg-white p-3 mb-3">
                <div class="row align-items-center">
                    <div class="col-3 col-md-2 text-center">
                        <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}" 
                             class="img-fluid rounded object-fit-contain" style="max-height: 80px;">
                    </div>
                    <div class="col-9 col-md-4">
                        <h6 class="mb-1 text-uppercase fw-bold small">{{ $item->product->external_name ?? 'Producto' }}</h6>
                        <span class="text-muted x-small">SKU: {{ $item->product->sku ?? '-' }}</span>
                    </div>
                    <div class="col-4 col-md-2 mt-3 mt-md-0 text-center">
                        <label class="sax-label d-block">CANT.</label>
                        <span class="fw-bold">{{ $item->quantity }}</span>
                    </div>
                    <div class="col-4 col-md-2 mt-3 mt-md-0 text-center">
                        <label class="sax-label d-block">UNITARIO</label>
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

    {{-- Suporte --}}
    <div class="help-footer-sax text-center mt-5 py-4 border-top">
        <p class="text-muted small mb-3 text-uppercase letter-spacing-1">¿Necesitas ayuda con este pedido?</p>
        <a href="https://wa.me/{{ env('WHATSAPP_NUMBER') }}?text={{ urlencode('Hola, necesito ayuda con mi pedido #' . $order->id) }}"
           target="_blank" class="btn btn-outline-success rounded-pill px-4 btn-sm fw-bold">
            <i class="fab fa-whatsapp me-2"></i> CONTACTAR SOPORTE
        </a>
    </div>
</div>
@endsection
<style>
    /* Cards Premium */
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

.card-sax-body { padding: 25px; }

/* Info Rows */
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

/* Receipt Preview */
.receipt-preview-link {
    position: relative;
    display: block;
    max-width: 200px;
}

.receipt-preview-link .overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.4);
    color: #fff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.7rem;
    font-weight: bold;
    opacity: 0;
    transition: 0.3s;
}

.receipt-preview-link:hover .overlay { opacity: 1; }

/* Upload Box */
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

/* Itens Row */
.item-sax-row { transition: 0.3s; }
.item-sax-row:hover { transform: scale(1.01); box-shadow: 0 10px 20px rgba(0,0,0,0.05) !important; }

.sax-title-sub {
    font-size: 0.9rem;
    font-weight: 700;
    border-left: 4px solid #000;
    padding-left: 15px;
}

/* Helpers */
.x-small { font-size: 0.65rem; }
.italic { font-style: italic; }
</style>