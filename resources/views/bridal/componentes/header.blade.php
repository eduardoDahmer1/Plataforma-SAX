{{-- SAX Bridal — Header / Navigation --}}
<style>
    .navbar-bridal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
        padding: 24px 0;
        transition: var(--transition);
        background: transparent;
    }

    .navbar-bridal.scrolled {
        padding: 14px 0;
        background: rgba(255, 255, 255, 0.97);
        backdrop-filter: blur(10px);
        border-bottom: 1px solid rgba(201, 169, 97, 0.15);
        box-shadow: 0 1px 8px rgba(0, 0, 0, 0.04);
    }

    .navbar-bridal .collapse {
        background: rgba(255, 255, 255, 0.98);
    }

    /* 3-column grid: nav-left | logo | nav-right */
    .navbar-bridal-inner {
        display: grid;
        grid-template-columns: 1fr auto 1fr;
        align-items: center;
    }

    .nav-bridal-left { justify-self: start; }
    .nav-bridal-right { justify-self: end; }

    .brand-bridal {
        font-family: var(--font-display);
        font-size: 1.6rem;
        letter-spacing: 6px;
        color: var(--bridal-white);
        text-decoration: none;
        font-weight: 400;
        justify-self: center;
        transition: color 0.3s ease;
    }

    .brand-bridal span { font-weight: 200; }
    .brand-bridal:hover { color: var(--bridal-gold-light); }

    .navbar-bridal.scrolled .brand-bridal { color: var(--bridal-dark); }
    .navbar-bridal.scrolled .brand-bridal:hover { color: var(--bridal-gold); }

    .nav-link-bridal {
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 2.5px;
        color: var(--bridal-gold-light);
        text-decoration: none;
        font-weight: 500;
        position: relative;
        padding: 4px 0;
        transition: color 0.3s ease;
    }

    .navbar-bridal.scrolled .nav-link-bridal { color: var(--bridal-dark); }
    .navbar-bridal.scrolled .nav-link-bridal:hover { color: var(--bridal-gold); }
    #bridalMobileNav .nav-link-bridal { color: var(--bridal-dark); }

    .hamburger-line { background: var(--bridal-white); }
    .navbar-bridal.scrolled .hamburger-line { background: var(--bridal-dark); }

    .nav-link-bridal::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background: var(--bridal-gold);
        transition: width 0.4s ease;
    }

    .nav-link-bridal:hover { color: var(--bridal-gold); }
    .nav-link-bridal:hover::after { width: 100%; }

    .dropdown-bridal { position: relative; }

    /* Puente invisible — elimina el dead zone entre trigger y panel */
    .dropdown-bridal::after {
        content: '';
        position: absolute;
        top: 100%;
        left: 0;
        width: 100%;
        height: 15px;
        display: none;
    }

    .dropdown-bridal:hover::after { display: block; }

    .dropdown-panel {
        opacity: 0;
        visibility: hidden;
        pointer-events: none;
        position: absolute;
        top: calc(100% + 10px);
        left: 0;
        background: var(--bridal-white);
        min-width: 190px;
        box-shadow: 0 8px 24px rgba(0, 0, 0, 0.08);
        padding: 16px 0;
        z-index: 100;
        transition: opacity 0.2s ease;
    }

    .dropdown-panel a {
        display: block;
        padding: 10px 22px;
        color: var(--bridal-dark);
        text-decoration: none;
        font-size: 0.65rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        transition: background-color 0.3s ease;
    }

    .dropdown-panel a:hover {
        background-color: var(--bridal-cream);
        color: var(--bridal-gold);
    }

    .dropdown-bridal:hover .dropdown-panel {
        opacity: 1;
        visibility: visible;
        pointer-events: auto;
    }

    .btn-events-bridal {
        background-color: var(--bridal-gold);
        color: var(--bridal-white);
        padding: 9px 22px;
        border-radius: 30px;
        text-decoration: none;
        font-size: 0.65rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 500;
        transition: var(--transition);
        display: inline-block;
    }

    .btn-events-bridal:hover {
        background-color: var(--bridal-dark);
        color: var(--bridal-white);
        transform: translateY(-2px);
    }

    .hamburger-line {
        display: block;
        width: 24px;
        height: 1px;
        margin-bottom: 5px;
    }

    .hamburger-short {
        width: 14px;
        margin-left: auto;
    }

    @media (max-width: 991px) {
        .navbar-bridal { padding: 16px 0; }

        .navbar-bridal-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-bridal { font-size: 1.3rem; letter-spacing: 4px; }

        .navbar-bridal .btn[aria-label="Toggle navigation"] {
            padding: 7px 9px !important;
            background: rgba(255, 255, 255, 0.18);
            backdrop-filter: blur(6px);
            border-radius: 5px;
            margin-right: 4px;
        }

        .navbar-bridal.scrolled .btn[aria-label="Toggle navigation"] {
            background: rgba(44, 44, 44, 0.07);
        }
    }
</style>

<header class="navbar-bridal" id="bridalNav">
    <div class="container navbar-bridal-inner">

        {{-- Desktop Nav Left --}}
        <nav class="nav-bridal-left d-none d-lg-flex align-items-center gap-4">
            <div class="dropdown-bridal">
                <a href="#" class="nav-link-bridal">Institucional</a>
                <div class="dropdown-panel">
                    <a href="{{ route('home') }}">Tienda Virtual</a>
                    <a href="#palace">Palace</a>
                </div>
            </div>
            <a href="#products" class="nav-link-bridal">Productos</a>
        </nav>

        {{-- Brand (centered) --}}
        <a href="/" class="brand-bridal">SAX <span>BRIDAL</span></a>

        {{-- Desktop Nav Right --}}
        <div class="nav-bridal-right d-flex align-items-center justify-content-end gap-3">
            <nav class="d-none d-lg-flex align-items-center gap-4 me-2">
                <a href="#contact" class="nav-link-bridal">Contacto</a>
            </nav>
            <a href="https://wa.me/1234567890" class="btn-events-bridal d-none d-lg-inline-block">Events</a>

            {{-- Mobile toggle --}}
            <button class="btn border-0 p-0 d-lg-none" type="button"
                    data-bs-toggle="collapse" data-bs-target="#bridalMobileNav"
                    aria-controls="bridalMobileNav" aria-expanded="false" aria-label="Toggle navigation">
                <span class="hamburger-line"></span>
                <span class="hamburger-line hamburger-short"></span>
            </button>
        </div>
    </div>

    {{-- Mobile Nav --}}
    <div class="collapse d-lg-none" id="bridalMobileNav">
        <nav class="container py-4">
            <ul class="list-unstyled text-center mb-0">
                <li><a href="{{ route('home') }}" class="nav-link-bridal d-block py-2">Tienda Virtual</a></li>
                <li><a href="#palace" class="nav-link-bridal d-block py-2">Palace</a></li>
                <li><a href="#contact" class="nav-link-bridal d-block py-2">Contacto</a></li>
                <li><a href="#products" class="nav-link-bridal d-block py-2">Productos</a></li>
                <li class="mt-3">
                    <a href="https://wa.me/1234567890" class="btn-events-bridal">Events</a>
                </li>
            </ul>
        </nav>
    </div>
</header>
