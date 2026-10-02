@props([
    'request', 'brands' => [], 'categories' => [], 'subcategories' => [], 'categoriasfilhas' => [],
    'sizes' => [], 'sizeGroups' => [], 'colors' => [], 'couponProductCount' => 0,
    'priceBounds' => ['min' => 0, 'max' => 0, 'selected_min' => 0, 'selected_max' => 0],
    'currencyContext' => ['sign' => 'US$', 'step' => .01, 'decimals' => 2],
    'contextFilters' => [], 'clearUrl' => null, 'formAction' => null,
])

@php
    $isOpticalLayout = app(\App\Services\StorefrontLayoutService::class)->effective() === 'vista';
    $selectedSizes = collect((array) $request->input('sizes', []))->filter()->values();
    $selectedColors = collect((array) $request->input('colors', []))->map(fn ($color) => strtoupper($color))->filter()->values();
    $singleFilters = collect([
        'brand' => collect($brands)->firstWhere('id', $request->brand)?->name,
        'category' => $isOpticalLayout ? null : collect($categories)->firstWhere('id', $request->category)?->name,
        'subcategory' => collect($subcategories)->firstWhere('id', $request->subcategory)?->name,
        'categoriasfilhas' => collect($categoriasfilhas)->firstWhere('id', $request->categoriasfilhas)?->name,
    ])->filter();
    $lockedFilterNames = collect(array_keys($contextFilters));
    $activeFilterCount = $singleFilters->except($lockedFilterNames)->count() + $selectedSizes->count() + $selectedColors->count()
        + ($request->boolean('coupon') ? 1 : 0)
        + ($request->filled('min_price') ? 1 : 0) + ($request->filled('max_price') ? 1 : 0);
    $clearUrl = $clearUrl ?: ($request->filled('collection')
        ? route('collections.show', ['collection' => $request->collection])
        : route('search', array_filter(['search' => $request->search])));
    $formAction = $formAction ?: route('search');
    $sizeLabels = collect($sizeGroups)->flatMap(fn ($group) => $group->options)->pluck('label', 'key');
    $numberFormat = fn ($value) => number_format((float) $value, $currencyContext['decimals'], ',', '.');
@endphp

<div class="toolbar-container search-toolbar d-flex justify-content-between align-items-center mb-3">
    <button class="btn-filter-trigger search-filter-button d-flex align-items-center gap-2" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#modalFiltros" aria-controls="modalFiltros">
        <i class="fa-solid fa-sliders" aria-hidden="true"></i>
        <span class="x-small fw-bold text-uppercase tracking-widest">{{ __('messages.todos_filtros') }}</span>
        <span class="search-filter-count {{ $activeFilterCount ? '' : 'd-none' }}" id="search-filter-count">{{ $activeFilterCount }}</span>
    </button>

    <div class="d-flex align-items-center gap-3" id="sortForm">
        <input type="hidden" name="search" value="{{ $request->search }}" data-filter-context>
        @foreach($contextFilters as $contextName => $contextValue)
            <input type="hidden" name="{{ $contextName }}" value="{{ $contextValue }}" data-filter-context>
        @endforeach
        @if($request->filled('collection'))
            <input type="hidden" name="collection" value="{{ $request->collection }}" data-filter-context>
        @endif

        <div class="toolbar-control d-flex align-items-center gap-2">
            <label class="toolbar-label d-none d-md-block mb-0">{{ __('messages.ordenar_por') }}</label>
            <select name="sort_by" data-filter class="form-select toolbar-select">
                <option value="">{{ __('messages.ordenar_padrao') }}</option>
                <option value="latest" @selected($request->sort_by == 'latest')>{{ __('messages.ordenar_ultimo') }}</option>
                <option value="trending" @selected($request->sort_by == 'trending')>Mais vistos</option>
                <option value="price_low" @selected($request->sort_by == 'price_low')>{{ __('messages.ordenar_menor_preco') }}</option>
                <option value="price_high" @selected($request->sort_by == 'price_high')>{{ __('messages.ordenar_maior_preco') }}</option>
                <option value="name_az" @selected($request->sort_by == 'name_az')>A–Z</option>
            </select>
        </div>

        <div class="toolbar-control d-flex align-items-center gap-2">
            <label class="toolbar-label d-none d-md-block mb-0">{{ __('messages.mostrar') }}</label>
            <select name="per_page" data-filter class="form-select toolbar-select" style="width: 68px;">
                <option value="36" @selected((int) $request->per_page === 36)>36</option>
                <option value="72" @selected((int) $request->per_page === 72)>72</option>
                <option value="100" @selected((int) $request->per_page === 100)>100</option>
            </select>
        </div>
    </div>
</div>

