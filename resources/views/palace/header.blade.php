<header class="navbar navbar-expand-lg fixed-top main-header transition-all shadow-sm" id="mainHeader">
    <div class="container">
        <a class="navbar-brand me-4" href="/">
            @if (isset($attributes) && $attributes->logo_palace)
                <img src="{{ asset('storage/uploads/' . $attributes->logo_palace) }}" alt="SAX Palace" height="40" class="logo-img">
            @else
                <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Palace" height="40" class="logo-img">
            @endif
        </a>

        <button class="navbar-toggler border-0 shadow-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarContent" aria-controls="navbarContent" aria-expanded="false" aria-label="Toggle navigation">
            <i class="bi bi-list text-white fs-1"></i>
        </button>

        <div class="collapse navbar-collapse" id="navbarContent">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0 text-uppercase small fw-bold tracking-widest">
                <li class="nav-item">
                    <a class="nav-link px-lg-3 py-3 py-lg-0 text-white active" href="/">Loja</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-lg-3 py-3 py-lg-0 text-white" href="{{ route('bridal.index') }}">BRIDAL</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-lg-3 py-3 py-lg-0 text-white" href="{{ route('blogs.index') }}">#SAXNEWS</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link px-lg-3 py-3 py-lg-0 text-white" href="{{ route('contact.form') }}">Contato</a>
                </li>

            </ul>

            <div class="d-flex align-items-center gap-3 mt-3 mt-lg-0">
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" 
                   target="_blank" 
                   class="btn btn-gold px-4 py-2 rounded-0 text-uppercase fw-bold btn-sm">
                    Reservar <i class="bi bi-calendar-check ms-2"></i>
                </a>
            </div>
        </div>
    </div>
</header>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const header = document.querySelector('#mainHeader');
        
        window.addEventListener('scroll', () => {
            if (window.scrollY > 50) {
                header.classList.add('scrolled');
            } else {
                header.classList.remove('scrolled');
            }
        });
    });
</script>