@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Pedidos dos Clientes</h2>

    <form method="GET" action="{{ route('admin.orders.index') }}" class="mb-3">
        <div class="row g-2">
            <div class="col-md-2">
                <select name="payment_method" class="form-control">
                    <option value="">Todos os Pagamentos</option>
                    <option value="bancard" {{ request('payment_method') == 'bancard' ? 'selected' : '' }}>Bancard</option>
                    <option value="deposito" {{ request('payment_method') == 'deposito' ? 'selected' : '' }}>Depósito</option>
                    <option value="whatsapp" {{ request('payment_method') == 'whatsapp' ? 'selected' : '' }}>WhatsApp</option>
                </select>
            </div>
            <div class="col-md-2">
                <select name="status" class="form-control">
                    <option value="">Todos os Status</option>
                    <option value="pending" {{ request('status') == 'pending' ? 'selected' : '' }}>Pendente</option>
                    <option value="processing" {{ request('status') == 'processing' ? 'selected' : '' }}>Processando</option>
                    <option value="completed" {{ request('status') == 'completed' ? 'selected' : '' }}>Concluído</option>
                    <option value="canceled" {{ request('status') == 'canceled' ? 'selected' : '' }}>Cancelado</option>
                </select>
            </div>
            <div class="col-md-2">
                <input type="text" name="user_name" class="form-control" placeholder="Nome do Cliente" value="{{ request('user_name') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_from" class="form-control" value="{{ request('date_from') }}">
            </div>
            <div class="col-md-2">
                <input type="date" name="date_to" class="form-control" value="{{ request('date_to') }}">
            </div>
            <div class="col-md-2 d-flex">
                <button type="submit" class="btn btn-primary me-2">Filtrar</button>
            </div>
        </div>
    </form>

    <table class="table table-bordered table-striped mt-4">
        <thead>
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
                            <span class="badge bg-success">Bancard</span>
                            @break
                        @case('deposito')
                            <span class="badge bg-info text-dark">Depósito</span>
                            @break
                        @case('whatsapp')
                            <span class="badge bg-warning text-dark">WhatsApp</span>
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
                <td>
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">Ver Pedido</a>
                    <form action="{{ route('admin.orders.destroy', $order->id) }}" method="POST"
                        style="display:inline-block;"
                        onsubmit="return confirm('Tem certeza que deseja excluir este pedido?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="btn btn-sm btn-danger">Excluir</button>
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

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>
@endsection
