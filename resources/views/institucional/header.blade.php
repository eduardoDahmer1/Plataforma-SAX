<header class="navbar navbar-expand-lg fixed-top inst-header transition-all" id="mainHeader">
    <div class="container-fluid px-lg-5">
        {{-- Logo --}}
        <a class="navbar-brand inst-logo" href="{{ url('/') }}">
            <span class="sax-text">SAX</span>
            <span class="inst-text">Institucional</span>
        </a>

        {{-- BOTÃO MOBILE (Visível apenas em dispositivos móveis) --}}
        <button class="navbar-toggler border-0 shadow-none d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#instNavbarMobile">
            <div class="hamburger-icon">
                <span></span><span></span><span></span>
            </div>
        </button>

        {{-- 1. NAVEGAÇÃO DESKTOP (Sempre visível em telas grandes) --}}
        <div class="collapse navbar-collapse d-none d-lg-flex justify-content-between" id="instNavbarDesktop">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-uppercase">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/">Início</a>
                </li>
                <li class="nav-item dropdown inst-dropdown-mega">
                    <a class="nav-link dropdown-toggle" href="#" id="catDropDesk">Shop</a>
                    <div class="dropdown-menu border-0 shadow-lg">
                        <div class="container-fluid p-4">
                            <div class="row">
                                <div class="col-lg-6 border-end">
                                    <h6 class="dropdown-header-title">Departamentos</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <a class="dropdown-item" href="{{ route('categories.show', 'feminino') }}">Mulher</a>
                                            <a class="dropdown-item" href="{{ route('categories.show', 'masculino') }}">Homem</a>
                                            <a class="dropdown-item" href="{{ route('categories.show', 'infantil') }}">Crianças</a>
                                        </div>
                                        <div class="col-6">
                                            <a class="dropdown-item" href="{{ route('categories.show', 'optico') }}">Lente</a>
                                            <a class="dropdown-item" href="{{ route('categories.show', 'casa') }}">Casa</a>
                                            <a class="dropdown-item text-gold fw-bold" href="{{ route('categories.index') }}">Ver Todas</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 ps-4">
                                    <h6 class="dropdown-header-title">Experiências</h6>
                                    <a class="dropdown-item" href="{{ route('palace.index') }}">SAX Palace</a>
                                    <a class="dropdown-item" href="{{ route('bridal.index') }}">SAX Bridal</a>
                                    <a class="dropdown-item" href="{{ route('cafe_bistro.index') }}">Café & Bistrô</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                <li class="nav-item"><a class="nav-link" href="#sobre">Sobre Nós</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.form') }}">Contato</a></li>
            </ul>
            
            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="btn-shop-link">IR PARA LOJA <i class="bi bi-arrow-right"></i></a>
                <a href="https://wa.me/595983123456" target="_blank" class="btn-contact-gold">FALE CONOSCO <i class="bi bi-whatsapp"></i></a>
            </div>
        </div>

        {{-- 2. NAVEGAÇÃO MOBILE (Apenas para telas pequenas) --}}
        <div class="collapse navbar-collapse d-lg-none" id="instNavbarMobile">
            <ul class="navbar-nav pt-4 text-uppercase">
                <li class="nav-item"><a class="nav-link" href="/">Início</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="shopDropMobile" data-bs-toggle="dropdown">Shop</a>
                    <ul class="dropdown-menu bg-transparent border-0 ps-3">
                        <li><a class="dropdown-item text-gold small" href="{{ route('categories.index') }}">Ver Tudo</a></li>
                        <li><a class="dropdown-item" href="{{ route('categories.show', 'feminino') }}">Mulher</a></li>
                        <li><a class="dropdown-item" href="{{ route('categories.show', 'masculino') }}">Homem</a></li>
                        <li><a class="dropdown-item" href="{{ route('palace.index') }}">SAX Palace</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#sobre">Sobre Nós</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.form') }}">Contato</a></li>
            </ul>
            <div class="d-grid gap-2 mt-4 pb-4">
                <a href="{{ route('home') }}" class="btn-shop-link text-center">IR PARA LOJA</a>
                <a href="https://wa.me/595983123456" target="_blank" class="btn-contact-gold text-center">FALE CONOSCO</a>
            </div>
        </div>
    </div>
</header>
<style>
    /* BASE HEADER */
    .inst-header {
        padding: 20px 0;
        background: transparent;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.4s ease;
        z-index: 1050;
    }

    .inst-header.scrolled {
        background: rgba(0, 0, 0, 0.95);
        padding: 10px 0;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(197, 160, 89, 0.3);
    }

    /* LOGO */
    .inst-logo .sax-text { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.8rem; letter-spacing: 4px; color: #fff !important; line-height: 1; }
    .inst-logo .inst-text { font-family: 'Montserrat', sans-serif; font-size: 0.6rem; letter-spacing: 5px; color: #c5a059 !important; text-transform: uppercase; display: block; }

    /* LINKS */
    .inst-header .nav-link { color: #fff !important; font-size: 0.75rem; letter-spacing: 2px; font-weight: 500; padding: 0.5rem 1.2rem !important; transition: 0.3s; }
    .inst-header .nav-link:hover, .inst-header .nav-link.active { color: #c5a059 !important; }

    /* MEGA MENU DESKTOP */
    @media (min-width: 992px) {
        #instNavbarMobile { display: none !important; } /* Evita duplicidade no PC */

        .inst-dropdown-mega .dropdown-menu {
            background: #0a0a0a;
            border-top: 2px solid #c5a059 !important;
            border-radius: 0;
            min-width: 500px;
            display: block;
            opacity: 0;
            visibility: hidden;
            transform: translateY(10px);
            transition: all 0.3s ease;
            position: absolute;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
        }
        .inst-dropdown-mega:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }
    }

    /* DROPDOWN ITEMS */
    .dropdown-header-title { color: #c5a059; font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 5px; }
    .dropdown-item { color: #bbb !important; font-size: 0.75rem; text-transform: uppercase; padding: 8px 0; background: transparent !important; transition: 0.2s; }
    .dropdown-item:hover { color: #fff !important; padding-left: 5px; }

    /* BOTÕES */
    .btn-shop-link { color: #fff; font-size: 0.7rem; font-weight: 600; letter-spacing: 1.5px; padding: 10px 20px; border: 1px solid rgba(255,255,255,0.2); text-decoration: none; transition: 0.3s; }
    .btn-shop-link:hover { background: #fff; color: #000 !important; }
    .btn-contact-gold { background: #c5a059; color: #000 !important; font-size: 0.7rem; font-weight: 700; letter-spacing: 1.5px; padding: 11px 22px; text-decoration: none; transition: 0.3s; }
    .btn-contact-gold:hover { background: #fff; transform: translateY(-2px); }

    /* HAMBURGER */
    .hamburger-icon { width: 25px; height: 18px; display: flex; flex-direction: column; justify-content: space-between; }
    .hamburger-icon span { display: block; height: 2px; width: 100%; background: #fff; }

    /* MOBILE MENU CSS */
    @media (max-width: 991px) {
        .inst-header { background: rgba(0,0,0,0.9); }
        .navbar-collapse { background: #000; padding: 0 20px; max-height: 80vh; overflow-y: auto; }
    }
</style>