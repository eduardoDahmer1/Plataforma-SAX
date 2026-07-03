@extends('layout.layout')

@section('content')
@php
    $orderCurrencySign = $order->currency_sign ?: 'US$';
    $orderCurrencyValue = (float) ($order->currency_value ?: 1);
    $totalSelectedCurrency = (float) $order->total * $orderCurrencyValue;
    $orderStatus = strtolower((string) $order->status);
@endphp

<section class="bancard-checkout-shell py-4 py-lg-5">
    <div class="container">
        <div class="bancard-checkout-panel mx-auto">
            <div class="bancard-head text-center mb-4">
                <p class="bancard-kicker mb-2">Checkout seguro</p>
                <h1 class="bancard-title mb-2">Pagamento Bancard V2</h1>
                <p class="bancard-subtitle mb-0">Finalize seu pedido com cartão ou QR em ambiente protegido.</p>
            </div>

            <div class="bancard-meta-grid mb-4">
                <div class="bancard-meta-item">
                    <span class="meta-label">Pedido</span>
                    <strong class="meta-value">#{{ $order->id }}</strong>
                </div>
                <div class="bancard-meta-item">
                    <span class="meta-label">Total na moeda selecionada</span>
                    <strong class="meta-value text-primary">{{ $orderCurrencySign }} {{ number_format($totalSelectedCurrency, 2, ',', '.') }}</strong>
                </div>
                <div class="bancard-meta-item highlight">
                    <span class="meta-label">Total em Guaranies</span>
                    <strong class="meta-value">{{ $pygSymbol }} {{ number_format((float) $totalInPyg, 0, ',', '.') }}</strong>
                </div>
                <div class="bancard-meta-item">
                    <span class="meta-label">Status</span>
                    <strong class="meta-value status-badge {{ $orderStatus }}">{{ strtoupper($orderStatus) }}</strong>
                </div>
            </div>

            <div class="bancard-frame-wrapper-clean">
                <div id="iframe-container"></div>
            </div>

            <div class="bancard-actions mt-4">
                <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-action btn-light-custom">
                    <i class="fa fa-eye me-2"></i> Ver pedido
                </a>
                <a href="{{ route('checkout.bancard.v2.cancel', $order->id) }}" class="btn btn-action btn-danger-custom">
                    <i class="fa fa-times me-2"></i> Cancelar pagamento
                </a>
            </div>

            <div class="bancard-footer-info mt-4">
                <p class="bancard-note mb-0">
                    <i class="fa fa-info-circle me-1"></i> Se houver instabilidade no retorno, acompanhe a confirmação no detalhe do pedido.
                </p>
            </div>
        </div>
    </div>
</section>

