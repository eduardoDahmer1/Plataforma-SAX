<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta tags essenciais -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SAX - Painel Administrativo</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- CSS -->
    <link href="{{ asset('css/app.css') }}" rel="stylesheet">

    <!-- Bootstrap -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">

    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">

</head>

<body>
    {{-- Header --}}
    @include('components.header')

    <main class="py-4">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center mb-3 px-3">
                <h2 class="fw-bold">Painel Administrativo</h2>
                <button class="btn-menu-mobile d-md-none" id="openDrawer">
                    <i class="fa fa-bars"></i> Menu
                </button>
            </div>

            <div class="row">
                {{-- Menu Desktop --}}
                <div class="col-md-3 d-none d-md-block admin-sidebar">
                    @include('admin.menu-lateral')
                </div>

                {{-- Conteúdo principal --}}
                <div class="col-md-9 px-3">
                    <div class="card shadow-sm">
                        <div class="card-header bg-dark text-white">
                            <strong>Quadro Principal</strong>
                        </div>
                        <div class="card-body">
                            @yield('content')
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <!-- Botão Voltar ao Topo -->
    <button id="backToTop" class="btn position-fixed" style="bottom:30px; right:1em; display:none; width:3em;">
        <i class="fa fa-arrow-up"></i>
    </button>

    {{-- Drawer Mobile --}}
    <div class="drawer-overlay" id="drawerOverlay"></div>
    <div class="drawer-mobile" id="drawerMobile">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Menu</h5>
            <button class="btn text-black" id="closeDrawer">&times;</button>
        </div>
        @include('admin.menu-lateral')
    </div>

    {{-- Footer --}}
    @include('components.footer')

    <!-- JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Voltar ao topo
        const backToTop = document.getElementById("backToTop");
        window.addEventListener("scroll", () => {
            backToTop.style.display = window.scrollY > 200 ? "block" : "none";
        });
        backToTop.addEventListener("click", () => {
            window.scrollTo({
                top: 0,
                behavior: "smooth"
            });
        });

        // Drawer Mobile
        const openDrawer = document.getElementById("openDrawer");
        const closeDrawer = document.getElementById("closeDrawer");
        const drawer = document.getElementById("drawerMobile");
        const overlay = document.getElementById("drawerOverlay");

        openDrawer.addEventListener("click", () => {
            drawer.classList.add("open");
            overlay.classList.add("show");
        });
        closeDrawer.addEventListener("click", () => {
            drawer.classList.remove("open");
            overlay.classList.remove("show");
        });
        overlay.addEventListener("click", () => {
            drawer.classList.remove("open");
            overlay.classList.remove("show");
        });
    </script>
</body>

</html>
