@props([
    'palaceWhatsapp' => null,
    'cafeBistro' => null,
    'cafeBistroLocations' => null,
])

@php
    // 1. Identificação da rota (necessário para as cores/estilos)
    $isBridal = Request::is('bridal*');
    $isPalace = Request::is('palace*');
    $isBistro = Request::is('bistro*');
    $isInst   = Request::is('institucional*');
    $isBistroAsuncion = $isBistro && ($cafeBistro?->slug === 'asuncion');

    // 2. Lógica simplificada do WhatsApp (usando o que vem do Controller)
    $whatsapp = match(true) {
        $isBridal => collect($bridalData->locations ?? [])->first()['whatsapp_url'] ?? 'https://wa.me/595983123456',
        $isPalace => $palaceWhatsapp
            ? 'https://wa.me/' . preg_replace('/\D/', '', $palaceWhatsapp) . '?text=' . urlencode(__('messages.msg_consultoria_para_grandes_eventos'))
            : '#',
        $isBistro => ($cafeBistro ?? null)?->whatsapp_link ?? 'https://wa.me/595983000000',
        $isInst   => 'https://wa.me/595983123456',
        default   => '#'
    };

    // 3. Configuração Visual (Mapeamento limpo)
    $config = [
        'brand_name' => $isBridal ? 'Bridal' : ($isPalace ? 'Palace' : ($isBistro ? 'Café & Bistrô' : ($isInst ? __('messages.institucional_badge') : 'SAX'))),
        'logo_key'   => $isBridal ? 'logo_bridal' : ($isPalace ? 'logo_palace' : ($isBistro ? ($isBistroAsuncion ? 'logo_cafe_bistro_asuncion' : 'logo_cafe_bistro') : ($isInst ? 'header_image' : ''))),
        'whatsapp'   => $whatsapp,
        'cta_label'  => ($isPalace || $isBistro) ? __('messages.reservar') : __('messages.fale_conosco'),
        'cta_icon'   => ($isPalace || $isBistro) ? 'bi-calendar-check' : 'bi-whatsapp',
        'logo_filter'=> $isInst ? 'brightness(0) invert(1)' : 'none',
        'color' => [
            'scrollBg'   => $isBridal ? 'rgba(250,248,245,0.98)' : ($isBistro ? 'rgba(15,29,53,0.98)' : 'rgba(0,0,0,0.95)'),
            'accent'     => $isBridal ? '#C9A76E' : ($isBistro ? '#4a6fa5' : '#C5A059'),
            'scrollText' => $isBridal ? '#2C2C2C' : '#fff',
        ],
    ];

    $siteAttributes = View::shared('attributes');
    $selectedLogo = $siteAttributes?->{$config['logo_key']}
        ?: ($isBistroAsuncion ? $siteAttributes?->logo_cafe_bistro : null);
    $hasCustomLogo  = !empty($selectedLogo);
    // No Institucional, `header_image` e uma imagem editorial/hero, nao um
    // logotipo horizontal apropriado para a barra compacta do mobile.
    $hasMobileLogo  = $hasCustomLogo && !$isInst;
    $bistroLocations = $isBistro ? collect($cafeBistroLocations ?? []) : collect();
    $currentBistro = $isBistro ? ($cafeBistro ?? null) : null;

    // 4. Idioma e moeda são renderizados por <x-locale-currency-selector variant="nav" />
@endphp

{{-- Header mobile exclusivo das experiências: inspirado no app da loja,
     preservando identidade, atalhos e navegação editorial próprios. --}}
