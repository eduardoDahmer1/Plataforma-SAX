{{-- SAX Bridal — Header / Navigation --}}
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