<div class="search-discovery" aria-label="Sugestões de filtros">
    <div class="search-discovery__label"><i class="fa-regular fa-compass"></i> Explore também</div>
    <div class="search-discovery__items">
        <button type="button" data-filter-suggestion data-name="sort_by" data-value="trending">Mais procurados</button>
        <button type="button" data-filter-suggestion data-name="sort_by" data-value="latest">Novidades</button>
        @unless($lockedFilterNames->contains('brand'))
            @foreach(collect($brands)->take(4) as $brand)
                <button type="button" data-filter-suggestion data-name="brand" data-value="{{ $brand->id }}">{{ $brand->name }}</button>
            @endforeach
        @endunless
        @unless($isOpticalLayout || $lockedFilterNames->contains('category'))
            @foreach(collect($categories)->take(3) as $category)
                <button type="button" data-filter-suggestion data-name="category" data-value="{{ $category->id }}">{{ $category->name }}</button>
            @endforeach
        @endunless
        @unless($lockedFilterNames->contains('subcategory'))
            @foreach(collect($subcategories)->take(3) as $subcategory)
                <button type="button" data-filter-suggestion data-name="subcategory" data-value="{{ $subcategory->id }}">{{ $subcategory->name }}</button>
            @endforeach
        @endunless
    </div>
</div>

<div class="search-active-filters {{ $activeFilterCount ? '' : 'd-none' }}" id="search-active-filters" aria-live="polite">
    <span class="search-active-filters__title">Filtros selecionados</span>
    <div class="search-active-filters__chips" id="search-active-filter-chips">
        @foreach($singleFilters->except($lockedFilterNames) as $name => $label)
            <button type="button" data-remove-filter="{{ $name }}"><span>{{ $label }}</span><i class="fa-solid fa-xmark"></i></button>
        @endforeach
        @foreach($selectedSizes as $size)
            <button type="button" data-remove-filter="sizes[]" data-filter-value="{{ $size }}"><span>{{ $sizeLabels[$size] ?? $size }}</span><i class="fa-solid fa-xmark"></i></button>
        @endforeach
        @foreach($selectedColors as $color)
            <button type="button" data-remove-filter="colors[]" data-filter-value="{{ $color }}"><span>Cor</span><span class="search-chip-swatch" style="--chip-color: {{ str_starts_with($color, '#') ? $color : '#'.$color }}"></span><i class="fa-solid fa-xmark"></i></button>
        @endforeach
        @if($request->filled('min_price'))
            <button type="button" data-remove-filter="min_price"><span>A partir de {{ $currencyContext['sign'] }} {{ $numberFormat($request->min_price) }}</span><i class="fa-solid fa-xmark"></i></button>
        @endif
        @if($request->filled('max_price'))
            <button type="button" data-remove-filter="max_price"><span>Até {{ $currencyContext['sign'] }} {{ $numberFormat($request->max_price) }}</span><i class="fa-solid fa-xmark"></i></button>
        @endif
        @if($request->boolean('coupon'))
            <button type="button" data-remove-filter="coupon"><span>Com cupom vigente</span><i class="fa-solid fa-xmark"></i></button>
        @endif
    </div>
    <a href="{{ $clearUrl }}" class="search-active-filters__clear">Limpar todos</a>
</div>

