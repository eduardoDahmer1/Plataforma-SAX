<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

@php
    $recentAbandonedCarts = auth()->user()->abandonedCarts()->latest('abandoned_at')->limit(3)->get();

    $userPageTitle = match (true) {
        request()->routeIs('user.dashboard') => __('messages.minha_conta'),
        request()->routeIs('user.profile.*') => __('messages.actualizar_registro'),
        request()->routeIs('user.addresses.*') => 'Meus endereços',
        request()->routeIs('user.password.*') => __('messages.menu_seguranca'),
        request()->routeIs('user.orders.show') => 'Detalhes do pedido',
        request()->routeIs('user.orders') => __('messages.historico_pedidos_titulo'),
        request()->routeIs('user.cupons') => __('messages.cupon_meus_cupons_titulo'),
        request()->routeIs('user.preferences') => __('messages.wishlist_titulo'),
        request()->routeIs('user.abandoned-carts.show') => __('messages.user_view_cart'),
        request()->routeIs('user.abandoned-carts.*') => __('messages.user_abandoned_carts'),
        request()->routeIs('receipts.*') => __('messages.ver_recibo'),
        default => __('messages.minha_conta'),
    };
@endphp

<body class="sax-storefront sax-user-area sax-account-body">
    <x-marketing-body-start />

    <div class="sax-account-shell">
        <aside class="sax-account-rail d-none d-lg-flex">
            <a href="{{ route('user.dashboard') }}" class="sax-account-rail__brand">
                <strong>{{ __('messages.minha_conta') }}</strong>
                <small>Área do cliente</small>
            </a>

            <div class="sax-account-rail__navigation">
                <x-users.menu :showModal="false" :recentAbandonedCarts="$recentAbandonedCarts" />
            </div>

            <div class="sax-account-rail__account">
                <span class="sax-account-rail__avatar">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?: 'U', 0, 1)) }}</span>
                <span>
                    <strong>{{ auth()->user()?->name }}</strong>
                    <small>{{ auth()->user()?->email }}</small>
                </span>
            </div>
        </aside>

        <div class="sax-account-workspace">
            <header class="sax-account-topbar">
                <div class="sax-account-container sax-account-topbar__inner">
                    <div class="sax-account-topbar__context">
                        <button class="sax-account-menu-toggle d-lg-none" type="button" data-bs-toggle="offcanvas"
                            data-bs-target="#userMenu" aria-controls="userMenu" aria-label="Abrir menu da conta">
                            <i class="fa-solid fa-bars" aria-hidden="true"></i>
                        </button>
                        <span class="sax-account-topbar__title">{{ $userPageTitle }}</span>
                    </div>

                    <div class="sax-account-topbar__actions">
                        <div class="d-none d-xl-flex sax-account-locale">
                            <x-locale-currency-selector variant="account" />
                        </div>
                        @include('users.notifications-menu')
                        <a href="{{ route('home') }}" class="sax-account-action" title="Ir para a loja">
                            <i class="fa-solid fa-store" aria-hidden="true"></i>
                            <span>Ir para a loja</span>
                        </a>
                        <a href="{{ route('cart.view') }}" class="sax-account-action sax-account-action--cart" title="Abrir carrinho">
                            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
                            <span>Carrinho</span>
                        </a>
                    </div>
                </div>
            </header>

            <main class="sax-account-main">
                <div class="sax-account-container">
                    @include('components.catalog-integration-notice')
                    @include('components.store-control-notice')

                    <section class="sax-user-content">
                        @yield('content')
                    </section>
                </div>
            </main>

            <footer class="sax-account-footer">
                <div class="sax-account-container d-sm-flex justify-content-between align-items-center gap-2">
                    <span>&copy; {{ now()->year }} SAX E-commerce</span>
                    <a href="{{ route('home') }}">Continuar comprando</a>
                </div>
            </footer>
        </div>
    </div>

    <div class="offcanvas offcanvas-start sax-user-menu-drawer" tabindex="-1" id="userMenu" aria-labelledby="userMenuLabel">
        <div class="offcanvas-header drawer-header">
            <div class="drawer-header-copy">
                <span>Área do cliente</span>
                <strong id="userMenuLabel">{{ __('messages.minha_conta') }}</strong>
            </div>
            <button type="button" class="btn-close-drawer" data-bs-dismiss="offcanvas" aria-label="Fechar">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="offcanvas-body p-0 sax-user-drawer-body">
            <div class="drawer-auth-section sax-user-drawer-account">
                <a href="{{ route('user.dashboard') }}" class="drawer-user-summary">
                    <span class="user-avatar">{{ mb_strtoupper(mb_substr(auth()->user()?->name ?: 'U', 0, 1)) }}</span>
                    <span class="drawer-user-copy">
                        <small>{{ __('messages.ola') }}</small>
                        <strong>{{ auth()->user()?->name }}</strong>
                        <span>{{ auth()->user()?->email }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-right drawer-user-arrow" aria-hidden="true"></i>
                </a>
            </div>
            <div class="sax-user-drawer-locale">
                <x-locale-currency-selector variant="mobile" />
            </div>
            <x-users.menu :showModal="false" :recentAbandonedCarts="$recentAbandonedCarts" />
        </div>
    </div>

    <x-users.menu :showNavigation="false" />

    <button id="backToTop" class="sax-account-back-to-top" title="Voltar ao topo" aria-label="Voltar ao topo">
        <i class="fa fa-arrow-up" aria-hidden="true"></i>
    </button>

    <x-scripts-master />
</body>

</html>
