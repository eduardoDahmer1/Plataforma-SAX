<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

@php
    $commerceContext = match (true) {
        request()->routeIs('cart.*') => [__('messages.commerce_cart_title'), __('messages.commerce_cart_subtitle')],
        request()->routeIs('checkout.index', 'checkout.store') => [__('messages.commerce_checkout_title'), __('messages.commerce_checkout_subtitle')],
        request()->routeIs('checkout.success', 'bancard.v2.success') => [__('messages.commerce_order_confirmed_title'), __('messages.commerce_order_confirmed_subtitle')],
        request()->routeIs('checkout.error', 'bancard.v2.error') => [__('messages.commerce_payment_title'), __('messages.commerce_payment_review_subtitle')],
        request()->routeIs('checkout.rendix.pix*') => [__('messages.commerce_pix_payment_title'), __('messages.commerce_pix_payment_subtitle')],
        request()->routeIs('checkout.deposito*') => [__('messages.commerce_deposit_payment_title'), __('messages.commerce_deposit_payment_subtitle')],
        request()->routeIs('checkout.bancard.v2*', 'bancard.v2.*') => [__('messages.commerce_bancard_payment_title'), __('messages.commerce_bancard_payment_subtitle')],
        default => [__('messages.commerce_secure_purchase_title'), __('messages.commerce_secure_purchase_subtitle')],
    };
@endphp

<body class="sax-storefront sax-checkout-page sax-commerce-body">
    <x-marketing-body-start />

    <header class="sax-commerce-topbar">
        <div class="sax-commerce-frame sax-commerce-topbar__inner">
            <div class="sax-commerce-context">
                <a href="{{ route('home') }}" class="sax-commerce-home" aria-label="{{ __('messages.ir_para_loja') }}">
                    <i class="fa-solid fa-arrow-left" aria-hidden="true"></i>
                </a>
                <div>
                    <strong>{{ $commerceContext[0] }}</strong>
                    <span>{{ $commerceContext[1] }}</span>
                </div>
            </div>

            <div class="sax-commerce-actions">
                <div class="d-none d-lg-flex sax-commerce-locale">
                    <x-locale-currency-selector variant="account" />
                </div>
                <span class="sax-commerce-secure d-none d-md-inline-flex">
                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                    {{ __('messages.commerce_secure_environment') }}
                </span>
                @auth
                    <a href="{{ route('user.dashboard') }}" class="sax-commerce-action" title="{{ __('messages.minha_conta') }}">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">{{ __('messages.minha_conta') }}</span>
                    </a>
                @endauth
                <a href="{{ route('home') }}" class="sax-commerce-action sax-commerce-action--primary" title="{{ __('messages.ir_para_loja') }}">
                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline">{{ __('messages.ir_para_loja') }}</span>
                </a>
            </div>
        </div>
    </header>

    <main class="sax-checkout-main">
        <div class="sax-commerce-frame">
            @include('components.catalog-integration-notice')
            @include('components.store-control-notice')
            @yield('content')
        </div>
    </main>

    <footer class="sax-commerce-footer">
        <div class="sax-commerce-frame sax-commerce-footer__inner">
            <span><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i> {{ __('messages.commerce_protected_purchase') }}</span>
            <span>&copy; {{ now()->year }} SAX E-commerce</span>
        </div>
    </footer>

    <x-scripts-master />
</body>

</html>
