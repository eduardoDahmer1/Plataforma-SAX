<header class="sax-header py-3">
    <div class="container-fluid px-lg-5">
        
        {{-- Seletor de Moeda e Links de Topo --}}
        <div class="d-flex justify-content-between align-items-center mb-3 border-bottom pb-2">
            <div class="d-none d-lg-flex gap-3">
                <span class="sax-top-text">#SAXNEWS</span>
                <span class="sax-top-text">SERVICIOS SAX PALACE</span>
            </div>
            <x-currency-selector />
        </div>

        {{-- Topo: Logo e Ações --}}
        <div class="row align-items-center mb-4">
            
            {{-- Espaçador para manter logo no centro --}}
            <div class="col-md-4 d-none d-md-block"></div>

            {{-- Logo centralizada --}}
            <div class="col-12 col-md-4 text-center">
                @if ($webpImage)
                <a href="{{ route('home') }}">
                    <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="SAX Logo" 
                         class="img-fluid sax-logo">
                </a>
                @endif
            </div>

            {{-- Login/Logout (Direita) --}}
            <div class="col-12 col-md-4 text-center text-md-end mt-3 mt-md-0">
                @if (Auth::check())
                    <div class="d-flex justify-content-center justify-content-md-end align-items-center gap-3">
                        <span class="sax-user-name">HOLA, {{ explode(' ', Auth::user()->name)[0] }}</span>
                        <form action="{{ route('logout') }}" method="POST" class="d-inline">
                            @csrf
                            <button type="submit" class="sax-btn-link text-danger">
                                <i class="fa fa-sign-out-alt"></i> SALIR
                            </button>
                        </form>
                    </div>
                @else
                    <button class="sax-btn-auth" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fa fa-user me-2"></i> INICIAR SESIÓN
                    </button>
                @endif
            </div>
        </div>

        {{-- Menu de navegação (Estilo Abas) --}}
        <div class="sax-nav-wrapper d-flex flex-wrap justify-content-center border-top border-bottom">
            <a href="{{ route('home') }}" class="sax-nav-item">MUJER</a>
            <a href="{{ route('home') }}" class="sax-nav-item">HOMBRE</a>
            <a href="{{ route('home') }}" class="sax-nav-item">NIÑOS</a>
            
            @if(auth()->check())
                @if(auth()->user()->user_type == 1)
                    <a href="{{ route('admin.index') }}" class="sax-nav-item sax-gold">ADMIN</a>
                @else
                    <a href="{{ route('user.dashboard') }}" class="sax-nav-item sax-gold">MI PANEL</a>
                @endif
            @endif
        </div>
        
    </div>
</header>

<style>
    /* Reset e Cores Base */
    .sax-header { background: #fff; color: #000; font-family: 'Inter', sans-serif; }
    
    .sax-top-text { font-size: 10px; letter-spacing: 1px; color: #666; font-weight: 500; }

    /* Logo */
    .sax-logo { max-height: 80px; width: auto; }

    /* Botões de Auth */
    .sax-btn-auth {
        background: transparent;
        border: none;
        font-size: 12px;
        font-weight: 600;
        letter-spacing: 1.5px;
        color: #000;
        transition: opacity 0.3s;
    }
    
    .sax-btn-link {
        background: transparent;
        border: none;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1px;
    }

    .sax-user-name { font-size: 11px; font-weight: 500; letter-spacing: 1px; }

    /* Menu de Navegação - Seguindo imagem 6 */
    .sax-nav-wrapper { gap: 0; }
    
    .sax-nav-item {
        padding: 15px 25px;
        text-decoration: none !important;
        color: #000 !important;
        font-size: 12px;
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        position: relative;
        border-right: 1px solid #f0f0f0;
    }

    .sax-nav-item:last-child { border-right: none; }
    .sax-nav-item:hover { background: #fafafa; }
    
    /* Destaque para Admin/Panel */
    .sax-gold { color: #8e6d45 !important; font-weight: 700; }

    @media (max-width: 768px) {
        .sax-logo { max-height: 55px; }
        .sax-nav-item { padding: 10px 15px; font-size: 10px; }
    }
</style>

@include('components.modal-login')