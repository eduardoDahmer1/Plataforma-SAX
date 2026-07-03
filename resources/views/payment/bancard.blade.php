@extends('layout.layout')

@section('content')
@php
    $orderCurrencySign = $order->currency_sign ?: 'US$';
    $orderCurrencyValue = (float) ($order->currency_value ?: 1);
    $totalSelectedCurrency = (float) $order->total * $orderCurrencyValue;
@endphp

<section class="payment-wait-shell py-4 py-lg-5">
    <div class="container">
        <div class="payment-wait-card mx-auto">
            <div class="text-center mb-4">
                <div class="wait-icon mx-auto mb-3">
                    <i class="fas fa-credit-card"></i>
                </div>
                <h1 class="wait-title mb-2">Redirecionando para Bancard</h1>
                <p class="wait-subtitle mb-0">Seu pedido foi registrado. Estamos preparando o ambiente seguro de pagamento.</p>
            </div>

            <div class="wait-grid mb-4">
                <div class="wait-item">
                    <span class="wait-label">Pedido</span>
                    <strong class="wait-value">#{{ $order->id }}</strong>
                </div>
                <div class="wait-item">
                    <span class="wait-label">Cliente</span>
                    <strong class="wait-value">{{ $order->user->name ?? 'Cliente' }}</strong>
                </div>
                <div class="wait-item">
                    <span class="wait-label">Valor</span>
                    <strong class="wait-value">{{ $orderCurrencySign }} {{ number_format($totalSelectedCurrency, 2, ',', '.') }}</strong>
                </div>
                <div class="wait-item">
                    <span class="wait-label">Metodo</span>
                    <strong class="wait-value">Bancard</strong>
                </div>
            </div>

            <form id="payForm" action="{{ $bancardUrl }}" method="POST" class="text-center">
                @csrf
                <input type="hidden" name="public_key" value="{{ config('services.bancard.public_key') }}">
                <input type="hidden" name="order_id" value="{{ $order->id }}">
                <input type="hidden" name="amount" value="{{ $order->total }}">
                <input type="hidden" name="token" value="{{ $token }}">
                <button type="submit" class="btn btn-dark px-4 py-2">
                    <i class="fa fa-credit-card me-2"></i> Ir para pagamento
                </button>
            </form>

            <p class="text-muted text-center mt-3 mb-0">Se o redirecionamento nao acontecer automaticamente, clique no botao acima.</p>
        </div>
    </div>
</section>

<style>
    .payment-wait-shell {
        background: linear-gradient(180deg, #f6f6f6 0%, #fcfcfc 100%);
        min-height: 72vh;
    }

    .payment-wait-card {
        max-width: 860px;
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.06);
    }

    .wait-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.2rem;
        background: #f1f1f1;
        border: 1px solid #e2e2e2;
        color: #1f1f1f;
    }

    .wait-title {
        font-size: clamp(1.45rem, 3vw, 2rem);
        font-weight: 800;
        color: #131313;
    }

    .wait-subtitle {
        color: #6d6d6d;
    }

    .wait-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .wait-item {
        background: #f8f8f8;
        border: 1px solid #e9e9e9;
        border-radius: 12px;
        padding: 0.9rem 1rem;
    }

    .wait-label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #939393;
        margin-bottom: 0.2rem;
    }

    .wait-value {
        font-size: 0.98rem;
        color: #232323;
        word-break: break-word;
    }

    @media (max-width: 768px) {
        .wait-grid {
            grid-template-columns: 1fr;
        }
    }
</style>

<script>
    setTimeout(function () {
        document.getElementById('payForm').submit();
    }, 3000);
</script>
@endsection
