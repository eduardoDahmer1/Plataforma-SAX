@php
    // Detectar experiencia por ruta y auto-configurar colores/logo/whatsapp
    if (Request::is('bridal*')) {
        $bridalData = Cache::remember('bridal_data', 28800, fn() => \App\Models\Bridal::first());
        $firstLocation = collect($bridalData->locations ?? [])->first();
        $config = [
            'logo_key'   => 'logo_bridal',
            'brand_name' => 'Bridal',
            'whatsapp'   => $firstLocation['whatsapp_url'] ?? 'https://wa.me/595983123456',
            'cta_label'  => 'Fale Conosco',
            'cta_icon'   => 'bi-whatsapp',
            'color' => [
                'scrollBg'   => 'rgba(250,248,245,0.98)',
                'accent'     => '#C9A76E',
                'scrollText' => '#2C2C2C',
            ],
        ];
    } elseif (Request::is('palace*')) {
        $palaceData = Cache::remember('palace_data', 28800, fn() => \App\Models\Palace::first());
        $config = [
            'logo_key'   => 'logo_palace',
            'brand_name' => 'Palace',
            'whatsapp'   => 'https://wa.me/' . preg_replace('/\D/', '', $palaceData->contato_whatsapp ?? '595983000000'),
            'cta_label'  => 'Reservar',
            'cta_icon'   => 'bi-calendar-check',
            'color' => [
                'scrollBg'   => 'rgba(0,0,0,0.95)',
                'accent'     => '#C5A059',
                'scrollText' => '#fff',
            ],
        ];
    } elseif (Request::is('bistro*')) {
        $cafeData = Cache::remember('cafe_bistro_data', 28800, fn() => \App\Models\CafeBistro::first());
        $config = [
            'logo_key'   => 'logo_cafe_bistro',
            'brand_name' => 'Café & Bistrô',
            'whatsapp'   => $cafeData->whatsapp_link ?? 'https://wa.me/595983000000',
            'cta_label'  => 'Reservar',
            'cta_icon'   => 'bi-calendar-check',
            'color' => [
                'scrollBg'   => 'rgba(15,29,53,0.98)',
                'accent'     => '#4a6fa5',
                'scrollText' => '#fff',
            ],
        ];
    } elseif (Request::is('institucional*')) {
        $config = [
            'logo_key'    => 'header_image',
            'logo_filter' => 'brightness(0) invert(1)',
            'brand_name'  => 'Institucional',
            'whatsapp'    => 'https://wa.me/595983123456',
            'cta_label'   => 'Fale Conosco',
            'cta_icon'    => 'bi-whatsapp',
            'color' => [
                'scrollBg'   => 'rgba(0,0,0,0.95)',
                'accent'     => '#C5A059',
                'scrollText' => '#fff',
            ],
        ];
    } else {
        // Fallback genérico
        $config = [
            'logo_key'   => '',
            'brand_name' => 'SAX',
            'whatsapp'   => '#',
            'cta_label'  => 'Fale Conosco',
            'cta_icon'   => 'bi-whatsapp',
            'color' => [
                'scrollBg'   => 'rgba(0,0,0,0.95)',
                'accent'     => '#C5A059',
                'scrollText' => '#fff',
            ],
        ];
    }
@endphp

<header class="navbar navbar-expand-lg fixed-top exp-header transition-all" id="mainHeader">
    <div class="container-fluid px-lg-5">

        {{-- Extraemos 'attributes' de View::shared() porque $attributes es una variable reservada de blade--}}

        @php $siteAttributes = View::shared('attributes'); @endphp
        
        <a class="navbar-brand exp-logo" href="{{ url('/') }}">
            @if($siteAttributes && !empty($siteAttributes->{$config['logo_key'] ?? ''}))
                <img src="{{ asset('storage/uploads/' . $siteAttributes->{$config['logo_key']}) }}"
                     alt="SAX {{ $config['brand_name'] }}" class="exp-logo-img" decoding="async">
            @else
                <span class="sax-text">SAX</span>
                <span class="exp-text">{{ $config['brand_name'] }}</span>
            @endif
        </a>

        {{-- BOTÃO MOBILE (Visível apenas em dispositivos móveis) --}}
        <button class="navbar-toggler border-0 shadow-none d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#expNavbarMobile">
            <div class="hamburger-icon">
                <span></span><span></span><span></span>
            </div>
        </button>

        {{-- 1. NAVEGAÇÃO DESKTOP (Sempre visível em telas grandes) --}}
        <div class="collapse navbar-collapse d-none d-lg-flex justify-content-between" id="expNavbarDesktop">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-uppercase">
                <li class="nav-item">
                    <a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/">Início</a>
                </li>
                <li class="nav-item dropdown exp-dropdown-mega">
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
                <a href="{{ $config['whatsapp'] }}" target="_blank" class="btn-contact-gold">{{ strtoupper($config['cta_label']) }} <i class="bi {{ $config['cta_icon'] }}"></i></a>
            </div>
        </div>

        {{-- 2. NAVEGAÇÃO MOBILE (Apenas para telas pequenas) --}}
        <div class="collapse navbar-collapse d-lg-none" id="expNavbarMobile">
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
                <a href="{{ $config['whatsapp'] }}" target="_blank" class="btn-contact-gold text-center">{{ strtoupper($config['cta_label']) }}</a>
            </div>
        </div>
    </div>
