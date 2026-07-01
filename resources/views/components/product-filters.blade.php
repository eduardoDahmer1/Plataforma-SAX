@props(['categories', 'brands', 'currentCategory' => null, 'currentSub' => null, 'currentChild' => null])

@php
    $curCatId = is_object($currentCategory) ? $currentCategory->id : $currentCategory;
    $curSubId = is_object($currentSub) ? $currentSub->id : $currentSub;
    $curChildId = is_object($currentChild) ? $currentChild->id : $currentChild;
    $uid = uniqid('pf_', false);
@endphp

<div class="product-filters-wrapper" id="{{ $uid }}" data-filter-root>
    <h6 class="filter-title">Filtrar</h6>

    <div class="filter-group mb-4">
        <label class="filter-label" for="{{ $uid }}_category_search">Categorias</label>

        <div class="filter-search-wrap mb-2">
            <input
                type="text"
                id="{{ $uid }}_category_search"
                class="filter-search-input"
                data-filter-search="category"
                placeholder="Buscar categoria, subcategoria ou filha">
            <i class="fas fa-search filter-search-icon" aria-hidden="true"></i>
        </div>

        <div class="accordion accordion-flush" id="accordionFilters_{{ $uid }}" data-filter-block="category">
            @foreach($categories as $cat)
                <div class="accordion-item border-0 bg-transparent" data-filter-item data-filter-name="{{ mb_strtolower($cat->name) }}">
                    <div class="d-flex align-items-center justify-content-between">
                        <a
                            href="{{ route('categories.show', $cat->slug ?? $cat->id) }}"
                            class="filter-link filter-link-main flex-grow-1 {{ $curCatId == $cat->id ? 'active fw-bold' : '' }}"
                            data-prefetch>
                            {{ $cat->name }}
                        </a>

                        @if($cat->subcategories->count() > 0)
                            <button
                                class="btn btn-link btn-sm p-0 text-muted accordion-trigger {{ ($curCatId == $cat->id || (isset($currentSub->category_id) && $currentSub->category_id == $cat->id)) ? '' : 'collapsed' }}"
                                type="button"
                                data-bs-toggle="collapse"
                                data-bs-target="#collapse-cat-{{ $uid }}-{{ $cat->id }}"
                                aria-label="Expandir {{ $cat->name }}">
                                <i class="fas fa-chevron-down small-icon"></i>
                            </button>
                        @endif
                    </div>

                    @if($cat->subcategories->count() > 0)
                        <div
                            id="collapse-cat-{{ $uid }}-{{ $cat->id }}"
                            class="collapse {{ ($curCatId == $cat->id || (isset($currentSub->category_id) && $currentSub->category_id == $cat->id)) ? 'show' : '' }}"
                            data-bs-parent="#accordionFilters_{{ $uid }}">

                            <ul class="list-unstyled ms-2 mt-1 border-start ps-2">
                                @foreach($cat->subcategories as $sub)
                                    <li class="mb-1" data-filter-item data-filter-name="{{ mb_strtolower($sub->name) }}">
                                        <div class="d-flex align-items-center justify-content-between">
                                            <a
                                                href="{{ route('subcategories.show', $sub->slug ?? $sub->id) }}"
                                                class="filter-link filter-link-sub {{ $curSubId == $sub->id ? 'active fw-bold text-dark' : '' }}"
                                                data-prefetch>
                                                {{ $sub->name }}
                                            </a>

                                            @if($sub->categoriasfilhas->count() > 0)
                                                <button
                                                    class="btn btn-link btn-sm p-0 text-muted accordion-trigger {{ ($curSubId == $sub->id || (isset($currentChild->subcategory_id) && $currentChild->subcategory_id == $sub->id)) ? '' : 'collapsed' }}"
                                                    type="button"
                                                    data-bs-toggle="collapse"
                                                    data-bs-target="#collapse-sub-{{ $uid }}-{{ $sub->id }}"
                                                    aria-label="Expandir {{ $sub->name }}">
                                                    <i class="fas fa-chevron-down extra-small-icon"></i>
                                                </button>
                                            @endif
                                        </div>

                                        @if($sub->categoriasfilhas->count() > 0)
                                            <div
                                                id="collapse-sub-{{ $uid }}-{{ $sub->id }}"
                                                class="collapse {{ ($curSubId == $sub->id || (isset($currentChild->subcategory_id) && $currentChild->subcategory_id == $sub->id)) ? 'show' : '' }}">
                                                <ul class="list-unstyled ms-3 mt-1">
                                                    @foreach($sub->categoriasfilhas as $filha)
                                                        <li data-filter-item data-filter-name="{{ mb_strtolower($filha->name) }}">
                                                            <a
                                                                href="{{ route('categorias-filhas.show', $filha->slug ?? $filha->id) }}"
                                                                class="filter-link filter-link-child {{ $curChildId == $filha->id ? 'active fw-bold text-dark opacity-100' : '' }}"
                                                                data-prefetch>
                                                                {{ $filha->name }}
                                                            </a>
                                                        </li>
                                                    @endforeach
                                                </ul>
                                            </div>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    @endif
                </div>
            @endforeach

            <div class="filter-empty d-none" data-filter-empty="category">
                Nenhuma categoria encontrada.
            </div>
        </div>
    </div>

    @if(isset($brands) && $brands->count() > 0)
        <div class="filter-group mb-0 border-top pt-3">
            <label class="filter-label" for="{{ $uid }}_brand_search">Marcas</label>

            <div class="filter-search-wrap mb-2">
                <input
                    type="text"
                    id="{{ $uid }}_brand_search"
                    class="filter-search-input"
                    data-filter-search="brand"
                    placeholder="Buscar marca">
                <i class="fas fa-search filter-search-icon" aria-hidden="true"></i>
            </div>

            <div class="brand-filter-scroll" data-filter-block="brand">
                <ul class="list-unstyled mb-0">
                    @foreach($brands as $brand)
                        <li class="mb-1" data-filter-item data-filter-name="{{ mb_strtolower($brand->name) }}">
                            <a
                                href="{{ route('brands.show', $brand->slug) }}"
                                class="filter-link filter-link-brand d-block py-1"
                                data-prefetch>
                                {{ $brand->name }}
                            </a>
                        </li>
                    @endforeach
                </ul>

                <div class="filter-empty d-none" data-filter-empty="brand">
                    Nenhuma marca encontrada.
                </div>
            </div>
        </div>
    @endif
