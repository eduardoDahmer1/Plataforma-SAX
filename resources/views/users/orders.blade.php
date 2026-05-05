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

@endsection