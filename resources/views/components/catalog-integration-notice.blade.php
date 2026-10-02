@php
    $catalogAvailable = (bool) ($catalogIntegrationStatus['available'] ?? true);
    $purchaseWasBlocked = session()->has('catalog_purchase_blocked');
    $temporaryTestActive = (bool) ($storeControls['purchase_test_active'] ?? $catalogIntegrationStatus['temporary_test'] ?? false);
    $temporaryTestUntil = $storeControls['purchase_test_until'] ?? $catalogIntegrationStatus['temporary_test_until'] ?? null;
    $showTemporaryTestNotice = $temporaryTestActive && (auth()->user()?->canShop() ?? false);
@endphp

@if($showTemporaryTestNotice)
    <aside class="sax-temporary-purchase-notice" role="status"
           aria-label="Loja em atualização. Carrinho e checkout disponíveis para testes temporários."
           title="Carrinho e checkout estão disponíveis temporariamente; frete, estoque e pagamentos podem passar por validações.">
        <span class="sax-temporary-purchase-notice__icon"><i class="fa-solid fa-screwdriver-wrench" aria-hidden="true"></i></span>
        <strong>Loja em atualização</strong>
        @if($temporaryTestUntil)
            <span class="sax-temporary-purchase-notice__time">até {{ $temporaryTestUntil->format('H:i') }}</span>
        @endif
    </aside>

    @once
        <style>
            .sax-temporary-purchase-notice{position:fixed;z-index:9998;top:6px;left:12px;width:auto;max-width:calc(100vw - 24px);min-height:24px;display:inline-flex;align-items:center;gap:6px;margin:0;padding:3px 7px;background:#fff;border:1px solid #d3d5d7;border-radius:3px;color:#3f4348;box-shadow:0 2px 8px rgba(24,27,31,.08);font-family:Inter,-apple-system,BlinkMacSystemFont,"Segoe UI",sans-serif;white-space:nowrap}
            .sax-temporary-purchase-notice__icon{width:16px;height:16px;display:grid;place-items:center;background:#34373b;border-radius:2px;color:#fff;font-size:.48rem}
            .sax-temporary-purchase-notice strong{color:#34373b;font-size:.56rem;font-weight:750;line-height:1}
            .sax-temporary-purchase-notice__time{padding-left:6px;border-left:1px solid #d7d8da;color:#74787d;font-size:.52rem;font-weight:700;line-height:1;white-space:nowrap}
            .sax-account-body .sax-temporary-purchase-notice,.sax-commerce-body .sax-temporary-purchase-notice{top:76px;right:12px;bottom:auto;left:auto}
            @media(max-width:575.98px){.sax-temporary-purchase-notice{top:5px;left:8px;max-width:calc(100vw - 16px);min-height:22px;padding:3px 6px}.sax-account-body .sax-temporary-purchase-notice,.sax-commerce-body .sax-temporary-purchase-notice{top:68px;right:8px;bottom:auto;left:auto}.sax-temporary-purchase-notice strong{font-size:.53rem}}
        </style>
    @endonce
@endif

@if (! $catalogAvailable || $purchaseWasBlocked)
    <div class="modal fade sax-catalog-pause-modal" id="catalogIntegrationPauseModal" tabindex="-1"
         aria-labelledby="catalogIntegrationPauseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close sax-catalog-pause-modal__close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.fechar') }}"></button>
                <div class="modal-body text-center">
                    <span class="sax-catalog-pause-modal__icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                    <h2 id="catalogIntegrationPauseModalLabel">{{ __('messages.catalog_purchase_paused_title') }}</h2>
                    <p>{{ __('messages.checkout_pause_message') }}</p>
                    @include('components.cart-whatsapp-action')
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">
                        {{ __('messages.catalog_purchase_paused_continue') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.saxCatalogPurchasingAvailable = @json($catalogAvailable);
        window.saxCatalogPurchaseWasBlocked = @json($purchaseWasBlocked);
    </script>
@else
    <script>window.saxCatalogPurchasingAvailable = true;</script>
@endif
