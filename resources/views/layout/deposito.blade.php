@extends('layout.app')

@section('content')
<div class="container mt-5">
    <h3>Pagamento via Depósito</h3>
    <p>Pedido #{{ $order->id }} criado com sucesso!</p>

    <h5>Dados bancários:</h5>
    <ul>
        @foreach($bankAccounts as $bank)
        <li>{{ $bank->name }} - {{ $bank->account_number }} - {{ $bank->bank_name }}</li>
        @endforeach
    </ul>

    <p>Após enviar o comprovante, o pagamento será confirmado.</p>
</div>
@endsection
