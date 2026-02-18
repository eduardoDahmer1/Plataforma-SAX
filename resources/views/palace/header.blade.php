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
<style>
    /* Variáveis de Identidade */
:root {
    --palace-gold: #c5a059;
    --palace-dark: #0a0a0a;
    --nav-transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
}

/* Header Base */
.main-header {
    padding: 1.5rem 0;
    transition: var(--nav-transition);
    z-index: 1050;
    background: transparent;
}

/* Efeito ao Scroll (Ativado via JS) */
.main-header.scrolled {
    padding: 0.8rem 0;
    background: rgba(10, 10, 10, 0.85) !important;
    backdrop-filter: blur(15px); /* Efeito Vidro */
    -webkit-backdrop-filter: blur(15px);
    border-bottom: 1px solid rgba(197, 160, 89, 0.2);
}

/* Links de Navegação */
.nav-link {
    letter-spacing: 1.5px;
    font-size: 0.85rem;
    position: relative;
    opacity: 0.8;
    transition: var(--nav-transition);
}

.nav-link:hover, .nav-link.active {
    color: var(--palace-gold) !important;
    opacity: 1;
}

/* Linha Animada no Hover (Desktop) */
@media (min-width: 992px) {
    .nav-link::after {
        content: '';
        position: absolute;
        bottom: -5px;
        left: 50%;
        width: 0;
        height: 1px;
        background: var(--palace-gold);
        transition: var(--nav-transition);
        transform: translateX(-50%);
    }
    .nav-link:hover::after {
        width: 30px;
    }
}

/* Customização do Botão Gold */
.btn-gold {
    background-color: var(--palace-gold);
    color: #fff;
    border: 1px solid var(--palace-gold);
    transition: var(--nav-transition);
}

.btn-gold:hover {
    background-color: transparent;
    color: var(--palace-gold);
}

/* Ajustes Mobile */
@media (max-width: 991px) {
    .navbar-collapse {
        background: var(--palace-dark);
        margin-top: 1rem;
        padding: 1.5rem;
        border-radius: 8px;
        border: 1px solid rgba(197, 160, 89, 0.2);
    }
}
</style>
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