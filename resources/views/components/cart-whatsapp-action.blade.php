@if (auth()->check() && ($storeControls['whatsapp_enabled'] ?? true) && app(\App\Services\CartWhatsappService::class)->contact(request()))
    <a href="{{ route('cart.whatsapp') }}" class="btn btn-success w-100 mb-3">
        <i class="fab fa-whatsapp me-2" aria-hidden="true"></i>{{ __('messages.checkout_pause_send') }}
    </a>
@endif