<style>
    .bancard-checkout-shell {
        background: linear-gradient(180deg, #f6f6f6 0%, #fcfcfc 100%);
        min-height: 80vh;
        display: flex;
        align-items: center;
    }

    .bancard-checkout-panel {
        max-width: 900px;
        padding: 1.2rem;
        background: #ffffff;
        border: 1px solid #ececec;
        border-radius: 16px;
        box-shadow: 0 14px 28px rgba(0, 0, 0, 0.06);
    }

    .bancard-kicker {
        font-size: 0.72rem;
        font-weight: 700;
        letter-spacing: 1.8px;
        text-transform: uppercase;
        color: #8c8c8c;
    }

    .bancard-title {
        font-size: clamp(1.55rem, 4vw, 2.25rem);
        font-weight: 800;
        color: #131313;
    }

    .bancard-subtitle {
        color: #6d6d6d;
        font-size: 0.95rem;
    }

    .bancard-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 10px;
    }

    .bancard-meta-item {
        background: #f8f8f8;
        border-radius: 12px;
        padding: 14px;
        border: 1px solid #eaeaea;
    }

    .bancard-meta-item.highlight {
        background: #f1f1f1;
        border-color: #dfdfdf;
    }

    .meta-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #9a9a9a;
        margin-bottom: 5px;
        letter-spacing: 0.8px;
    }

    .meta-value {
        font-size: 1.02rem;
        color: #1f1f1f;
        font-weight: 700;
    }

    .status-badge {
        text-transform: uppercase;
    }

    .status-badge.pending,
    .status-badge.processing {
        color: #ad7f00;
    }

    .status-badge.paid,
    .status-badge.completed {
        color: #198754;
    }

    .status-badge.failed,
    .status-badge.canceled,
    .status-badge.cancelled {
        color: #c0392b;
    }

    .bancard-frame-wrapper-clean {
        background: #ffffff;
        border: 1px solid #ebebeb;
        border-radius: 12px;
        padding: 0.35rem;
        overflow: hidden;
    }

    #iframe-container {
        min-height: 500px;
        overflow: visible;
        background: transparent;
    }

    #iframe-container iframe {
        border: none !important;
        box-shadow: none !important;
    }

    .bancard-actions {
        display: flex;
        gap: 12px;
        justify-content: center;
        flex-wrap: wrap;
    }

    .btn-action {
        border-radius: 10px;
        padding: 11px 22px;
        font-weight: 600;
        font-size: 0.88rem;
        transition: all 0.25s;
        min-width: 210px;
        border: none;
    }

    .btn-light-custom {
        background: #f3f3f3;
        color: #333;
        border: 1px solid #d9d9d9;
    }

    .btn-light-custom:hover {
        background: #e9e9e9;
        color: #111;
    }

    .btn-danger-custom {
        background: #fff4f4;
        color: #c0392b;
        border: 1px solid #f0c9c9;
    }

    .btn-danger-custom:hover {
        background: #c0392b;
        color: #fff;
    }

    .bancard-note {
        text-align: center;
        font-size: 0.84rem;
        color: #727272;
        max-width: 500px;
        margin: 0 auto;
    }

    @media (max-width: 850px) {
        .bancard-meta-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }

    @media (max-width: 576px) {
        .bancard-meta-grid {
            grid-template-columns: 1fr;
        }

        .bancard-actions {
            flex-direction: column;
        }

        .btn-action {
            width: 100%;
        }
    }
</style>

<script src="{{ $checkoutJsUrl ?? 'https://vpos.infonet.com.py/checkout/javascript/dist/bancard-checkout-4.0.0.js' }}"></script>
<script>
    window.addEventListener('load', function () {
        if (!window.Bancard || !Bancard.Checkout) {
            console.error('Bancard checkout-js não carregado.');
            return;
        }

        const options = {
            styles: {
                'form-background-color': '#ffffff',
                'form-border-color': '#ffffff',
                'header-background-color': '#ffffff',
                'header-text-color': '#1a202c',
                'button-background-color': '#000000',
                'button-border-color': '#000000',
                'button-text-color': '#ffffff',
                'input-background-color': '#f7fafc',
                'input-text-color': '#2d3748',
                'input-placeholder-color': '#a0aec0'
            }
        };

        Bancard.Checkout.createForm('iframe-container', '{{ $processId }}', options);

        function adjustIframeSize(iframe) {
            iframe.setAttribute('scrolling', 'no');
            iframe.style.overflow = 'hidden';
            iframe.style.border = '0';
            iframe.style.width = '100%';

            try {
                if (iframe.contentWindow && iframe.contentWindow.document.body.scrollHeight) {
                    const newHeight = iframe.contentWindow.document.body.scrollHeight + 50;
                    iframe.style.height = newHeight + 'px';
                    return;
                }
            } catch (e) {
                console.log('Cross-origin bloqueou deteccao de altura. Usando fallback.');
            }

            let targetHeight = 650;
            if (window.innerWidth < 576) targetHeight = 850;
            else if (window.innerWidth < 992) targetHeight = 750;

            iframe.style.height = targetHeight + 'px';
        }

        let attempts = 0;
        const checkIframe = setInterval(function () {
            const iframe = document.querySelector('#iframe-container iframe');
            attempts++;

            if (iframe) {
                adjustIframeSize(iframe);

                window.addEventListener('resize', () => adjustIframeSize(iframe));

                setTimeout(() => adjustIframeSize(iframe), 2000);

                clearInterval(checkIframe);
            }

            if (attempts > 30) clearInterval(checkIframe);
        }, 200);
    });
</script>
@endsection