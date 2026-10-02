<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <x-head-master />
</head>

@php
    $headerLayoutService = app(\App\Services\StorefrontLayoutService::class);
    $adminHeaderLayout = $headerLayoutService->headerLayout();
    $adminBrandName = $adminHeaderLayout === 'vista' ? 'VISTA&CO' : 'SAX';
@endphp
<body class="sax-admin-body header-layout-{{ $adminHeaderLayout }}">
    <div class="sax-admin-shell">
        <aside class="sax-admin-rail d-none d-lg-flex">
            <a href="{{ route('admin.index') }}" class="sax-admin-rail__brand" aria-label="Página inicial do painel">
                <span>
                    <strong>Painel</strong>
                    <small>Administração</small>
                </span>
            </a>

            <div class="sax-admin-rail__navigation">
                @include('admin.menu-lateral', ['menuInstance' => 'desktop'])
            </div>

            <div class="sax-admin-rail__account">
                <span class="sax-admin-rail__avatar"><i class="fa-solid fa-user-shield" aria-hidden="true"></i></span>
                <span>
                    <strong>{{ auth()->user()?->name }}</strong>
                    <small>{{ auth()->user()?->isMasterAdmin() ? 'Admin Master' : 'Admin / Editor' }}</small>
                </span>
            </div>
        </aside>

        <div class="sax-admin-workspace">
            <x-header-admin />

            <main class="sax-admin-layout">
                <div class="container-fluid sax-admin-main-container">
                    <section class="sax-admin-content">
                        @yield('content')
                    </section>
                </div>
            </main>

            <footer class="sax-admin-footer">
                <div class="container-fluid sax-admin-main-container d-sm-flex justify-content-between align-items-center gap-2">
                    <span>&copy; {{ now()->year }} {{ $adminHeaderLayout === 'vista' ? 'Vista & Co' : 'SAX' }} E-commerce</span>
                    <span>Painel administrativo</span>
                </div>
            </footer>
        </div>
    </div>

    <button id="backToTop" class="sax-back-to-top shadow-lg border-0" title="Voltar ao topo">
        <i class="fa fa-chevron-up"></i>
    </button>

    {{-- Drawer Mobile --}}
    <div class="drawer-overlay" id="adminDrawerOverlay"></div>
    <div class="drawer-mobile" id="adminDrawerMobile" role="dialog" aria-modal="true" aria-label="Menu administrativo" aria-hidden="true">
        <div class="admin-drawer-header drawer-header d-flex justify-content-between align-items-center">
            <div class="drawer-header-copy">
                <span>Administração</span>
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
