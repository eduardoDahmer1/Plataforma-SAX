@extends('layout.layout')

@section('content')
<div class="container">
    <h3>Obrigado pela sua compra!</h3>

    <p>Seu pedido #{{ $order->id }} foi registrado com sucesso.</p>
    <p>Você será redirecionado para o processamento do pagamento com Bancard em alguns segundos.</p>

    <form id="payForm" action="{{ $bancardUrl }}" method="POST">
        @csrf
        <input type="hidden" name="public_key" value="{{ config('services.bancard.public_key') }}">
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <input type="hidden" name="amount" value="{{ $order->total }}">
        <input type="hidden" name="token" value="{{ $token }}">
    </form>

    <script>
        setTimeout(function() {
            document.getElementById('payForm').submit();
        }, 3000); // redireciona depois de 3 segundos
    </script>
</div>
@endsection
