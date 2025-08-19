@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-3">Pedidos dos Clientes</h2>

    <!-- Filtros -->
    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-3">
        <div class="row g-2 flex-column flex-md-row">
            <div class="col-12 col-md-2">
                <select name="payment_method" class="form-control">
                    <option value="">Todos os Pagamentos</option>
                    <option value="bancard" {{ request('payment_method') == 'bancard' ? 'selected' : '' }}>Bancard</option>
                    <option value="deposito" {{ request('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div class="col-12 col-md-2">
                <select name="status" class="form-control">
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
            <div class="col-12 col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-fill">
                    <i class="fa fa-filter me-1"></i> Filtrar
                </button>
                <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary flex-fill">
                    <i class="fa fa-refresh me-1"></i> Limpar
                </a>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped table-responsive">
        <thead class="table-light">
            <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Data do Pedido</th>
                <th>Status</th>
                <th>Pagamento</th>
                <th>Total</th>
                <th>Ações</th>
            </tr>
        </thead>
        <tbody>
            @forelse($orders as $order)
            <tr>
                <td>{{ $order->id }}</td>
                <td>{{ $order->user->name ?? 'Cliente não encontrado' }}</td>
                <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @php
                        $statusColors = [
                            'pending' => 'warning',
                            'processing' => 'info',
                            'completed' => 'success',
                            'canceled' => 'danger'
                        ];
                    @endphp
                    <span class="badge bg-{{ $statusColors[$order->status] ?? 'secondary' }}">
                        {{ ucfirst($order->status) }}
                    </span>
                </td>
                <td>
                    @switch($order->payment_method)
                        @case('bancard')
                            <span class="badge bg-success"><i class="fa fa-credit-card me-1"></i>Bancard</span>
                            @break
                        @case('deposito')
                            <span class="badge bg-info text-dark"><i class="fa fa-university me-1"></i>Depósito</span>
                            @break
                        @case('whatsapp')
                            <span class="badge bg-warning text-dark"><i class="fa fa-whatsapp me-1"></i>WhatsApp</span>
                            @break
                        @default
                            <span class="badge bg-secondary">Não informado</span>
                    @endswitch
                </td>
                <td>
                    @php
                        $total = $order->items->sum(fn($item) => $item->price * $item->quantity);
                    @endphp
                    R$ {{ number_format($total, 2, ',', '.') }}
                </td>
                <td class="d-flex flex-column flex-md-row gap-2">
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary flex-fill">
                        <i class="fa fa-eye me-1"></i> Ver Pedido
                    </a>
                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST" class="flex-fill m-0"
                        onsubmit="return confirm('Tem certeza que deseja excluir este pedido?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger w-100">
                            <i class="fa fa-trash me-1"></i> Excluir
                        </button>
                    </form>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="7" class="text-center">Nenhum pedido encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center mt-4">
        {{ $orders->links() }}
    </div>
</div>
@endsection
