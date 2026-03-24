@extends('layout.layout')

@section('content')
<section class="bancard-checkout-shell py-4 py-lg-5">
    <div class="container">
        <div class="bancard-checkout-panel mx-auto">
            <div class="bancard-head">
                <p class="bancard-kicker mb-2">Checkout seguro</p>
                <h1 class="bancard-title mb-2">Pagamento Bancard V2</h1>
                <p class="bancard-subtitle mb-0">Finalize seu pedido com cartao ou QR em ambiente protegido.</p>
            </div>

            <div class="bancard-meta-grid">
                <div class="bancard-meta-item">
                    <span class="meta-label">Pedido</span>
                    <strong class="meta-value">#{{ $order->id }}</strong>
                </div>
                <div class="bancard-meta-item">
                    <span class="meta-label">Total</span>
                    <strong class="meta-value">{{ number_format((float) $order->total, 2, ',', '.') }}</strong>
                </div>
                <div class="bancard-meta-item">
                    <span class="meta-label">Status</span>
                    <strong class="meta-value text-uppercase">{{ strtolower((string) $order->status) }}</strong>
                </div>
            </div>

            <div class="bancard-frame-wrap">
                <div id="iframe-container"></div>
            </div>

            <div class="bancard-actions">
                <a href="{{ route('user.orders.show', $order->id) }}" class="btn btn-outline-secondary">
                    Ver pedido
                </a>
                <a href="{{ route('checkout.bancard.v2.cancel', $order->id) }}" class="btn btn-outline-danger">
                    Cancelar pagamento
                </a>
            </div>

            <p class="bancard-note mb-0">
                Se houver instabilidade no retorno, acompanhe a confirmacao no detalhe do pedido.
            </p>
        </div>
    </div>
</section>

<style>
    .bancard-checkout-shell {
        background:
            radial-gradient(900px 350px at 100% 0%, rgba(14, 74, 120, 0.08), transparent 60%),
            radial-gradient(700px 300px at 0% 100%, rgba(36, 36, 36, 0.06), transparent 60%),
            #f8f9fb;
    }

    .bancard-checkout-panel {
        max-width: 860px;
        background: transparent;
        border: none;
        border-radius: 18px;
        padding: 24px;
        box-shadow: none;
    }

    .bancard-kicker {
        font-size: 0.75rem;
        font-weight: 800;
        letter-spacing: 0.1em;
        text-transform: uppercase;
        color: #5b6472;
    }

    .bancard-title {
        font-size: clamp(1.4rem, 1.15rem + 1vw, 2rem);
        font-weight: 800;
        color: #121722;
        letter-spacing: 0.01em;
    }

    .bancard-subtitle {
        color: #5b6472;
    }

    .bancard-meta-grid {
        display: grid;
        grid-template-columns: repeat(3, minmax(0, 1fr));
        gap: 8px;
        margin: 16px 0 14px;
    }

    .bancard-meta-item {
        background: #fff;
        border: 1px solid #e3e7ef;
        border-radius: 10px;
        padding: 10px 12px;
    }

    .meta-label {
        display: block;
        font-size: 0.72rem;
        text-transform: uppercase;
        letter-spacing: 0.06em;
        color: #6b7280;
    }

    .meta-value {
        font-size: 1rem;
        color: #111827;
        font-weight: 700;
    }

    .bancard-frame-wrap {
        max-width: 600px;
        margin: 0 auto;
        border: none;
        border-radius: 0;
        padding: 0;
        background: transparent;
        box-shadow: none;
        overflow: hidden;
        margin-top: 8px;
        margin-bottom: 0;
    }

    #iframe-container {
        min-height: 580px;
        overflow: hidden;
    }

    .bancard-actions {
        margin-top: 10px;
        display: flex;
        justify-content: center;
        gap: 10px;
        flex-wrap: wrap;
    }

    .bancard-actions .btn {
        min-width: 170px;
        font-size: 0.9rem;
        padding: 8px 14px;
    }

    .bancard-note {
        margin-top: 10px;
        text-align: center;
        font-size: 0.8rem;
        color: #606978;
    }

    @media (max-width: 991.98px) {
        .bancard-checkout-panel {
            padding: 18px;
            border-radius: 14px;
        }

        .bancard-meta-grid {
            grid-template-columns: 1fr;
        }

        #iframe-container {
            min-height: 660px;
        }

        .bancard-frame-wrap {
            max-width: 560px;
        }
    }

    @media (max-width: 575.98px) {
        .bancard-checkout-shell {
            padding-top: 1.2rem;
        }

        .bancard-checkout-panel {
            padding: 14px;
        }

        #iframe-container {
            min-height: 730px;
        }

        .bancard-actions .btn {
            width: 100%;
            min-width: 0;
        }
    }
</style>

<script src="{{ $checkoutJsUrl ?? 'https://vpos.infonet.com.py/checkout/javascript/dist/bancard-checkout-4.0.0.js' }}"></script>
<script>
    window.addEventListener('load', function () {
        if (!window.Bancard || !Bancard.Checkout || typeof Bancard.Checkout.createForm !== 'function') {
            console.error('Bancard checkout-js não carregado.');
            return;
        }

        const options = {
            styles: {
                'form-background-color': '#ffffff',
                'form-border-color': '#e5e5e5',
                'header-background-color': '#f8f8f8',
                'header-text-color': '#111111',
                'button-background-color': '#111111',
                'button-border-color': '#111111',
                'button-text-color': '#ffffff'
            }
        };
        Bancard.Checkout.createForm('iframe-container', '{{ $processId }}', options);

        // Bancard injects a cross-origin iframe. If its default height is too small,
        // it shows an internal scrollbar. We force a responsive fixed height.
        function getTargetHeight() {
            if (window.innerWidth < 576) return 730;
            if (window.innerWidth < 992) return 660;
            return 580;
        }

        function setIframeSize(iframe) {
            const safetyBuffer = 20;
            const targetHeight = getTargetHeight() + safetyBuffer;
            iframe.style.width = '100%';
            iframe.style.minHeight = targetHeight + 'px';
            iframe.style.height = targetHeight + 'px';
            iframe.style.border = '0';
            iframe.setAttribute('scrolling', 'no');
        }

        let attempts = 0;
        const applyIframeSizing = setInterval(function () {
            const iframe = document.querySelector('#iframe-container iframe');
            attempts++;

            if (iframe) {
                setIframeSize(iframe);

                window.addEventListener('resize', function () {
                    setIframeSize(iframe);
                });

                clearInterval(applyIframeSizing);
            }

            if (attempts > 20) {
                clearInterval(applyIframeSizing);
            }
        }, 150);
    });
</script>
@endsection
