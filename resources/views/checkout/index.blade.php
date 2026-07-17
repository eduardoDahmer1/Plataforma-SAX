@extends('layout.checkout')

@section('content')
@php
    $initialStep = (int) old('step', 1);
    $errorFields = $errors->keys();

    if (!empty($errorFields)) {
        if (collect($errorFields)->contains(fn ($field) => in_array($field, ['name', 'document', 'email', 'phone'], true))) {
            $initialStep = 2;
        } elseif (collect($errorFields)->contains(fn ($field) => in_array($field, ['shipping', 'country', 'cep', 'street', 'number', 'city', 'state', 'store', 'observations'], true))) {
            $initialStep = 3;
        } elseif (collect($errorFields)->contains(fn ($field) => in_array($field, ['payment_method', 'deposit_receipt', 'accept_terms'], true))) {
            $initialStep = 4;
        }
    }

    $initialStep = max(1, min(4, $initialStep));
@endphp
<div class="container mt-5">
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
        <div class="sax-checkout-progress" aria-hidden="true">
            <div class="sax-checkout-progress-step {{ $initialStep >= 1 ? 'is-current' : '' }}">1</div>
            <div class="sax-checkout-progress-step {{ $initialStep >= 2 ? 'is-current' : '' }}">2</div>
            <div class="sax-checkout-progress-step {{ $initialStep >= 3 ? 'is-current' : '' }}">3</div>
            <div class="sax-checkout-progress-step {{ $initialStep >= 4 ? 'is-current' : '' }}">4</div>
        </div>

    <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" id="currentStep" value="{{ $initialStep }}">

        <x-checkout.step1-cart :cart="$cart" :resumo="$resumo" />
        <x-checkout.step2-user />
        <x-checkout.step3-shipping />
        <x-checkout.step4-payment :cart="$cart" :resumo="$resumo" :payment-methods="$paymentMethods" :policies="$policies" />
    </form>
</div>
</div>

@endsection
