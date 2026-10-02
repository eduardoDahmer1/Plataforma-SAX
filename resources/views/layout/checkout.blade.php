<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

@php
    $commerceContext = match (true) {
        request()->routeIs('cart.*') => ['Carrinho', 'Revise seus produtos antes de continuar'],
        request()->routeIs('checkout.index', 'checkout.store') => ['Checkout', 'Finalize sua compra com segurança'],
        request()->routeIs('checkout.success', 'bancard.v2.success') => ['Pedido confirmado', 'Acompanhe os detalhes da sua compra'],
        request()->routeIs('checkout.error', 'bancard.v2.error') => ['Pagamento', 'Revise os dados e tente novamente'],
        request()->routeIs('checkout.rendix.pix*') => ['Pagamento via PIX', 'Conclua o pagamento no seu banco'],
        request()->routeIs('checkout.deposito*') => ['Pagamento por depósito', 'Consulte os dados do seu pedido'],
        request()->routeIs('checkout.bancard.v2*', 'bancard.v2.*') => ['Pagamento seguro', 'Ambiente protegido Bancard'],
        default => ['Compra segura', 'Carrinho e pagamento'],
    };
@endphp

<body class="sax-storefront sax-checkout-page sax-commerce-body">
    <x-marketing-body-start />

    <header class="sax-commerce-topbar">
        <div class="sax-commerce-frame sax-commerce-topbar__inner">
            <div class="sax-commerce-context">
                <a href="{{ route('home') }}" class="sax-commerce-home" aria-label="Ir para a loja">
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
                    Ambiente seguro
                </span>
                @auth
                    <a href="{{ route('user.dashboard') }}" class="sax-commerce-action" title="Minha conta">
                        <i class="fa-regular fa-user" aria-hidden="true"></i>
                        <span class="d-none d-sm-inline">Minha conta</span>
                    </a>
                @endauth
                <a href="{{ route('home') }}" class="sax-commerce-action sax-commerce-action--primary" title="Ir para a loja">
                    <i class="fa-solid fa-store" aria-hidden="true"></i>
                    <span class="d-none d-sm-inline">Ir para a loja</span>
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
            <span><i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i> Compra protegida</span>
            <span>&copy; {{ now()->year }} SAX E-commerce</span>
        </div>
    </footer>

    <x-scripts-master />
</body>

</html>