</div>

<script>
    (function () {
        var root = document.getElementById('{{ $uid }}');
        if (!root || root.dataset.ready === '1') {
            return;
        }
        root.dataset.ready = '1';

        var normalize = function (value) {
            return (value || '')
                .toString()
                .normalize('NFD')
                .replace(/[\u0300-\u036f]/g, '')
                .toLowerCase()
                .trim();
        };

        var applyFilter = function (kind, term) {
            var block = root.querySelector('[data-filter-block="' + kind + '"]');
            if (!block) return;

            var needle = normalize(term);
            var items = block.querySelectorAll('[data-filter-item]');
            var shown = 0;

            items.forEach(function (item) {
                var name = normalize(item.getAttribute('data-filter-name'));
                var match = !needle || name.indexOf(needle) !== -1;
                item.classList.toggle('d-none', !match);
                if (match) shown += 1;
            });

            var empty = root.querySelector('[data-filter-empty="' + kind + '"]');
            if (empty) {
                empty.classList.toggle('d-none', shown > 0);
            }
        };

        var prefetchCache = new Set();
        var prefetch = function (href) {
            if (!href || prefetchCache.has(href)) return;
            if (!href.startsWith(window.location.origin) && !href.startsWith('/')) return;

            prefetchCache.add(href);
            var link = document.createElement('link');
            link.rel = 'prefetch';
            link.href = href;
            link.as = 'document';
            document.head.appendChild(link);
        };

        root.querySelectorAll('[data-filter-search]').forEach(function (input) {
            input.addEventListener('input', function (event) {
                applyFilter(input.getAttribute('data-filter-search'), event.target.value);
            }, { passive: true });
        });

        root.querySelectorAll('a[data-prefetch]').forEach(function (anchor, index) {
            anchor.addEventListener('mouseenter', function () {
                prefetch(anchor.href);
            }, { passive: true });

            anchor.addEventListener('touchstart', function () {
                prefetch(anchor.href);
            }, { passive: true });

            if (index < 8) {
                var idleCb = window.requestIdleCallback || function (cb) { return setTimeout(cb, 150); };
                idleCb(function () {
                    prefetch(anchor.href);
                });
            }
        });
    })();
</script>

<style>
    .product-filters-wrapper {
        padding: 2px;
    }

    .filter-title {
        margin: 0 0 14px;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: 1.8px;
        font-weight: 700;
        color: #111;
    }

    .filter-label {
        display: block;
        font-size: 10px;
        text-transform: uppercase;
        letter-spacing: 1.2px;
        font-weight: 700;
        color: #6f6b63;
        margin-bottom: 8px;
    }

    .filter-search-wrap {
        position: relative;
    }

    .filter-search-input {
        width: 100%;
        border: 1px solid #e6e2da;
        background: #f7f5f2;
        border-radius: 10px;
        height: 34px;
        font-size: 11px;
        letter-spacing: 0.3px;
        padding: 0 30px 0 10px;
        color: #29251f;
        transition: border-color 0.18s ease, background-color 0.18s ease;
    }

    .filter-search-input:focus {
        outline: none;
        border-color: #cbc3b6;
        background: #fff;
    }

    .filter-search-icon {
        position: absolute;
        right: 10px;
        top: 10px;
        font-size: 11px;
        color: #999183;
    }

    .filter-link {
        font-size: 11px;
        text-transform: uppercase;
        color: #716a5f;
        text-decoration: none;
        transition: color 0.16s ease, transform 0.16s ease;
        letter-spacing: 0.45px;
        line-height: 1.35;
    }

    .filter-link:hover,
    .filter-link.active {
        color: #111;
        transform: translateX(2px);
    }

    .filter-link-main {
        font-size: 11px;
    }

    .filter-link-sub {
        font-size: 10px;
        color: #5f5950;
    }

    .filter-link-child {
        font-size: 10px;
        color: #8a8376;
        display: block;
        padding: 2px 0;
    }

    .filter-link-child::before {
        content: '•';
        margin-right: 6px;
        color: #b3ac9f;
    }

    .filter-link-brand {
        font-size: 10px;
    }

    .filter-empty {
        font-size: 10px;
        color: #8a8376;
        padding: 8px 0;
    }

    .border-start {
        border-left: 1px solid #ede8de !important;
    }

    .accordion-trigger i {
        transition: transform 0.2s ease;
        font-size: 0.68rem;
    }

    .accordion-trigger.collapsed i {
        transform: rotate(-90deg);
    }

    .small-icon {
        font-size: 0.68rem;
    }

    .extra-small-icon {
        font-size: 0.53rem;
    }

    .brand-filter-scroll {
        max-height: 260px;
        overflow-y: auto;
        padding-right: 2px;
    }

    .brand-filter-scroll::-webkit-scrollbar {
        width: 3px;
    }

    .brand-filter-scroll::-webkit-scrollbar-thumb {
        background: #d5cec2;
        border-radius: 10px;
    }
</style>