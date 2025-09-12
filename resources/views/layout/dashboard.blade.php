<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SAX - E-commerce de Luxo</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">
    <link href="{{ asset('css/checkout.css') }}" rel="stylesheet">

    <!-- Bootstrap 5.3.3 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
</head>

<body>

    {{-- Header --}}
    @include('components.header')

    <main class="py-4 container">
        <div class="row">
            <!-- Botão para abrir menu no mobile -->
            <div class="d-md-none mb-3">
                <button class="btn btn-outline-dark w-100" type="button" data-bs-toggle="offcanvas"
                    data-bs-target="#userMenu" aria-controls="userMenu">
                    <i class="fa fa-bars me-2"></i> Menu do Usuário
                </button>
            </div>

            <!-- Menu lateral fixo no desktop -->
            <div class="col-md-3 mb-4 d-none d-md-block">
                @include('users.components.menu')
            </div>

            <!-- Conteúdo principal -->
            <div class="col-md-9">
                @yield('content')
            </div>
        </div>
    </main>

    <!-- Menu lateral Offcanvas (mobile) -->
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

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn btn-primary position-fixed"
        style="bottom:30px; right:1em; display:none; z-index:1050;width: 3em;">
        <i class="fa fa-arrow-up"></i>
    </button>

    {{-- Footer --}}
    @include('components.footer')

    {{-- Scripts --}}
    @include('components.scripts')

    <!-- Bootstrap Bundle (com JS do Offcanvas) -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ENjdO4Dr2bkBIFxQpeoQbS0n0OzO2zCk41ZJL6hFQ6nG1q3KfIFJSkGrydg7Y+Y+" crossorigin="anonymous"></script>

    <script>
        // Mostrar/ocultar botão voltar ao topo
        const backToTop = document.getElementById("backToTop");
        window.addEventListener("scroll", () => {
            backToTop.style.display = window.scrollY > 300 ? "block" : "none";
        });
        backToTop.addEventListener("click", () => {
            window.scrollTo({ top: 0, behavior: "smooth" });
        });
    </script>

</body>
</html>
