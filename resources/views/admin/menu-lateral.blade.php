@php
    $menuSuffix = isset($menuInstance) ? '-' . $menuInstance : '';
    $isMasterAdmin = auth()->user()?->isMasterAdmin() ?? false;
    $catalogosOpen = request()->routeIs('admin.products.*', 'admin.brands.*', 'admin.subcategories.*', 'admin.categorias-filhas.*')
        || ($isMasterAdmin && request()->routeIs('admin.categories.*'));
    $vendasOpen = request()->routeIs('admin.orders.*', 'admin.clients.*', 'admin.abandoned-carts.*');
    $conteudosOpen = request()->routeIs('admin.blogs.*', 'admin.contatos.*', 'admin.contacts.*', 'admin.emails.*', 'admin.email-templates.*', 'admin.contact-guide.*', 'admin.policies.*');
    $institucionaisOpen = request()->routeIs(
        'admin.palace.*',
        'admin.bridal.*',
        'admin.cafe_bistro.*',
        'admin.institucional.*'
    );
    $sistemaOpen = request()->routeIs(
        'admin.banners.*',
        'admin.sections_home.*',
        'admin.currencies.*',
        'admin.payments.*',
        'admin.cupons.*',
        'admin.activate.*',
        'admin.languages.*',
        'admin.marketing.*',
        'admin.theme-settings.*',
        'admin.store-controls.*',
        'admin.ai-settings.*',
        'admin.whatsapp.*',
        'admin.dhl.*'
    );
@endphp

