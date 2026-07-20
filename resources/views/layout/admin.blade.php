<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body class="sax-admin-body">
    {{-- Header --}}
    @include('components.header')

<main class="sax-admin-layout py-3 py-lg-4">
    <div class="container-fluid px-3 px-lg-4 px-xxl-5">
        {{-- Cabeçalho do Painel --}}
        <div class="sax-admin-header d-lg-none d-flex justify-content-between align-items-center mb-3">
            <div class="d-flex align-items-center gap-2">
                <button class="sax-btn-mobile" id="openAdminDrawer" type="button" aria-label="Abrir menu administrativo" aria-controls="adminDrawerMobile" aria-expanded="false">
                    <i class="fa fa-bars"></i>
                </button>
                <span class="fw-semibold">Painel administrativo</span>
            </div>
        </div>

        <div class="row g-4">
            {{-- Menu Lateral (Desktop) --}}
            <aside class="col-lg-3 col-xxl-2 d-none d-lg-block">
                <div class="sax-sidebar-card shadow-sm">
                    @include('admin.menu-lateral', ['menuInstance' => 'desktop'])
                </div>
            </aside>

            {{-- Área de Conteúdo --}}
            <section class="col-12 col-lg-9 col-xxl-10 sax-admin-content">
                @yield('content')
            </section>
        </div>
    </div>
</main>

<button id="backToTop" class="sax-back-to-top shadow-lg border-0" title="Voltar ao topo">
    <i class="fa fa-chevron-up"></i>
</button>

    {{-- Drawer Mobile --}}
    <div class="drawer-overlay" id="adminDrawerOverlay"></div>
    <div class="drawer-mobile" id="adminDrawerMobile" role="dialog" aria-modal="true" aria-label="Menu administrativo" aria-hidden="true">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5>Menu</h5>
            <button class="btn text-black" id="closeAdminDrawer">&times;</button>
        </div>
        @include('admin.menu-lateral', ['menuInstance' => 'mobile'])
    </div>

    <footer class="sax-admin-footer">
        <div class="container-fluid px-3 px-lg-4 px-xxl-5 d-sm-flex justify-content-between align-items-center gap-2">
            <span>&copy; {{ now()->year }} SAX E-commerce</span>
            <span>Painel administrativo</span>
        </div>
    </footer>

    {{-- Modal global de confirmación (forms con data-confirm) --}}
    <x-admin.confirm-modal />

    <x-scripts-master />


</body>

</html>
