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

        $currentUser = Auth::check() ? Auth::user() : null;
        $userName = $currentUser ? explode(' ', $currentUser->name)[0] : null;
        $isAdminUser = $currentUser && $currentUser->user_type == 1;
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
                    <a href="{{ route('categories.index') }}">{{ __('messages.categorias') }}</a>
                    <div class="mega-menu-box" style="display: none;">
                        <div class="container text-start">
                            <div class="row py-4">
                                @foreach ($headerCategories as $cat)
                                    <div class="col-md-3 category-col mb-4">
                                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}" class="mega-title">
                                            {{ $cat->name }}
                                        </a>
                                        <ul class="list-unstyled sub-list">
                                            @foreach ($cat->subcategories as $sub)
                                                <li class="subcategory-item">
                                                    <a href="{{ route('subcategories.show', $sub->slug ?? $sub->id) }}">
                                                        {{ $sub->name }}
                                                    </a>
                                                    <div class="filhas-flyout">
                                                        @foreach ($sub->categoriasfilhas as $filha)
                                                            <a href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}">
                                                                {{ $filha->name }}
                                                            </a>
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
                    <a href="{{ route('all-categories.index') }}">{{ __('messages.categorias_gerais') }}</a>
                </li>
            </ul>
        </div>
    </div>

    <div class="sax-header-main container-fluid px-lg-5 py-3 border-top border-bottom bg-white">
        <div class="row align-items-center g-2">
            <div class="col-3 d-lg-none">
                <div class="sax-mobile-actions d-flex align-items-center gap-2">
                    <button class="btn-menu-open" id="mobileMenuBtn" type="button" aria-label="{{ __('messages.abrir_menu') }}">
                        <i class="fa fa-bars"></i>
                    </button>
                    <button class="btn-menu-open" id="mobileSearchBtn" type="button" aria-label="{{ __('messages.abrir_busca') }}">
                        <i class="fa fa-search"></i>
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

    <div id="saxDrawer" class="sax-drawer">
        <div class="drawer-header p-3 d-flex justify-content-between align-items-center bg-white">
            <span class="fw-bold text-uppercase tracking-2">{{ __('messages.menu') }}</span>
            <button class="btn-close-drawer" id="closeDrawer"><i class="fa fa-times"></i></button>
        </div>

        <div class="drawer-body">
            <div class="drawer-auth-section p-3">
                @if ($currentUser)
                    <div class="d-flex align-items-center mb-3">
                        <div class="user-avatar"><i class="fa fa-user"></i></div>
                        <div class="ms-2">
                            <small class="d-block text-muted">{{ __('messages.ola') }}</small>
                            <span class="fw-bold">{{ $userName }}</span>
                        </div>
                    </div>
                    <div class="d-grid gap-2">
                        <a href="{{ $isAdminUser ? route('admin.index') : route('user.dashboard') }}" class="btn btn-dark w-100 rounded-0">
                            <i class="fa fa-cog me-2"></i> {{ __('messages.meu_painel') }}
                        </a>
                        <form action="{{ route('logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger w-100 rounded-0">{{ __('messages.sair') }}</button>
                        </form>
                    </div>
                @else
                    <button class="btn btn-dark w-100 rounded-0 py-3" data-bs-toggle="modal" data-bs-target="#loginModal">
                        <i class="fa fa-sign-in-alt me-2"></i> {{ __('messages.entrar') }}
                    </button>
                @endif
            </div>

            <ul class="list-unstyled mb-0">
                @foreach ($mainCategories as $cat)
                    <li>
                        <a href="{{ route('categories.show', $cat->slug ?? $cat->id) }}" class="drawer-link fw-bold text-uppercase">
                            {{ $labelMap[$cat->slug] ?? $cat->name }}
                        </a>
                    </li>
                @endforeach
                <li><x-language-selector variant="mobile" /></li>

                <hr class="my-2">

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