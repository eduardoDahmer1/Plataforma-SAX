@extends('layout.layout')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('users.components.menu')
    </div>
    <div class="col-md-9">
        <h2>Detalhes do Pedido #{{ $order->id }}</h2>

        <p>Status: {{ $order->status }}</p>
        <p>Total: R$ {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 2, ',', '.') }}</p>

        <h3>Itens</h3>
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
                    <td>{{ $item->name ?? $item->external_name }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>R$ {{ number_format($item->price, 2, ',', '.') }}</td>
                    <td>R$ {{ number_format($item->price * $item->quantity, 2, ',', '.') }}</td>
                </tr>
                @endforeach
            </tbody>
        </table>

        <a href="{{ route('user.dashboard') }}" class="btn btn-secondary mt-3">Voltar</a>
    </div>

</div>
@endsection