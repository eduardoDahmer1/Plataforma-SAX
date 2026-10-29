@extends('layout.checkout')

@section('content')
@php
    $initialStep = (int) old('step', 1);
    $errorFields = $errors->keys();

    if (!empty($errorFields)) {
        if (collect($errorFields)->contains(fn ($field) => in_array($field, ['name', 'document', 'document_type', 'email', 'phone'], true))) {
            $initialStep = 2;
        } elseif (collect($errorFields)->contains(fn ($field) => in_array($field, ['shipping', 'shipping_address_id', 'address_label', 'country', 'cep', 'street', 'number', 'city', 'state', 'store', 'observations'], true))) {
            $initialStep = 3;
        } elseif (collect($errorFields)->contains(fn ($field) => in_array($field, ['payment_method', 'deposit_receipt', 'accept_terms', 'accept_pix_terms'], true))) {
            $initialStep = 4;
        }
    }

    $initialStep = max(1, min(4, $initialStep));
    $reservationCopy = match (app()->getLocale()) {
        'en' => [
            'title' => 'Temporary stock reservation',
            'body' => 'Your items are reserved while this checkout remains active. The reservation is renewed automatically, but stock is only guaranteed after payment confirmation. If you leave or the time expires, the items return to stock.',
        ],
        'es' => [
            'title' => 'Reserva temporal de stock',
            'body' => 'Tus artículos quedan reservados mientras este checkout siga activo. La reserva se renueva automáticamente, pero el stock solo queda garantizado al confirmarse el pago. Si sales o vence el plazo, los artículos vuelven al stock.',
        ],
        default => [
            'title' => 'Reserva temporária de estoque',
            'body' => 'Seus itens ficam reservados enquanto este checkout estiver ativo. A reserva é renovada automaticamente, mas o estoque só fica garantido após a confirmação do pagamento. Se você sair ou o prazo expirar, os itens voltam ao estoque.',
        ],
    };
@endphp
<div class="sax-checkout-container">
    <div class="sax-commerce-page-heading">
        <div>
            <span>Compra segura</span>
            <h1>Finalize seu pedido</h1>
            <p>Revise cada etapa com calma. Seus dados permanecem protegidos durante o processo.</p>
        </div>
        <a href="{{ route('cart.view') }}" class="sax-commerce-edit-cart">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i> Editar carrinho
        </a>
    </div>
    @if ($errors->any() || session('error'))
        <div class="alert alert-danger border-0 rounded-3 mb-4 sax-checkout-alert">
            @if (session('error'))
                <div>{{ session('error') }}</div>
            @endif

            @if ($errors->any())
                <ul class="mb-0 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            @endif
        </div>
    @endif

    <div class="alert alert-warning border-0 rounded-3 mb-4 sax-checkout-alert d-flex align-items-start gap-3" role="status">
        <i class="fa-solid fa-hourglass-half mt-1" aria-hidden="true"></i>
        <div>
            <strong class="d-block">{{ $reservationCopy['title'] }}</strong>
            <span>{{ $reservationCopy['body'] }}</span>
            <small class="d-block mt-1 fw-semibold" id="stockReservationCountdown"
                   data-expires-at="{{ $reservationExpiresAt->toIso8601String() }}"></small>
        </div>
    </div>

    <div class="sax-checkout-shell">
        <div class="sax-checkout-progress" aria-label="Etapas do checkout">
            <div class="sax-checkout-progress-step {{ $initialStep > 1 ? 'is-complete' : ($initialStep === 1 ? 'is-current' : '') }}"><b>1</b><span>Itens</span></div>
            <div class="sax-checkout-progress-step {{ $initialStep > 2 ? 'is-complete' : ($initialStep === 2 ? 'is-current' : '') }}"><b>2</b><span>Identificação</span></div>
            <div class="sax-checkout-progress-step {{ $initialStep > 3 ? 'is-complete' : ($initialStep === 3 ? 'is-current' : '') }}"><b>3</b><span>Entrega</span></div>
            <div class="sax-checkout-progress-step {{ $initialStep === 4 ? 'is-current' : '' }}"><b>4</b><span>Pagamento</span></div>
        </div>

    <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" id="currentStep" value="{{ $initialStep }}">

        <x-checkout.step1-cart :cart="$cart" :resumo="$resumo" />
        <x-checkout.step2-user />
        <x-checkout.step3-shipping :addresses="$addresses" :dhl-enabled="$dhlEnabled" />
        <x-checkout.step4-payment :cart="$cart" :resumo="$resumo" :payment-methods="$paymentMethods" :policies="$policies" :pyg-currency="$pygCurrency" />
    </form>
</div>
</div>

<script>
document.addEventListener('DOMContentLoaded', () => {
    const checkoutForm = document.getElementById('checkoutForm');
    const countdown = document.getElementById('stockReservationCountdown');
    const csrf = checkoutForm?.querySelector('input[name="_token"]')?.value;
    let submitting = false;
    let expiresAt = new Date(countdown?.dataset.expiresAt || 0);

    checkoutForm?.addEventListener('submit', () => { submitting = true; });

    const renderCountdown = () => {
        if (!countdown || Number.isNaN(expiresAt.getTime())) return;
        const seconds = Math.max(0, Math.floor((expiresAt.getTime() - Date.now()) / 1000));
        const minutes = String(Math.floor(seconds / 60)).padStart(2, '0');
        const rest = String(seconds % 60).padStart(2, '0');
        countdown.textContent = `Reserva ativa: ${minutes}:${rest}`;
    };

    const refreshReservation = async () => {
        if (submitting || !csrf) return;
        try {
            const response = await fetch(@json(route('checkout.reservation.refresh')), {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrf,
                    'X-Requested-With': 'XMLHttpRequest',
                },
            });
            if (!response.ok) return;
            const data = await response.json();
            if (data.expires_at) {
                expiresAt = new Date(data.expires_at);
                if (countdown) countdown.dataset.expiresAt = data.expires_at;
                renderCountdown();
            }
        } catch (_) {}
    };

    window.addEventListener('pagehide', () => {
        if (submitting || !csrf || !navigator.sendBeacon) return;
        const payload = new FormData();
        payload.append('_token', csrf);
        payload.append('_method', 'DELETE');
        navigator.sendBeacon(@json(route('checkout.reservation.release')), payload);
    });

    renderCountdown();
    setInterval(renderCountdown, 1000);
    setInterval(refreshReservation, 60000);
});
</script>

@endsection
