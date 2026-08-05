<header class="sax-header">
    @php
        use App\Models\Category;

        $menuSlugs = ['feminino', 'masculino', 'infantil', 'optico', 'casa'];

        $mainCategories = Category::whereIn('slug', $menuSlugs)
            ->orderByRaw("FIELD(slug, 'feminino', 'masculino', 'infantil', 'optico', 'casa')")
            ->get();

        $labelMap = [
            'feminino' => __('messages.mulher'),
            'masculino' => __('messages.homem'),
            'infantil' => __('messages.criancas'),
            'optico' => __('messages.lente'),
            'casa' => __('messages.casa'),
        ];

        $mobileIconMap = [
            'feminino' => 'fa-venus',
            'masculino' => 'fa-mars',
            'infantil' => 'fa-child-reaching',
            'optico' => 'fa-glasses',
            'casa' => 'fa-house',
        ];

        $currentUser = Auth::check() ? Auth::user() : null;
        $userName = $currentUser ? explode(' ', $currentUser->name)[0] : null;
        $isAdminUser = $currentUser?->isAdmin() ?? false;
    @endphp

    <div class="sax-aux-nav d-none d-lg-block">
        <div class="container text-center py-2">
            <ul class="list-inline m-0 main-nav-list">
                <li class="list-inline-item">
                    <x-language-selector variant="desktop" />
                </li>
                <li class="list-inline-item"><a href="{{ route('blogs.index') }}">{{ __('messages.sax_news_tag') }}</a></li>
                <li class="list-inline-item border-start ps-3"><a href="{{ route('palace.index') }}">{{ __('messages.sax_palace') }}</a></li>
                <li class="list-inline-item border-start ps-3"><a href="{{ route('contact.form') }}">{{ __('messages.contato') }}</a></li>

                <li class="list-inline-item border-start ps-3 dropdown-mega-parent">
                    <a href="{{ route('categories.index') }}" class="mega-menu-trigger">
                        {{ __('messages.categorias') }}
                        <i class="fa fa-chevron-down" aria-hidden="true"></i>
                    </a>
                    <div class="mega-menu-box">
                        <div class="container text-start mega-menu-shell">
                            <div class="mega-menu-header">
                                <div>
                                    <span class="mega-menu-eyebrow">Explore o catálogo</span>
                                    <strong>Compre por categoria</strong>
                                </div>
                                <a href="{{ route('categories.index') }}" class="mega-menu-view-all">
                                    Ver todas as categorias
                                    <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                </a>
                            </div>
                            <div class="mega-menu-grid">
                                @foreach ($headerCategories as $cat)
                                    <div class="category-col">
                                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}" class="mega-title">
                                            <span>{{ $cat->name }}</span>
                                            <i class="fa fa-arrow-right" aria-hidden="true"></i>
                                        </a>
                                        <ul class="list-unstyled sub-list">
                                            @foreach ($cat->subcategories as $sub)
                                                <li class="subcategory-item">
                                                    <a href="{{ route('subcategories.show', $sub->slug ?? $sub->id) }}">
                                                        <span>{{ $sub->name }}</span>
                                                        @if ($sub->categoriasfilhas->isNotEmpty())
                                                            <i class="fa fa-chevron-right" aria-hidden="true"></i>
                                                        @endif
                                                    </a>
                                                    @if ($sub->categoriasfilhas->isNotEmpty())
                                                        <div class="filhas-flyout">
                                                            <span class="filhas-flyout-title">{{ $sub->name }}</span>
                                                            @foreach ($sub->categoriasfilhas as $filha)
                                                                <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}">
                                                                    {{ $filha->name }}
                                                                </a>
                                                            @endforeach
                                                        </div>
                                                    @endif
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
                    <a href="{{ route('all-categories.index') }}">{{ __('messages.categorias_gerais') }}</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sax-header-main container-fluid px-lg-5 py-3 border-top border-bottom bg-white">
        <div class="row align-items-center g-2">
            <div class="col-3 d-lg-none sax-mobile-header-start">
                <div class="sax-mobile-actions d-flex align-items-center gap-2">
                    <button class="btn-menu-open" id="mobileMenuBtn" type="button" aria-label="{{ __('messages.abrir_menu') }}" aria-controls="saxDrawer" aria-expanded="false">
                        <i class="fa fa-bars"></i>
                    </button>
                </div>
            </div>

            <div class="col-6 col-lg-2 text-center text-lg-start sax-header-logo-col">
                <a href="{{ route('home') }}">
                    @if ($webpImage)
                        <img src="{{ asset('storage/uploads/' . $webpImage) }}" alt="SAX" class="logo-img">
                    @else
                        <span class="logo-fallback">SAX</span>
                    @endif
                </a>
            </div>

            <div class="col-lg-7 d-none d-lg-block">
                <x-search />
            </div>

            <div class="col-3 col-lg-3 text-end d-flex justify-content-end align-items-center gap-3 sax-header-actions">
                @if ($isAdminUser)
                    @include('admin.notifications-menu')
                @elseif ($currentUser)
                    @include('users.notifications-menu')
                @endif

                <div class="sax-auth-links d-none d-lg-flex align-items-center">
                    <i class="fa-regular fa-user me-2"></i>
                    @if ($currentUser)
                        <div class="dropdown">
                            <a href="#" class="dropdown-toggle text-uppercase fw-bold" data-bs-toggle="dropdown">
                                {{ $userName }}
                            </a>
                            <ul class="dropdown-menu dropdown-menu-end">
                                <li>
                                    <a class="dropdown-item" href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}">
                                        {{ __('messages.meu_painel') }}
                                    </a>
                                </li>
                                <li><hr class="dropdown-divider"></li>
                                <li>
                                    <form action="{{ route('logout') }}" method="POST">
                                        @csrf
                                        <button type="submit" class="dropdown-item text-danger">{{ __('messages.sair') }}</button>
                                    </form>
                                </li>
                            </ul>
                        </div>
                    @else
                        <a href="javascript:void(0)" data-bs-toggle="modal" data-bs-target="#loginModal"
                            class="fw-bold uppercase small tracking-1">{{ __('messages.entrar') }}</a>
                    @endif
                </div>

                @if ($currentUser && !$isAdminUser)
                    <a href="{{ route('user.preferences') }}" class="d-none d-sm-inline fs-5 color-black">
                        <i class="fa-regular fa-heart"></i>
                    </a>
                @endif

                <x-carrinho-header />
            </div>
        </div>
    </div>

    <div class="sax-mobile-discovery d-lg-none">
        <button class="sax-mobile-searchbar" id="mobileSearchBar" type="button" aria-label="{{ __('messages.abrir_busca') }}">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span>{{ __('messages.pesquisar') }}</span>
            <i class="fa-solid fa-sliders" aria-hidden="true"></i>
        </button>

        <nav class="sax-mobile-category-nav" aria-label="{{ __('messages.categorias') }}">
            <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
                <i class="fa-solid fa-house" aria-hidden="true"></i>
                <span>{{ __('messages.inicio') }}</span>
            </a>
            @foreach ($mainCategories as $cat)
                <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}">
                    <span>{{ $labelMap[$cat->slug] ?? $cat->name }}</span>
                </a>
            @endforeach
        </nav>
    </div>

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
                <li class="list-inline-item">
                    <a href="{{ route('institucional.index') }}" class="text-institucional">{{ __('messages.institucional') }}</a>
                </li>
                <li class="list-inline-item"><a href="{{ route('bridal.index') }}" class="text-bridal">{{ __('messages.bridal') }}</a></li>
                <li class="list-inline-item"><a href="{{ route('palace.index') }}" class="text-palace">{{ __('messages.palace') }}</a></li>
                <li class="list-inline-item">
                    <a href="{{ route('cafe_bistro.index') }}" class="text-bistro">{{ __('messages.cafe_bistro') }}</a>
                </li>
                <li class="list-inline-item"><a href="{{ route('blogs.index') }}" class="text-muted">{{ __('messages.sax_news_tag') }}</a></li>
            </ul>
        </div>
    </nav>

    <div id="saxDrawer" class="sax-drawer" aria-hidden="true">
        <div class="drawer-header p-3 d-flex justify-content-between align-items-center bg-white">
            <div class="drawer-header-copy">
                <span>SAX</span>
                <strong>{{ __('messages.menu') }}</strong>
            </div>
            <button class="btn-close-drawer" id="closeDrawer" type="button" aria-label="{{ __('messages.fechar') }}"><i class="fa fa-times"></i></button>
        </div>

        <div class="drawer-body">
            <div class="drawer-auth-section p-3">
                @if ($currentUser)
                    <a href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}" class="drawer-user-summary">
                        <span class="user-avatar"><i class="fa fa-user"></i></span>
                        <span class="drawer-user-copy">
                            <small>{{ __('messages.ola') }}</small>
                            <strong>{{ $userName }}</strong>
                            <span>{{ __('messages.minha_conta') }}</span>
                        </span>
                        <i class="fa-solid fa-chevron-right drawer-user-arrow" aria-hidden="true"></i>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="drawer-signout-form">
                        @csrf
                        <button type="submit" class="drawer-signout">
                            <i class="fa fa-sign-out-alt" aria-hidden="true"></i>
                            <span>{{ __('messages.sair') }}</span>
                        </button>
                    </form>
                @else
                    <button class="btn btn-dark w-100 py-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fa fa-sign-in-alt me-2"></i> {{ __('messages.entrar') }}
                    </button>
                @endif
            </div>

            <ul class="list-unstyled mb-0 drawer-navigation">
                <li class="drawer-section-label">{{ __('messages.categorias') }}</li>
                @foreach ($mainCategories as $cat)
                    <li>
                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}" class="drawer-link drawer-link--primary">
                            <span class="drawer-link-icon"><i class="fa-solid {{ $mobileIconMap[$cat->slug] ?? 'fa-tag' }}"></i></span>
                            <span>{{ $labelMap[$cat->slug] ?? $cat->name }}</span>
                            <i class="fa-solid fa-chevron-right drawer-link-arrow"></i>
                        </a>
                    </li>
                @endforeach
                <li class="drawer-preferences"><x-language-selector variant="mobile" /></li>

                <li class="drawer-section-label">SAX Experiences</li>
                <li><a href="{{ route('institucional.index') }}" class="drawer-link"><i class="fa fa-info-circle me-3"></i>{{ __('messages.institucional') }}</a></li>
                <li><a href="{{ route('bridal.index') }}" class="drawer-link"><i class="fa fa-ring me-3"></i>{{ __('messages.bridal') }}</a></li>
                <li><a href="{{ route('palace.index') }}" class="drawer-link"><i class="fa fa-crown me-3"></i>{{ __('messages.sax_palace') }}</a></li>
                <li><a href="{{ route('cafe_bistro.index') }}" class="drawer-link"><i class="fa fa-coffee me-3"></i>{{ __('messages.cafe_bistro') }}</a></li>
                <li><a href="{{ route('blogs.index') }}" class="drawer-link"><i class="fa fa-newspaper me-3"></i>{{ __('messages.sax_news_tag') }}</a></li>
                <li><a href="{{ route('contact.form') }}" class="drawer-link"><i class="fa fa-envelope me-3"></i>{{ __('messages.contato') }}</a></li>
                <li><a href="{{ route('categories.index') }}" class="drawer-link"><i class="fa fa-th me-3"></i>{{ __('messages.categorias') }}</a></li>
                <li><a href="{{ route('brands.index') }}" class="drawer-link"><i class="fa fa-tag me-3"></i>{{ __('messages.nossas_marcas') }}</a></li>
                <li><a href="{{ route('search') }}" class="drawer-link"><i class="fa fa-search me-3"></i>{{ __('messages.pesquisar') }}</a></li>
                <li><a href="{{ route('all-categories.index') }}" class="drawer-link"><i class="fa fa-th me-3"></i>{{ __('messages.categorias_gerais') }}</a></li>
            </ul>
        </div>
    </div>

    <div class="drawer-overlay" id="drawerOverlay"></div>

    <x-search-mobile />
