<header class="sax-header">
    @php
        use App\Models\Currency;
        use App\Models\Category;

        $currencies = Currency::all();
        $sessionCurrency = session('currency');
        $currentCurrencyId = is_object($sessionCurrency)
            ? $sessionCurrency->id ?? Currency::where('is_default', 1)->value('id')
            : $sessionCurrency ?? Currency::where('is_default', 1)->value('id');

        $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];

        $mainCategories = Category::whereIn('slug', $menuSlugs)
            ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
            ->get();

        $labelMap = [
            'feminino' => 'MULHER',
            'masculino' => 'HOMEM',
            'infantil' => 'CRIANÇAS',
            'optico' => 'LENTE',
            'casa' => 'CASA',
        ];
    @endphp

    {{-- 1. TOP PROMO (BANNER PRETO) --}}
    <div class="sax-top-promo">
        <div class="container-fluid px-lg-5">
            <div class="row align-items-center py-2">
                <div class="col-12 col-lg-3 d-flex justify-content-center justify-content-lg-start mb-2 mb-lg-0">
                    <form action="{{ route('currency.change') }}" method="POST" id="currency-form">
                        @csrf
                        <select name="currency_id" class="sax-currency-select" onchange="this.form.submit()">
                            @foreach ($currencies as $currency)
                                <option value="{{ $currency->id }}"
                                    {{ (int) $currency->id === (int) $currentCurrencyId ? 'selected' : '' }}>
                                    {{ $currency->sign }} {{ $currency->name }}
                                </option>
                            @endforeach
                        </select>
                    </form>
                </div>

                <div class="col-12 col-lg-6 text-center">
                    <p class="m-0 sax-promo-text">
                        <strong>UENO BANK.</strong> Compra em até <strong>12x sem juros</strong>
                        <strong>$1.000</strong> o más.
                    </p>
                </div>
                <div class="col-lg-3 d-none d-lg-block"></div>
            </div>
        </div>
    </div>

    {{-- 2. AUX NAV (DESKTOP SOMENTE) --}}
    <div class="sax-aux-nav d-none d-lg-block">
        <div class="container text-center py-2">
            <ul class="list-inline m-0 main-nav-list">
                <li class="list-inline-item"><a href="{{ route('blogs.index') }}">#SAXNEWS</a></li>
                <li class="list-inline-item border-start ps-3"><a href="{{ route('palace.index') }}">SAX PALACE</a></li>
                <li class="list-inline-item border-start ps-3"><a href="{{ route('contact.form') }}">CONTATO</a></li>

                <li class="list-inline-item border-start ps-3 dropdown-mega-parent">
                    <a href="{{ route('categories.index') }}">CATEGORÍAS</a>
                    <div class="mega-menu-box" style="display: none;">
                        <div class="container text-start">
                            <div class="row py-4">
                                @foreach ($headerCategories as $cat)
                                    <div class="col-md-3 category-col mb-4">
                                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}"
                                            class="mega-title">
                                            {{ $cat->name }}
                                        </a>
                                        <ul class="list-unstyled sub-list">
                                            @foreach ($cat->subcategories as $sub)
                                                <li class="subcategory-item">
                                                    <a
                                                        href="{{ route('subcategories.show', $sub->slug ?? $sub->id) }}">
                                                        {{ $sub->name }}
                                                    </a>
                                                    <div class="filhas-flyout">
                                                        @foreach ($sub->categoriasfilhas as $filha)
                                                            <a
                                                                href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}">{{ $filha->name }}</a>
                                                        @endforeach
                                                    </div>
                                                </li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                </li>
                <li class="list-inline-item border-start ps-3">
                    <a href="{{ route('all-categories.index') }}">CATEGORÍAS GERAIS</a>
                </li>
            </ul>
        </div>
    </div>

    {{-- 3. MAIN HEADER (LOGO E BUSCA) --}}
    <div class="container-fluid px-lg-5 py-3 border-top border-bottom bg-white">
        <div class="row align-items-center">
            {{-- Mobile: Botões Hamburguer e Busca --}}
            <div class="col-3 d-lg-none">
                <div class="d-flex align-items-center gap-3">
                    <button class="btn-menu-open" id="mobileMenuBtn">
                        <i class="fa fa-bars"></i>
                    </button>
                    <button class="btn-menu-open" id="mobileSearchBtn">
                        <i class="fa fa-search"></i>
                    </button>
                </div>
            </div>

            {{-- Logo --}}
            <div class="col-6 col-lg-2 text-center text-lg-start">
                <a href="{{ route('home') }}">
                    @if ($webpImage)
                        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="SAX" class="logo-img">
                    @else
                        <span class="logo-fallback">SAX</span>
                    @endif
                </a>
            </div>

            {{-- Busca Desktop --}}
            <div class="col-lg-7 d-none d-lg-block">
                <form action="{{ route('search') }}" method="GET" class="sax-search-container">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0">
                            <i class="fa fa-search"></i>
                        </span>
                        <input type="text" name="search" class="form-control sax-search-input"
                            placeholder="¿Qué deseas buscar?" value="{{ request('search') }}">
                    </div>
                </form>
            </div>

            {{-- Ícones de Ação --}}
            <div class="col-3 col-lg-3 text-end d-flex justify-content-end align-items-center gap-3">
                <div class="sax-auth-links d-none d-lg-flex align-items-center">
                    <i class="fa-regular fa-user me-2"></i>
                    @if (Auth::check())
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle text-uppercase fw-bold" data-bs-toggle="dropdown">
                                {{ explode(' ', auth()->user()->name)[0] }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li><a class="dropdown-item"
                                        href="{{ auth()->user()->user_type == 1 ? route('admin.index') : route('user.dashboard') }}">MEU
                                        PAINEL</a></li>
                                <li>
                                    <hr class="dropdown-divider">
                                </li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">SAIR</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginModal"
                            class="fw-bold uppercase small tracking-1">INICIAR SESÃO</a>
                    @endif
                </div>
                @if (Auth::check())
                    <a href="{{ route('user.preferences') }}" class="d-none d-sm-inline fs-5 color-black"><i
                            class="fa-regular fa-heart"></i></a>
                @endif
                <x-carrinho-header />
            </div>
        </div>
    </div>

    {{-- 4. NAV PRINCIPAL (DESKTOP) --}}
    <nav class="sax-main-nav d-none d-lg-block">
        <div class="container text-center py-3">
            <ul class="list-inline m-0">
                @foreach ($mainCategories as $cat)
                    <li class="list-inline-item">
                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}">
                            {{ $labelMap[$cat->slug] ?? strtoupper($cat->name) }}
                        </a>
                    </li>
                @endforeach
                <li class="list-inline-item"><a href="{{ route('institucional.index') }}" class="text-institucional">INSTITUCIONAL</a></li>
                <li class="list-inline-item"><a href="{{ route('bridal.index') }}" class="text-bridal">BRIDAL</a></li>
                <li class="list-inline-item"><a href="{{ route('palace.index') }}" class="text-palace">PALACE</a></li>
                <li class="list-inline-item"><a href="{{ route('cafe_bistro.index') }}" class="text-bistro">CAFÉ & BISTRÔ</a></li>
                <li class="list-inline-item"><a href="{{ route('blogs.index') }}" class="text-muted">#SAXNEWS</a>
                </li>
            </ul>
        </div>
    </nav>

    {{-- 5. DRAWER MOBILE (MULTINÍVEL) --}}
    <div id="saxDrawer" class="sax-drawer">
        <div
            class="drawer-header border-bottom p-3 d-flex justify-content-between align-items-center bg-white sticky-top">
            <span class="fw-bold tracking-2">MENU</span>
            <button class="btn-close-drawer" id="closeDrawer">&times;</button>
        </div>

        <div class="drawer-body">
            {{-- Login/User Mobile --}}
            <div class="p-3 bg-light border-bottom">
                @if (Auth::check())
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <i class="fa-regular fa-user"></i>
                        <span class="fw-bold small">OLÁ, {{ explode(' ', Auth::user()->name)[0] }}</span>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ auth()->user()->user_type == 1 ? route('admin.index') : route('user.dashboard') }}"
                            class="btn btn-dark btn-sm rounded-0">MEU PAINEL</a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit"
                                class="btn btn-outline-danger btn-sm w-100 rounded-0">SAIR</button>
                        </form>
                    </div>
                @else
                    <button class="btn btn-dark btn-sm w-100 rounded-0 py-2 tracking-1" data-bs-toggle="modal"
                        data-bs-target="#loginModal">INICIAR SESÃO</button>
                @endif
            </div>

            {{-- Categorias Mobile Acordeão --}}
            <div class="drawer-menu-list">
                @foreach ($headerCategories as $cat)
                    <div class="drawer-item border-bottom">
                        <div class="d-flex justify-content-between align-items-center p-3">
                            <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}"
                                class="fw-bold text-dark text-decoration-none small uppercase">{{ $cat->name }}</a>
                            <button class="btn p-0 toggle-sub" data-target="m-sub-{{ $cat->id }}">
                                <i class="fa fa-chevron-down small text-muted"></i>
                            </button>
                        </div>
                        {{-- Subcategorias Mobile --}}
                        <div class="drawer-sub-menu d-none bg-light" id="m-sub-{{ $cat->id }}">
                            @foreach ($cat->subcategories as $sub)
                                <div class="ps-3 border-top">
                                    <div class="d-flex justify-content-between align-items-center p-3">
                                        <a href="{{ route('subcategories.show', $sub->slug ?? $sub->id) }}"
                                            class="text-muted text-decoration-none small">{{ $sub->name }}</a>
                                        @if ($sub->categoriasfilhas->count() > 0)
                                            <button class="btn p-0 toggle-sub"
                                                data-target="m-filha-{{ $sub->id }}">
                                                <i class="fa fa-plus x-small"></i>
                                            </button>
                                        @endif
                                    </div>
                                    {{-- Filhas Mobile --}}
                                    <div class="drawer-filha-menu d-none ps-3 pb-3 bg-white"
                                        id="m-filha-{{ $sub->id }}">
                                        @foreach ($sub->categoriasfilhas as $filha)
                                            <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}"
                                                class="d-block py-2 text-muted small text-decoration-none border-bottom mx-3">—
                                                {{ $filha->name }}</a>
                                        @endforeach
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endforeach

                {{-- Links Fixos --}}
                <div class="p-3 bg-light small fw-bold tracking-1">INSTITUCIONAL</div>
                <li><a href="{{ route('blogs.index') }}"
                        class="p-3 d-block text-dark text-decoration-none small border-bottom">#SAXNEWS</a></li>
                <li><a href="{{ route('bridal.index') }}"
                        class="p-3 d-block text-dark text-decoration-none small border-bottom">SAX BRIDAL</a></li>
                <li><a href="{{ route('contact.form') }}"
                        class="p-3 d-block text-dark text-decoration-none small border-bottom">CONTATO</a></li>
                        <li><a href="{{ route('palace.index') }}"
                        class="p-3 d-block text-dark text-decoration-none small border-bottom">SAX PALACE</a></li>
                <li>
            </div>
        </div>
    </div>
    <div class="drawer-overlay" id="drawerOverlay"></div>

    {{-- 6. SEARCH OVERLAY MOBILE --}}
    <div id="mobileSearchOverlay" class="mobile-search-overlay">
        <div class="p-4 text-end">
            <button class="btn-close-search" id="closeSearch">&times;</button>
        </div>
        <div class="container">
            <form action="{{ route('search') }}" method="GET">
                <div class="sax-search-container bg-white border">
                    <div class="input-group">
                        <span class="input-group-text bg-transparent border-0"><i class="fa fa-search"></i></span>
                        <input type="text" name="search" id="mobileSearchInput"
                            class="form-control sax-search-input" placeholder="Buscar productos...">
                    </div>
                </div>
            </form>
        </div>
    </div>
