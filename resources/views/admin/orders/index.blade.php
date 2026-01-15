@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Órdenes</h1>
            <p class="small text-secondary mb-0">{{ $orders->total() }} transacciones registradas</p>
        </div>
        <div class="d-flex gap-2">
            <button class="btn btn-sm btn-outline-dark border-0 rounded-0 text-uppercase fw-bold" type="button" data-bs-toggle="collapse" data-bs-target="#filterCollapse">
                <i class="fa fa-sliders-h me-2"></i> Filtros
            </button>
        </div>
    </div>

    {{-- Filtros em Colapso (Para manter o visual limpo) --}}
    <div class="collapse {{ request()->anyFilled(['payment_method', 'status', 'user_name']) ? 'show' : '' }} mb-5" id="filterCollapse">
        <form method="GET" action="{{ route('admin.orders.index') }}" class="row g-3 border-bottom pb-4">
            <div class="col-md-2">
                <select name="payment_method" class="form-select border-0 bg-light-subtle small rounded-0">
                    <option value="">Método de Pago</option>
                    <option value="bancard" {{ request('payment_method') == 'bancard' ? 'selected' : '' }}>Bancard</option>
                    <option value="deposito" {{ request('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-select border-0 bg-light-subtle small rounded-0">
                    <option value="">Estado</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendiente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Procesando</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Completado</option>
                </select>
            </div>
            <div class="col-md-3">
                <input type="text" name="user_name" class="form-control border-0 bg-light-subtle small rounded-0" placeholder="Nombre del cliente" value="{{ request('user_name') }}">
            </div>
            <div class="col-md-3 d-flex gap-2">
                <button type="submit" class="btn btn-dark btn-sm px-4 rounded-0">Aplicar</button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-light btn-sm px-4 rounded-0 border">Limpiar</a>
            </div>
        </form>
    </div>

    {{-- Tabela Estilo "Lista Limpa" --}}
    <div class="table-responsive">
        <table class="table table-hover align-middle border-top">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold" style="width: 80px;">ID</th>
                    <th class="py-3 border-0 fw-bold">Cliente</th>
                    <th class="py-3 border-0 fw-bold">Fecha</th>
                    <th class="py-3 border-0 fw-bold">Estado</th>
                    <th class="py-3 border-0 fw-bold">Método</th>
                    <th class="py-3 border-0 fw-bold text-end">Monto Total</th>
                    <th class="py-3 border-0 fw-bold text-end">Acciones</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($orders as $order)
                @php
                    $total = $order->items->sum(fn($item) => $item->price * $item->quantity);
                @endphp
                <tr class="border-bottom clickable-row">
                    <td class="py-4 text-dark fw-medium">#{{ $order->id }}</td>
                    <td class="py-4">
                        <span class="d-block fw-bold text-dark">{{ $order->user->name ?? 'Anónimo' }}</span>
                        <span class="x-small text-muted text-lowercase">{{ $order->user->email ?? '' }}</span>
                    </td>
                    <td class="py-4 text-secondary small">
                        {{ $order->created_at->format('d/m/Y') }}
                    </td>
                    <td class="py-4">
                        <span class="status-dot {{ $order->status }}"></span>
                        <span class="x-small text-uppercase fw-bold text-secondary">{{ $order->status }}</span>
                    </td>
                    <td class="py-4 text-secondary small">
                        {{ ucfirst($order->payment_method) }}
                    </td>
                    <td class="py-4 text-end fw-bold text-dark">
                        {{ number_format($total, 0, '.', '.') }} <span class="x-small fw-normal">Gs.</span>
                    </td>
                    <td class="py-4 text-end">
                        <div class="dropdown">
                            <button class="btn btn-link text-dark p-0" type="button" data-bs-toggle="dropdown">
                                <i class="fa fa-ellipsis-h"></i>
                            </button>
                            <ul class="dropdown-menu dropdown-menu-end border shadow-sm rounded-0">
                                <li><a class="dropdown-item small" href="{{ route('admin.orders.show', $order->id) }}">Ver detalles</a></li>
                                <li>
                                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" onsubmit="return confirm('¿Eliminar?');">
                                        @csrf @method('DELETE')
                                        <button class="dropdown-item small text-danger">Eliminar registro</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-5 text-muted small">No hay órdenes para mostrar.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="d-flex justify-content-between align-items-center mt-4">
        <div class="x-small text-muted text-uppercase tracking-wider">
            Página {{ $orders->currentPage() }} de {{ $orders->lastPage() }}
        </div>
        {{ $orders->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
<style>
    /* Tipografia e Espaçamento */
.tracking-wider { letter-spacing: 0.1em; }
.x-small { font-size: 0.7rem; }
.fs-7 { font-size: 0.8rem; }
.fw-bold { font-weight: 700; }

/* Status Dot (Substitui os badges grandes) */
.status-dot {
    height: 8px;
    width: 8px;
    border-radius: 50%;
    display: inline-block;
    margin-right: 8px;
    background-color: #dee2e6; /* Default */
}
.status-dot.pending { background-color: #f59e0b; }
.status-dot.processing { background-color: #3b82f6; }
.status-dot.completed { background-color: #10b981; }
.status-dot.canceled { background-color: #ef4444; }

/* Tabela e Inputs */
.table td {
    border-bottom: 1px solid #f8f9fa;
}

.clickable-row:hover {
    background-color: #fafafa !important;
}

.form-select, .form-control {
    box-shadow: none !important;
    border: 1px solid transparent;
}

.form-select:focus, .form-control:focus {
    background-color: #fff;
    border-color: #000;
}

/* Paginação Customizada Minimal */
.pagination {
    --bs-pagination-border-radius: 0;
    --bs-pagination-color: #000;
    --bs-pagination-active-bg: #000;
    --bs-pagination-active-border-color: #000;
}

.dropdown-item:active {
    background-color: #000;
}

.dropdown-menu {
    --bs-dropdown-min-width: 160px;
    padding: 8px 0;
}
</style>