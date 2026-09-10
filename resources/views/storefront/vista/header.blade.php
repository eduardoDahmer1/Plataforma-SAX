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
            @if($currentUser && !$isAdminUser)
                <a href="{{ route('user.preferences') }}" aria-label="Favoritos"><i class="fa-regular fa-heart"></i></a>
            @else
                <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal" aria-label="Favoritos"><i class="fa-regular fa-heart"></i></button>
            @endif
            <x-carrinho-header />
            @if($currentUser)
                <a class="vista-account" href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}"><i class="fa-regular fa-user"></i><span>{{ explode(' ', $currentUser->name)[0] }}</span></a>
            @else
                <button class="vista-account" type="button" data-bs-toggle="modal" data-bs-target="#loginModal"><i class="fa-regular fa-user"></i><span>Iniciar sesión</span></button>
            @endif
        </div>
    </div>

    <nav class="vista-nav" data-vista-menu aria-label="Categorías principales">
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
    </nav>
</header>

@guest
    @include('components.modal-login')
@endguest

<script>
document.addEventListener('DOMContentLoaded', function () {
    const menuButton = document.querySelector('[data-vista-menu-toggle]');
    const menu = document.querySelector('[data-vista-menu]');
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

    menuButton?.addEventListener('click', function () {
        const open = menu.classList.toggle('is-open');
        menuButton.setAttribute('aria-expanded', String(open));
        menuButton.setAttribute('aria-label', open ? 'Cerrar menú' : 'Abrir menú');
        if (!open) closeSubmenus();
    });

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
    desktopMenu.addEventListener?.('change', () => closeSubmenus());

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
