@php
    $catalogosOpen = request()->routeIs('admin.products.*', 'admin.brands.*', 'admin.categories.*', 'admin.subcategories.*', 'admin.categorias-filhas.*');
    $vendasOpen = request()->routeIs('admin.orders.*', 'admin.clients.*');
    $conteudosOpen = request()->routeIs('admin.blogs.*', 'admin.contatos.*');
    $sistemaOpen = request()->routeIs(
        'admin.sections_home.*',
        'admin.currencies.*',
        'admin.payments.*',
        'admin.palace.*',
        'admin.bridal.*',
        'admin.cafe_bistro.*',
        'admin.institucional.*',
        'admin.cupons.*',
        'admin.activate.*'
    );
@endphp

<div class="sax-admin-sidebar">
    <nav class="sax-nav-container">
        <a href="{{ route('admin.index') }}" class="sax-nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <div class="nav-icon-box bg-soft-warning">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <span class="nav-text">Banners / Home</span>
        </a>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $catalogosOpen ? '' : 'collapsed' }} {{ $catalogosOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuCatalogos" aria-expanded="{{ $catalogosOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-success">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="nav-text">Catálogos</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $catalogosOpen ? 'show' : '' }}" id="menuCatalogos">
                <a href="{{ route('admin.products.index') }}" class="submenu-link {{ request()->routeIs('admin.products.*') ? 'active' : '' }}"><i class="fa-solid fa-box"></i>
                    Produtos</a>
                <a href="{{ route('admin.brands.index') }}" class="submenu-link {{ request()->routeIs('admin.brands.*') ? 'active' : '' }}"><i class="fa-solid fa-copyright"></i>
                    Marcas</a>
                <a href="{{ route('admin.categories.index') }}" class="submenu-link {{ request()->routeIs('admin.categories.*') ? 'active' : '' }}"><i class="fa-solid fa-tags"></i>
                    Categorias</a>
                <a href="{{ route('admin.subcategories.index') }}" class="submenu-link {{ request()->routeIs('admin.subcategories.*') ? 'active' : '' }}"><i class="fa-solid fa-tag"></i>
                    Subcategorias</a>
                <a href="{{ route('admin.categorias-filhas.index') }}" class="submenu-link {{ request()->routeIs('admin.categorias-filhas.*') ? 'active' : '' }}"><i
                        class="fa-solid fa-sitemap"></i> Filhas</a>
            </div>
        </div>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $vendasOpen ? '' : 'collapsed' }} {{ $vendasOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuVendas" aria-expanded="{{ $vendasOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-primary">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <span class="nav-text">Vendas</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $vendasOpen ? 'show' : '' }}" id="menuVendas">
                <a href="{{ route('admin.orders.index') }}" class="submenu-link {{ request()->routeIs('admin.orders.*') ? 'active' : '' }}"><i class="fa-solid fa-receipt"></i>
                    Pedidos</a>
                <a href="{{ route('admin.clients.index') }}" class="submenu-link {{ request()->routeIs('admin.clients.*') ? 'active' : '' }}"><i class="fa-solid fa-user-tie"></i>
                    Clientes</a>
            </div>
        </div>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $conteudosOpen ? '' : 'collapsed' }} {{ $conteudosOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuConteudos" aria-expanded="{{ $conteudosOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-info">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <span class="nav-text">Conteúdos</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $conteudosOpen ? 'show' : '' }}" id="menuConteudos">
                <a href="{{ route('admin.blogs.index') }}" class="submenu-link {{ request()->routeIs('admin.blogs.*') ? 'active' : '' }}"><i class="fa-solid fa-blog"></i>
                    Blog</a>
                <a href="{{ route('admin.contatos.index') }}" class="submenu-link {{ request()->routeIs('admin.contatos.*') ? 'active' : '' }}"><i class="fa-solid fa-envelope"></i>
                    Contato</a>
                <a href="#" class="submenu-link"><i class="fa-solid fa-scale-balanced"></i> Políticas</a>
            </div>
        </div>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse {{ $sistemaOpen ? '' : 'collapsed' }} {{ $sistemaOpen ? 'active' : '' }}" data-bs-toggle="collapse" href="#menuSistema" aria-expanded="{{ $sistemaOpen ? 'true' : 'false' }}">
                <div class="nav-icon-box bg-soft-danger">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <span class="nav-text">Sistema</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu {{ $sistemaOpen ? 'show' : '' }}" id="menuSistema">
                <a href="{{ route('admin.sections_home.index') }}" class="submenu-link {{ request()->routeIs('admin.sections_home.*') ? 'active' : '' }}"><i
                        class="fas fa-sliders-h"></i> Seções Home</a>
                <button id="clearCacheBtn" 
        data-url="{{ secure_url('admin/clear-cache') }}" 
        data-csrf="{{ csrf_token() }}"
        class="submenu-link border-0 bg-transparent w-100 text-start">
    <i class="fa-solid fa-broom"></i> Limpar Cache
</button>
                <a href="{{ route('admin.currencies.index') }}" class="submenu-link {{ request()->routeIs('admin.currencies.*') ? 'active' : '' }}"><i class="fa-solid fa-coins"></i>
                    Moedas</a>
                <a href="{{ route('admin.payments.index') }}" class="submenu-link {{ request()->routeIs('admin.payments.*') ? 'active' : '' }}"><i class="fa-solid fa-plug-circle-bolt"></i>
                    Gateways de pagamento</a>
                <a href="{{ route('admin.palace.index') }}" class="submenu-link {{ request()->routeIs('admin.palace.*') ? 'active' : '' }}"><i class="fas fa-hotel"></i> Conteúdo
                    Palace</a>
                <a href="{{ route('admin.bridal.index') }}" class="submenu-link {{ request()->routeIs('admin.bridal.*') ? 'active' : '' }}"><i class="fas fa-heart"></i> Conteúdo
                    Bridal</a>
                <a href="{{ route('admin.cafe_bistro.index') }}" class="submenu-link {{ request()->routeIs('admin.cafe_bistro.*') ? 'active' : '' }}"><i class="fas fa-coffee"></i> Conteúdo
                    Café & Bistrô</a>
                    <a href="{{ route('admin.institucional.index') }}" class="submenu-link {{ request()->routeIs('admin.institucional.*') ? 'active' : '' }}"><i class="fas fa-university"></i> Conteúdo
                    Institucional</a>
                <a href="{{ route('admin.cupons.index') }}" class="submenu-link {{ request()->routeIs('admin.cupons.*') ? 'active' : '' }}"><i class="fa-solid fa-ticket"></i>
                    Cupons</a>
                <a href="{{ route('admin.activate.index') }}" class="submenu-link {{ request()->routeIs('admin.activate.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-toggle-on"></i> Ativar Marcas/Categorias
                </a>
            </div>
        </div>
    </nav>
</div>
