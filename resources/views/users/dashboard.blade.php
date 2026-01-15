@extends('layout.dashboard')

@section('content')
<div class="sax-dashboard-wrapper">
    {{-- Cabeçalho de Boas-vindas --}}
    <div class="dashboard-header mb-5">
        <h1 class="sax-title text-uppercase letter-spacing-2">Olá, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p class="text-muted x-small text-uppercase letter-spacing-1">Gerencie suas informações e acompanhe seus pedidos</p>
        <div class="sax-divider-gold"></div>
    </div>

    {{-- Perfil e Dados --}}
    <div class="section-label mb-3">
        <h6 class="fw-bold text-uppercase letter-spacing-1 small">Informações da Conta</h6>
    </div>

    <div class="row g-3 mb-5">
        @php
            $fields = [
                ['label' => 'Nome', 'value' => auth()->user()->name, 'icon' => 'user'],
                ['label' => 'Email', 'value' => auth()->user()->email, 'icon' => 'envelope'],
                ['label' => 'Telefone', 'value' => (auth()->user()->phone_country ?? '') . ' ' . (auth()->user()->phone_number ?? ''), 'icon' => 'phone'],
                ['label' => 'Endereço', 'value' => auth()->user()->address, 'icon' => 'home'],
                ['label' => 'Documento', 'value' => auth()->user()->document, 'icon' => 'id-card'],
                ['label' => 'Cidade/Estado', 'value' => auth()->user()->city . ' - ' . auth()->user()->state, 'icon' => 'map-marker-alt'],
            ];
        @endphp

        @foreach($fields as $field)
            @if(trim($field['value']))
            <div class="col-12 col-md-4">
                <div class="sax-info-card">
                    <div class="card-icon-minimal">
                        <i class="fas fa-{{ $field['icon'] }}"></i>
                    </div>
                    <div class="card-details">
                        <span class="label text-muted text-uppercase">{{ $field['label'] }}</span>
                        <div class="value fw-semibold">{{ $field['value'] }}</div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    {{-- Seção de Pedidos --}}
    <div class="section-label d-flex justify-content-between align-items-center mb-4">
        <h6 class="fw-bold text-uppercase letter-spacing-1 small m-0">Pedidos Recentes</h6>
        @if ($orders->count() > 5)
            <a href="{{ route('user.orders') }}" class="btn-link-sax">Ver histórico completo <i class="fas fa-chevron-right ms-1"></i></a>
        @endif
    </div>

    @if ($orders->count())
        <div class="order-grid">
            @foreach ($orders->take(5) as $order)
                <div class="order-item-sax shadow-sm border rounded-4 p-4 mb-3 bg-white">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <span class="text-muted x-small d-block">NÚMERO DO PEDIDO</span>
                            <span class="fw-bold fs-5">#{{ $order->id }}</span>
                            <span class="text-muted d-block x-small">{{ $order->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted x-small d-block">PAGAMENTO</span>
                            <span class="badge-payment {{ $order->payment_method }}">{{ ucfirst($order->payment_method) }}</span>
                        </div>
                        <div class="col-md-3">
                            <span class="text-muted x-small d-block">STATUS</span>
                            <div class="status-indicator {{ $order->status }}">
                                <span class="dot"></span> 
                                @switch($order->status)
                                    @case('pending') Pendente @break
                                    @case('processing') Em Andamento @break
                                    @case('completed') Completo @break
                                    @case('canceled') Cancelado @break
                                    @default Desconhecido
                                @endswitch
                            </div>
                        </div>
                        <div class="col-md-2 text-md-end">
                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-dark btn-sax-sm">DETALHES</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state text-center py-5 border rounded-4 bg-light">
            <i class="fas fa-box-open fa-3x text-muted mb-3 opacity-25"></i>
            <p class="text-muted">Você ainda não realizou pedidos.</p>
        </div>
    @endif
</div>
@endsection
<style>
    /* Layout Base */
.sax-dashboard-wrapper { font-family: 'Inter', sans-serif; color: #1a1a1a; }
.letter-spacing-1 { letter-spacing: 1px; }
.letter-spacing-2 { letter-spacing: 3px; }
.x-small { font-size: 0.65rem; font-weight: bold; }

.sax-divider-gold { 
    width: 40px; 
    height: 2px; 
    background: #000; /* Mude para #c5a059 se quiser um toque dourado */
    margin-top: 10px;
}

/* Info Cards */
.sax-info-card {
    background: #fff;
    border: 1px solid #f0f0f0;
    padding: 20px;
    display: flex;
    align-items: center;
    gap: 15px;
    border-radius: 12px;
    transition: all 0.3s ease;
}

.sax-info-card:hover { border-color: #000; transform: translateY(-3px); }

.card-icon-minimal {
    width: 40px;
    height: 40px;
    background: #f8f9fa;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #333;
}

.card-details .label { font-size: 0.6rem; letter-spacing: 0.5px; }
.card-details .value { font-size: 0.9rem; }

/* Order Items */
.order-item-sax { transition: 0.3s ease; }
.order-item-sax:hover { box-shadow: 0 10px 30px rgba(0,0,0,0.05) !important; }

/* Status & Badges */
.status-indicator { font-size: 0.8rem; font-weight: 600; display: flex; align-items: center; gap: 6px; }
.status-indicator .dot { width: 8px; height: 8px; border-radius: 50%; }

.status-indicator.completed { color: #198754; }
.status-indicator.completed .dot { background: #198754; }
.status-indicator.pending { color: #ffc107; }
.status-indicator.pending .dot { background: #ffc107; }

.badge-payment {
    font-size: 0.7rem;
    padding: 4px 10px;
    border-radius: 50px;
    background: #f0f0f0;
    font-weight: bold;
    color: #555;
}

.badge-payment.whatsapp { background: #e7f7ed; color: #198754; }

/* Botões */
.btn-sax-sm {
    font-size: 0.65rem;
    letter-spacing: 1px;
    padding: 10px 20px;
    border-radius: 50px;
    font-weight: 700;
}

.btn-link-sax {
    color: #000;
    text-decoration: none;
    font-size: 0.75rem;
    font-weight: bold;
    border-bottom: 1px solid #000;
    transition: 0.3s;
}

.btn-link-sax:hover { opacity: 0.6; }

@media (max-width: 768px) {
    .order-item-sax .col-md-2 { margin-top: 15px; }
}
</style>