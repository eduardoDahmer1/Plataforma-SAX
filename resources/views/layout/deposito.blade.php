@extends('layout.checkout')

@section('content')
<div class="container mt-5 mb-5">
    <div class="text-center mb-4">
        <h2 class="fw-bold"><i class="fa fa-university"></i> Pagamento via Depósito</h2>
        <p class="text-muted">Pedido #{{ $order->id }} criado com sucesso!</p>
    </div>

    {{-- Dados Bancários --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="fa fa-building"></i> Escolha o banco para depósito</h5>
        </div>
        <div class="card-body">
            <div class="row">
                @foreach($bankAccounts as $bank)
                    <div class="col-12 col-sm-6 col-md-4 mb-3">
                        <div class="card h-100 border-primary">
                            <div class="card-body">
                                <h6 class="card-title fw-bold"><i class="fa fa-university"></i> {{ $bank->name }}</h6>
                                <p class="card-text text-muted">{{ $bank->bank_details }}</p>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Upload do Comprovante --}}
    <div class="card mb-4 shadow-sm">
        <div class="card-header bg-success text-white">
            <h5 class="mb-0"><i class="fa fa-file-upload"></i> Envio do Comprovante</h5>
        </div>
        <div class="card-body">
            <form action="{{ route('checkout.deposito.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="deposit_receipt" class="form-label">Envie o comprovante de depósito</label>
                    <input type="file" name="deposit_receipt" id="deposit_receipt" class="form-control">
                </div>

                @if($order->deposit_receipt)
                    <p class="text-success mt-2">Comprovante enviado: 
                        <a href="{{ asset('storage/'.$order->deposit_receipt) }}" target="_blank" class="fw-bold">Ver arquivo</a>
                    </p>
                @endif

                <button type="submit" class="btn btn-success w-100 mt-3"><i class="fa fa-check-circle"></i> Enviar Comprovante</button>
            </form>
        </div>
    </div>

    {{-- Resumo do Pedido --}}
    @php $totalPedido = 0; @endphp
    <div class="card shadow-sm">
        <div class="card-header bg-secondary text-white">
            <h5 class="mb-0"><i class="fa fa-receipt"></i> Resumo do Pedido</h5>
        </div>
        <div class="card-body">
            @foreach ($orderItems as $item)
                <div class="d-flex justify-content-between align-items-center mb-2 p-2 border-bottom">
                    <div>
                        <p class="mb-1 fw-bold">{{ $item->product->slug ?? 'Produto' }}</p>
                        <small class="text-muted">Qtd: {{ $item->quantity }}</small>
                    </div>
                    <div class="text-end">
                        <p class="mb-1">{{ currency_format($item->product->price ?? 0) }}</p>
                        <small class="text-muted">Total: {{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</small>
                    </div>
                </div>
                @php $totalPedido += ($item->product->price ?? 0) * $item->quantity; @endphp
            @endforeach
            <hr>
            <h5 class="text-end"><i class="fa fa-money-bill-wave"></i> Total do Pedido: {{ currency_format($totalPedido) }}</h5>
        </div>
    </div>

    <p class="mt-4 text-center text-muted">Após enviar o comprovante, o pagamento será confirmado.</p>
</div>
@endsection
