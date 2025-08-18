@extends('layout.layout')

@section('content')
<div class="row">
    <div class="col-md-3">
        @include('users.components.menu')
    </div>
    <div class="col-md-9">
        <h2>Detalhes do Pedido #{{ $order->id }}</h2>

        <p>Status:
            <span class="badge 
@switch($order->status)
    @case('pending') bg-warning @break
    @case('processing') bg-info @break {{-- Aqui --}}
    @case('completed') bg-success @break
    @case('canceled') bg-danger @break
    @default bg-secondary
@endswitch">
                {{ ucfirst($order->status) }}
            </span>
        </p>


        <p>Método de Pagamento:
            <span class="badge 
        @switch($order->payment_method)
            @case('whatsapp') bg-success @break
            @case('bancard') bg-primary @break
            @case('deposito') bg-warning @break
            @default bg-secondary
        @endswitch">
                {{ ucfirst($order->payment_method) }}
            </span>
        </p>

        <p>Total: R$ {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 2, ',', '.') }}</p>

        {{-- Comprovante --}}
        @if($order->deposit_receipt)
        <div class="mb-3">
            <h5>Comprovante de Depósito</h5>
            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank">
                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" alt="Comprovante"
                    style="max-width:200px; border:1px solid #ccc;">
            </a>
        </div>
        @endif

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
                    <td>{{ $item->product->external_name ?? 'Produto' }}</td>
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