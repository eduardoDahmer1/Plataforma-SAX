@extends('layout.dashboard')

@section('content')
<div class="sax-orders-wrapper">
    {{-- Cabeçalho --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Historial de Pedidos</h2>
            <div class="sax-divider-dark"></div>
        </div>
        <a href="{{ route('user.dashboard') }}" class="btn-back-minimal">
            <i class="fas fa-chevron-left me-1"></i> VOLVER
        </a>
    </div>

    @if ($orders->count())
        <div class="table-responsive sax-table-container">
            <table class="table sax-table">
                <thead>
                    <tr>
                        <th class="text-uppercase letter-spacing-1">Nº Pedido</th>
                        <th class="text-uppercase letter-spacing-1">Fecha</th>
                        <th class="text-uppercase letter-spacing-1">Pago</th>
                        <th class="text-uppercase letter-spacing-1">Status</th>
                        <th class="text-center text-uppercase letter-spacing-1">Acción</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($orders as $order)
                        <tr>
                            <td class="fw-bold">#{{ $order->id }}</td>
                            <td class="text-muted">{{ $order->created_at->format('d/m/Y') }}</td>
                            <td>
                                <span class="badge-payment-sax {{ $order->payment_method }}">
                                    {{ ucfirst($order->payment_method) }}
                                </span>
                            </td>
                            <td>
                                <div class="status-indicator-sax {{ $order->status }}">
                                    <span class="dot"></span>
                                    @switch($order->status)
                                        @case('pending') Pendiente @break
                                        @case('processing') En Proceso @break
                                        @case('completed') Completado @break
                                        @case('canceled') Cancelado @break
                                        @default Desconocido
                                    @endswitch
                                </div>
                            </td>
                            <td class="text-center">
                                <a href="{{ route('user.orders.show', $order->id) }}" class="btn-view-order">
                                    VER DETALLES
                                </a>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @else
        <div class="text-center py-5 bg-light rounded-4">
            <i class="fas fa-shopping-bag fa-3x text-muted mb-3 opacity-25"></i>
            <p class="text-muted text-uppercase letter-spacing-1 small">Aún não has realizado pedidos.</p>
        </div>
    @endif
</div>
@endsection
<style>
    /* Container e Header */
.sax-orders-wrapper { font-family: 'Inter', sans-serif; }
.sax-divider-dark { width: 40px; height: 3px; background: #000; margin-top: 10px; }
.letter-spacing-1 { letter-spacing: 1px; }
.letter-spacing-2 { letter-spacing: 3px; }

/* Tabela Premium */
.sax-table-container {
    background: #fff;
    border-radius: 12px;
}

.sax-table {
    vertical-align: middle;
    margin-bottom: 0;
}

.sax-table thead th {
    background-color: #fcfcfc;
    border-top: none;
    border-bottom: 1px solid #eee;
    color: #999;
    font-size: 0.65rem;
    padding: 20px;
}

.sax-table tbody td {
    padding: 20px;
    font-size: 0.85rem;
    border-bottom: 1px solid #f8f8f8;
}

/* Badges de Pagamento */
.badge-payment-sax {
    font-size: 0.7rem;
    padding: 5px 12px;
    border-radius: 50px;
    background: #f0f0f0;
    font-weight: 700;
    color: #666;
}

.badge-payment-sax.whatsapp { background: #e7f7ed; color: #198754; }
.badge-payment-sax.bancard { background: #eef2ff; color: #4f46e5; }
.badge-payment-sax.deposito { background: #fffbeb; color: #d97706; }

/* Status com Indicador de Ponto (Dot) */
.status-indicator-sax {
    display: flex;
    align-items: center;
    gap: 8px;
    font-weight: 600;
    font-size: 0.8rem;
}

.status-indicator-sax .dot {
    width: 8px;
    height: 8px;
    border-radius: 50%;
    background: #ccc;
}

.status-indicator-sax.completed { color: #198754; }
.status-indicator-sax.completed .dot { background: #198754; box-shadow: 0 0 5px #198754; }
.status-indicator-sax.pending { color: #d97706; }
.status-indicator-sax.pending .dot { background: #d97706; }
.status-indicator-sax.canceled { color: #dc3545; }
.status-indicator-sax.canceled .dot { background: #dc3545; }

/* Botões */
.btn-view-order {
    font-size: 0.7rem;
    font-weight: 800;
    color: #000;
    text-decoration: none;
    border: 1px solid #000;
    padding: 8px 16px;
    transition: all 0.3s;
}

.btn-view-order:hover {
    background: #000;
    color: #fff;
}

.btn-back-minimal {
    font-size: 0.7rem;
    font-weight: 700;
    color: #888;
    text-decoration: none;
    transition: color 0.3s;
}

.btn-back-minimal:hover { color: #000; }

/* Responsivo */
@media (max-width: 768px) {
    .sax-table thead { display: none; }
    .sax-table tbody td {
        display: block;
        text-align: right;
        padding: 10px 20px;
    }
    .sax-table tbody td::before {
        content: attr(data-label);
        float: left;
        font-weight: bold;
        text-transform: uppercase;
        font-size: 0.65rem;
        color: #999;
    }
}
</style>