</header>

<nav class="sax-mobile-dock d-lg-none" aria-label="{{ __('messages.menu') }}">
        <a href="{{ route('home') }}" class="{{ request()->routeIs('home') ? 'active' : '' }}">
            <i class="fa-solid fa-house" aria-hidden="true"></i>
            <span>{{ __('messages.inicio') }}</span>
        </a>
        <a href="{{ route('categories.index') }}" class="{{ request()->routeIs('categories.*') ? 'active' : '' }}">
            <i class="fa-solid fa-layer-group" aria-hidden="true"></i>
            <span>{{ __('messages.categorias') }}</span>
        </a>
        <button type="button" id="mobileDockSearch" aria-label="{{ __('messages.abrir_busca') }}">
            <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
            <span>{{ __('messages.pesquisar') }}</span>
        </button>
        @if ($currentUser)
            <a href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}" class="{{ request()->routeIs('user.*') || request()->routeIs('admin.*') ? 'active' : '' }}">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
                <span>{{ __('messages.minha_conta') }}</span>
            </a>
        @else
            <button type="button" data-bs-toggle="modal" data-bs-target="#loginModal">
                <i class="fa-regular fa-user" aria-hidden="true"></i>
                <span>{{ __('messages.entrar') }}</span>
            </button>
        @endif
        <button type="button" id="mobileDockCart" aria-label="{{ __('messages.carrinho') }}">
            <i class="fa-solid fa-bag-shopping" aria-hidden="true"></i>
            <span>{{ __('messages.carrinho') }}</span>
        </button>
