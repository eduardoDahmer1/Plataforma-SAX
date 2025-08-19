@extends('layout.layout')

@section('content')
<div class="row">
    <div class="col-md-3 mb-4">
        @include('users.components.menu')
    </div>

    <div class="col-md-9">
        <h2 class="mb-3"><i class="fas fa-receipt me-2"></i>Detalhes do Pedido #{{ $order->id }}</h2>

        <p>
            <i class="fas fa-info-circle me-1"></i>Status:
            <span class="badge 
                @switch($order->status)
                    @case('pending') bg-warning text-dark @break
                    @case('processing') bg-info text-dark @break
                    @case('completed') bg-success @break
                    @case('canceled') bg-danger @break
                    @default bg-secondary
                @endswitch">
                {{ ucfirst($order->status) }}
            </span>
        </p>

        <p>
            <i class="fas fa-credit-card me-1"></i>Método de Pagamento:
            <span class="badge 
                @switch($order->payment_method)
                    @case('whatsapp') bg-success @break
                    @case('bancard') bg-primary @break
                    @case('deposito') bg-warning text-dark @break
                    @default bg-secondary
                @endswitch">
                {{ ucfirst($order->payment_method) }}
            </span>
        </p>

        <p><i class="fas fa-dollar-sign me-1"></i>Total: R$ {{ number_format($order->items->sum(fn($i) => $i->price * $i->quantity), 2, ',', '.') }}</p>

        {{-- Comprovante --}}
        @if($order->deposit_receipt)
        <div class="mb-3">
            <h5><i class="fas fa-file-image me-1"></i>Comprovante de Depósito</h5>
            <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank">
                <img src="{{ asset('storage/' . $order->deposit_receipt) }}" alt="Comprovante"
                    class="img-fluid border rounded" style="max-width: 250px;">
            </a>
        </div>
        @endif

        <h3 class="mb-3"><i class="fas fa-boxes me-2"></i>Itens</h3>
        <div class="table-responsive">
            <table class="table table-striped align-middle">
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
        </div>

        <a href="{{ route('user.dashboard') }}" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
    </div>
</div>
@endsection
