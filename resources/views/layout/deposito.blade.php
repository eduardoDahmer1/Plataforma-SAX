@extends('layout.checkout')

@section('content')
    <div class="container mt-5 mb-5 sax-checkout-page">
        <div class="text-center mb-5">
            <h2 class="sax-title-payment text-uppercase">{{ __('messages.pagamento_via_deposito') }}</h2>
            <div class="sax-order-badge mt-2">{{ __('messages.pedido_numero') }} #{{ $order->id }}</div>
            <p class="text-muted small mt-2 tracking-wider">{{ __('messages.pedido_criado_sucesso') }}</p>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="sax-checkout-box mb-4">
                    <h4 class="sax-step-title">
                        <span class="step-number"><i class="fa fa-university"></i></span> 
                        {{ __('messages.dados_bancarios') }}
                    </h4>
                    <p class="text-muted small mb-4">{{ __('messages.escolha_conta_deposito') }}</p>

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

                <div class="sax-checkout-box">
                    <h4 class="sax-step-title">
                        <span class="step-number"><i class="fa fa-file-upload"></i></span> 
                        {{ __('messages.confirmar_pagamento') }}
                    </h4>
                    <form action="{{ route('checkout.deposito.submit', $order->id) }}" method="POST" enctype="multipart/form-data">
                        @csrf
                        <div class="sax-input-group mb-4">
                            <label class="sax-label">{{ __('messages.anexar_comprovante') }}</label>
                            <div class="sax-file-wrapper">
                                <input type="file" name="deposit_receipt" id="deposit_receipt" class="form-control sax-form-control-file">
                            </div>
                        </div>

                        @if ($order->deposit_receipt)
                            <div class="sax-alert-success mb-3 d-flex align-items-center">
                                <i class="fa fa-check-circle me-2"></i>
                                <span>
                                    {{ __('messages.comprovante_enviado') }} 
                                    <a href="{{ asset('storage/' . $order->deposit_receipt) }}" target="_blank" class="text-dark fw-bold text-decoration-underline">
                                        {{ __('messages.ver_arquivo') }}
                                    </a>
                                </span>
                            </div>
                        @endif

                        <button type="submit" class="sax-btn-finish w-100">
                            {{ __('messages.enviar_comprovante_botao') }}
                        </button>
                    </form>
                </div>
            </div>

            <div class="col-lg-5">
                <div class="sax-checkout-box sticky-top" style="top: 20px;">
                    <h4 class="sax-step-title">{{ __('messages.resumo_do_pedido') }}</h4>

                    @php $totalPedido = 0; @endphp
                    <div class="sax-summary-list">
                        @foreach ($orderItems as $item)
                            <div class="d-flex align-items-center gap-3 mb-3 pb-3 border-bottom border-light">
                                <div class="sax-cart-img-wrapper" style="width: 60px; height: 75px;">
                                    <img src="{{ $item->product->photo_url ?? asset('storage/uploads/noimage.webp') }}"
                                         alt="{{ $item->product->external_name }}" class="img-fluid">
                                </div>
                                <div class="flex-grow-1">
                                    <p class="mb-0 sax-item-name text-truncate" style="max-width: 200px;">
                                        {{ $item->product->external_name ?? 'Produto' }}
                                    </p>
                                    <small class="text-muted">{{ __('messages.quantidade') }}: {{ $item->quantity }}</small>
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
                            <span class="text-muted">{{ __('messages.subtotal') }}</span>
                            <span>{{ currency_format($totalPedido) }}</span>
                        </div>
                        <div class="d-flex justify-content-between mb-3">
                            <span class="text-muted">{{ __('messages.envio') }}</span>
                            <span class="text-success small fw-bold text-uppercase">{{ __('messages.a_confirmar') }}</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center border-top pt-3">
                            <span class="fw-bold h5 mb-0">{{ __('messages.total') }}</span>
                            <span class="fw-bold h4 mb-0">{{ currency_format($totalPedido) }}</span>
                        </div>
                    </div>
                </div>
                <p class="mt-4 text-center text-muted small tracking-wider px-3">
                    <i class="fa fa-info-circle me-1"></i> {{ __('messages.aviso_processamento_deposito') }}
                </p>
            </div>
        </div>
    </div>
@endsection