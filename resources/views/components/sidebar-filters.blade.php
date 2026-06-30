@props(['request', 'brands' => [], 'categories' => [], 'subcategories' => [], 'categoriasfilhas' => []])

@php
    use App\Models\Currency;
    $currentCurrencyId = session('currency', Currency::where('is_default', 1)->first()?->id);
    $currencySign      = Currency::find($currentCurrencyId)?->sign ?? '$';
@endphp

<div class="toolbar-container d-flex justify-content-between align-items-center py-3 mb-4 border-top border-bottom">

    <button class="btn-filter-trigger d-flex align-items-center gap-2" type="button"
            data-bs-toggle="offcanvas" data-bs-target="#modalFiltros">
        <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
            <path d="M0 1H18M0 6H12M0 11H18" stroke="currentColor" stroke-width="1.5"/>
        </svg>
        <span class="x-small fw-bold text-uppercase tracking-widest">{{ __('messages.todos_filtros') }}</span>
    </button>

    <div class="d-flex align-items-center gap-3" id="sortForm">
        <input type="hidden" name="search"           data-filter value="{{ $request->search }}">
        <input type="hidden" name="brand"            data-filter value="{{ $request->brand }}">
        <input type="hidden" name="category"         data-filter value="{{ $request->category }}">
        <input type="hidden" name="subcategory"      data-filter value="{{ $request->subcategory }}">
        <input type="hidden" name="categoriasfilhas" data-filter value="{{ $request->categoriasfilhas }}">
        <input type="hidden" name="min_price"        data-filter value="{{ $request->min_price }}">
        <input type="hidden" name="max_price"        data-filter value="{{ $request->max_price }}">

        <div class="d-flex align-items-center gap-2">
            <label class="toolbar-label d-none d-md-block mb-0">{{ __('messages.ordenar_por') }}</label>
            <select name="sort_by" data-filter class="form-select toolbar-select">
                <option value="">{{ __('messages.ordenar_padrao') }}</option>
                <option value="latest"     @selected($request->sort_by == 'latest')>{{ __('messages.ordenar_ultimo') }}</option>
                <option value="price_low"  @selected($request->sort_by == 'price_low')>{{ __('messages.ordenar_menor_preco') }}</option>
                <option value="price_high" @selected($request->sort_by == 'price_high')>{{ __('messages.ordenar_maior_preco') }}</option>
                <option value="name_az"    @selected($request->sort_by == 'name_az')>A–Z</option>
            </select>
        </div>

        <div class="d-flex align-items-center gap-2 border-start ps-3">
            <label class="toolbar-label d-none d-md-block mb-0">{{ __('messages.mostrar') }}</label>
            <select name="per_page" data-filter class="form-select toolbar-select" style="width: 68px;">
                <option value="36"  @selected($request->per_page == 36)>36</option>
                <option value="72"  @selected($request->per_page == 72)>72</option>
                <option value="102" @selected($request->per_page == 102)>100</option>
            </select>
        </div>
    </div>
</div>

<div class="offcanvas offcanvas-end offcanvas-filter" tabindex="-1" id="modalFiltros">
    <div class="offcanvas-header px-4 py-4 border-bottom">
        <h5 class="offcanvas-title x-small fw-bold text-uppercase tracking-widest mb-0">{{ __('messages.filtrar_por') }}</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas" aria-label="Fechar"></button>
    </div>

    <div class="offcanvas-body px-4 py-4">
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
                <div class="mb-5">
                    <label class="x-small fw-bold text-uppercase tracking-widest d-block mb-2">{{ $f['label'] }}</label>
                    <input type="text"
                           id="{{ $f['input'] }}"
                           list="{{ $f['listId'] }}"
                           class="form-control sax-filter-input"
                           placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                           value="{{ collect($f['items'])->firstWhere('id', $request->{$f['name']})?->name ?? '' }}"
                           autocomplete="off">
                    <input type="hidden" id="{{ $f['hidden'] }}" name="{{ $f['name'] }}" data-filter value="{{ $request->{$f['name']} }}">
                    <datalist id="{{ $f['listId'] }}">
                        @foreach ($f['items'] as $item)
                            <option data-id="{{ $item->id }}" value="{{ $item->name }}">
                        @endforeach
                    </datalist>
                </div>
            @endforeach

            <div class="mb-5">
                <label class="x-small fw-bold text-uppercase tracking-widest d-block mb-2">
                    {{ __('messages.preco') }} ({{ $currencySign }})
                </label>
                <div class="d-flex gap-2">
                    <input type="number" name="min_price" data-filter class="form-control sax-filter-input" placeholder="MIN" value="{{ $request->min_price }}">
                    <input type="number" name="max_price" data-filter class="form-control sax-filter-input" placeholder="MAX" value="{{ $request->max_price }}">
                </div>
            </div>

            <div class="d-grid gap-2">
                <button type="submit" class="btn btn-dark rounded-0 py-3 x-small fw-bold text-uppercase tracking-widest">
                    {{ __('messages.aplicar_filtros') }}
                </button>
                <a href="{{ route('search', ['search' => $request->search]) }}"
                   class="btn btn-link text-dark text-decoration-none text-center x-small text-uppercase tracking-widest mt-1">
                    {{ __('messages.limpar_tudo') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const syncDatalist = (inputId, hiddenId, datalistId) => {
        const input    = document.getElementById(inputId);
        const hidden   = document.getElementById(hiddenId);
        const datalist = document.getElementById(datalistId);
        if (!input || !hidden || !datalist) return;

        input.addEventListener('input', function () {
            const match  = [...datalist.options].find(o => o.value === this.value);
            hidden.value = match ? match.getAttribute('data-id') : '';
            hidden.dispatchEvent(new Event('input', { bubbles: true }));
        });

        input.addEventListener('blur', function () {
            if (!this.value) {
                hidden.value = '';
                hidden.dispatchEvent(new Event('input', { bubbles: true }));
            }
        });
    };

    syncDatalist('brand-input',         'brand-id',         'list-brands');
    syncDatalist('category-input',      'category-id',      'list-categories');
    syncDatalist('subcategory-input',   'subcategory-id',   'list-subcategories');
    syncDatalist('child-category-input','child-category-id','list-child-categories');
});
</script>
