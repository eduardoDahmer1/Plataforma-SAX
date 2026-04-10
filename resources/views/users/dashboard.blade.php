@extends('layout.dashboard')

@section('content')
<div class="sax-dashboard-wrapper">
    {{-- Cabeçalho de Boas-vindas --}}
    <div class="dashboard-header mb-5">
        <h1 class="sax-title">{{ __('messages.ola') }}, {{ explode(' ', auth()->user()->name)[0] }}</h1>
        <p class="sax-subtitle">{{ __('messages.gerencie_infos') }}</p>
        <div class="sax-divider-black"></div>
    </div>

    {{-- Perfil e Dados --}}
    <div class="section-label mb-3">
        <h6 class="sax-section-title">{{ __('messages.infos_conta') }}</h6>
    </div>

    <div class="row g-3 mb-5">
        @php
            $fields = [
                ['label' => __('messages.label_nome'), 'value' => auth()->user()->name, 'icon' => 'user'],
                ['label' => __('messages.label_email'), 'value' => auth()->user()->email, 'icon' => 'envelope'],
                ['label' => __('messages.label_telefone'), 'value' => (auth()->user()->phone_country ?? '') . ' ' . (auth()->user()->phone_number ?? ''), 'icon' => 'phone'],
                ['label' => __('messages.label_endereco'), 'value' => auth()->user()->address, 'icon' => 'home'],
                ['label' => __('messages.label_documento'), 'value' => auth()->user()->document, 'icon' => 'id-card'],
                ['label' => __('messages.label_cidade_estado'), 'value' => auth()->user()->city . ' - ' . auth()->user()->state, 'icon' => 'map-marker-alt'],
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
                        <span class="label">{{ $field['label'] }}</span>
                        <div class="value">{{ $field['value'] }}</div>
                    </div>
                </div>
            </div>
            @endif
        @endforeach
    </div>

    {{-- Seção de Pedidos --}}
    <div class="section-label d-flex justify-content-between align-items-end mb-4">
        <h6 class="sax-section-title m-0">{{ __('messages.pedidos_recentes') }}</h6>
        @if ($orders->count() > 0)
            <a href="{{ route('user.orders') }}" class="btn-link-sax">
                {{ __('messages.ver_historico') }} <i class="fas fa-chevron-right ms-1"></i>
            </a>
        @endif
    </div>

    @if ($orders->count())
        <div class="order-container">
            @foreach ($orders->take(5) as $order)
                <div class="order-card-sax shadow-sm">
                    <div class="order-content">
                        {{-- Info Principal --}}
                        <div class="order-block">
                            <span class="sax-label-min">{{ __('messages.num_pedido') }}</span>
                            <span class="order-id">#{{ $order->id }}</span>
                            <span class="order-date">{{ $order->created_at->format('d/m/Y') }}</span>
                        </div>

                        {{-- Pagamento --}}
                        <div class="order-block">
                            <span class="sax-label-min">{{ __('messages.pagamento') }}</span>
                            <span class="badge-payment">{{ ucfirst($order->payment_method) }}</span>
                        </div>

                        {{-- Status --}}
                        <div class="order-block">
                            <span class="sax-label-min">{{ __('messages.status') }}</span>
                            @php($status = strtolower((string) $order->status))
                            <div class="status-indicator {{ $status }}">
                                <span class="dot"></span> 
                                @switch($status)
                                    @case('pending') {{ __('messages.status_pending') }} @break
                                    @case('processing') {{ __('messages.status_processing') }} @break
                                    @case('completed') {{ __('messages.status_completed') }} @break
                                    @case('paid') {{ __('messages.status_paid') }} @break
                                    @case('failed') {{ __('messages.status_failed') }} @break
                                    @case('canceled') 
                                    @case('cancelled') {{ __('messages.status_canceled') }} @break
                                    @default {{ __('messages.status_unknown') }}
                                @endswitch
                            </div>
                        </div>

                        <div class="order-action">
                            <a href="{{ route('user.orders.show', $order->id) }}" class="btn-sax-black">{{ __('messages.detalhes') }}</a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-box-open fa-2x mb-3 opacity-50"></i>
            <p>{{ __('messages.sem_pedidos') }}</p>
        </div>
    @endif
</div>

<style>
    /* Estilo Base SAX */
    .sax-dashboard-wrapper { 
        font-family: 'Helvetica Neue', Arial, sans-serif; 
        color: #1a1a1a; 
        max-width: 1200px; 
        margin: auto; 
        padding: 20px;
    }

    /* Tipografia */
    .sax-title { font-weight: 700; letter-spacing: 3px; font-size: 1.8rem; text-transform: uppercase; margin-bottom: 5px; }
    .sax-subtitle { color: #777; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 15px; }
    .sax-section-title { font-weight: 700; text-transform: uppercase; letter-spacing: 1.5px; font-size: 0.85rem; color: #000; }
    .sax-label-min { display: block; font-size: 0.65rem; font-weight: 700; color: #888; letter-spacing: 0.5px; margin-bottom: 4px; }
    
    .sax-divider-black { width: 40px; height: 3px; background: #000; }

    /* Cards de Info */
    .sax-info-card {
        background: #fff;
        border: 1px solid #eee;
        padding: 20px;
        display: flex;
        align-items: center;
        gap: 15px;
        border-radius: 8px;
        height: 100%;
    }
    .card-icon-minimal { color: #808080; font-size: 1.2rem; width: 30px; }
    .card-details .label { display: block; font-size: 0.6rem; text-transform: uppercase; color: #999; font-weight: 700; }
    .card-details .value { font-size: 0.95rem; font-weight: 600; color: #333; }

    /* Grid de Pedidos (Estilo Card Mobile/Linha Desktop) */
    .order-card-sax {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 25px;
        margin-bottom: 15px;
        transition: transform 0.2s;
    }
    .order-card-sax:hover { transform: translateY(-2px); border-color: #ccc; }

    .order-content {
        display: grid;
        grid-template-columns: 1fr; /* Mobile: Uma coluna */
        gap: 20px;
        align-items: center;
    }

    .order-id { font-size: 1.4rem; font-weight: 800; display: block; color: #1a1a1a; line-height: 1; }
    .order-date { font-size: 0.8rem; color: #777; font-weight: 500; }

    /* Badges e Status */
    .badge-payment {
        background: #f0f0f0;
        padding: 4px 12px;
        border-radius: 4px;
        font-size: 0.75rem;
        font-weight: 700;
        color: #555;
    }
    .status-indicator { font-size: 0.85rem; font-weight: 700; display: flex; align-items: center; gap: 8px; }
    .status-indicator .dot { width: 10px; height: 10px; border-radius: 50%; }
    
    .status-indicator.pending { color: #f1b400; }
    .status-indicator.pending .dot { background: #f1b400; }
    .status-indicator.completed { color: #198754; }
    .status-indicator.completed .dot { background: #198754; }
    .status-indicator.paid { color: #198754; }
    .status-indicator.paid .dot { background: #198754; }
    .status-indicator.failed,
    .status-indicator.canceled,
    .status-indicator.cancelled { color: #dc3545; }
    .status-indicator.failed .dot,
    .status-indicator.canceled .dot,
    .status-indicator.cancelled .dot { background: #dc3545; }

    /* Botões */
    .btn-sax-black {
        background: #222;
        color: #fff;
        text-decoration: none;
        padding: 12px 25px;
        border-radius: 6px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1px;
        display: inline-block;
        text-align: center;
        transition: 0.3s;
    }
    .btn-sax-black:hover { background: #000; color: #fff; }
    
    .btn-link-sax {
        color: #000;
        font-size: 0.75rem;
        font-weight: 700;
        text-decoration: none;
        border-bottom: 2px solid #000;
        padding-bottom: 2px;
    }

    /* Estado Vazio */
    .empty-state {
        padding: 60px;
        text-align: center;
        background: #f9f9f9;
        border: 1px dashed #ccc;
        border-radius: 12px;
        color: #888;
    }

    /* Media Query para Desktop */
    @media (min-width: 768px) {
        .order-content {
            grid-template-columns: 2fr 1.5fr 1.5fr 1fr; /* Desktop: Layout em linha */
            text-align: left;
        }
        .order-action { text-align: right; }
        .order-id { font-size: 1.2rem; }
    }
</style>
@endsection