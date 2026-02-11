<header class="palace-nav sticky-top" id="mainHeader">
    <div class="container d-flex justify-content-between align-items-center">
        <div class="logo">
            <a href="/">
                @if (isset($attributes) && $attributes->logo_palace)
                    {{-- Carrega a logo enviada pelo painel --}}
                    <img src="{{ asset('storage/uploads/' . $attributes->logo_palace) }}" alt="SAX Palace" height="45">
                @else
                    {{-- Fallback: Caso não tenha imagem no banco, mantém a padrão --}}
                    <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Palace" height="45">
                @endif
            </a>
        </div>

        <nav class="d-none d-lg-block">
            <ul class="nav">
                <li class="nav-item"><a href="/" class="nav-link">Loja</a></li>
                <li class="nav-item"><a href="#gastronomia" class="nav-link">Gastronomia</a></li>
                <li class="nav-item"><a href="#eventos" class="nav-link">Eventos</a></li>
                <li class="nav-item"><a href="{{ route('contact.form') }}" class="nav-link">Contato</a></li>
            </ul>
        </nav>

        <div class="header-actions d-flex align-items-center">
            {{-- Link de reserva dinâmico usando o WhatsApp do banco --}}
            <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" target="_blank" class="btn-palace d-none d-sm-block">
                Reservar <i class="fa fa-calendar-check ms-2"></i>
            </a>
            
            <button class="btn text-white d-lg-none ms-3" type="button" data-bs-toggle="collapse"
                data-bs-target="#mobileNav">
                <i class="bi bi-list fs-1"></i>
            </button>
        </div>
    </div>

    {{-- Menu Mobile Dinâmico --}}
    <div class="collapse d-lg-none bg-black" id="mobileNav">
        <ul class="nav flex-column p-4 text-center">
            <li class="nav-item"><a href="/" class="nav-link py-3">Loja</a></li>
            <li class="nav-item"><a href="#gastronomia" class="nav-link py-3">Gastronomia</a></li>
            <li class="nav-item"><a href="#bar" class="nav-link py-3">Bodega</a></li>
            <li class="nav-item"><a href="#eventos" class="nav-link py-3">Eventos</a></li>
            <li class="nav-item">
                <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" target="_blank" class="nav-link py-3 gold-text">
                    Reservar Agora
                </a>
            </li>
        </ul>
    </div>
</header>