<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

<body>
    {{-- Header --}}
    @include('components.header')

<main class="sax-admin-layout py-4">
    <div class="container-fluid px-md-5">
        {{-- Cabeçalho do Painel --}}
        <div class="sax-admin-header d-flex justify-content-between align-items-center mb-4">
            
            <div class="d-flex gap-2">
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

    <x-scripts-master />


</body>

</html>