</header>
<style>

    
    :root{
        --header-scroll-bg: {{ $config['color']['scrollBg'] ?? 'rgba(0,0,0,0.95)' }};
        --header-accent:      {{ $config['color']['accent']   ?? '#c5a059' }};
        --header-scroll-text: {{ $config['color']['scrollText'] ?? '#fff' }};
        --logo-filter:        {{ $config['logo_filter'] ?? 'none' }};
    }


    /* CONTAINER PADDING */
    @media (min-width: 992px) {
        .exp-header .container-fluid { padding-left: 4rem !important; padding-right: 4rem !important; }
    }

    /* BASE HEADER */
    .exp-header {
        padding: 20px 0;
        background: transparent;
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
        transition: all 0.4s ease;
        z-index: 1050;
    }

    .exp-header.scrolled {
        background: var(--header-scroll-bg);
        padding: 10px 0;
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(255, 255, 255, 0.1);
    }

    .exp-header.scrolled .nav-link         { color: var(--header-scroll-text) !important; }
    .exp-header.scrolled .exp-logo .sax-text { color: var(--header-scroll-text) !important; }
    .exp-header.scrolled .btn-shop-link    { color: var(--header-scroll-text); border-color: var(--header-scroll-text); }

    /* LOGO */
    .exp-logo .sax-text { font-family: 'Playfair Display', serif; font-weight: 700; font-size: 1.8rem; letter-spacing: 4px; color: #fff !important; line-height: 1; }
    .exp-logo .exp-text { font-family: 'Montserrat', sans-serif; font-size: 0.6rem; letter-spacing: 5px; color: var(--header-accent) !important; text-transform: uppercase; display: block; }
    .exp-logo-img { height: 3em; width: auto; filter: var(--logo-filter); }

    /* LINKS */
    .exp-header .nav-link { color: #fff !important; font-size: 0.75rem; letter-spacing: 2px; font-weight: 500; padding: 0.5rem 1.2rem !important; transition: 0.3s; }
    .exp-header .nav-link:hover, .exp-header .nav-link.active { color: var(--header-accent) !important; }

    /* MEGA MENU DESKTOP */
    @media (min-width: 992px) {
        #expNavbarMobile { display: none !important; } /* Evita duplicidade no PC */

        .exp-dropdown-mega .dropdown-menu {
            background: var(--header-scroll-bg);
            border-top: 2px solid var(--header-accent) !important;
            border-radius: 0;
            min-width: 500px;
            display: block;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            position: absolute;
            left: 50%;
            transform: translateX(-50%) translateY(10px);
        }
        .exp-dropdown-mega:hover .dropdown-menu {
            opacity: 1;
            visibility: visible;
            transform: translateX(-50%) translateY(0);
        }
    }

    /* DROPDOWN ITEMS */
    .dropdown-header-title { color: var(--header-accent); font-size: 0.65rem; font-weight: 700; text-transform: uppercase; letter-spacing: 2px; margin-bottom: 15px; border-bottom: 1px solid rgba(255,255,255,0.05); padding-bottom: 5px; }
    .dropdown-item { color: #bbb !important; font-size: 0.75rem; text-transform: uppercase; padding: 8px 0; background: transparent !important; transition: 0.2s; }
    .dropdown-item:hover { color: #fff !important; padding-left: 5px; }

    /* BOTÕES */
    .btn-shop-link { color: #fff; font-size: 0.7rem; font-weight: 600; letter-spacing: 1.5px; padding: 10px 20px; border: 1px solid rgba(255,255,255,0.2); text-decoration: none; transition: 0.3s; }
    .btn-shop-link:hover { background: #fff; color: #000 !important; }
    .btn-contact-gold { background: var(--header-accent); color: #000 !important; font-size: 0.7rem; font-weight: 700; letter-spacing: 1.5px; padding: 11px 22px; text-decoration: none; transition: 0.3s; }
    .btn-contact-gold:hover { background: #fff; transform: translateY(-2px); }

    /* HAMBURGER */
    .hamburger-icon { width: 25px; height: 18px; display: flex; flex-direction: column; justify-content: space-between; }
    .hamburger-icon span { display: block; height: 2px; width: 100%; background: #fff; }

    /* MOBILE MENU CSS */
    @media (max-width: 991px) {
        .exp-header { background: var(--header-scroll-bg); padding: 0.5rem 0; backdrop-filter: blur(10px); }
        .navbar-collapse { background: var(--header-scroll-bg); padding: 0 1.25rem; max-height: 80vh; overflow-y: auto; }
        .exp-header .nav-link { color: var(--header-scroll-text) !important; }
        .exp-header .exp-logo .sax-text { color: var(--header-scroll-text) !important; }
        .exp-header .btn-shop-link { color: var(--header-scroll-text); border-color: var(--header-scroll-text); }
        .exp-header .hamburger-icon span { background: var(--header-accent); }
    }
</style>