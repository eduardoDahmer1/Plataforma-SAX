@php
    $manualCartAvailable = (bool) ($storeControls['cart_enabled'] ?? true);
    $manualCheckoutAvailable = $manualCartAvailable && (bool) ($storeControls['checkout_enabled'] ?? true);
    $manualAddAvailable = $manualCartAvailable && (bool) ($storeControls['add_to_cart_enabled'] ?? true);
    $manualBlockMessage = session('store_feature_blocked');
    $manualBlockTitle = session('store_feature_blocked_title', __('messages.store_cart_disabled_title'));
@endphp

@if (! $manualCartAvailable || ! $manualCheckoutAvailable || ! $manualAddAvailable || $manualBlockMessage)
    <div class="modal fade" id="storeControlPauseModal" tabindex="-1" aria-labelledby="storeControlPauseModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg">
                <button type="button" class="btn-close position-absolute end-0 top-0 m-3" data-bs-dismiss="modal" aria-label="{{ __('messages.fechar') }}"></button>
                <div class="modal-body text-center p-4 p-md-5">
                    <span class="d-inline-flex align-items-center justify-content-center bg-light mb-3" style="width:60px;height:60px;border-radius:8px"><i class="fa-solid fa-store-slash fs-3"></i></span>
                    <h2 class="h4 fw-bold" id="storeControlPauseModalLabel">{{ $manualBlockTitle }}</h2>
                    <p class="text-muted mb-4">
                        {{ $manualBlockMessage ?: (! $manualCartAvailable ? __('messages.store_cart_disabled_message') : (! $manualCheckoutAvailable ? __('messages.store_checkout_disabled_message') : __('messages.store_add_to_cart_disabled_message'))) }}
                    </p>
                    <button type="button" class="btn btn-dark w-100 py-2" data-bs-dismiss="modal">{{ __('messages.catalog_purchase_paused_continue') }}</button>
                </div>
            </div>
        </div>
    </div>

    @if($manualBlockMessage)
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const element = document.getElementById('storeControlPauseModal');
                if (element && window.bootstrap) bootstrap.Modal.getOrCreateInstance(element).show();
            });
        </script>
    @endif
@endif
