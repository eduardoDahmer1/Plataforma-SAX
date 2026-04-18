@extends('layout.layout')

@section('content')
<section class="bancard-checkout-shell py-4 py-lg-5">
    <div class="container">
        <div class="bancard-checkout-panel mx-auto">
            <div class="bancard-head text-center mb-4">
                <p class="bancard-kicker mb-2">Checkout seguro</p>
                <h1 class="bancard-title mb-2">Pagamento Bancard V2</h1>
                <p class="bancard-subtitle mb-0">Finalize seu pedido com cartão ou QR em ambiente protegido.</p>
            </div>

            {{-- Grid de Informações Consolidado --}}
            <div class="bancard-meta-grid mb-4">
                <div class="bancard-meta-item">
                    <span class="meta-label">Pedido</span>
                    <strong class="meta-value">#{{ $order->id }}</strong>
                </div>
                <div class="bancard-meta-item">
                    <span class="meta-label">Total em Dólar</span>
                    <strong class="meta-value text-primary">{{ currency_format((float) $order->total) }}</strong>
                </div>
                <div class="bancard-meta-item highlight">
                    <span class="meta-label">Total em Guaraníes</span>
                    <strong class="meta-value">{{ $pygSymbol }} {{ number_format((float) $totalInPyg, 0, ',', '.') }}</strong>
                </div>
                <div class="bancard-meta-item">
                    <span class="meta-label">Status</span>
                    <strong class="meta-value status-badge">{{ strtolower((string) $order->status) }}</strong>
                </div>
            </div>

            {{-- Container do Iframe - Limpo e Adaptável --}}
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
    /* Shell e Background - Limpo */
    .bancard-checkout-shell {
        background: #fdfdfd; /* Fundo quase branco para destacar apenas os itens */
        min-height: 80vh;
        display: flex;
        align-items: center;
    }

    .bancard-checkout-panel {
        max-width: 800px;
        padding: 10px;
        background: transparent;
        border: none;
        box-shadow: none;
    }

    /* Tipografia */
    .bancard-kicker {
        font-size: 0.7rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        text-transform: uppercase;
        color: #8a94a6;
    }

    .bancard-title {
        font-size: clamp(1.5rem, 5vw, 2.2rem);
        font-weight: 800;
        color: #1a202c;
    }

    .bancard-subtitle {
        color: #718096;
        font-size: 0.95rem;
    }

    /* Grid de Metadados - Estilo Limpo */
    .bancard-meta-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 12px;
    }

    .bancard-meta-item {
        background: #f7fafc; /* Fundo cinza muito claro */
        border-radius: 12px;
        padding: 15px;
        border: 1px solid #edf2f7;
        box-shadow: none; /* Removendo sombra */
    }

    .bancard-meta-item.highlight {
        background: #edf2f7;
        border-color: #e2e8f0;
    }

    .meta-label {
        display: block;
        font-size: 0.65rem;
        font-weight: 700;
        text-transform: uppercase;
        color: #a0aec0;
        margin-bottom: 4px;
    }

    .meta-value {
        font-size: 1.05rem;
        color: #2d3748;
        font-weight: 700;
    }

    .status-badge {
        color: #38a169;
        text-transform: uppercase;
    }

    /* Container do Iframe - Totalmente Limpo */
    .bancard-frame-wrapper-clean {
        background: transparent;
        border: none;
        padding: 0;
        margin: 0;
        box-shadow: none;
        overflow: visible; /* Importante para não cortar e evitar rolagem */
    }

    #iframe-container {
        min-height: 500px; /* Altura mínima inicial, o JS ajustará */
        overflow: visible;
        background: transparent;
    }

    #iframe-container iframe {
        border: none !important;
        box-shadow: none !important;
    }

    /* Botões Customizados */
    .bancard-actions {
        display: flex;
        gap: 15px;
        justify-content: center;
    }

    .btn-action {
        border-radius: 10px;
        padding: 12px 24px;
        font-weight: 600;
        font-size: 0.9rem;
        transition: all 0.3s;
        min-width: 200px;
        border: none;
    }

    .btn-light-custom {
        background: #e2e8f0;
        color: #4a5568;
    }

    .btn-light-custom:hover {
        background: #cbd5e0;
        color: #2d3748;
    }

    .btn-danger-custom {
        background: #fff5f5;
        color: #c53030;
        border: 1px solid #feb2b2;
    }

    .btn-danger-custom:hover {
        background: #c53030;
        color: #fff;
    }

    .bancard-note {
        text-align: center;
        font-size: 0.85rem;
        color: #718096;
        max-width: 500px;
        margin: 0 auto;
    }

    /* Responsividade */
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

        // Função para tentar ajustar o tamanho do iframe e remover rolagem
        function adjustIframeSize(iframe) {
            // Remove qualquer scroll interno que a Bancard force
            iframe.setAttribute('scrolling', 'no');
            iframe.style.overflow = 'hidden';
            iframe.style.border = '0';
            iframe.style.width = '100%';

            // Tenta detectar a altura do conteúdo se possível (cross-origin pode bloquear)
            try {
                if (iframe.contentWindow && iframe.contentWindow.document.body.scrollHeight) {
                    const newHeight = iframe.contentWindow.document.body.scrollHeight + 50; // Buffer de segurança
                    iframe.style.height = newHeight + 'px';
                    return;
                }
            } catch (e) {
                // Se o cross-origin bloquear, usamos uma altura segura baseada na tela
                // O Iframe da Bancard tende a ser alto no mobile para acomodar QR e Form
                console.log("Cross-origin bloqueou detecção de altura. Usando fallback.");
            }

            // Fallback de altura fixa responsiva para garantir que nada seja cortado
            let targetHeight = 650; // Altura padrão segura para desktop
            if (window.innerWidth < 576) targetHeight = 850; // Muito mais alto no mobile
            else if (window.innerWidth < 992) targetHeight = 750;

            iframe.style.height = targetHeight + 'px';
        }

        let attempts = 0;
        const checkIframe = setInterval(function () {
            const iframe = document.querySelector('#iframe-container iframe');
            attempts++;

            if (iframe) {
                // Aplica o ajuste inicial
                adjustIframeSize(iframe);
                
                // Reaplica ao redimensionar a tela
                window.addEventListener('resize', () => adjustIframeSize(iframe));
                
                // Tenta reaplicar após um pequeno delay, pois o iframe da Bancard carrega em etapas
                setTimeout(() => adjustIframeSize(iframe), 2000);

                clearInterval(checkIframe);
            }

            if (attempts > 30) clearInterval(checkIframe);
        }, 200);
    });
</script>
@endsection