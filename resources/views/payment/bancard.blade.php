@extends('layouts.app')

@section('content')
<div class="container">
    <h3>Redirecionando para Bancard...</h3>

    <form id="payForm" action="{{ $bancardUrl }}" method="POST">
        @csrf
        <input type="hidden" name="public_key" value="{{ config('services.bancard.public_key') }}">
        <input type="hidden" name="order_id" value="{{ $order->id }}">
        <input type="hidden" name="amount" value="{{ $order->total }}">
        <input type="hidden" name="token" value="{{ $token }}">
        <!-- mais dados se necessário -->
    </form>

    <script>
        document.getElementById('payForm').submit();
    </script>
</div>
@endsection
