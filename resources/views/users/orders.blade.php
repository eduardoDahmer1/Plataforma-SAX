@extends('layout.dashboard')

@section('content')
<div class="sax-orders-wrapper">
    <div class="dashboard-header d-flex justify-content-between align-items-end mb-5">
        <div>
            <h2 class="sax-title text-uppercase">{{ __('messages.historico_pedidos_titulo') }}</h2>
            <div class="sax-divider-black"></div>
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> {{ __('messages.voltar') }}
        </a>
    </div>

    @if ($orders->count())
        {{-- Versão Desktop --}}
        <div class="d-none d-md-block shadow-sm sax-table-container">
            <table class="table sax-table">
                <thead>
                    <tr>
                        <th>{{ __('messages.num_pedido') }}</th>
                        <th>{{ __('messages.col_data') }}</th>
                        <th>{{ __('messages.col_pago') }}</th>
                        <th>{{ __('messages.status') }}</th>
                        <th class="text-end">{{ __('messages.col_accao') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        @php($status = strtolower((string) $order->status))
                        <tr>
                            <td class="fw-bold fs-6">#{{ $order->id }}</td>
                            <td class="text-muted">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge-payment-sax">{{ ucfirst($order->payment_method) }}</span>
                            </td>
                            <td>
                                <div class="status-indicator-sax {{ $status }}">
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
                            </td>
                            <td class="text-end">
                                <a href="{{ route('user.orders.show', $order->id) }}" class="btn-sax-black-sm text-uppercase">
                                    {{ __('messages.detalhes') }}
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        {{-- Versão Mobile --}}
        <div class="d-md-none">
            @foreach ($orders as $order)
                @php($status = strtolower((string) $order->status))
                <div class="order-card-sax shadow-sm">
                    <div class="order-mobile-header">
                        <div>
                            <span class="sax-label-min">{{ __('messages.num_pedido') }}</span>
                            <span class="order-id">#{{ $order->id }}</span>
                            <span class="order-date">{{ $order->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="status-indicator-sax {{ $status }}">
                            <span class="dot"></span>
                        </div>
                    </div>
                    
                    <div class="order-mobile-body mt-3">
                        <div class="mb-3">
                            <span class="sax-label-min">{{ __('messages.pagamento') }}</span>
                            <span class="badge-payment-sax">{{ ucfirst($order->payment_method) }}</span>
                        </div>
                        <div class="mb-3">
                            <span class="sax-label-min">{{ __('messages.status') }}</span>
                            <span class="status-text-mobile {{ $status }}">
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
                            </span>
                        </div>
                    </div>

                    <a href="{{ route('user.orders.show', $order->id) }}" class="btn-sax-black w-100 mt-2 text-uppercase">
                        {{ __('messages.detalhes') }}
                    </a>
                </div>
            @endforeach
        </div>
    @else
        <div class="empty-state">
            <i class="fas fa-shopping-bag fa-2x mb-3 opacity-50"></i>
            <p>{{ __('messages.sem_pedidos') }}</p>
        </div>
    @endif
</div>

<style>
    /* Base & Typography */
    .sax-orders-wrapper { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; max-width: 1200px; margin: auto; }
    .sax-title { font-weight: 700; letter-spacing: 3px; font-size: 1.6rem; text-transform: uppercase; margin: 0; }
    .sax-divider-black { width: 40px; height: 3px; background: #000; margin-top: 10px; }
    .sax-label-min { display: block; font-size: 0.65rem; font-weight: 700; color: #888; letter-spacing: 0.5px; margin-bottom: 4px; }
    
    /* Desktop Table */
    .sax-table-container { background: #fff; border-radius: 8px; overflow: hidden; border: 1px solid #eee; }
    .sax-table { margin-bottom: 0; vertical-align: middle; }
    .sax-table thead th { 
        background: #fcfcfc; 
        font-size: 0.7rem; 
        font-weight: 700; 
        padding: 20px; 
        color: #999; 
        border-bottom: 1px solid #eee;
        letter-spacing: 1px;
    }
    .sax-table tbody td { padding: 20px; border-bottom: 1px solid #f8f8f8; color: #333; }

    /* Badges & Status (Uniformizados) */
    .badge-payment-sax { 
        background: #f0f0f0; 
        padding: 5px 12px; 
        border-radius: 4px; 
        font-size: 0.7rem; 
        font-weight: 700; 
        color: #555; 
    }
    .status-indicator-sax { display: flex; align-items: center; gap: 8px; font-weight: 700; font-size: 0.8rem; }
    .status-indicator-sax .dot { width: 10px; height: 10px; border-radius: 50%; }
    
    .status-indicator-sax.completed { color: #198754; }
    .status-indicator-sax.completed .dot { background: #198754; }
    .status-indicator-sax.paid { color: #198754; }
    .status-indicator-sax.paid .dot { background: #198754; }
    .status-indicator-sax.pending { color: #f1b400; }
    .status-indicator-sax.pending .dot { background: #f1b400; }
    .status-indicator-sax.failed,
    .status-indicator-sax.canceled,
    .status-indicator-sax.cancelled { color: #dc3545; }
    .status-indicator-sax.failed .dot,
    .status-indicator-sax.canceled .dot,
    .status-indicator-sax.cancelled .dot { background: #dc3545; }

    /* Mobile Cards */
    .order-card-sax {
        background: #fff;
        border: 1px solid #eee;
        border-radius: 12px;
        padding: 20px;
        margin-bottom: 15px;
    }
    .order-mobile-header { display: flex; justify-content: space-between; align-items: flex-start; }
    .order-id { font-size: 1.3rem; font-weight: 800; display: block; line-height: 1; margin-bottom: 2px; }
    .order-date { font-size: 0.8rem; color: #777; }
    .status-text-mobile { font-weight: 700; font-size: 0.9rem; }
    .status-text-mobile.paid { color: #198754; }
    .status-text-mobile.failed,
    .status-text-mobile.canceled,
    .status-text-mobile.cancelled { color: #dc3545; }
    .status-text-mobile.pending { color: #f1b400; }

    /* Buttons */
    .btn-sax-black-sm {
        background: #222;
        color: #fff !important;
        padding: 8px 16px;
        font-size: 0.65rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-decoration: none;
        border-radius: 4px;
        transition: 0.3s;
    }
    .btn-sax-black {
        background: #222;
        color: #fff !important;
        padding: 12px;
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 1px;
        text-decoration: none;
        display: block;
        text-align: center;
        border-radius: 6px;
    }
    .btn-sax-black:hover, .btn-sax-black-sm:hover { background: #000; }

    .btn-back-minimal { font-size: 0.7rem; font-weight: 700; color: #888; text-decoration: none; border-bottom: 1px solid transparent; }
    .btn-back-minimal:hover { color: #000; border-bottom-color: #000; }

    .empty-state { padding: 80px; text-align: center; background: #f9f9f9; border-radius: 12px; color: #888; letter-spacing: 1px; }
</style>
@endsection