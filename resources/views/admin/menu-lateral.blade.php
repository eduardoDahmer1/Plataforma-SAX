<div class="sax-admin-sidebar">
    <div class="sidebar-header-label mb-3">
        <span class="text-uppercase letter-spacing-2 x-small fw-bold text-muted">Navegação Principal</span>
    </div>

    <nav class="sax-nav-container">
        <a href="{{ route('admin.index') }}" class="sax-nav-item {{ request()->routeIs('admin.index') ? 'active' : '' }}">
            <div class="nav-icon-box bg-soft-warning">
                <i class="fa-solid fa-gauge-high"></i>
            </div>
            <span class="nav-text">Banners / Home</span>
        </a>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse" data-bs-toggle="collapse" href="#menuCatalogos" aria-expanded="false">
                <div class="nav-icon-box bg-soft-success">
                    <i class="fa-solid fa-boxes-stacked"></i>
                </div>
                <span class="nav-text">Catálogos</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu" id="menuCatalogos">
                <a href="{{ route('admin.products.index') }}" class="submenu-link"><i class="fa-solid fa-box"></i> Produtos</a>
                <a href="{{ route('admin.brands.index') }}" class="submenu-link"><i class="fa-solid fa-copyright"></i> Marcas</a>
                <a href="{{ route('admin.categories.index') }}" class="submenu-link"><i class="fa-solid fa-tags"></i> Categorias</a>
                <a href="{{ route('admin.subcategories.index') }}" class="submenu-link"><i class="fa-solid fa-tag"></i> Subcategorias</a>
                <a href="{{ route('admin.childcategories.index') }}" class="submenu-link"><i class="fa-solid fa-sitemap"></i> Filhas</a>
            </div>
        </div>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse" data-bs-toggle="collapse" href="#menuVendas">
                <div class="nav-icon-box bg-soft-primary">
                    <i class="fa-solid fa-cart-shopping"></i>
                </div>
                <span class="nav-text">Vendas</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu" id="menuVendas">
                <a href="{{ route('admin.orders.index') }}" class="submenu-link"><i class="fa-solid fa-receipt"></i> Pedidos</a>
                <a href="{{ route('admin.clients.index') }}" class="submenu-link"><i class="fa-solid fa-user-tie"></i> Clientes</a>
                <a href="{{ route('admin.payments.index') }}" class="submenu-link"><i class="fa-solid fa-credit-card"></i> Pagamentos</a>
            </div>
        </div>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse" data-bs-toggle="collapse" href="#menuConteudos">
                <div class="nav-icon-box bg-soft-info">
                    <i class="fa-solid fa-file-alt"></i>
                </div>
                <span class="nav-text">Conteúdos</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu" id="menuConteudos">
                <a href="{{ route('admin.blogs.index') }}" class="submenu-link"><i class="fa-solid fa-blog"></i> Blog</a>
                <a href="{{ route('admin.contatos.index') }}" class="submenu-link"><i class="fa-solid fa-envelope"></i> Contato</a>
                <a href="#" class="submenu-link"><i class="fa-solid fa-scale-balanced"></i> Políticas</a>
            </div>
        </div>

        <div class="nav-group">
            <a class="sax-nav-item has-collapse" data-bs-toggle="collapse" href="#menuSistema">
                <div class="nav-icon-box bg-soft-danger">
                    <i class="fa-solid fa-gears"></i>
                </div>
                <span class="nav-text">Sistema</span>
                <i class="fa-solid fa-chevron-down ms-auto arrow-icon"></i>
            </a>
            <div class="collapse sax-submenu" id="menuSistema">
                <a href="{{ route('admin.sections_home.index') }}" class="submenu-link"><i class="fas fa-sliders-h"></i> Seções Home</a>
                <button id="clearCacheBtn" data-url="{{ route('admin.clear-cache') }}" data-csrf="{{ csrf_token() }}" class="submenu-link border-0 bg-transparent w-100 text-start">
                    <i class="fa-solid fa-broom"></i> Limpar Cache
                </button>
                <a href="{{ route('admin.currencies.index') }}" class="submenu-link"><i class="fa-solid fa-coins"></i> Moedas</a>
                <a href="{{ route('admin.cupons.index') }}" class="submenu-link"><i class="fa-solid fa-ticket"></i> Cupons</a>
                <a href="{{ route('admin.activate.index') }}" class="submenu-link">
                    <i class="fa-solid fa-toggle-on"></i> Ativar Marcas/Categorias
                </a>
            </div>
        </div>
    </nav>
</div>
<style>
    /* Sidebar Container */
.sax-admin-sidebar {
    padding: 10px;
}

.letter-spacing-2 { letter-spacing: 2px; }
.x-small { font-size: 0.65rem; }

/* Itens Principais */
.sax-nav-item {
    display: flex;
    align-items: center;
    padding: 12px 15px;
    color: #444;
    text-decoration: none !important;
    border-radius: 12px;
    margin-bottom: 5px;
    transition: all 0.3s ease;
    font-size: 0.9rem;
    font-weight: 500;
}

.sax-nav-item:hover {
    background-color: #f8f9fa;
    color: #000;
}

.sax-nav-item.active {
    background-color: #000;
    color: #fff;
}

.sax-nav-item.active .nav-icon-box {
    background: rgba(255,255,255,0.2);
    color: #fff;
}

/* Ícones com Cores Suaves (Soft BG) */
.nav-icon-box {
    width: 32px;
    height: 32px;
    border-radius: 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-right: 12px;
    font-size: 0.9rem;
}

.bg-soft-warning { background: #fff8e1; color: #ffa000; }
.bg-soft-success { background: #e8f5e9; color: #2e7d32; }
.bg-soft-primary { background: #e3f2fd; color: #1976d2; }
.bg-soft-info    { background: #e0f7fa; color: #00838f; }
.bg-soft-danger  { background: #ffebee; color: #c62828; }

/* Submenu */
.sax-submenu {
    padding-left: 45px;
    margin-bottom: 10px;
}

.submenu-link {
    display: flex;
    align-items: center;
    padding: 8px 0;
    color: #777;
    text-decoration: none !important;
    font-size: 0.85rem;
    transition: 0.2s;
}

.submenu-link i {
    font-size: 0.75rem;
    margin-right: 10px;
    width: 15px;
    text-align: center;
}

.submenu-link:hover {
    color: #000;
    transform: translateX(5px);
}

/* Animação da Seta */
.arrow-icon {
    font-size: 0.7rem;
    transition: transform 0.3s;
}

.has-collapse:not(.collapsed) .arrow-icon {
    transform: rotate(180deg);
}
</style>