@extends('layout.checkout')

@section('content')
    <div class="container mt-5 mb-5 sax-checkout-page">
        {{-- Cabeçalho Minimalista --}}
        <div class="text-center mb-5">
            <h2 class="sax-title-payment text-uppercase">Pagamento via Depósito</h2>
            <div class="sax-order-badge mt-2">Pedido #{{ $order->id }}</div>
            <p class="text-muted small mt-2 tracking-wider">PEDIDO CRIADO COM SUCESSO</p>
        </div>

        <div class="row">
            {{-- Coluna da Esquerda: Bancos e Envio --}}
            <div class="col-lg-7">
                {{-- Escolha do Banco --}}
                <div class="sax-checkout-box mb-4">
                    <h4 class="sax-step-title"><span class="step-number"><i class="fa fa-university"></i></span> Datos Bancários</h4>
                    <p class="text-muted small mb-4">Escolha uma de nossas contas para realizar o depósito ou transferência:</p>
                    
                    <div class="row g-3">
                        @foreach ($bankAccounts as $bank)
                            <div class="col-md-6">
                                <div class="sax-bank-card h-100">
                                    <h6 class="fw-bold mb-2 text-uppercase" style="letter-spacing: 1px;">{{ $bank->name }}</h6>
                                    <div class="sax-bank-details">
                                        {!! nl2br(e($bank->bank_details)) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Upload do Comprovante --}}
                <div class="sax-checkout-box">
                    <h4 class="sax-step-title"><span class="step-number"><i class="fa fa-file-upload"></i></span> Confirmar Pagamento</h4>
                    <form action="{{ route('checkout.deposito.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="sax-input-group mb-4">
                            <label class="sax-label">Adjuntar Comprovante de Depósito</label>
                            <div class="sax-file-wrapper">
                                <input type="file" name="deposit_receipt" id="deposit_receipt" class="form-control sax-form-control-file">
                            </div>
                        </div>

                        @if ($order->deposit_receipt)
                            <div class="sax-alert-success mb-3 d-flex align-items-center">
                                <i class="fa fa-check-circle me-2"></i>
                                <span>Comprovante enviado: <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank" class="text-dark fw-bold text-decoration-underline">Ver Arquivo</a></span>
                            </div>
                        @endif

                        <button type="submit" class="sax-btn-finish w-100">
                            Enviar Comprovante de Pago
                        </button>
                    </form>
                </div>
            </div>

            {{-- Coluna da Direita: Resumo do Pedido --}}
            <div class="col-lg-5">
                <div class="sax-checkout-box sticky-top" style="top: 20px;">
                    <h4 class="sax-step-title">Resumo del Pedido</h4>
                    
                    @php $totalPedido = 0; @endphp
                    <div class="sax-summary-list">
                        @foreach ($orderItems as $item)
                            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                                <div class="sax-cart-img-wrapper" style="width: 60px; height: 75px;">
                                    <img src="{{ $item->product->photo_url ?? 'https://via.placeholder.com/60' }}"
                                         alt="{{ $item->product->slug }}" class="img-fluid">
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 sax-item-name text-truncate" style="max-width: 200px;">{{ $item->product->slug ?? 'Produto' }}</p>
                                    <small class="text-muted">Cantidad: {{ $item->quantity }}</small>
                                </div>
                                <div class="text-end">
                                    <span class="d-block fw-bold">{{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</span>
                                </div>
                            </div>
                            @php $totalPedido += ($item->product->price ?? 0) * $item->quantity; @endphp
                        @endforeach
                    </div>

                    <div class="sax-summary-total pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="text-muted">Subtotal</span>
                            <span>{{ currency_format($totalPedido) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">Envío</span>
                            <span class="text-success small fw-bold text-uppercase">A Confirmar</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="fw-bold h5 mb-0">TOTAL</span>
                            <span class="fw-bold h4 mb-0">{{ currency_format($totalPedido) }}</span>
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-center text-muted small tracking-wider px-3">
                    <i class="fa fa-info-circle me-1"></i> Su pedido será procesado una vez confirmado el depósito.
                </p>
            </div>
        </div>
    </div>
@endsection
<style>
    /* 1. Ajuste de Contraste da Página */
    body {
        background-color: #f8f9fa !important; /* Fundo cinza claro para destacar os boxes brancos */
        color: #1a1a1a;
    }

    /* 2. Configurações dos Boxes (Checkout Box) */
    .sax-checkout-box {
        background: #ffffff;
        border: 1px solid #e5e5e5;
        padding: 30px;
        margin-bottom: 20px;
        box-shadow: 0 4px 12px rgba(0, 0, 0, 0.03); /* Sombra suave para dar profundidade */
    }

    /* 3. Títulos e Tipografia */
    .sax-title-payment {
        font-weight: 800;
        letter-spacing: 4px;
        font-size: 1.8rem;
        color: #000;
    }

    .sax-step-title {
        font-size: 1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        border-bottom: 1px solid #f0f0f0;
        padding-bottom: 15px;
    }

    .step-number {
        background: #000;
        color: #fff;
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.8rem;
        margin-right: 15px;
        border-radius: 0; /* Quadrado padrão SAX */
    }

    /* 4. Cartões de Banco */
    .sax-bank-card {
        background: #ffffff;
        border: 1px solid #eee;
        padding: 20px;
        transition: all 0.3s ease;
        border-left: 4px solid #000; /* Detalhe lateral preto */
    }

    .sax-bank-card:hover {
        border-color: #000;
        box-shadow: 0 5px 15px rgba(0,0,0,0.08);
    }

    .sax-bank-details {
        font-size: 0.8rem;
        line-height: 1.6;
        color: #444;
    }

    /* 5. Resumo do Pedido e Imagens */
    .sax-cart-img-wrapper {
        background: #f5f5f5; /* Fundo para as fotos de produtos */
        padding: 5px;
        border: 1px solid #eee;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    /* 6. Botão Preto SAX */
    .sax-btn-finish {
        background: #000;
        color: #fff !important;
        border: none;
        padding: 18px;
        font-size: 0.85rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        transition: 0.3s ease;
        cursor: pointer;
    }

    .sax-btn-finish:hover {
        background: #333;
        transform: translateY(-2px);
    }

    /* 7. Input de Arquivo */
    .sax-file-wrapper {
        border: 2px dashed #ccc;
        padding: 30px;
        background: #fafafa;
        text-align: center;
        cursor: pointer;
    }

    .sax-file-wrapper:hover {
        border-color: #000;
        background: #fff;
    }

    .sax-order-badge {
        display: inline-block;
        background: #000;
        color: #fff;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 600;
        letter-spacing: 1px;
    }

    .tracking-wider {
        letter-spacing: 2px;
    }
</style>