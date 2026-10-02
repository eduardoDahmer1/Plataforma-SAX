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
        <x-checkout.step3-shipping :addresses="$addresses" />
        <x-checkout.step4-payment :cart="$cart" :resumo="$resumo" :payment-methods="$paymentMethods" :policies="$policies" :pyg-currency="$pygCurrency" />
    </form>
</div>
</div>

@endsection
