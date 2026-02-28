{{-- SAX Bridal — Header / Navigation --}}
<style>
    .navbar-bridal {
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        z-index: 1050;
        padding: 1.5rem 0;
        transition: var(--transition);
        background: transparent;
    }

    .navbar-bridal.scrolled {
        padding: 0.875rem 0;
        background: rgba(250, 247, 242, 0.97);
        backdrop-filter: blur(0.625rem);
        border-bottom: 1px solid rgba(198, 167, 110, 0.20);
        box-shadow: 0 1px 12px rgba(0, 0, 0, 0.06);
    }

    .navbar-bridal .collapse {
        background: rgba(250, 247, 242, 0.98);
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
    .brand-bridal img {
    height: 3em;
    width: auto;
    }

    .brand-bridal:hover { color: var(--bridal-gold-light); }

    .navbar-bridal.scrolled .brand-bridal { color: #1C1C1C; }
    .navbar-bridal.scrolled .brand-bridal:hover { color: #C6A76E; }

    .nav-link-bridal {
        font-size: 0.79rem;
        text-transform: uppercase;
        letter-spacing: 2.7px;
        color: rgba(250, 247, 242, 0.85);
        text-decoration: none;
        font-weight: 500;
        position: relative;
        padding: 0.25rem 0;
        transition: color 0.3s ease;
    }

    .navbar-bridal.scrolled .nav-link-bridal { color: #1C1C1C; }
    .navbar-bridal.scrolled .nav-link-bridal:hover { color: #C6A76E; }
    #bridalMobileNav .nav-link-bridal { color: var(--bridal-dark); }

    .hamburger-line { background: var(--bridal-gold); }
    .navbar-bridal.scrolled .hamburger-line { background: var(--bridal-gold); }

    .nav-link-bridal::after {
        content: '';
        position: absolute;
        bottom: 0;
        left: 0;
        width: 0;
        height: 1px;
        background: #C6A76E;
        transition: width 0.4s ease;
    }

    .nav-link-bridal:hover { color: #C6A76E; }
    .nav-link-bridal:hover::after { width: 100%; }


    .hamburger-line {
        display: block;
        width: 1.5rem;
        height: 1px;
        margin-bottom: 0.3125rem;
    }

    .hamburger-short {
        width: 0.875rem;
        margin-left: auto;
    }

    @media (max-width: 991px) {
        .navbar-bridal { padding: 1rem 0; }

        .navbar-bridal-inner {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .brand-bridal { font-size: 1.3rem; letter-spacing: 4px; }

        .navbar-bridal .btn[aria-label="Toggle navigation"] {
            padding: 0.4375rem 0.5625rem !important;
            background: rgba(255, 255, 255, 0.08);
            backdrop-filter: blur(0.375rem);
            border-radius: 0.3125rem;
            margin-right: 0.25rem;
        }

        .navbar-bridal.scrolled .btn[aria-label="Toggle navigation"] {
            background: rgba(201, 169, 97, 0.01);
        }
    }
</style>

<header class="navbar-bridal" id="bridalNav">
    <div class="container navbar-bridal-inner">

        {{-- Desktop Nav Left --}}
        <nav class="nav-bridal-left d-none d-lg-flex align-items-center gap-4">
            <span class="nav-link-bridal" style="cursor: default;">Institucional</span>
            <a href="{{ route('home') }}" class="nav-link-bridal">Loja</a>
        </nav>

        {{-- Brand (centered) --}}
       <a href="/" class="brand-bridal">
    @if(isset($attributes) && $attributes->logo_bridal)
        <img src="{{ asset('storage/uploads/' . $attributes->logo_bridal) }}" alt="SAX Bridal" class="logo-bridal-img"
             width="120" height="48" decoding="async">
    @else
        SAX <span>BRIDAL</span>
    @endif
</a>

        {{-- Desktop Nav Right --}}
        <div class="nav-bridal-right d-flex align-items-center justify-content-end gap-3">
            <nav class="d-none d-lg-flex align-items-center gap-4 me-2">
                <a href="{{ route('palace.index') }}" class="nav-link-bridal">Palace</a>
                <a href="#contact" class="nav-link-bridal">Contacto</a>
            </nav>

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
                <li><span class="nav-link-bridal d-block py-2" style="cursor: default;">Institucional</span></li>
                <li><a href="{{ route('home') }}" class="nav-link-bridal d-block py-2">Loja</a></li>
                <li><a href="{{ route('palace.index') }}" class="nav-link-bridal d-block py-2">Palace</a></li>
                <li><a href="#contact" class="nav-link-bridal d-block py-2">Contacto</a></li>
            </ul>
        </nav>
    </div>
</header>
