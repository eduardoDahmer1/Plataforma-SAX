@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Pedidos dos Clientes</h2>

    <table class="table table-bordered table-striped mt-4">
        <thead>
            <tr>
                <th>ID Pedido</th>
                <th>Cliente</th>
                <th>Data do Pedido</th>
                <th>Status</th>
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
                <td>{{ $order->status }}</td>
                <td>R$ {{ number_format($order->total ?? 0, 2, ',', '.') }}</td>
                <td>
                    <a href="{{ route('admin.orders.show', $order->id) }}" class="btn btn-sm btn-primary">Ver Pedido</a>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6" class="text-center">Nenhum pedido encontrado.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <div class="d-flex justify-content-center">
        {{ $orders->links() }}
    </div>
</div>
@endsection
