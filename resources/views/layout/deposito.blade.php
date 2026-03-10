@extends('layout.checkout')

@section('content')
    <div class="container mt-5 mb-5 sax-checkout-page">
        <div class="text-center mb-5">
            <h2 class="sax-title-payment text-uppercase">Pagamento via Depósito</h2>
            <div class="sax-order-badge mt-2">Pedido #{{ $order->id }}</div>
            <p class="text-muted small mt-2 tracking-wider">PEDIDO CRIADO COM SUCESSO</p>
        </div>

        <div class="row">
            <div class="col-lg-7">
                <div class="sax-checkout-box mb-4">
                    <h4 class="sax-step-title"><span class="step-number"><i class="fa fa-university"></i></span> Datos
                        Bancários</h4>
                    <p class="text-muted small mb-4">Escolha uma de nossas contas para realizar o depósito ou transferência:
                    </p>

                    <div class="row g-3">
                        @foreach ($bankAccounts as $bank)
                            <div class="col-md-6">
                                <div class="sax-bank-card h-100">
                                    <h6 class="fw-bold mb-2 text-uppercase" style="letter-spacing: 1px;">{{ $bank->name }}
                                    </h6>
                                    <div class="sax-bank-details">
                                        {!! nl2br(e($bank->bank_details)) !!}
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="sax-checkout-box">
                    <h4 class="sax-step-title"><span class="step-number"><i class="fa fa-file-upload"></i></span> Confirmar
                        Pagamento</h4>
                    <form action="{{ route('checkout.deposito.submit', $order->id) }}" method="POST"
                        enctype="multipart/form-data">
                        @csrf
                        <div class="sax-input-group mb-4">
                            <label class="sax-label">Adjuntar Comprovante de Depósito</label>
                            <div class="sax-file-wrapper">
                                <input type="file" name="deposit_receipt" id="deposit_receipt"
                                    class="form-control sax-form-control-file">
                            </div>
                        </div>

                        @if ($order->deposit_receipt)
                            <div class="sax-alert-success mb-3 d-flex align-items-center">
                                <i class="fa fa-check-circle me-2"></i>
                                <span>Comprovante enviado: <a href="{{ asset('storage/' . $order->deposit_receipt) }}"
                                        target="_blank" class="text-dark fw-bold text-decoration-underline">Ver
                                        Arquivo</a></span>
                            </div>
                        @endif

                        <button type="submit" class="sax-btn-finish w-100">
                            Enviar Comprovante de Pago
                        </button>
                    </form>
                </div>
            </div>

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
                                    <p class="mb-0 sax-item-name text-truncate" style="max-width: 200px;">
                                        {{ $item->product->slug ?? 'Produto' }}</p>
                                    <small class="text-muted">Cantidad: {{ $item->quantity }}</small>
                                </div>
                                <div class="text-end">
                                    <span
                                        class="d-block fw-bold">{{ currency_format(($item->product->price ?? 0) * $item->quantity) }}</span>
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
