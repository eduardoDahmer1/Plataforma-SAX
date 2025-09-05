@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Pedidos dos Clientes</h2>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-4">
        <div class="row g-2">
            <div class="col-12 col-md-2">
                <select name="payment_method" class="form-select">
                    <option value="">Todos os Pagamentos</option>
                    <option value="bancard" {{ request('payment_method') == 'bancard' ? 'selected' : '' }}>Bancard</option>
                    <option value="deposito" {{ request('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="status" class="form-select">
                    <option value="">Todos os Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processando</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluído</option>
                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <input type="text" name="user_name" class="form-control" placeholder="Nome do Cliente" value="{{ request('user_name') }}">
            </div>
            <div class="col-12 col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-12 col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-12 col-md-2 d-flex gap-2 flex-wrap">
                <button type="submit" class="btn btn-primary btn-sm flex-grow-1">
                    <i class="fa fa-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary btn-sm flex-grow-1">
                    <i class="fa fa-refresh me-1"></i> Limpar
                </a>
            </div>
        </div>
    </form>

    {{-- Lista de pedidos --}}
    <div class="row g-3">
        @forelse($orders as $order)
        @php
            $total = $order->items->sum(fn($item) => $item->price * $item->quantity);
            $statusColors = ['pending'=>'warning','processing'=>'info','completed'=>'success','canceled'=>'danger'];
        @endphp
        <div class="col-12">
            <div class="card shadow-sm">
                <div class="card-body">
                    <div class="row align-items-center gy-2">
                        <div class="col-6 col-md-1 fw-bold">#{{ $order->id }}</div>
                        <div class="col-6 col-md-3 text-truncate">{{ $order->user->name ?? 'Cliente não encontrado' }}</div>
                        <div class="col-6 col-md-2">{{ $order->created_at->format('d/m/Y H:i') }}</div>
                        <div class="col-6 col-md-1">
                            <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                                {{ ucfirst($order->status) }}
                            </span>
                        </div>
                        <div class="col-6 col-md-2">
                            @switch($order->payment_method)
                                @case('bancard') <span class="badge bg-success"><i class="fa fa-credit-card me-1"></i>Bancard</span> @break
                                @case('deposito') <span class="badge bg-info text-dark"><i class="fa fa-university me-1"></i>Depósito</span> @break
                                @case('whatsapp') <span class="badge bg-warning text-dark"><i class="fa fa-whatsapp me-1"></i>WhatsApp</span> @break
                                @default <span class="badge bg-secondary">Não informado</span>
                            @endswitch
                        </div>
                        <div class="col-6 col-md-2 fw-bold">R$ {{ number_format($total, 2, ',', '.') }}</div>
                        <div class="col-12 col-md-1 d-flex gap-1 flex-wrap">
                            <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary w-100">
                                <i class="fa fa-eye me-1"></i> Ver
                            </a>
                            <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="w-100 m-0" onsubmit="return confirm('Tem certeza que deseja excluir este pedido?');">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-sm btn-danger w-100">
                                    <i class="fa fa-trash me-1"></i>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="col-12">
            <div class="alert alert-info text-center">
                Nenhum pedido encontrado.
            </div>
        </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
</div>

<style>
.card { border-radius: 12px; }
.card-body { font-size: 0.95rem; line-height: 1.5; }
.fw-bold { font-weight: 600; }
</style>
@endsection
