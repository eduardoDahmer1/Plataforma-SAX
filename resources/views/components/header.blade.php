<header class="sax-header">
    @php
        use App\Models\Currency;
        use App\Models\Category;

        $currencies = Currency::all();
        $sessionCurrency = session('currency');
        $currentCurrencyId = is_object($sessionCurrency)
            ? $sessionCurrency->id ?? Currency::where('is_default', 1)->value('id')
            : $sessionCurrency ?? Currency::where('is_default', 1)->value('id');

        $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];

        $mainCategories = Category::whereIn('slug', $menuSlugs)
            ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
            ->get();

        $labelMap = [
            'feminino' => 'MUJER',
            'masculino' => 'HOMBRE',
            'infantil' => 'NIÑOS',
            'optico' => 'LENTES',
            'casa' => 'HOGAR',
        ];
    @endphp

    <div class="sax-top-promo">
        <div class="container-fluid px-lg-5">
            <div class="row align-items-center py-2">
                <div class="col-12 col-lg-3 d-flex justify-content-center justify-content-lg-start mb-2 mb-lg-0">
                    <form action="{{ route('currency.change') }}" method="POST" id="currency-form">
                        @csrf
                        <select name="currency_id" class="sax-currency-select" onchange="this.form.submit()">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                    {{ (int) $currency->id === (int) $currentCurrencyId ? 'selected' : '' }}>
                                    {{ $currency->sign }} {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="col-12 col-lg-6 text-center">
                    <p class="m-0 sax-promo-text">
                        <strong>UENO BANK.</strong> Hasta <strong>12 Cuotas sin intereses</strong> comprando
                        <strong>$1.000</strong> o más en tus prendas.
                    </p>
                </div>
                <div class="col-lg-3 d-none d-lg-block"></div>
            </div>
        </div>
    </div>

    <div class="sax-aux-nav d-none d-lg-block">
        <div class="container text-center py-2">
            <ul class="list-inline m-0">
                <li class="list-inline-item"><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                <li class="list-inline-item border-start ps-3"><a href="#">GIFT CARDS</a></li>
                <li class="list-inline-item border-start ps-3"><a href="{{ route('palace.index') }}">SERVICIOS SAX
                        PALACE</a></li>
                <li class="list-inline-item border-start ps-3"><a href="#">NUESTROS EVENTOS</a></li>
            </ul>
        </div>
    </div>

    <div class="container-fluid px-lg-5 py-3 border-top border-bottom">
        <div class="row align-items-center">
            <div class="col-2 d-lg-none d-flex gap-3">
                <button class="btn-menu-open" id="mobileMenuBtn">
                    <i class="fa fa-bars"></i>
                </button>
                {{-- NOVO: Botão de Busca Mobile --}}
                <button class="btn-menu-open" id="mobileSearchBtn">
                    <i class="fa fa-search"></i>
                </button>
            </div>

            <div class="col-8 col-lg-2 text-center text-lg-start">
                <a href="{{ route('home') }}">
                    @if ($webpImage)
                        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="SAX" class="logo-img">
                    @else
                        <span class="logo-fallback">SAX</span>
                    @endif
                </a>
            </div>

            <div class="col-12 col-lg-7 d-none d-lg-block">
                <form action="{{ route('search') }}" method="GET" class="sax-search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control sax-search-input"
                            placeholder="¿Qué deseas buscar?" value="{{ request('search') }}">
                    </div>
                </form>
            </div>

            <div class="col-2 col-lg-3 text-end d-flex justify-content-end align-items-center gap-3">
                <div class="sax-auth-links d-none d-lg-flex align-items-center">
                    <i class="fa-regular fa-user me-2"></i>
                    @if (Auth::check())
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle text-uppercase fw-bold" data-bs-toggle="dropdown">
                                {{ explode(' ', auth()->user()->name)[0] }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                @if (auth()->user()->user_type == 1)
                                    <li><a class="dropdown-item" href="{{ route('admin.index') }}">ADMINISTRAÇÃO</a>
                                    </li>
                                @else
                                    <li><a class="dropdown-item" href="{{ route('user.dashboard') }}">MEU PAINEL</a>
                                    </li>
                                @endif
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">SAIR</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginModal"
                            class="fw-bold">INICIAR SESIÓN</a>
                    @endif
                </div>
                @if (Auth::check())
                    <a href="{{ route('user.preferences') }}" class="d-none d-sm-inline fs-5 color-black"><i
                            class="fa-regular fa-heart"></i></a>
                    <x-carrinho-header />
                @endif
            </div>
        </div>
    </div>

    {{-- NOVO: Overlay de Busca Mobile --}}
    <div id="mobileSearchOverlay" class="mobile-search-overlay">
        <button class="btn-close-search" id="closeSearch">&times;</button>
        <div class="container h-100 d-flex align-items-center">
            <form action="{{ route('search') }}" method="GET" class="w-100">
                <div class="sax-search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" name="search" id="mobileSearchInput"
                            class="form-control sax-search-input" placeholder="¿Qué deseas buscar?"
                            value="{{ request('search') }}">
                    </div>
                </div>
            </form>
        </div>
    </div>

    <nav class="sax-main-nav d-none d-lg-block">
        <div class="container text-center py-3">
            <ul class="list-inline m-0">
                @foreach ($mainCategories as $cat)
                    <li class="list-inline-item">
                        <a href="{{ url('categorias/' . $cat->slug) }}">
                            {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                        </a>
                    </li>
                @endforeach
                <li class="list-inline-item"><a href="{{ route('bridal.index') }}" class="text-bridal">BRIDAL</a>
                </li>
                <li class="list-inline-item"><a href="{{ route('palace.index') }}" class="text-palace">PALACE</a>
                </li>
                <li class="list-inline-item"><a href="#">CAFÉ & BISTRÓ</a></li>
                <li class="list-inline-item"><a href="{{ route('blogs.index') }}" class="text-muted">#SAXNEWS</a>
                </li>
            </ul>
        </div>
    </nav>

    <div id="saxDrawer" class="sax-drawer">
        <div class="drawer-header border-bottom p-3 d-flex justify-content-between align-items-center">
            @if ($webpImage)
                <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Logo" style="height: 30px;">
            @else
                <span class="fw-bold">SAX</span>
            @endif
            <button class="btn-close-drawer" id="closeDrawer">&times;</button>
        </div>

        <div class="drawer-body">
            <div class="p-3 bg-light border-bottom">
                @if (Auth::check())
                    <span class="d-block mb-2 fw-bold">Olá, {{ Auth::user()->name }}</span>
                    <a href="{{ auth()->user()->user_type == 1 ? route('admin.index') : route('user.dashboard') }}"
                        class="btn btn-dark btn-sm w-100 mb-2">Meu Painel</a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-outline-danger btn-sm w-100">Sair</button>
                    </form>
                @else
                    <button class="btn btn-dark w-100" data-bs-toggle="modal" data-bs-target="#loginModal">INICIAR
                        SESIÓN</button>
                @endif
            </div>

            <ul class="drawer-menu-list">
                <li><a href="{{ route('home') }}">HOME</a></li>
                @foreach ($mainCategories as $cat)
                    <li>
                        <a href="{{ url('categorias/' . $cat->slug) }}">
                            {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                        </a>
                    </li>
                @endforeach
                <li><a href="{{ route('categories.index') }}">CATEGORIAS</a></li>
                <li><a href="{{ route('brands.index') }}">MARCAS</a></li>
                <li><a href="{{ route('contact.form') }}">CONTATO</a></li>
            </ul>
        </div>
    </div>
    <div class="drawer-overlay" id="drawerOverlay"></div>
</header>

@include('components.modal-login')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // Drawer Menu Logic
        const btnOpen = document.getElementById('mobileMenuBtn');
        const btnClose = document.getElementById('closeDrawer');
        const drawer = document.getElementById('saxDrawer');
        const overlay = document.getElementById('drawerOverlay');

        // Search Logic
        const btnSearchOpen = document.getElementById('mobileSearchBtn');
        const btnSearchClose = document.getElementById('closeSearch');
        const searchOverlay = document.getElementById('mobileSearchOverlay');
        const searchInput = document.getElementById('mobileSearchInput');

        function toggleDrawer() {
            drawer.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
        }

        function toggleSearch() {
            searchOverlay.classList.toggle('active');
            if (searchOverlay.classList.contains('active')) {
                setTimeout(() => searchInput.focus(), 300);
                document.body.style.overflow = 'hidden';
            } else {
                document.body.style.overflow = '';
            }
        }

        if (btnOpen) btnOpen.addEventListener('click', toggleDrawer);
        if (btnClose) btnClose.addEventListener('click', toggleDrawer);
        if (overlay) overlay.addEventListener('click', toggleDrawer);

        if (btnSearchOpen) btnSearchOpen.addEventListener('click', toggleSearch);
        if (btnSearchClose) btnSearchClose.addEventListener('click', toggleSearch);
    });
</script>

<style>
    .sax-header {
        background-color: #fff;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #000;
        position: relative;
    }

    .sax-top-promo {
        background-color: #000;
        color: #fff;
        font-size: 11px;
        letter-spacing: 1px;
        text-transform: uppercase;
    }

    .sax-aux-nav {
        font-size: 11px;
        font-weight: 600;
    }

    .sax-aux-nav a {
        color: #666;
        text-decoration: none;
    }

    .logo-img {
        height: 55px;
        width: auto;
        object-fit: contain;
    }

    .logo-fallback {
        font-size: 32px;
        font-weight: 900;
        letter-spacing: -2px;
    }

    .sax-search-container {
        background-color: #f1f1f1;
        border-radius: 4px;
        overflow: hidden;
    }

    .sax-search-input {
        background-color: transparent !important;
        border: none !important;
        box-shadow: none !important;
        font-size: 14px;
        padding: 12px;
    }

    .sax-auth-links a {
        font-size: 11px;
        color: #000;
        text-decoration: none;
        letter-spacing: 0.5px;
    }

    .color-black {
        color: #000;
    }

    .sax-main-nav .list-inline-item {
        margin: 0 15px;
    }

    .sax-main-nav a {
        color: #000;
        text-decoration: none;
        font-size: 13px;
        font-weight: 700;
    }

    .text-bridal {
        color: #b2945e !important;
    }

    .text-palace {
        color: #a31d24 !important;
    }

    .sax-drawer {
        position: fixed;
        top: 0;
        left: 0;
        width: 300px;
        height: 100%;
        background: #fff;
        z-index: 1050;
        transform: translateX(-100%);
        transition: transform 0.4s cubic-bezier(0.165, 0.84, 0.44, 1);
    }

    .sax-drawer.active {
        transform: translateX(0);
    }

    .drawer-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        z-index: 1040;
        display: none;
    }

    .drawer-overlay.active {
        display: block;
    }

    .btn-menu-open {
        background: none;
        border: none;
        font-size: 20px;
        padding: 0;
    }

    .btn-close-drawer {
        background: none;
        border: none;
        font-size: 30px;
    }

    .drawer-menu-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .drawer-menu-list li {
        border-bottom: 1px solid #eee;
    }

    .drawer-menu-list li a {
        display: block;
        padding: 15px 20px;
        color: #000;
        text-decoration: none;
        font-weight: bold;
        font-size: 14px;
    }

    .sax-currency-select {
        background-color: transparent;
        color: #fff;
        border: 1px solid #444;
        border-radius: 4px;
        font-size: 10px;
        font-weight: bold;
        padding: 2px 8px;
        outline: none;
        cursor: pointer;
        text-transform: uppercase;
    }

    .sax-currency-select option {
        background-color: #000;
        color: #fff;
    }

    /* Estilos do Novo Overlay de Busca Mobile */
    .mobile-search-overlay {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: #ffffff0f;
        backdrop-filter: blur(8px);
        -webkit-backdrop-filter: blur(8px);
        z-index: 1100;
        display: none;
        opacity: 0;
        transition: opacity 0.3s ease;
    }

    .mobile-search-overlay.active {
        display: block;
        opacity: 1;
    }

    .btn-close-search {
        position: absolute;
        right: 20px;
        background: none;
        border: none;
        font-size: 4em;
        color: #ffffff;
    }
</style>
