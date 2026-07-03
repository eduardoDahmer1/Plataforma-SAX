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
                <div class="wait-icon success mx-auto mb-3">
                    <i class="fas fa-university"></i>
                </div>
                <h1 class="wait-title mb-2">Pedido reservado para deposito</h1>
                <p class="wait-subtitle mb-0">Seu pedido foi criado com sucesso. Envie o comprovante para acelerar a liberacao.</p>
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
                    <strong class="wait-value">{{ ucfirst((string) $order->payment_method) }}</strong>
                </div>
            </div>

            <div class="alert alert-light border rounded-3 mb-4 text-center">
                Assim que o comprovante for validado, voce recebera atualizacoes no painel e por e-mail.
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-center">
                <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-dark px-4">
                    <i class="fa fa-eye me-2"></i> Ver pedido
                </a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">
                    <i class="fa fa-store me-2"></i> Voltar para a loja
                </a>
            </div>
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

    .wait-icon.success {
        background: #eef8f0;
        color: #198754;
        border-color: #d5ebda;
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
@endsection
