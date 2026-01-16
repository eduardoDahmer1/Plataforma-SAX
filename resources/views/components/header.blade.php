<header class="sax-header">
    <div class="sax-top-promo">
        <div class="container-fluid px-lg-5">
            <div class="row align-items-center py-2">

                <div class="col-12 col-lg-3 d-flex justify-content-center justify-content-lg-start mb-2 mb-lg-0">
                    @php
                        use App\Models\Currency;
                        $currencies = Currency::all();
                        $sessionCurrency = session('currency');

                        // Lógica simplificada
                        $currentCurrencyId = is_object($sessionCurrency)
                            ? $sessionCurrency->id ?? Currency::where('is_default', 1)->value('id')
                            : $sessionCurrency ?? Currency::where('is_default', 1)->value('id');
                    @endphp

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
                <li class="list-inline-item border-start ps-3"><a href="#">SERVICIOS SAX PALACE</a></li>
                <li class="list-inline-item border-start ps-3"><a href="#">NUESTROS EVENTOS</a></li>
            </ul>
        </div>
    </div>

    <div class="container-fluid px-lg-5 py-3 border-top border-bottom">
        <div class="row align-items-center">
            <div class="col-2 d-lg-none">
                <button class="btn-menu-open" id="mobileMenuBtn">
                    <i class="fa fa-bars"></i>
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
                                {{ Auth::user()->name }}
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

                <a href="#" class="d-none d-sm-inline fs-5 color-black"><i class="fa-regular fa-heart"></i></a>
                <x-carrinho-header />
            </div>
        </div>

        <div class="row d-lg-none mt-3">
            <div class="col-12">
                <form action="{{ route('search') }}" method="GET" class="sax-search-container">
                    <input type="text" name="search" class="form-control sax-search-input" placeholder="Buscar..."
                        value="{{ request('search') }}">
                </form>
            </div>
        </div>
    </div>

    <nav class="sax-main-nav d-none d-lg-block">
        <div class="container text-center py-3">
            <ul class="list-inline m-0">
                <li class="list-inline-item"><a href="#">MUJER</a></li>
                <li class="list-inline-item"><a href="#">HOMBRE</a></li>
                <li class="list-inline-item"><a href="#">NIÑOS</a></li>
                <li class="list-inline-item"><a href="#">LENTES</a></li>
                <li class="list-inline-item"><a href="#">HOGAR</a></li>
                <li class="list-inline-item"><a href="#" class="text-bridal">BRIDAL</a></li>
                <li class="list-inline-item"><a href="#" class="text-palace">PALACE</a></li>
                <li class="list-inline-item"><a href="#">CAFÉ & BISTRÓ</a></li>
                <li class="list-inline-item"><a href="#" class="text-muted">#SAXNEWS</a></li>
            </ul>
        </div>
    </nav>

    <div id="saxDrawer" class="sax-drawer">
        <div class="drawer-header border-bottom p-3 d-flex justify-content-between align-items-center">
            <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="Logo" style="height: 30px;">
            <button class="btn-close-drawer" id="closeDrawer">&times;</button>
        </div>

        <div class="drawer-body">
            <div class="p-3 bg-light border-bottom">
                @if (Auth::check())
                    <span class="d-block mb-2 fw-bold">Olá, {{ Auth::user()->name }}</span>
                    @if (auth()->user()->user_type == 1)
                        <a href="{{ route('admin.index') }}" class="btn btn-dark btn-sm w-100 mb-2">Painel Admin</a>
                    @else
                        <a href="{{ route('user.dashboard') }}" class="btn btn-dark btn-sm w-100 mb-2">Meu Painel</a>
                    @endif
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
                <li><a href="#">MUJER</a></li>
                <li><a href="#">HOMBRE</a></li>
                <li><a href="#">NIÑOS</a></li>
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
        const btnOpen = document.getElementById('mobileMenuBtn');
        const btnClose = document.getElementById('closeDrawer');
        const drawer = document.getElementById('saxDrawer');
        const overlay = document.getElementById('drawerOverlay');

        function toggleDrawer() {
            drawer.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
        }

        btnOpen.addEventListener('click', toggleDrawer);
        btnClose.addEventListener('click', toggleDrawer);
        overlay.addEventListener('click', toggleDrawer);
    });
</script>

<style>
    /* Configurações Gerais */
    .sax-header {
        background-color: #fff;
        font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
        color: #000;
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

    /* Logo */
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

    /* Busca */
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

    /* Auth e Ações */
    .sax-auth-links a {
        font-size: 11px;
        color: #000;
        text-decoration: none;
        letter-spacing: 0.5px;
    }

    .color-black {
        color: #000;
    }

    /* Menu Categorias */
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

    /* Drawer Mobile */
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
        font-size: 22px;
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

    /* Container da barra */
    .sax-top-promo {
        background-color: #000;
        color: #fff;
        font-size: 11px;
        border-bottom: 1px solid #333;
    }

    /* Texto da promoção */
    .sax-promo-text {
        letter-spacing: 0.5px;
        text-transform: uppercase;
        line-height: 1.2;
    }

    /* Estilização do Select de Moeda */
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
        transition: all 0.3s ease;
    }

    .sax-currency-select:hover {
        border-color: #888;
        background-color: rgba(255, 255, 255, 0.1);
    }

    .sax-currency-select option {
        background-color: #000;
        /* Fundo das opções em preto */
        color: #fff;
    }

    /* Ajuste Mobile */
    @media (max-width: 991px) {
        .sax-promo-text {
            font-size: 10px;
        }

        .sax-currency-select {
            margin-bottom: 5px;
        }
    }
</style>
