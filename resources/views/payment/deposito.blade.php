@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Pagamento via Depósito Bancário</h3>

    <p>Seu pedido #{{ $order->id }} está aguardando pagamento.</p>

    <p>Realize o pagamento em uma das contas abaixo:</p>

    @foreach($bankAccounts as $account)
        <div class="mb-4 p-3 border">
            <strong>{{ $account->bank_name }}</strong><br>
            RUC: {{ $account->ruc }}<br>
            Conta: {{ $account->account_number }}<br>
            Moeda: {{ $account->currency }}<br>
            <small>{{ $account->note }}</small>
        </div>
    @endforeach

    <p>Envie o comprovante pelo WhatsApp após o pagamento: <strong>+595 984 167575</strong></p>
</div>
@endsection