</nav>

@include('components.modal-login')
<script>
    window.saxSearchLang = {
        sku_prefix: @json(__('messages.sku_prefix')),
        usd_prefix: @json(__('messages.usd_prefix')),
        no_products: @json(__('messages.nenhum_produto_encontrado')),
    };

    document.addEventListener("DOMContentLoaded", function () {
        const searchInputs = document.querySelectorAll('.search-autocomplete-input');

        searchInputs.forEach(input => {
            const form = input.closest('form');
            const resultsContainer = form.querySelector('.autocomplete-results');
            let timeout = null;

            input.addEventListener('input', function () {
                const query = this.value.trim();
                clearTimeout(timeout);

                if (query.length < 2) {
                    resultsContainer.innerHTML = '';
                    resultsContainer.classList.add('d-none');
                    return;
                }

                timeout = setTimeout(() => {
                    fetch(`/search/autocomplete?q=${encodeURIComponent(query)}`)
                        .then(response => response.json())
                        .then(data => {
                            let html = '';

                            if (data && data.length > 0) {
                                data.forEach(product => {
                                    const secondaryName = (product.external_name && product.external_name !== product.name)
                                        ? `<small class="text-muted d-block" style="font-size: 10px;">${product.external_name}</small>`
                                        : '';

                                    html += `
                                        <a href="${product.url}" class="autocomplete-item">
                                            <img src="${product.photo}" class="autocomplete-img" onerror="this.src='https://placehold.co/50x50?text=SEM+FOTO';">
                                            <div class="autocomplete-info">
                                                <div class="autocomplete-left">
                                                    <span class="autocomplete-title">${product.name}</span>
                                                    ${secondaryName}
                                                    <span class="autocomplete-sku">${window.saxSearchLang.sku_prefix} ${product.sku}</span>
                                                </div>
                                                <div class="autocomplete-right">
                                                    <span class="autocomplete-brand">${product.brand}</span>
                                                    <span class="autocomplete-price">${window.saxSearchLang.usd_prefix} ${product.price}</span>
                                                </div>
                                            </div>
                                        </a>`;
                                });
                                resultsContainer.innerHTML = html;
                                resultsContainer.classList.remove('d-none');
                            } else {
                                resultsContainer.innerHTML = '<div class="p-3 text-center text-muted">' + window.saxSearchLang.no_products + '</div>';
                                resultsContainer.classList.remove('d-none');
                            }
                        })
                        .catch(err => {
                            console.error('Erro na busca:', err);
                            resultsContainer.classList.add('d-none');
                        });
                }, 300);
            });
        });

        document.addEventListener('click', function (e) {
            searchInputs.forEach(input => {
                const form = input.closest('form');
                const resultsContainer = form?.querySelector('.autocomplete-results');
                if (form && resultsContainer && !form.contains(e.target)) {
                    resultsContainer.classList.add('d-none');
                }
            });
        });
    });
</script>
