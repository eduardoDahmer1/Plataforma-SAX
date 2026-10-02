@php
    $adminPageTitle = match (true) {
        request()->routeIs('admin.index', 'admin.overview') => 'Visão geral',
        request()->routeIs('admin.notifications.*') => 'Notificações',
        request()->routeIs('admin.products.*', 'admin.produto.*') => 'Produtos',
        request()->routeIs('admin.brands.*') => 'Marcas',
        request()->routeIs('admin.categories.*') => 'Categorias',
        request()->routeIs('admin.subcategories.*') => 'Subcategorias',
        request()->routeIs('admin.categorias-filhas.*') => 'Categorias filhas',
        request()->routeIs('admin.orders.*') => 'Pedidos',
        request()->routeIs('admin.clients.*', 'admin.users.*') => 'Clientes e usuários',
        request()->routeIs('admin.abandoned-carts.*') => 'Carrinhos abandonados',
        request()->routeIs('admin.blogs.*', 'admin.blog-categories.*') => 'Conteúdo do blog',
        request()->routeIs('admin.contatos.*', 'admin.contacts.*', 'admin.emails.*', 'admin.email-templates.*') => 'Contatos',
        request()->routeIs('admin.contact-guide.*') => 'Guia de atendimento',
        request()->routeIs('admin.trabalhe_conosco.*') => 'Trabalhe conosco',
        request()->routeIs('admin.policies.*') => 'Políticas',
        request()->routeIs('admin.palace.*') => 'SAX Palace',
        request()->routeIs('admin.bridal.*') => 'SAX Bridal',
        request()->routeIs('admin.cafe_bistro.*') => 'Café & Bistrô',
        request()->routeIs('admin.institucional.*') => 'Institucional',
        request()->routeIs('admin.banners.*', 'admin.home-banners.*') => 'Banners',
        request()->routeIs('admin.sections_home.*') => 'Seções da home',
        request()->routeIs('admin.currencies.*') => 'Moedas',
        request()->routeIs('admin.payments.*') => 'Pagamentos',
        request()->routeIs('admin.cupons.*') => 'Cupons',
        request()->routeIs('admin.languages.*') => 'Idiomas',
        request()->routeIs('admin.activate.*') => 'Ativação do catálogo',
        request()->routeIs('admin.marketing.*') => 'SEO e marketing',
        request()->routeIs('admin.theme-settings.*') => 'Identidade visual',
        request()->routeIs('admin.store-controls.*') => 'Controle da loja',
        request()->routeIs('admin.ai-settings.*') => 'Inteligência artificial',
        request()->routeIs('admin.whatsapp.*') => 'WhatsApp',
        request()->routeIs('admin.dhl.*') => 'DHL Express',
        default => 'Painel administrativo',
    };
@endphp

<header class="sax-admin-topbar">
    <div class="container-fluid sax-admin-main-container sax-admin-topbar__inner">
        <div class="sax-admin-topbar__context">
            <button class="sax-admin-menu-toggle d-lg-none" id="openAdminDrawer" type="button" aria-label="Abrir menu administrativo" aria-controls="adminDrawerMobile" aria-expanded="false">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>
            <span class="sax-admin-topbar__title">{{ $adminPageTitle }}</span>
        </div>

        <div class="sax-admin-topbar__actions">
            <a href="{{ route('home') }}" class="sax-admin-store-link">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i>
                <span>Ir para a loja</span>
            </a>
            <form action="{{ route('logout') }}" method="POST" class="m-0">
                @csrf
                <button type="submit" class="sax-admin-logout" aria-label="Sair do painel" title="Sair do painel">
                    <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>
                </button>
            </form>
        </div>
    </div>
</header>
