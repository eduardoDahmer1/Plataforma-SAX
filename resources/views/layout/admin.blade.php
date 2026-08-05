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
            <div class="sax-admin-mobile-entry d-flex align-items-center gap-2 w-100">
                <button class="sax-btn-mobile" id="openAdminDrawer" type="button" aria-label="Abrir menu administrativo" aria-controls="adminDrawerMobile" aria-expanded="false">
                    <i class="fa fa-bars"></i>
                </button>
                <span class="sax-admin-mobile-entry__copy">
                    <small>SAX</small>
                    <strong>Painel administrativo</strong>
                </span>
                <i class="fa-solid fa-chevron-right sax-admin-mobile-entry__arrow" aria-hidden="true"></i>
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
        <div class="admin-drawer-header drawer-header d-flex justify-content-between align-items-center">
            <div class="drawer-header-copy">
                <span>SAX</span>
                <strong>Menu</strong>
            </div>
            <button class="btn-close-drawer" id="closeAdminDrawer" type="button" aria-label="Fechar menu">
                <i class="fa-solid fa-xmark" aria-hidden="true"></i>
            </button>
        </div>
        <div class="admin-drawer-body">
            <div class="drawer-auth-section admin-drawer-account">
                <a href="{{ route('admin.index') }}" class="drawer-user-summary">
                    <span class="user-avatar"><i class="fa-solid fa-user-shield"></i></span>
                    <span class="drawer-user-copy">
                        <small>{{ __('messages.ola') }}</small>
                        <strong>{{ auth()->user()?->name }}</strong>
                        <span>{{ auth()->user()?->isMasterAdmin() ? 'Admin Master' : 'Admin / Editor' }}</span>
                    </span>
                    <i class="fa-solid fa-chevron-right drawer-user-arrow" aria-hidden="true"></i>
                </a>
            </div>
            @include('admin.menu-lateral', ['menuInstance' => 'mobile'])
        </div>
    </div>

    <footer class="sax-admin-footer">
        <div class="container-fluid px-3 px-lg-4 px-xxl-5 d-sm-flex justify-content-between align-items-center gap-2">
            <span>&copy; {{ now()->year }} SAX E-commerce</span>
            <span>Painel administrativo</span>
        </div>
    </footer>

    {{-- Modal global de confirmación (forms con data-confirm) --}}
    <x-admin.confirm-modal />

    @if(session('admin_access_restricted_area'))
        <div class="modal fade" id="adminAccessRestrictedModal" tabindex="-1" aria-labelledby="adminAccessRestrictedTitle" aria-hidden="true" data-bs-backdrop="static">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content border-0 shadow-lg rounded-3 overflow-hidden">
                    <div class="modal-body text-center px-4 px-md-5 py-5">
                        <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-warning-subtle text-warning-emphasis mb-3" style="width: 64px; height: 64px;">
                            <i class="fa-solid fa-lock fs-3"></i>
                        </div>
                        <h4 class="fw-bold text-dark mb-3" id="adminAccessRestrictedTitle">
                            {{ __('messages.admin_access_restricted_title') }}
                        </h4>
                        <p class="text-muted mb-4">
                            {{ __('messages.admin_access_restricted_message', [
                                'area' => __('messages.' . session('admin_access_restricted_area')),
                            ]) }}
                        </p>
                        <button type="button" class="btn btn-dark rounded-2 px-4 py-2 fw-bold" data-bs-dismiss="modal">
                            <i class="fa-solid fa-arrow-left me-2"></i>
                            {{ __('messages.admin_back_to_dashboard') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif

    <x-scripts-master />

    @if(session('admin_access_restricted_area'))
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                const modalElement = document.getElementById('adminAccessRestrictedModal');
                if (modalElement && window.bootstrap) {
                    bootstrap.Modal.getOrCreateInstance(modalElement).show();
                }
            });
        </script>
    @endif

</body>

</html>
