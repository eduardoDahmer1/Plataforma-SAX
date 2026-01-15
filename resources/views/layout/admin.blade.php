<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <!-- Meta tags essenciais -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>SAX - Painel Administrativo</title>
    <link rel="icon" href="{{ asset('favicon.ico') }}" type="image/x-icon">

    <!-- Preconnect: otimiza carregamento de recursos externos -->
    <link rel="preconnect" href="https://fonts.googleapis.com" crossorigin>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link rel="preconnect" href="https://cdn.jsdelivr.net" crossorigin>
    <link rel="preconnect" href="https://cdnjs.cloudflare.com" crossorigin>
    <link rel="preconnect" href="https://unpkg.com" crossorigin>

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

<main class="sax-admin-layout py-4">
    <div class="container-fluid px-md-5">
        {{-- Cabeçalho do Painel --}}
        <div class="sax-admin-header d-flex justify-content-between align-items-center mb-4">
            <div>
                <h2 class="fw-bold sax-admin-title m-0 text-uppercase letter-spacing-2">Dashboard Admin</h2>
                <span class="text-muted x-small">Sistema de Gerenciamento Interno</span>
            </div>
            
            <div class="d-flex gap-2">
                <a href="{{ route('home') }}" class="btn btn-outline-dark btn-sm rounded-pill px-3">
                    <i class="fa fa-external-link-alt me-1"></i> Ver Site
                </a>
                <button class="sax-btn-mobile d-md-none" id="openDrawer">
                    <i class="fa fa-bars"></i>
                </button>
            </div>
        </div>

        <div class="row g-4">
            {{-- Menu Lateral (Desktop) --}}
            <aside class="col-md-3 d-none d-md-block">
                <div class="sax-sidebar-card shadow-sm">
                    @include('admin.menu-lateral')
                </div>
            </aside>

            {{-- Área de Conteúdo --}}
            <main class="col-md-9">
                <div class="sax-main-card shadow-sm border-0">
                    <div class="sax-card-header d-flex align-items-center justify-content-between">
                        <div class="d-flex align-items-center">
                            <div class="header-indicator me-3"></div>
                            <strong class="text-uppercase small letter-spacing-1">Quadro de Trabalho</strong>
                        </div>
                        <i class="fas fa-thumbtack text-muted opacity-50"></i>
                    </div>
                    <div class="sax-card-body">
                        @yield('content')
                    </div>
                </div>
            </main>
        </div>
    </div>
</main>

<button id="backToTop" class="sax-back-to-top shadow-lg border-0" title="Voltar ao topo">
    <i class="fa fa-chevron-up"></i>
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
