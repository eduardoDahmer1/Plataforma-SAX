<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>

    @include('components.header')

    <main class="py-4 container">
        <div class="row">
            <div class="d-md-none mb-3">
                <button class="btn btn-outline-dark w-100" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#userMenu" aria-controls="userMenu">
                    <i class="fa fa-bars me-2"></i> Menu do Usuário
                </button>
            </div>

            <div class="col-md-3 mb-4 d-none d-md-block">
                @include('users.components.menu')
            </div>

            <div class="col-md-9">
                @yield('content')
            </div>
        </div>
    </main>

    <div class="offcanvas offcanvas-start" tabindex="-1" id="userMenu" aria-labelledby="userMenuLabel">
        <div class="offcanvas-header bg-dark text-white">
            <h5 class="offcanvas-title" id="userMenuLabel"><i class="fa fa-user me-2"></i> Minha Conta</h5>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="offcanvas"
                aria-label="Fechar"></button>
        </div>
        <div class="offcanvas-body p-0">
            @include('users.components.menu')
        </div>
    </div>

    <button id="backToTop" class="btn btn-primary position-fixed"
        style="bottom:30px; right:1em; display:none; z-index:1050;width: 3em;">
        <i class="fa fa-arrow-up"></i>
    </button>

    @include('components.footer')

    <x-scripts-master />

    {{-- JS back-to-top ya existe en app-custom.js --}}
</body>

</html>
