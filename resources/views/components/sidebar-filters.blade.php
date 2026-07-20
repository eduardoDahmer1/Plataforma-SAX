@props(['request', 'brands' => [], 'categories' => [], 'subcategories' => [], 'categoriasfilhas' => []])

@php
    use App\Models\Currency;
    $currentCurrencyId = session('currency', Currency::where('is_default', 1)->first()?->id);
    $currencySign      = Currency::find($currentCurrencyId)?->sign ?? '$';
    $activeFilterCount = collect([
        $request->brand, $request->category, $request->subcategory,
        $request->categoriasfilhas, $request->min_price, $request->max_price,
    ])->filter(fn ($value) => filled($value))->count();
@endphp

<div class="toolbar-container search-toolbar d-flex justify-content-between align-items-center mb-4">

    <button class="btn-filter-trigger search-filter-button d-flex align-items-center gap-2" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#modalFiltros">
        <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 1H18M0 6H12M0 11H18" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <span class="x-small fw-bold text-uppercase tracking-widest">{{ __('messages.todos_filtros') }}</span>
        @if($activeFilterCount)
            <span class="search-filter-count">{{ $activeFilterCount }}</span>
        @endif
    </button>

    <div class="d-flex align-items-center gap-3" id="sortForm">
        <input type="hidden" name="search"           data-filter value="{{ $request->search }}">
        <input type="hidden" name="brand"            data-filter value="{{ $request->brand }}">
        <input type="hidden" name="category"         data-filter value="{{ $request->category }}">
        <input type="hidden" name="subcategory"      data-filter value="{{ $request->subcategory }}">
        <input type="hidden" name="categoriasfilhas" data-filter value="{{ $request->categoriasfilhas }}">
        <input type="hidden" name="min_price"        data-filter value="{{ $request->min_price }}">
        <input type="hidden" name="max_price"        data-filter value="{{ $request->max_price }}">

        <div class="toolbar-control d-flex align-items-center gap-2">
            <label class="toolbar-label d-none d-md-block mb-0">{{ __('messages.ordenar_por') }}</label>
            <select name="sort_by" data-filter class="form-select toolbar-select">
                <option value="">{{ __('messages.ordenar_padrao') }}</option>
                <option value="latest"     @selected($request->sort_by == 'latest')>{{ __('messages.ordenar_ultimo') }}</option>
                <option value="price_low"  @selected($request->sort_by == 'price_low')>{{ __('messages.ordenar_menor_preco') }}</option>
                <option value="price_high" @selected($request->sort_by == 'price_high')>{{ __('messages.ordenar_maior_preco') }}</option>
                <option value="name_az"    @selected($request->sort_by == 'name_az')>A–Z</option>
            </select>
        </div>

        <div class="toolbar-control d-flex align-items-center gap-2">
            <label class="toolbar-label d-none d-md-block mb-0">{{ __('messages.mostrar') }}</label>
            <select name="per_page" data-filter class="form-select toolbar-select" style="width: 68px;">
                <option value="36"  @selected($request->per_page == 36)>36</option>
                <option value="72"  @selected($request->per_page == 72)>72</option>
                <option value="100" @selected($request->per_page == 100)>100</option>
            </select>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end offcanvas-filter search-filter-drawer" tabindex="-1" id="modalFiltros">
    <div class="offcanvas-header search-filter-header">
        <div>
            <span class="search-filter-eyebrow">Catálogo</span>
            <h5 class="offcanvas-title mb-0">{{ __('messages.filtrar_por') }}</h5>
        </div>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>

    <div class="offcanvas-body search-filter-body">
        <form action="{{ route('search') }}" method="GET" id="filterSidebarForm">
            <input type="hidden" name="sort_by"  value="{{ $request->sort_by }}">
            <input type="hidden" name="per_page" value="{{ $request->per_page }}">
            <input type="hidden" name="search"   data-filter value="{{ $request->search }}">

            @php
                $filterFields = [
                    ['label' => __('messages.marca'),           'input' => 'brand-input',         'hidden' => 'brand-id',         'name' => 'brand',           'listId' => 'list-brands',           'items' => $brands],
                    ['label' => __('messages.categoria'),       'input' => 'category-input',      'hidden' => 'category-id',      'name' => 'category',        'listId' => 'list-categories',       'items' => $categories],
                    ['label' => __('messages.subcategoria'),    'input' => 'subcategory-input',   'hidden' => 'subcategory-id',   'name' => 'subcategory',     'listId' => 'list-subcategories',    'items' => $subcategories],
                    ['label' => __('messages.categoria_filha'), 'input' => 'child-category-input','hidden' => 'child-category-id','name' => 'categoriasfilhas','listId' => 'list-child-categories', 'items' => $categoriasfilhas],
                ];
            @endphp

            @foreach ($filterFields as $f)
                <div class="search-filter-group">
                    <label for="{{ $f['input'] }}">{{ $f['label'] }}</label>
                    <div class="search-filter-combobox">
                        <i class="fa-solid fa-magnifying-glass"></i>
                        <input type="text"
                               id="{{ $f['input'] }}"
                               class="form-control sax-filter-input"
                               placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                               value="{{ collect($f['items'])->firstWhere('id', $request->{$f['name']})?->name ?? '' }}"
                               data-filter-combobox
                               data-options="{{ $f['listId'] }}"
                               aria-controls="{{ $f['listId'] }}"
                               aria-expanded="false"
                               autocomplete="off">
                        <button type="button" class="search-filter-clear" aria-label="Limpar {{ $f['label'] }}" tabindex="-1"><i class="fa-solid fa-xmark"></i></button>
                    </div>
                    <input type="hidden" id="{{ $f['hidden'] }}" name="{{ $f['name'] }}" data-filter value="{{ $request->{$f['name']} }}">
                    <div class="search-filter-options" id="{{ $f['listId'] }}" role="listbox">
                        @foreach ($f['items'] as $item)
                            <button type="button" role="option" data-id="{{ $item->id }}" data-value="{{ $item->name }}">{{ $item->name }}</button>
                        @endforeach
                        <span class="search-filter-no-results">Nenhuma opção encontrada</span>
                    </div>
                </div>
            @endforeach

            <div class="search-filter-group">
                <label>
                    {{ __('messages.preco') }} ({{ $currencySign }})
                </label>
                <div class="search-price-grid">
                    <div><span>De</span><input type="number" name="min_price" data-filter class="form-control sax-filter-input" placeholder="0" value="{{ $request->min_price }}"></div>
                    <div><span>Até</span><input type="number" name="max_price" data-filter class="form-control sax-filter-input" placeholder="Sem limite" value="{{ $request->max_price }}"></div>
                </div>
            </div>

            <div class="search-filter-actions">
                <button type="submit" class="btn btn-dark search-filter-apply">
                    {{ __('messages.aplicar_filtros') }}
                </button>
                <a href="{{ route('search', ['search' => $request->search]) }}"
                   class="search-filter-reset">
                    <i class="fa-solid fa-rotate-left"></i> {{ __('messages.limpar_tudo') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const setupCombobox = (inputId, hiddenId, optionsId) => {
        const input    = document.getElementById(inputId);
        const hidden   = document.getElementById(hiddenId);
        const options  = document.getElementById(optionsId);
        if (!input || !hidden || !options) return;

        const choices = [...options.querySelectorAll('[data-id]')];
        const clearButton = input.closest('.search-filter-combobox')?.querySelector('.search-filter-clear');

        const close = () => {
            options.classList.remove('is-open');
            input.setAttribute('aria-expanded', 'false');
        };

        const filter = () => {
            const term = input.value.trim().toLocaleLowerCase();
            let visible = 0;
            choices.forEach(choice => {
                const matches = choice.dataset.value.toLocaleLowerCase().includes(term);
                const show = matches && visible < 60;
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
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            clearButton?.classList.toggle('is-visible', Boolean(this.value));
            filter();
        });

        input.addEventListener('focus', filter);
        choices.forEach(choice => choice.addEventListener('click', () => {
            input.value = choice.dataset.value;
            hidden.value = choice.dataset.id;
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            clearButton?.classList.add('is-visible');
            close();
        }));
        clearButton?.classList.toggle('is-visible', Boolean(input.value));
        clearButton?.addEventListener('click', () => {
            input.value = '';
            hidden.value = '';
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
            clearButton.classList.remove('is-visible');
            input.focus();
            filter();
        });
        document.addEventListener('click', event => {
            if (!input.closest('.search-filter-group')?.contains(event.target)) close();
        });
        input.addEventListener('keydown', event => {
            if (event.key === 'Escape') close();
        });
    };

    setupCombobox('brand-input',         'brand-id',         'list-brands');
    setupCombobox('category-input',      'category-id',      'list-categories');
    setupCombobox('subcategory-input',   'subcategory-id',   'list-subcategories');
    setupCombobox('child-category-input','child-category-id','list-child-categories');
});
</script>