</header>

@include('components.modal-login')

<script>
    document.addEventListener('DOMContentLoaded', function() {
        // --- Drawer Menu ---
        const btnOpen = document.getElementById('mobileMenuBtn');
        const btnClose = document.getElementById('closeDrawer');
        const drawer = document.getElementById('saxDrawer');
        const overlay = document.getElementById('drawerOverlay');

        function toggleDrawer() {
            drawer.classList.toggle('active');
            overlay.classList.toggle('active');
            document.body.style.overflow = drawer.classList.contains('active') ? 'hidden' : '';
        }

        btnOpen?.addEventListener('click', toggleDrawer);
        btnClose?.addEventListener('click', toggleDrawer);
        overlay?.addEventListener('click', toggleDrawer);

        // --- Search Mobile ---
        const btnSearchOpen = document.getElementById('mobileSearchBtn');
        const btnSearchClose = document.getElementById('closeSearch');
        const searchOverlay = document.getElementById('mobileSearchOverlay');
        const searchInput = document.getElementById('mobileSearchInput');

        btnSearchOpen?.addEventListener('click', () => {
            searchOverlay.classList.add('active');
            setTimeout(() => searchInput.focus(), 300);
            document.body.style.overflow = 'hidden';
        });

        btnSearchClose?.addEventListener('click', () => {
            searchOverlay.classList.remove('active');
            document.body.style.overflow = '';
        });

        // --- Accordion Logic ---
        document.querySelectorAll('.toggle-sub').forEach(btn => {
            btn.addEventListener('click', function() {
                const targetId = this.getAttribute('data-target');
                const targetEl = document.getElementById(targetId);
                targetEl.classList.toggle('d-none');
                this.querySelector('i').classList.toggle('fa-chevron-up');
                this.querySelector('i').classList.toggle('fa-chevron-down');
            });
        });
    });
</script>
