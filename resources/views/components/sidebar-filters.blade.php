@props(['request', 'brands' => [], 'categories' => [], 'subcategories' => [], 'categoriasfilhas' => []])

@php
    use App\Models\Currency;
    $currentCurrencyId = session('currency', Currency::where('is_default', 1)->first()?->id);
    $currentCurrency = Currency::find($currentCurrencyId);
    $currencySign = $currentCurrency?->sign ?? '$';
@endphp

<div class="toolbar-container d-flex justify-content-between align-items-center px-2 py-3 border-bottom border-top mb-4">
    <div class="flex-shrink-0">
        <button class="btn-filter-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#modalFiltros">
            <span class="fw-bold text-uppercase x-small tracking-widest">{{ __('messages.todos_filtros') }}</span>
            <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 1H18M0 6H12M0 11H18" stroke="currentColor" stroke-width="1.5" />
            </svg>
        </button>
    </div>

    <div class="flex-shrink-0">
        <form id="sortForm" method="GET" class="d-flex align-items-center gap-2 gap-md-4">
            <input type="hidden" name="search" value="{{ $request->search }}">
            <input type="hidden" name="brand" value="{{ $request->brand }}">
            <input type="hidden" name="category" value="{{ $request->category }}">
            <input type="hidden" name="subcategory" value="{{ $request->subcategory }}">
            <input type="hidden" name="categoriasfilhas" value="{{ $request->categoriasfilhas }}">
            <input type="hidden" name="min_price" value="{{ $request->min_price }}">
            <input type="hidden" name="max_price" value="{{ $request->max_price }}">

            <div class="d-flex align-items-center gap-1 gap-md-2">
                <label class="toolbar-label d-none d-lg-block mb-0">{{ __('messages.ordenar_por') }}</label>
                <select name="sort_by" class="form-select toolbar-select" onchange="this.form.submit()">
                    <option value="">{{ __('messages.ordenar_padrao') }}</option>
                    <option value="latest" @selected($request->sort_by == 'latest')>{{ __('messages.ordenar_ultimo') }}</option>
                    <option value="price_low" @selected($request->sort_by == 'price_low')>{{ __('messages.ordenar_menor_preco') }}</option>
                    <option value="price_high" @selected($request->sort_by == 'price_high')>{{ __('messages.ordenar_maior_preco') }}</option>
                    <option value="name_az" @selected($request->sort_by == 'name_az')>A-Z</option>
                </select>
            </div>

            <div class="d-flex align-items-center gap-1 gap-md-2 border-start ps-2 ps-md-4">
                <label class="toolbar-label d-none d-lg-block mb-0">{{ __('messages.mostrar') }}</label>
                <select name="per_page" class="form-select toolbar-select" style="width: 70px;" onchange="this.form.submit()">
                    <option value="36" @selected($request->per_page == 36)>35</option>
                    <option value="72" @selected($request->per_page == 72)>70</option>
                    <option value="102" @selected($request->per_page == 102)>100</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end offcanvas-filter" tabindex="-1" id="modalFiltros">
    <div class="offcanvas-header border-bottom px-4 py-4">
        <h5 class="offcanvas-title text-uppercase fw-bold tracking-widest small">{{ __('messages.filtrar_por') }}</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body px-4 py-4">
        <form action="{{ route('search') }}" method="GET" id="filterSidebarForm">
            <input type="hidden" name="search" value="{{ $request->search }}">
            <input type="hidden" name="sort_by" value="{{ $request->sort_by }}">
            <input type="hidden" name="per_page" value="{{ $request->per_page }}">

            {{-- MARCA --}}
            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.marca') }}</label>
                <input type="text" list="list-brands" id="brand-input" class="form-control sax-filter-input" 
                       placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                       value="{{ collect($brands)->firstWhere('id', $request->brand)?->name ?? '' }}" autocomplete="off">
                <input type="hidden" name="brand" id="brand-id" value="{{ $request->brand }}">
                <datalist id="list-brands">
                    @foreach ($brands as $brand)
                        <option data-id="{{ $brand->id }}" value="{{ $brand->name }}">
                    @endforeach
                </datalist>
            </div>

            {{-- CATEGORIA --}}
            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.categoria') }}</label>
                <input type="text" list="list-categories" id="category-input" class="form-control sax-filter-input" 
                       placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                       value="{{ collect($categories)->firstWhere('id', $request->category)?->name ?? '' }}" autocomplete="off">
                <input type="hidden" name="category" id="category-id" value="{{ $request->category }}">
                <datalist id="list-categories">
                    @foreach ($categories as $category)
                        <option data-id="{{ $category->id }}" value="{{ $category->name }}">
                    @endforeach
                </datalist>
            </div>

            {{-- SUBCATEGORIA --}}
            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.subcategoria') }}</label>
                <input type="text" list="list-subcategories" id="subcategory-input" class="form-control sax-filter-input" 
                       placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                       value="{{ collect($subcategories)->firstWhere('id', $request->subcategory)?->name ?? '' }}" autocomplete="off">
                <input type="hidden" name="subcategory" id="subcategory-id" value="{{ $request->subcategory }}">
                <datalist id="list-subcategories">
                    @foreach ($subcategories as $sub)
                        <option data-id="{{ $sub->id }}" value="{{ $sub->name }}">
                    @endforeach
                </datalist>
            </div>

            {{-- CATEGORIA FILHA --}}
            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.categoria_filha') }}</label>
                <input type="text" list="list-child-categories" id="child-category-input" class="form-control sax-filter-input" 
                       placeholder="{{ __('messages.buscar_ou_selecionar') }}"
                       value="{{ collect($categoriasfilhas)->firstWhere('id', $request->categoriasfilhas)?->name ?? '' }}" autocomplete="off">
                <input type="hidden" name="categoriasfilhas" id="child-category-id" value="{{ $request->categoriasfilhas }}">
                <datalist id="list-child-categories">
                    @foreach ($categoriasfilhas as $filha)
                        <option data-id="{{ $filha->id }}" value="{{ $filha->name }}">
                    @endforeach
                </datalist>
            </div>

            {{-- PREÇO --}}
            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.preco') }} ({{ $currencySign }})</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" name="min_price" class="form-control sax-filter-input" placeholder="MIN" value="{{ $request->min_price }}">
                    <input type="number" name="max_price" class="form-control sax-filter-input" placeholder="MAX" value="{{ $request->max_price }}">
                </div>
            </div>

            <div class="d-grid gap-2 mt-5">
                <button type="submit" class="btn btn-dark rounded-0 py-3 text-uppercase fw-bold">
                    {{ __('messages.aplicar_filtros') }}
                </button>
                <a href="{{ route('search', ['search' => $request->search]) }}" class="btn btn-link text-dark text-decoration-none text-center mt-2">
                    {{ __('messages.limpar_tudo') }}
                </a>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const setupDatalistSync = (inputId, hiddenId, datalistId) => {
        const input = document.getElementById(inputId);
        const hidden = document.getElementById(hiddenId);
        const datalist = document.getElementById(datalistId);

        if (!input || !hidden || !datalist) return;

        input.addEventListener('input', function() {
            const val = this.value;
            const options = datalist.options;
            let foundId = '';

            for (let i = 0; i < options.length; i++) {
                if (options[i].value === val) {
                    foundId = options[i].getAttribute('data-id');
                    break;
                }
            }
            hidden.value = foundId;
        });

        input.addEventListener('blur', function() {
            if (this.value === '') {
                hidden.value = '';
            }
        });
    };

    setupDatalistSync('brand-input', 'brand-id', 'list-brands');
    setupDatalistSync('category-input', 'category-id', 'list-categories');
    setupDatalistSync('subcategory-input', 'subcategory-id', 'list-subcategories');
    setupDatalistSync('child-category-input', 'child-category-id', 'list-child-categories');
});
</script>