<header class="exp-mobile-appbar d-lg-none" id="experienceMobileHeader">
    <div class="exp-mobile-appbar__top">
        <button class="exp-mobile-appbar__button" type="button"
                data-bs-toggle="collapse" data-bs-target="#expAppMenu"
                aria-controls="expAppMenu" aria-expanded="false"
                aria-label="{{ __('messages.toggle_navigation') }}">
            <i class="fa-solid fa-bars" aria-hidden="true"></i>
        </button>

        <a class="exp-mobile-appbar__brand" href="{{ request()->url() }}" aria-label="SAX {{ $config['brand_name'] }}">
            @if($hasMobileLogo)
                <img src="{{ asset('storage/uploads/' . $selectedLogo) }}"
                     alt="SAX {{ $config['brand_name'] }}" decoding="async">
            @else
                <span class="exp-mobile-appbar__sax">SAX</span>
                <span class="exp-mobile-appbar__name">{{ $config['brand_name'] }}</span>
            @endif
        </a>

        <div class="exp-mobile-appbar__actions">
            <a href="{{ route('home') }}" class="exp-mobile-appbar__button" aria-label="{{ __('messages.ir_para_loja') }}">
                <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
            </a>
            @if($config['whatsapp'] !== '#')
                <a href="{{ $config['whatsapp'] }}" target="_blank" rel="noopener"
                   class="exp-mobile-appbar__button exp-mobile-appbar__button--accent"
                   aria-label="{{ $config['cta_label'] }}">
                    <i class="bi {{ $config['cta_icon'] }}" aria-hidden="true"></i>
                </a>
            @endif
        </div>
    </div>

    @if($isBistro && $bistroLocations->count() > 1)
        <nav class="exp-bistro-mobile-locations" aria-label="{{ __('messages.cafe_bistro_choose_location') }}">
            @foreach($bistroLocations as $location)
                <a href="{{ $location->slug === 'pedro-juan-caballero' ? route('cafe_bistro.index') : route('cafe_bistro.show', $location->slug) }}"
                   class="{{ $location->id === $currentBistro?->id ? 'active' : '' }}">
                    {{ $location->name }}
                </a>
            @endforeach
        </nav>
    @endif

    <div class="collapse exp-mobile-panel" id="expAppMenu">
        <div class="exp-mobile-panel__heading">
            <div>
                <span>SAX</span>
                <strong>{{ __('messages.experiencias') }}</strong>
            </div>
            <button type="button" class="exp-mobile-panel__close"
                    data-bs-toggle="collapse" data-bs-target="#expAppMenu"
                    aria-label="{{ __('messages.fechar') }}">
                <i class="bi bi-x-lg" aria-hidden="true"></i>
            </button>
        </div>

        <nav class="exp-mobile-panel__grid" aria-label="{{ __('messages.experiencias') }}">
            <a href="{{ route('institucional.index') }}" class="{{ $isInst ? 'active' : '' }}">
                <i class="fa-solid fa-landmark" aria-hidden="true"></i>
                <span>{{ __('messages.institucional') }}</span>
            </a>
            <a href="{{ route('bridal.index') }}" class="{{ $isBridal ? 'active' : '' }}">
                <i class="fa-solid fa-ring" aria-hidden="true"></i>
                <span>{{ __('messages.bridal') }}</span>
            </a>
            <a href="{{ route('palace.index') }}" class="{{ $isPalace ? 'active' : '' }}">
                <i class="fa-solid fa-crown" aria-hidden="true"></i>
                <span>{{ __('messages.palace') }}</span>
            </a>
            <a href="{{ route('cafe_bistro.index') }}" class="{{ $isBistro && request()->route('location') !== 'asuncion' ? 'active' : '' }}">
                <i class="fa-solid fa-mug-hot" aria-hidden="true"></i>
                <span>{{ __('messages.cafe_bistro_pjc') }}</span>
            </a>
            <a href="{{ route('cafe_bistro.show', 'asuncion') }}" class="{{ request()->route('location') === 'asuncion' ? 'active' : '' }}">
                <i class="fa-solid fa-mug-hot" aria-hidden="true"></i>
                <span>{{ __('messages.cafe_bistro_asuncion') }}</span>
            </a>
        </nav>

        <div class="exp-mobile-panel__links">
            <a href="{{ route('home') }}">
                <span><i class="fa-solid fa-store" aria-hidden="true"></i>{{ __('messages.ir_para_loja') }}</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('blogs.index') }}">
                <span><i class="fa-regular fa-newspaper" aria-hidden="true"></i>{{ __('messages.sax_news_tag') }}</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
            <a href="{{ route('contact.form') }}">
                <span><i class="fa-regular fa-envelope" aria-hidden="true"></i>{{ __('messages.contato') }}</span>
                <i class="bi bi-arrow-right" aria-hidden="true"></i>
            </a>
        </div>

        <div class="exp-mobile-panel__preferences">
            <x-locale-currency-selector variant="mobile" />
        </div>

        @if($config['whatsapp'] !== '#')
            <a href="{{ $config['whatsapp'] }}" target="_blank" rel="noopener" class="exp-mobile-panel__cta">
                <i class="bi {{ $config['cta_icon'] }}" aria-hidden="true"></i>
                <span>{{ strtoupper($config['cta_label']) }}</span>
            </a>
        @endif
    </div>
