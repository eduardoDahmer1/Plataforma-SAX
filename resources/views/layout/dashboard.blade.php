<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body class="sax-storefront sax-user-area">
    <x-marketing-body-start />

    @include('components.header')

    <main class="sax-user-shell py-4 container">
        <div class="row g-lg-4">
            <div class="d-md-none mb-3 sax-user-mobile-menu-trigger">
                <button class="btn btn-outline-dark w-100" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#userMenu" aria-controls="userMenu">
                    <span><i class="fa fa-user me-2"></i> {{ __('messages.minha_conta') }}</span>
                    <i class="fa fa-chevron-right" aria-hidden="true"></i>
                </button>
            </div>

            <div class="col-md-3 mb-4 d-none d-md-block">
                <x-users.menu />
            </div>

            <div class="col-md-9 sax-user-content">
                @yield('content')
            </div>
        </div>
    </main>

    <div class="offcanvas offcanvas-start sax-user-menu-drawer" tabindex="-1" id="userMenu" aria-labelledby="userMenuLabel">
        <div class="offcanvas-header drawer-header">
            <div class="drawer-header-copy">
                <span>SAX</span>
                <strong id="userMenuLabel">{{ __('messages.minha_conta') }}</strong>
            </div>
            <button type="button" class="btn-close-drawer" data-bs-dismiss="offcanvas" aria-label="Fechar">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="offcanvas-body p-0 sax-user-drawer-body">
            <div class="drawer-auth-section sax-user-drawer-account">
                <a href="{{ route('user.dashboard') }}" class="drawer-user-summary">
                    <span class="user-avatar"><i class="fa-solid fa-user"></i></span>
                    <span class="drawer-user-copy">
                        <small>{{ __('messages.ola') }}</small>
                        <strong>{{ auth()->user()?->name }}</strong>
                        <span>{{ __('messages.minha_conta') }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-right drawer-user-arrow" aria-hidden="true"></i>
                </a>
            </div>
            <x-users.menu />
        </div>
    </div>

    <button id="backToTop" class="btn btn-primary position-fixed">
        <i class="fa fa-arrow-up"></i>
    </button>

    @include('components.footer')

    <x-scripts-master />

    {{-- JS back-to-top ya existe en app-custom.js --}}
</body>

</html>