<div class="sax-admin-sidebar">
    <nav class="sax-nav-container">
        <p class="sax-sidebar-heading">Gestao</p>

        @if($isMasterAdmin)
        <a href="{{ route('admin.index') }}" class="sax-nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <div class="nav-icon-box bg-soft-primary"><i class="fa-solid fa-chart-line"></i></div>
            <span class="nav-text">Visão geral</span>
        </a>

        <a href="{{ route('admin.notifications.index') }}" class="sax-nav-item {{ request()->routeIs('admin.notifications.*') ? 'active' : '' }}">
            <div class="nav-icon-box bg-soft-info"><i class="fa-regular fa-bell"></i></div>
            <span class="nav-text">{{ __('messages.notifications_menu') }}</span>
        </a>
        @endif

        {{-- Catálogos --}}
        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $catalogosOpen ? '' : 'collapsed' }} {{ $catalogosOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuCatalogos{{ $menuSuffix }}" aria-expanded="{{ $catalogosOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-success">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="nav-text">{{ __('messages.menu_catalogos') }}</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $catalogosOpen ? 'show' : '' }}" id="menuCatalogos{{ $menuSuffix }}">
                <a href="{{ route('admin.products.index') }}" class="submenu-link {{ request()->routeIs('admin.products.*') && !request()->routeIs('admin.products.outlet.*', 'admin.products.ai-batches.*') ? 'active' : '' }}"><i class="fa-solid fa-box"></i> {{ __('messages.menu_produtos') }}</a>
                <a href="{{ route('admin.products.ai-batches.index') }}" class="submenu-link {{ request()->routeIs('admin.products.ai-batches.*') ? 'active' : '' }}"><i class="fa-solid fa-wand-magic-sparkles"></i> IA em lote</a>
                <a href="{{ route('admin.products.outlet.form') }}" class="submenu-link {{ request()->routeIs('admin.products.outlet.*') ? 'active' : '' }}"><i class="fa-solid fa-box-open"></i> Gestão de outlet</a>
                <a href="{{ route('admin.brands.index') }}" class="submenu-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"><i class="fa-solid fa-copyright"></i> {{ __('messages.menu_marcas') }}</a>
                @if($isMasterAdmin)
                    <a href="{{ route('admin.categories.index') }}" class="submenu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="fa-solid fa-tags"></i> {{ __('messages.menu_categorias') }}</a>
                @endif
                <a href="{{ route('admin.subcategories.index') }}" class="submenu-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}"><i class="fa-solid fa-tag"></i> {{ __('messages.menu_subcategorias') }}</a>
                <a href="{{ route('admin.categorias-filhas.index') }}" class="submenu-link {{ request()->routeIs('admin.categorias-filhas.*') ? 'active' : '' }}"><i class="fa-solid fa-sitemap"></i> {{ __('messages.menu_filhas') }}</a>
            </div>
        </div>

        @if($isMasterAdmin)
        {{-- Vendas --}}
        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $vendasOpen ? '' : 'collapsed' }} {{ $vendasOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuVendas{{ $menuSuffix }}" aria-expanded="{{ $vendasOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-primary">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <span class="nav-text">{{ __('messages.menu_vendas') }}</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $vendasOpen ? 'show' : '' }}" id="menuVendas{{ $menuSuffix }}">
                <a href="{{ route('admin.orders.index') }}" class="submenu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="fa-solid fa-receipt"></i> {{ __('messages.menu_pedidos') }}</a>
                <a href="{{ route('admin.clients.index') }}" class="submenu-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}"><i class="fa-solid fa-user-tie"></i> {{ __('messages.menu_clientes') }}</a>
                <a href="{{ route('admin.abandoned-carts.index') }}" class="submenu-link {{ request()->routeIs('admin.abandoned-carts.*') ? 'active' : '' }}"><i class="fa-solid fa-cart-arrow-down"></i> Carrinhos abandonados</a>
            </div>
        </div>
        @endif

        {{-- Conteúdos --}}
        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $conteudosOpen ? '' : 'collapsed' }} {{ $conteudosOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuConteudos{{ $menuSuffix }}" aria-expanded="{{ $conteudosOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-info">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <span class="nav-text">{{ __('messages.menu_conteudos') }}</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $conteudosOpen ? 'show' : '' }}" id="menuConteudos{{ $menuSuffix }}">
                <a href="{{ route('admin.blogs.index') }}" class="submenu-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"><i class="fa-solid fa-blog"></i> {{ __('messages.menu_blog') }}</a>
                @if($isMasterAdmin)
                <a href="{{ route('admin.contatos.index') }}" class="submenu-link {{ request()->routeIs('admin.contatos.*', 'admin.contacts.*', 'admin.emails.*', 'admin.email-templates.*') ? 'active' : '' }}"><i class="fa-solid fa-envelope"></i> {{ __('messages.menu_contato') }}</a>
                <a href="{{ route('admin.contact-guide.index') }}" class="submenu-link {{ request()->routeIs('admin.contact-guide.*') ? 'active' : '' }}"><i class="fa-solid fa-map-location-dot"></i> {{ __('messages.admin_guide_menu') }}</a>
                <a href="{{ route('admin.trabalhe_conosco.index') }}" class="submenu-link {{ request()->routeIs('admin.trabalhe_conosco.*') ? 'active' : '' }}"><i class="fa-solid fa-user-tie"></i> Trabalhe conosco</a>
                <a href="{{ route('admin.policies.index') }}" class="submenu-link {{ request()->routeIs('admin.policies.*') ? 'active' : '' }}"><i class="fa-solid fa-scale-balanced"></i> {{ __('messages.menu_politicas') }}</a>
                @endif
            </div>
        </div>

        {{-- Páginas institucionais --}}
        <p class="sax-sidebar-heading mt-3">Páginas institucionais</p>
        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $institucionaisOpen ? '' : 'collapsed' }} {{ $institucionaisOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuInstitucionais{{ $menuSuffix }}" aria-expanded="{{ $institucionaisOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-info">
                    <i class="fa-solid fa-landmark"></i>
                </div>
                <span class="nav-text">Páginas especiais</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $institucionaisOpen ? 'show' : '' }}" id="menuInstitucionais{{ $menuSuffix }}">
                <a href="{{ route('admin.palace.index') }}" class="submenu-link {{ request()->routeIs('admin.palace.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> {{ __('messages.menu_palace') }}</a>
                <a href="{{ route('admin.bridal.index') }}" class="submenu-link {{ request()->routeIs('admin.bridal.*') ? 'active' : '' }}"><i class="fas fa-heart"></i> {{ __('messages.menu_bridal') }}</a>
                <a href="{{ route('admin.cafe_bistro.index') }}" class="submenu-link {{ request()->routeIs('admin.cafe_bistro.*') ? 'active' : '' }}"><i class="fas fa-coffee"></i> {{ __('messages.menu_cafe') }}</a>
                <a href="{{ route('admin.institucional.index') }}" class="submenu-link {{ request()->routeIs('admin.institucional.*') ? 'active' : '' }}"><i class="fas fa-university"></i> {{ __('messages.menu_institucional') }}</a>
            </div>
        </div>

        {{-- Sistema --}}
        <p class="sax-sidebar-heading mt-3">Sistema</p>
        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $sistemaOpen ? '' : 'collapsed' }} {{ $sistemaOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuSistema{{ $menuSuffix }}" aria-expanded="{{ $sistemaOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-danger">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <span class="nav-text">{{ __('messages.menu_sistema') }}</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $sistemaOpen ? 'show' : '' }}" id="menuSistema{{ $menuSuffix }}">
                @if($isMasterAdmin)
                <a href="{{ route('admin.store-controls.edit') }}" class="submenu-link {{ request()->routeIs('admin.store-controls.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-sliders"></i> {{ __('messages.store_controls_menu') }}
                </a>
                <a href="{{ route('admin.ai-settings.edit') }}" class="submenu-link {{ request()->routeIs('admin.ai-settings.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-wand-magic-sparkles"></i> Configuración de IA
                </a>
                <a href="{{ route('admin.whatsapp.edit') }}" class="submenu-link {{ request()->routeIs('admin.whatsapp.*') ? 'active' : '' }}">
                    <i class="fa-brands fa-whatsapp"></i> WhatsApp flutuante
                </a>
                <a href="{{ route('admin.dhl.edit') }}" class="submenu-link {{ request()->routeIs('admin.dhl.edit') ? 'active' : '' }}">
                    <i class="fa-solid fa-plane-departure"></i> DHL Express
                </a>
                <a href="{{ route('admin.dhl.measurements.index') }}" class="submenu-link {{ request()->routeIs('admin.dhl.measurements.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-ruler-combined"></i> Medidas médias DHL
                </a>
                @endif
                <a href="{{ route('admin.banners.index') }}" class="submenu-link {{ request()->routeIs('admin.banners.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-images"></i> {{ __('messages.menu_banners_home') }}
                </a>
                <a href="{{ route('admin.sections_home.index') }}" class="submenu-link {{ request()->routeIs('admin.sections_home.*') ? 'active' : '' }}"><i class="fas fa-sliders-h"></i> {{ __('messages.menu_secoes_home') }}</a>
                @if($isMasterAdmin)
                <a href="{{ route('admin.marketing.edit') }}" class="submenu-link {{ request()->routeIs('admin.marketing.*') ? 'active' : '' }}"><i class="fa-solid fa-chart-simple"></i> SEO e Marketing</a>
                <a href="{{ route('admin.theme-settings.edit') }}" class="submenu-link {{ request()->routeIs('admin.theme-settings.*') ? 'active' : '' }}"><i class="fa-solid fa-palette"></i> Identidade visual</a>
                @endif
                <button type="button" id="clearCacheBtn{{ $menuSuffix }}" data-clear-cache data-url="{{ secure_url('admin/clear-cache') }}" data-csrf="{{ csrf_token() }}" class="submenu-link border-0 bg-transparent w-100 text-start">
                    <i class="fa-solid fa-broom"></i> {{ __('messages.menu_limpar_cache') }}
                </button>
                @if($isMasterAdmin)
                <a href="{{ route('admin.currencies.index') }}" class="submenu-link {{ request()->routeIs('admin.currencies.*') ? 'active' : '' }}"><i class="fa-solid fa-coins"></i> {{ __('messages.menu_moedas') }}</a>
                <a href="{{ route('admin.payments.index') }}" class="submenu-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"><i class="fa-solid fa-plug-circle-bolt"></i> {{ __('messages.menu_gateways') }}</a>
                <a href="{{ route('admin.cupons.index') }}" class="submenu-link {{ request()->routeIs('admin.cupons.*') ? 'active' : '' }}"><i class="fa-solid fa-ticket"></i> {{ __('messages.menu_cupons') }}</a>
                <a href="{{ route('admin.languages.index') }}" class="submenu-link {{ request()->routeIs('admin.languages.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-language"></i> {{ __('messages.menu_idiomas') }}
                </a>
                @endif
                <a href="{{ route('admin.activate.index') }}" class="submenu-link {{ request()->routeIs('admin.activate.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-toggle-on"></i> {{ __('messages.menu_ativar_marcas') }}
                </a>
            </div>
        </div>
    </nav>
</div>
