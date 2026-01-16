@extends('layout.checkout')

@section('content')
<div class="container mt-5">
    <form id="checkoutForm" method="POST" action="{{ route('checkout.store') }}" enctype="multipart/form-data">
        @csrf
        <input type="hidden" name="step" id="currentStep">

        {{-- STEP 1 --}}
        <x-checkout.step1-cart :cart="$cart" />

        {{-- STEP 2 --}}
        <x-checkout.step2-user />

        {{-- STEP 3 --}}
        <x-checkout.step3-shipping />

        {{-- STEP 4 --}}
        <x-checkout.step4-payment :cart="$cart" :payment-methods="$paymentMethods" />
    </form>
</div>

@endsection

<style>
    /* 1. CONFIGURAÇÕES GLOBAIS DE CHECKOUT */
    .sax-checkout-box {
        background: #fff;
        border: 1px solid #eee;
        padding: 30px;
        margin-bottom: 20px;
        transition: all 0.3s ease;
    }

    .sax-step-title {
        font-size: 1.1rem;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 2px;
        margin-bottom: 25px;
        display: flex;
        align-items: center;
        color: #000;
    }

    .step-number {
        background: #000;
        color: #fff;
        width: 28px;
        height: 28px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 0.75rem;
        margin-right: 15px;
        font-weight: 400;
    }

    /* 2. FORMULÁRIOS E INPUTS MINIMALISTAS (STEPS 2 & 3) */
    .sax-label {
        font-size: 11px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        margin-bottom: 8px;
        display: block;
        color: #333;
    }

    .sax-form-control {
        border: none;
        border-bottom: 1px solid #ddd;
        border-radius: 0;
        padding: 10px 0;
        font-size: 0.95rem;
        width: 100%;
        outline: none;
        background: transparent;
        transition: border-color 0.3s;
    }

    .sax-form-control:focus {
        border-bottom: 1px solid #000;
    }

    /* 3. LISTAGEM DE ITENS (STEPS 1 & 4) */
    .sax-cart-img-wrapper {
        width: 80px;
        height: 100px;
        background: #f5f5f5;
        padding: 5px;
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .sax-cart-img-wrapper img { 
        max-width: 100%; 
        max-height: 100%; 
        object-fit: contain; 
    }

    .sax-item-brand { font-size: 0.65rem; font-weight: 700; color: #888; text-transform: uppercase; letter-spacing: 1px; }
    .sax-item-name { font-size: 0.85rem; margin: 2px 0; color: #000; font-weight: 500; }
    .sax-item-meta { font-size: 0.75rem; color: #666; }
    .sax-item-subtotal { font-weight: 700; font-size: 0.95rem; color: #000; }

    /* 4. BOTÕES DE NAVEGAÇÃO */
    .sax-btn-next, .sax-btn-finish {
        background: #000;
        color: #fff !important;
        border: none;
        padding: 15px 40px;
        font-size: 0.75rem;
        font-weight: 700;
        letter-spacing: 2px;
        text-transform: uppercase;
        transition: all 0.3s ease;
        text-decoration: none;
    }

    .sax-btn-next:hover, .sax-btn-finish:hover { 
        background: #333; 
        transform: translateY(-2px);
    }

    .sax-btn-prev {
        background: transparent;
        border: 1px solid #ddd;
        color: #888;
        padding: 15px 30px;
        font-size: 0.75rem;
        text-transform: uppercase;
        font-weight: 700;
        letter-spacing: 1px;
        transition: all 0.3s;
    }
    
    .sax-btn-prev:hover { 
        border-color: #000; 
        color: #000; 
    }

    /* 5. MÉTODO DE ENTREGA (STEP 3) - CARDS */
    .sax-shipping-grid {
        display: grid;
        grid-template-columns: repeat(3, 1fr);
        gap: 12px;
    }

    .sax-method-card {
        border: 1px solid #eee;
        padding: 20px 10px;
        text-align: center;
        cursor: pointer;
        transition: all 0.3s ease;
        background: #fff;
    }

    .sax-method-card input { display: none; }

    .sax-method-card .method-icon {
        font-size: 1.3rem;
        margin-bottom: 8px;
        color: #aaa;
    }

    .sax-method-card .method-text {
        font-size: 10px;
        font-weight: 700;
        text-transform: uppercase;
        letter-spacing: 1px;
        color: #888;
    }

    .sax-method-card.active {
        border: 2px solid #000;
    }

    .sax-method-card.active .method-icon,
    .sax-method-card.active .method-text {
        color: #000;
    }

    .sax-address-preview {
        background: #f9f9f9;
        border-left: 3px solid #000;
        font-size: 0.9rem;
    }

    .sax-map-container {
        border: 1px solid #eee;
        filter: grayscale(1);
    }

    /* 6. PAGAMENTO (STEP 4) */
    .sax-payment-method {
        border: 2px solid #000;
        background: transparent;
        padding: 20px 40px;
        font-weight: 700;
        letter-spacing: 1px;
        text-transform: uppercase;
        font-size: 0.8rem;
        transition: 0.3s;
    }

    .sax-payment-notice { 
        font-size: 0.8rem; 
        color: #777; 
        max-width: 550px; 
        margin: 20px auto; 
        line-height: 1.5;
    }

    .sax-summary-total {
        font-size: 0.9rem;
    }

    .total-row { 
        font-size: 1.2rem; 
        color: #000; 
        padding-top: 15px; 
        letter-spacing: 1px;
    }

    /* RESPONSIVIDADE */
    @media (max-width: 768px) {
        .sax-shipping-grid { grid-template-columns: 1fr; }
        .sax-checkout-box { padding: 20px; }
        .sax-btn-next, .sax-btn-prev { width: 100%; margin-bottom: 10px; }
        .d-flex.justify-content-between { flex-direction: column; }
    }
    /* Garante que o clique no ícone ou texto seja repassado para o label/input */
    .sax-method-card * {
        pointer-events: none;
    }
    
    /* Reabilita pointer events para o próprio card e selects */
    .sax-method-card, .sax-form-control, select, input, textarea {
        pointer-events: auto !important;
    }
</style>