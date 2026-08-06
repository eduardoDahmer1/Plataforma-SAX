@php
    $catalogAvailable = (bool) ($catalogIntegrationStatus['available'] ?? true);
    $purchaseWasBlocked = session()->has('catalog_purchase_blocked');
@endphp

@if (! $catalogAvailable)
    <div class="modal fade sax-catalog-pause-modal" id="catalogIntegrationPauseModal" tabindex="-1"
         aria-labelledby="catalogIntegrationPauseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <button type="button" class="btn-close sax-catalog-pause-modal__close" data-bs-dismiss="modal"
                        aria-label="{{ __('messages.fechar') }}"></button>
                <div class="modal-body text-center">
                    <span class="sax-catalog-pause-modal__icon"><i class="fa-solid fa-arrows-rotate" aria-hidden="true"></i></span>
                    <h2 id="catalogIntegrationPauseModalLabel">{{ __('messages.catalog_purchase_paused_title') }}</h2>
                    <p>{{ __('messages.catalog_purchase_paused_message') }}</p>
                    <button type="button" class="btn btn-dark w-100" data-bs-dismiss="modal">
                        {{ __('messages.catalog_purchase_paused_continue') }}
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        window.saxCatalogPurchasingAvailable = false;
        window.saxCatalogPurchaseWasBlocked = @json($purchaseWasBlocked);
    </script>
@else
    <script>window.saxCatalogPurchasingAvailable = true;</script>
@endif
