@extends('layout.layout')

@section('content')
<section class="payment-result-shell py-4 py-lg-5">
    <div class="container">
        <div class="payment-result-card mx-auto">
            <div class="text-center mb-4">
                <div class="result-icon success mx-auto mb-3">
                    <i class="fas fa-check"></i>
                </div>
                <h1 class="result-title mb-2">Pagamento aprovado</h1>
                <p class="result-subtitle mb-0">Resumo da transacao confirmada pelo gateway Bancard V2.</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success border-0 mb-4" role="alert">
                    {{ session('success') }}
                </div>
            @endif

            <div class="result-grid mb-4">
                <div class="result-item">
                    <span class="result-label">Data e hora</span>
                    <strong class="result-value">{{ $transactionDateTime }}</strong>
                </div>
                <div class="result-item">
                    <span class="result-label">Pedido</span>
                    <strong class="result-value">#{{ $shopProcessId }}</strong>
                </div>
                <div class="result-item">
                    <span class="result-label">Valor pago</span>
                    <strong class="result-value">{{ $pygSymbol ?? 'G$' }} {{ number_format((float) $amount, 0, ',', '.') }}</strong>
                </div>
                <div class="result-item">
                    <span class="result-label">Descricao</span>
                    <strong class="result-value">{{ $responseDescription }}</strong>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-2 justify-content-center mt-4">
                <a href="{{ route('user.orders') }}" class="btn btn-dark px-4">Ver meus pedidos</a>
                <a href="{{ route('home') }}" class="btn btn-outline-secondary px-4">Voltar para a loja</a>
            </div>

            <small class="d-block text-muted text-center mt-3">
                Esta tela nao exibe codigo de autorizacao, codigo de resposta ou dados sensiveis de seguranca.
            </small>
        </div>
    </div>
</section>

<style>
    .payment-result-shell {
        background: linear-gradient(180deg, #f6f6f6 0%, #fcfcfc 100%);
        min-height: 72vh;
    }

    .payment-result-card {
        max-width: 860px;
        background: #fff;
        border: 1px solid #ececec;
        border-radius: 16px;
        padding: 1.5rem;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.06);
    }

    .result-icon {
        width: 56px;
        height: 56px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 1.4rem;
    }

    .result-icon.success {
        background: #eaf9ef;
        color: #198754;
        border: 1px solid #d4f0de;
    }

    .result-title {
        font-size: clamp(1.45rem, 3vw, 2rem);
        font-weight: 800;
        color: #131313;
    }

    .result-subtitle {
        color: #6d6d6d;
    }

    .result-grid {
        display: grid;
        grid-template-columns: repeat(2, minmax(0, 1fr));
        gap: 10px;
    }

    .result-item {
        background: #f8f8f8;
        border: 1px solid #e9e9e9;
        border-radius: 12px;
        padding: 0.9rem 1rem;
    }

    .result-label {
        display: block;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.7px;
        color: #939393;
        margin-bottom: 0.2rem;
    }

    .result-value {
        font-size: 0.98rem;
        color: #232323;
        word-break: break-word;
    }

    @media (max-width: 768px) {
        .result-grid {
            grid-template-columns: 1fr;
        }
    }
</style>
@endsection