</header>

<header class="navbar navbar-expand-lg fixed-top exp-header d-none d-lg-flex transition-all" id="mainHeader">
    <div class="container-fluid px-lg-5">
        
        <a class="navbar-brand exp-logo" href="{{ url('/') }}">
            @if($hasCustomLogo)
                <img src="{{ asset('storage/uploads/' . $selectedLogo) }}"
                     alt="SAX {{ $config['brand_name'] }}" class="exp-logo-img" decoding="async">
            @else
                <span class="sax-text">SAX</span>
                <span class="exp-text">{{ $config['brand_name'] }}</span>
            @endif
        </a>

        {{-- Botão Mobile --}}
        <button class="navbar-toggler border-0 shadow-none d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#expNavbarMobile"
                aria-controls="expNavbarMobile" aria-expanded="false" aria-label="{{ __('messages.toggle_navigation') }}">
            <div class="hamburger-icon">
                <span></span><span></span><span></span>
            </div>
        </button>

        {{-- Navegação Desktop --}}
        <div class="collapse navbar-collapse d-none d-lg-flex justify-content-between" id="expNavbarDesktop">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-uppercase">
                <li class="nav-item"><a class="nav-link {{ Request::is('/') ? 'active' : '' }}" href="/">{{ __('messages.inicio') }}</a></li>
                <li class="nav-item dropdown exp-dropdown-mega">
                    <a class="nav-link dropdown-toggle" href="#" id="catDropDesk">{{ __('messages.shop') }}</a>
                    <div class="dropdown-menu border-0 shadow-lg">
                        <div class="container-fluid p-4">
                            <div class="row">
                                <div class="col-lg-6 border-end">
                                    <h6 class="dropdown-header-title">{{ __('messages.departamentos') }}</h6>
                                    <div class="row">
                                        <div class="col-6">
                                            <a class="dropdown-item" href="{{ route('categories.show', 'feminino') }}">{{ __('messages.mulher') }}</a>
                                            <a class="dropdown-item" href="{{ route('categories.show', 'masculino') }}">{{ __('messages.homem') }}</a>
                                            <a class="dropdown-item" href="{{ route('categories.show', 'infantil') }}">{{ __('messages.criancas') }}</a>
                                        </div>
                                        <div class="col-6">
                                            <a class="dropdown-item" href="{{ route('categories.show', 'optico') }}">{{ __('messages.lente') }}</a>
                                            <a class="dropdown-item" href="{{ route('categories.show', 'casa') }}">{{ __('messages.casa') }}</a>
                                            <a class="dropdown-item text-gold fw-bold" href="{{ route('categories.index') }}">{{ __('messages.ver_todas') }}</a>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-lg-6 ps-4">
                                    <h6 class="dropdown-header-title">{{ __('messages.experiencias') }}</h6>
                                    <a class="dropdown-item" href="{{ route('palace.index') }}">{{ __('messages.sax_palace') }}</a>
                                    <a class="dropdown-item" href="{{ route('bridal.index') }}">{{ __('messages.sax_bridal') }}</a>
                                    <a class="dropdown-item" href="{{ route('cafe_bistro.index') }}">{{ __('messages.cafe_bistro_pjc') }}</a>
                                    <a class="dropdown-item" href="{{ route('cafe_bistro.show', 'asuncion') }}">{{ __('messages.cafe_bistro_asuncion') }}</a>
                                    <a class="dropdown-item" href="{{ route('institucional.index') }}">{{ __('messages.sax_institucional') }}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </li>
                @if(Request::is('institucional'))
                    <li class="nav-item">
                        <a class="nav-link" href="#sobre">{{ __('messages.sobre_nos') }}</a>
                    </li>
                @endif
                <li class="nav-item"><a class="nav-link" href="{{ route('blogs.index') }}">{{ __('messages.sax_news_tag') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.form') }}">{{ __('messages.contato') }}</a></li>

                {{-- Seletores de idioma e moeda (independentes) --}}
                <x-locale-currency-selector variant="nav" />
            </ul>

            @if($isBistro && $bistroLocations->count() > 1)
                <div class="exp-bistro-location dropdown">
                    <button type="button" class="exp-bistro-location__toggle dropdown-toggle"
                            data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-solid fa-location-dot" aria-hidden="true"></i>
                        <span>
                            <small>{{ __('messages.cafe_bistro_choose_location') }}</small>
                            <strong>{{ $currentBistro?->name }}</strong>
                        </span>
                    </button>
                    <ul class="dropdown-menu exp-bistro-location__menu">
                        @foreach($bistroLocations as $location)
                            <li>
                                <a class="dropdown-item {{ $location->id === $currentBistro?->id ? 'active' : '' }}"
                                   href="{{ $location->slug === 'pedro-juan-caballero' ? route('cafe_bistro.index') : route('cafe_bistro.show', $location->slug) }}">
                                    <span>{{ $location->name }}</span>
                                    @if($location->id === $currentBistro?->id)<i class="fa-solid fa-check" aria-hidden="true"></i>@endif
                                </a>
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="d-flex align-items-center gap-3">
                <a href="{{ route('home') }}" class="btn-shop-link">{{ __('messages.ir_para_loja') }} <i class="bi bi-arrow-right"></i></a>
                <a href="{{ $config['whatsapp'] }}" target="_blank" class="btn-contact-gold">{{ strtoupper($config['cta_label']) }} <i class="bi {{ $config['cta_icon'] }}"></i></a>
            </div>
        </div>

        {{-- Navegação Mobile --}}
        <div class="collapse navbar-collapse d-lg-none" id="expNavbarMobile">
            <ul class="navbar-nav pt-4 text-uppercase">
                <li class="nav-item"><a class="nav-link" href="/">{{ __('messages.inicio') }}</a></li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="shopDropMobile" data-bs-toggle="dropdown">{{ __('messages.shop') }}</a>
                    <ul class="dropdown-menu bg-transparent border-0 ps-3">
                        <li><a class="dropdown-item text-gold small" href="{{ route('categories.index') }}">{{ __('messages.ver_tudo') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('categories.show', 'feminino') }}">{{ __('messages.mulher') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('categories.show', 'masculino') }}">{{ __('messages.homem') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('palace.index') }}">{{ __('messages.sax_palace') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('bridal.index') }}">{{ __('messages.sax_bridal') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('institucional.index') }}">{{ __('messages.sax_institucional') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('cafe_bistro.index') }}">{{ __('messages.cafe_bistro_pjc') }}</a></li>
                        <li><a class="dropdown-item" href="{{ route('cafe_bistro.show', 'asuncion') }}">{{ __('messages.cafe_bistro_asuncion') }}</a></li>
                    </ul>
                </li>
                <li class="nav-item"><a class="nav-link" href="#sobre">{{ __('messages.sobre_nos') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('blogs.index') }}">{{ __('messages.sax_news_tag') }}</a></li>
                <li class="nav-item"><a class="nav-link" href="{{ route('contact.form') }}">{{ __('messages.contato') }}</a></li>

                {{-- Seletores de idioma e moeda (independentes) --}}
                <x-locale-currency-selector variant="nav-mobile" />
            </ul>
            <div class="d-grid gap-2 mt-4 pb-4">
                <a href="{{ route('home') }}" class="btn-shop-link text-center">{{ __('messages.ir_para_loja') }}</a>
                <a href="{{ $config['whatsapp'] }}" target="_blank" class="btn-contact-gold text-center">{{ strtoupper($config['cta_label']) }}</a>
            </div>
        </div>
    </div>
</header>

{{-- Navegação compacta exclusiva das experiências no mobile. --}}
<nav class="sax-mobile-dock sax-mobile-dock--experiences d-lg-none" aria-label="{{ __('messages.experiencias') }}">
    <a href="{{ route('home') }}">
        <i class="fa-solid fa-house" aria-hidden="true"></i>
        <span>{{ __('messages.inicio') }}</span>
    </a>
    <a href="{{ route('institucional.index') }}" class="{{ request()->routeIs('institucional.index') ? 'active' : '' }}">
        <i class="fa-solid fa-landmark" aria-hidden="true"></i>
        <span>{{ __('messages.institucional') }}</span>
    </a>
    <a href="{{ route('bridal.index') }}" class="{{ request()->routeIs('bridal.index') ? 'active' : '' }}">
        <i class="fa-solid fa-ring" aria-hidden="true"></i>
        <span>{{ __('messages.bridal') }}</span>
    </a>
    <a href="{{ route('palace.index') }}" class="{{ request()->routeIs('palace.index') ? 'active' : '' }}">
        <i class="fa-solid fa-crown" aria-hidden="true"></i>
        <span>{{ __('messages.palace') }}</span>
    </a>
    <a href="{{ route('cafe_bistro.index') }}" class="{{ request()->routeIs('cafe_bistro.index') ? 'active' : '' }}">
        <i class="fa-solid fa-mug-hot" aria-hidden="true"></i>
        <span>{{ __('messages.cafe_bistro') }}</span>
    </a>
</nav>
<style>
    html{
        overflow-x: hidden !important;
    }
    body{
        overflow-x: hidden !important;
    }
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

    @media (min-width:992px) and (max-width:1499px) {
        body.experience-page--bistro .exp-header .container-fluid { padding-left:1.4rem!important; padding-right:1.4rem!important; }
        body.experience-page--bistro .exp-header .nav-link { padding-right:.62rem!important; padding-left:.62rem!important; }
        body.experience-page--bistro .exp-bistro-location { margin-right:.55rem; }
        body.experience-page--bistro .exp-bistro-location__toggle { min-width:108px; }
        body.experience-page--bistro .exp-bistro-location__toggle small { display:none; }
        body.experience-page--bistro .exp-bistro-location__toggle strong { max-width:76px; }
        body.experience-page--bistro .btn-shop-link { padding-right:12px; padding-left:12px; }
        body.experience-page--bistro .btn-contact-gold { padding-right:14px; padding-left:14px; }
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

    .exp-bistro-location { flex:0 0 auto; margin-right:1rem; }
    .exp-bistro-location__toggle { display:flex; min-width:168px; padding:.48rem .65rem; align-items:center; gap:.55rem; border:1px solid rgba(255,255,255,.18); border-radius:9px; background:rgba(8,20,39,.36); color:#fff; text-align:left; backdrop-filter:blur(10px); }
    .exp-bistro-location__toggle>i { color:var(--header-accent); font-size:.72rem; }
    .exp-bistro-location__toggle>span { display:flex; min-width:0; flex-direction:column; line-height:1.15; }
    .exp-bistro-location__toggle small { color:rgba(255,255,255,.55); font-size:.48rem; font-weight:750; letter-spacing:.08em; text-transform:uppercase; }
    .exp-bistro-location__toggle strong { max-width:128px; overflow:hidden; color:#fff; font-size:.64rem; text-overflow:ellipsis; white-space:nowrap; }
    .exp-bistro-location__menu { min-width:210px!important; padding:.35rem!important; border:1px solid rgba(255,255,255,.12)!important; border-radius:10px!important; background:var(--header-scroll-bg)!important; }
    .exp-bistro-location__menu .dropdown-item { display:flex; padding:.62rem .7rem!important; align-items:center; justify-content:space-between; border-radius:7px; }
    .exp-bistro-location__menu .dropdown-item.active { color:#fff!important; background:rgba(255,255,255,.09)!important; }
    .exp-bistro-location__menu .dropdown-item i { color:var(--header-accent); }

    .exp-bistro-mobile-locations { display:none; }

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

        .exp-bistro-mobile-locations { display:grid; padding:.38rem .65rem .55rem; grid-template-columns:1fr 1fr; gap:.3rem; border-top:1px solid rgba(255,255,255,.07); }
        .exp-bistro-mobile-locations a { padding:.48rem .55rem; border:1px solid rgba(255,255,255,.13); border-radius:8px; color:rgba(255,255,255,.7); font-size:.59rem; font-weight:750; text-align:center; text-decoration:none; }
        .exp-bistro-mobile-locations a.active { border-color:#fff; background:#fff; color:#0f1d35; }

        /* Dropdown inline no mobile — evita clipping do overflow-y: auto */
        #expNavbarMobile .dropdown-menu {
            position: static;
            transform: none;
        }
    }

    .exp-lang-menu {
        background: var(--header-scroll-bg);
        border-top: 2px solid var(--header-accent) !important;
        border-radius: 0;
    }
    .exp-lang-menu .dropdown-item {
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        cursor: pointer;
        padding: 0.55rem 1.25rem;  
    }
    .exp-lang-menu .dropdown-item:hover { padding-left: 1.55rem; }  
    .exp-lang-menu .dropdown-item.active { color: var(--header-accent) !important; }
</style>
