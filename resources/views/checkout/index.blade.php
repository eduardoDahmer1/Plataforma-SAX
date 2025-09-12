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