<div class="offcanvas offcanvas-end offcanvas-filter search-filter-drawer" tabindex="-1" id="modalFiltros">
    <div class="offcanvas-header search-filter-header">
        <div>
            <span class="search-filter-eyebrow">Catálogo</span>
            <h5 class="offcanvas-title mb-0">{{ __('messages.filtrar_por') }}</h5>
            <small>Os resultados mudam enquanto você ajusta.</small>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>

    <div class="offcanvas-body search-filter-body">
        <form action="{{ $formAction }}" method="GET" id="filterSidebarForm" data-currency-sign="{{ $currencyContext['sign'] }}">
            <input type="hidden" name="search" value="{{ $request->search }}" data-filter-context>
            @foreach($contextFilters as $contextName => $contextValue)
                <input type="hidden" name="{{ $contextName }}" value="{{ $contextValue }}" data-filter-context>
            @endforeach
            @if($request->filled('collection'))
                <input type="hidden" name="collection" value="{{ $request->collection }}" data-filter-context>
            @endif

            @php
                $filterFields = [
                    ['label' => __('messages.marca'), 'input' => 'brand-input', 'hidden' => 'brand-id', 'name' => 'brand', 'listId' => 'list-brands', 'items' => $brands],
                    ['label' => __('messages.subcategoria'), 'input' => 'subcategory-input', 'hidden' => 'subcategory-id', 'name' => 'subcategory', 'listId' => 'list-subcategories', 'items' => $subcategories],
                    ['label' => __('messages.categoria_filha'), 'input' => 'child-category-input', 'hidden' => 'child-category-id', 'name' => 'categoriasfilhas', 'listId' => 'list-child-categories', 'items' => $categoriasfilhas],
                ];
                if (! $isOpticalLayout) {
                    array_splice($filterFields, 1, 0, [[
                        'label' => __('messages.categoria'), 'input' => 'category-input', 'hidden' => 'category-id',
                        'name' => 'category', 'listId' => 'list-categories', 'items' => $categories,
                    ]]);
                }
                $filterFields = collect($filterFields)
                    ->reject(fn ($field) => $lockedFilterNames->contains($field['name']))
                    ->values();
            @endphp

            <section class="search-filter-section">
                <h6>Produto</h6>
                @foreach ($filterFields as $f)
                    <div class="search-filter-group">
                        <label for="{{ $f['input'] }}">{{ $f['label'] }}</label>
                        <div class="search-filter-combobox">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input type="text" id="{{ $f['input'] }}" class="form-control sax-filter-input"
                                   placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                                   value="{{ collect($f['items'])->firstWhere('id', $request->{$f['name']})?->name ?? '' }}"
                                   data-filter-combobox data-options="{{ $f['listId'] }}" aria-controls="{{ $f['listId'] }}"
                                   aria-expanded="false" autocomplete="off">
                            <button type="button" class="search-filter-clear" aria-label="Limpar {{ $f['label'] }}" tabindex="-1"><i class="fa-solid fa-xmark"></i></button>
                        </div>
                        <input type="hidden" id="{{ $f['hidden'] }}" name="{{ $f['name'] }}" data-filter
                               data-filter-label="{{ $f['label'] }}"
                               data-selected-label="{{ collect($f['items'])->firstWhere('id', $request->{$f['name']})?->name ?? '' }}"
                               value="{{ $request->{$f['name']} }}">
                        <div class="search-filter-options" id="{{ $f['listId'] }}" role="listbox">
                            @foreach ($f['items'] as $item)
                                <button type="button" role="option" data-id="{{ $item->id }}" data-value="{{ $item->name }}">{{ $item->name }}</button>
                            @endforeach
                            <span class="search-filter-no-results">Nenhuma opção encontrada</span>
                        </div>
                    </div>
                @endforeach
            </section>

            <div id="search-dynamic-facets">
                @include('search.partials.dynamic-facets', [
                    'request' => $request,
                    'sizeGroups' => $sizeGroups,
                    'colors' => $colors,
                    'couponProductCount' => $couponProductCount,
                    'priceBounds' => $priceBounds,
                    'currencyContext' => $currencyContext,
                ])
            </div>

            <aside class="search-shipping-note">
                <i class="fa-solid fa-truck-fast"></i>
                <div><b>Frete calculado para você</b><span>Prazo e valor são confirmados no carrinho conforme o destino. Entregas internacionais usam DHL quando disponível.</span></div>
            </aside>

            <div class="search-filter-actions">
                <button type="submit" class="btn btn-dark search-filter-apply">Ver resultados</button>
                <a href="{{ $clearUrl }}" class="search-filter-reset"><i class="fa-solid fa-rotate-left"></i> {{ __('messages.limpar_tudo') }}</a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const setupCombobox = (inputId, hiddenId, optionsId) => {
        const input = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const options = document.getElementById(optionsId);
        if (!input || !hidden || !options) return;
        const choices = [...options.querySelectorAll('[data-id]')];
        const clearButton = input.closest('.search-filter-combobox')?.querySelector('.search-filter-clear');
        const close = () => { options.classList.remove('is-open'); input.setAttribute('aria-expanded', 'false'); };
        const filter = () => {
            const term = input.value.trim().toLocaleLowerCase();
            let visible = 0;
            choices.forEach(choice => {
                const show = choice.dataset.value.toLocaleLowerCase().includes(term) && visible < 60;
                choice.hidden = !show;
                if (show) visible++;
            });
            options.querySelector('.search-filter-no-results').hidden = visible > 0;
            options.classList.add('is-open');
            input.setAttribute('aria-expanded', 'true');
        };
        input.addEventListener('input', function () {
            const match = choices.find(choice => choice.dataset.value === this.value);
            hidden.value = match ? match.dataset.id : '';
            hidden.dataset.selectedLabel = match ? match.dataset.value : '';
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            clearButton?.classList.toggle('is-visible', Boolean(this.value));
            filter();
        });
        input.addEventListener('focus', filter);
        choices.forEach(choice => choice.addEventListener('click', () => {
            input.value = choice.dataset.value;
            hidden.value = choice.dataset.id;
            hidden.dataset.selectedLabel = choice.dataset.value;
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            clearButton?.classList.add('is-visible');
            close();
        }));
        clearButton?.classList.toggle('is-visible', Boolean(input.value));
        clearButton?.addEventListener('click', () => {
            input.value = ''; hidden.value = ''; hidden.dataset.selectedLabel = '';
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            clearButton.classList.remove('is-visible'); input.focus(); filter();
        });
        document.addEventListener('click', event => { if (!input.closest('.search-filter-group')?.contains(event.target)) close(); });
        input.addEventListener('keydown', event => { if (event.key === 'Escape') close(); });
    };

    setupCombobox('brand-input', 'brand-id', 'list-brands');
    @unless($isOpticalLayout || $lockedFilterNames->contains('category'))
    setupCombobox('category-input', 'category-id', 'list-categories');
    @endunless
    setupCombobox('subcategory-input', 'subcategory-id', 'list-subcategories');
    setupCombobox('child-category-input', 'child-category-id', 'list-child-categories');

});
</script>
