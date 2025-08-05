@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Detalhes do Pedido #{{ $order->id }}</h2>

    <table class="table table-bordered">
        <tr>
            <th>Cliente</th>
            <td>{{ $order->user->name }}</td>
        </tr>
        <tr>
            <th>Email</th>
            <td>({{ $order->user->email }})</td>
        </tr>
        <tr>
            <th>Status</th>
            <td>{{ $order->status }}</td>
        </tr>
        <tr>
            <th>Data do Pedido</th>
            <td>{{ $order->created_at->format('d/m/Y H:i') }}</td>
        </tr>
        <tr>
            <th>Total</th>

            <td>@php
                $total = $order->items->sum(function($item) {
                return $item->price * $item->quantity;
                });
                @endphp R$ {{ number_format($total, 2, ',', '.') }}</td>
        </tr>
        <tr>
            <th>Método de Pagamento</th>
            <td>{{ $order->payment_method }}</td>
        </tr>
    </table>

    <h3>Itens do Pedido</h3>
    @if($order->items->count())
    <table class="table table-striped">
        <thead>
            <tr>
                <th>Produto</th>
                <th>Quantidade</th>
                <th>Preço Unitário</th>
                <th>Subtotal</th>
            </tr>
        </thead>
        <tbody>
            @foreach($order->items as $item)
            <tr>
                <td>{{ $item->product->slug ?? 'Produto não encontrado' }}</td>
                <td>{{ $item->quantity }}</td>
                <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                <td>R$ {{ number_format($item->quantity * $item->price, 2, ',', '.') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @else
    <p>Este pedido não possui itens.</p>
    @endif

    <a href="{{ route('admin.orders.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection