@php
    $currentUser = auth()->user();
    $isAdminUser = $currentUser?->isAdmin() ?? false;
    $vistaTree = app(\App\Services\OpticalNavigationService::class)->tree();
    $vistaNav = $vistaTree
        ->concat($vistaTree->flatMap(fn (array $category) => $category['children']))
        ->take(4);
@endphp

<header class="vista-header">
    <div class="vista-header__main">
        <button class="vista-header__menu d-lg-none" type="button" data-vista-menu-toggle aria-label="Abrir menú" aria-expanded="false"><i class="fa-solid fa-bars"></i></button>
        <a class="vista-logo" href="{{ route('home') }}" aria-label="Vista & Co">
            @if($webpImage)
                <img src="{{ asset('storage/uploads/'.$webpImage) }}" alt="Vista & Co">
            @else
                <span>VISTA<small>&amp;</small>CO</span>
            @endif
        </a>

        <form action="{{ route('search') }}" method="GET" class="vista-search position-relative" role="search">
            <input name="search" type="search" value="{{ request('search') }}" placeholder="¿Qué deseas buscar?" autocomplete="off" class="search-autocomplete-input">
            <button type="submit" aria-label="Buscar"><i class="fa-solid fa-magnifying-glass"></i></button>
            <div class="autocomplete-results d-none"></div>
        </form>

        <div class="vista-header__actions">
            @if($currentUser?->isMasterAdmin())
                @include('admin.notifications-menu')
            @elseif($currentUser && !$isAdminUser)
                @include('users.notifications-menu')
                <a class="vista-favorites" href="{{ route('user.preferences') }}" aria-label="Favoritos"><i class="fa-regular fa-heart"></i></a>
            @else
                <button class="vista-favorites" type="button" data-bs-toggle="modal" data-bs-target="#loginModal" aria-label="Favoritos"><i class="fa-regular fa-heart"></i></button>
            @endif
            <x-carrinho-header />
            @if($currentUser)
                <div class="vista-account-menu dropdown">
                    <button class="vista-account dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                        <i class="fa-regular fa-user"></i><span>{{ explode(' ', $currentUser->name)[0] }}</span>
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end">
                        <li><a class="dropdown-item" href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}">{{ __('messages.meu_painel') }}</a></li>
                        <li><hr class="dropdown-divider"></li>
                        <li>
                            <form action="{{ route('logout') }}" method="POST">
                                @csrf
                                <button type="submit" class="dropdown-item vista-account-menu__logout"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>{{ __('messages.sair') }}</button>
                            </form>
                        </li>
                    </ul>
                </div>
            @else
                <button class="vista-account" type="button" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fa-regular fa-user"></i><span>Iniciar sesión</span></button>
            @endif
        </div>
    </div>

    <div class="vista-menu-overlay" data-vista-menu-overlay aria-hidden="true"></div>
    <nav class="vista-nav" data-vista-menu aria-label="Categorías principales">
        <div class="vista-nav__mobile-head d-lg-none">
            <div class="vista-nav__mobile-brand">
                <span class="vista-nav__mobile-mark"><i class="fa-solid fa-glasses" aria-hidden="true"></i></span>
                <span>
                    <small>VISTA&amp;CO</small>
                    <strong>Catálogo óptico</strong>
                </span>
            </div>
            <button type="button" data-vista-menu-close aria-label="Cerrar menú"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="vista-nav__section-label d-lg-none">Explorar catálogo</div>
        @foreach($vistaNav as $item)
            @php
                $submenuItems = collect($item['children']);
                $submenuId = 'vista-submenu-'.$item['type'].'-'.$item['id'];
            @endphp
            <div class="vista-nav__item" data-vista-nav-item>
                <div class="vista-nav__trigger">
                    <a href="{{ $item['url'] }}">{{ $item['label'] }}</a>
                    @if($submenuItems->isNotEmpty())
                        <button type="button"
                                data-vista-submenu-toggle
                                aria-expanded="false"
                                aria-controls="{{ $submenuId }}"
                                aria-label="Abrir subcategorías de {{ $item['label'] }}">
                            <i class="fa-solid fa-chevron-down" aria-hidden="true"></i>
                        </button>
                    @endif
                </div>
                @if($submenuItems->isNotEmpty())
                    <div id="{{ $submenuId }}"
                         class="vista-nav__dropdown {{ $item['type'] === 'subcategory' ? 'vista-nav__dropdown--compact' : '' }}"
                         data-vista-submenu
                         aria-hidden="true">
                        @if($item['type'] === 'category')
                            @foreach($submenuItems as $child)
                                <div class="vista-nav__group">
                                    <a class="vista-nav__group-title" href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                                    @foreach($child['children'] as $grandchild)
                                        <a class="vista-nav__leaf" href="{{ $grandchild['url'] }}">{{ $grandchild['label'] }}</a>
                                    @endforeach
                                </div>
                            @endforeach
                        @else
                            <div class="vista-nav__direct-list">
                                @foreach($submenuItems as $child)
                                    <a href="{{ $child['url'] }}">{{ $child['label'] }}</a>
                                @endforeach
                            </div>
                        @endif
                    </div>
                @endif
            </div>
        @endforeach

        <div class="vista-nav__footer-links d-lg-none">
            @if($currentUser)
                <section class="vista-nav__account-section">
                    <strong>{{ __('messages.minha_conta') }}</strong>
                    <a class="vista-nav__account-link" href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}">
                        <span class="vista-nav__account-avatar"><i class="fa-regular fa-user" aria-hidden="true"></i></span>
                        <span><small>{{ __('messages.ola') }}</small><b>{{ $currentUser->name }}</b></span>
                        <i class="fa-solid fa-chevron-right" aria-hidden="true"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST">
                        @csrf
                        <button type="submit"><i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i>{{ __('messages.sair') }}</button>
                    </form>
                </section>
            @endif
            <section>
                <strong>Acerca de VISTA&amp;CO</strong>
                <a href="{{ route('categories.index') }}"><i class="fa-solid fa-glasses" aria-hidden="true"></i>Categorías ópticas</a>
                <a href="{{ route('brands.index') }}"><i class="fa-regular fa-bookmark" aria-hidden="true"></i>Nuestras marcas</a>
                <a href="{{ route('contact.form') }}"><i class="fa-regular fa-user" aria-hidden="true"></i>Trabaja con nosotros</a>
            </section>
            <section>
                <strong>Ayuda</strong>
                <a href="{{ route('contact.form') }}"><i class="fa-regular fa-circle-question" aria-hidden="true"></i>Ayuda y contacto</a>
                <a href="{{ route('policies.index') }}"><i class="fa-solid fa-shield-halved" aria-hidden="true"></i>Política y términos</a>
            </section>
            <section class="vista-nav__social-section">
                <strong>Síguenos en las redes</strong>
                <div>
                    <a href="https://www.instagram.com/saxdepartment/" target="_blank" rel="noopener" aria-label="Instagram"><i class="fa-brands fa-instagram"></i></a>
                    <a href="https://www.facebook.com/saxdepartmentstore" target="_blank" rel="noopener" aria-label="Facebook"><i class="fa-brands fa-facebook-f"></i></a>
                    <a href="https://www.tiktok.com/@saxdepartment" target="_blank" rel="noopener" aria-label="TikTok"><i class="fa-brands fa-tiktok"></i></a>
                    <a href="https://www.youtube.com/" target="_blank" rel="noopener" aria-label="YouTube"><i class="fa-brands fa-youtube"></i></a>
                </div>
            </section>
        </div>
    </nav>
</header>

<nav class="vista-mobile-dock d-lg-none" aria-label="Navegación principal">
    <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'is-active' : '' }}">
        <i class="fa-solid fa-house" aria-hidden="true"></i><span>{{ __('messages.inicio') }}</span>
    </a>
    <button type="button" data-vista-dock-menu class="{{ request()->routeIs('categories.*', 'subcategories.*', 'categorias-filhas.*') ? 'is-active' : '' }}">
        <i class="fa-solid fa-glasses" aria-hidden="true"></i><span>{{ __('messages.categorias') }}</span>
    </button>
    <button type="button" data-vista-dock-search>
        <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i><span>{{ __('messages.pesquisar') }}</span>
    </button>
    @if($currentUser)
        <a href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}" class="{{ request()->routeIs('user.*', 'admin.*') ? 'is-active' : '' }}">
            <i class="fa-regular fa-user" aria-hidden="true"></i><span>{{ __('messages.minha_conta') }}</span>
        </a>
    @else
        <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal">
            <i class="fa-regular fa-user" aria-hidden="true"></i><span>{{ __('messages.entrar') }}</span>
        </button>
    @endif
    <button type="button" data-vista-dock-cart>
        <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i><span>{{ __('messages.carrinho') }}</span>
    </button>
</nav>

@guest
    @include('components.modal-login')
@endguest

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.querySelector('[data-vista-menu-toggle]');
    const menu = document.querySelector('[data-vista-menu]');
    const menuOverlay = document.querySelector('[data-vista-menu-overlay]');
    const menuClose = document.querySelector('[data-vista-menu-close]');
    const navItems = [...document.querySelectorAll('[data-vista-nav-item]')];
    const desktopMenu = window.matchMedia('(min-width: 992px)');

    const setSubmenuOpen = (item, open) => {
        const toggle = item.querySelector('[data-vista-submenu-toggle]');
        const submenu = item.querySelector('[data-vista-submenu]');
        if (!toggle || !submenu) return;
        item.classList.toggle('is-open', open);
        toggle.setAttribute('aria-expanded', String(open));
        toggle.setAttribute('aria-label', `${open ? 'Cerrar' : 'Abrir'} subcategorías`);
        submenu.setAttribute('aria-hidden', String(!open));
    };
    const closeSubmenus = except => navItems.forEach(item => {
        if (item !== except) setSubmenuOpen(item, false);
    });

    const setMenuOpen = open => {
        if (open) {
            document.getElementById('adminNotificationsClose')?.click();
            if (document.getElementById('cart-sidebar')?.classList.contains('open')) {
                document.getElementById('cart-close')?.click();
            }
        }
        menu?.classList.toggle('is-open', open);
        menuOverlay?.classList.toggle('is-open', open);
        document.body.classList.toggle('vista-menu-open', open);
        menu?.setAttribute('aria-hidden', String(!desktopMenu.matches && !open));
        if (menu) menu.inert = !desktopMenu.matches && !open;
        menuButton?.setAttribute('aria-expanded', String(open));
        menuButton?.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        if (!open) closeSubmenus();
    };

    menuButton?.addEventListener('click', function () {
        const open = !menu.classList.contains('is-open');
        setMenuOpen(open);
        if (open) requestAnimationFrame(() => menuClose?.focus());
    });
    menuClose?.addEventListener('click', () => { setMenuOpen(false); menuButton?.focus(); });
    menuOverlay?.addEventListener('click', () => { setMenuOpen(false); menuButton?.focus(); });
    document.querySelector('[data-vista-dock-menu]')?.addEventListener('click', () => {
        setMenuOpen(true);
        requestAnimationFrame(() => menuClose?.focus());
    });
    document.querySelector('[data-vista-dock-search]')?.addEventListener('click', () => {
        setMenuOpen(false);
        const search = document.querySelector('.vista-search input');
        search?.focus({ preventScroll: true });
        document.querySelector('.vista-header')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
    });
    document.querySelector('[data-vista-dock-cart]')?.addEventListener('click', () => {
        setMenuOpen(false);
        document.querySelector('.vista-header__actions .cart-toggle-btn')?.click();
    });
    document.getElementById('adminNotificationsButton')?.addEventListener('click', () => setMenuOpen(false));
    document.querySelector('.vista-header__actions .cart-toggle-btn')?.addEventListener('click', () => setMenuOpen(false));

    navItems.forEach(item => {
        const toggle = item.querySelector('[data-vista-submenu-toggle]');
        if (!toggle) return;
        let closeTimer;

        toggle.addEventListener('click', event => {
            event.stopPropagation();
            clearTimeout(closeTimer);
            const willOpen = !item.classList.contains('is-open');
            closeSubmenus(item);
            setSubmenuOpen(item, willOpen);
        });
        item.addEventListener('mouseenter', () => {
            if (!desktopMenu.matches) return;
            clearTimeout(closeTimer);
            closeSubmenus(item);
            setSubmenuOpen(item, true);
        });
        item.addEventListener('mouseleave', () => {
            if (!desktopMenu.matches) return;
            closeTimer = setTimeout(() => setSubmenuOpen(item, false), 180);
        });
        item.addEventListener('focusin', () => clearTimeout(closeTimer));
        item.addEventListener('focusout', () => {
            closeTimer = setTimeout(() => {
                if (!item.contains(document.activeElement)) setSubmenuOpen(item, false);
            }, 0);
        });
        item.addEventListener('keydown', event => {
            if (event.key !== 'Escape') return;
            setSubmenuOpen(item, false);
            toggle.focus();
        });
    });

    document.addEventListener('click', event => {
        if (!menu?.contains(event.target)) closeSubmenus();
    });
    document.addEventListener('keydown', event => {
        if (event.key === 'Escape' && menu?.classList.contains('is-open')) {
            setMenuOpen(false);
            menuButton?.focus();
        }
    });
    const syncMenuMode = () => {
        setMenuOpen(false);
        if (desktopMenu.matches) {
            menu?.setAttribute('aria-hidden', 'false');
            if (menu) menu.inert = false;
        }
    };
    syncMenuMode();
    if (typeof desktopMenu.addEventListener === 'function') {
        desktopMenu.addEventListener('change', syncMenuMode);
    } else {
        desktopMenu.addListener(syncMenuMode);
    }

    document.querySelectorAll('.vista-search .search-autocomplete-input').forEach(function (input) {
        const results = input.closest('form').querySelector('.autocomplete-results');
        let timer;
        input.addEventListener('input', function () {
            clearTimeout(timer);
            const query = input.value.trim();
            if (query.length < 2) { results.classList.add('d-none'); results.innerHTML = ''; return; }
            timer = setTimeout(function () {
                fetch('{{ route('search.autocomplete') }}?q=' + encodeURIComponent(query))
                    .then(response => response.json())
                    .then(items => {
                        results.innerHTML = items.length ? items.map(item => `<a class="autocomplete-item" href="${item.url}"><img src="${item.photo}" alt=""><span><b>${item.name}</b><small>${item.brand || ''}</small></span><strong>${item.price || ''}</strong></a>`).join('') : '<p class="m-0 p-3 text-muted">Sin resultados</p>';
                        results.classList.remove('d-none');
                    }).catch(() => results.classList.add('d-none'));
            }, 250);
        });
        document.addEventListener('click', event => { if (!input.closest('form').contains(event.target)) results.classList.add('d-none'); });
    });
});
</script>
