@props(['request', 'brands' => [], 'categories' => [], 'subcategories' => [], 'categorias-filhas' => []])

@php
    use App\Models\Currency;
    $currentCurrencyId = session('currency', Currency::where('is_default', 1)->first()?->id);
    $currentCurrency = Currency::find($currentCurrencyId);
    $currencySign = $currentCurrency?->sign ?? '$';
    $currencyRate = $currentCurrency?->rate ?? 1;
@endphp

<div class="toolbar-container d-flex justify-content-between align-items-center px-2 py-3 border-bottom border-top mb-4">

    {{-- Filtro (Esquerda) --}}
    <div class="flex-shrink-0">
        <button class="btn-filter-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#modalFiltros">
            <span class="fw-bold text-uppercase x-small tracking-widest">{{ __('messages.todos_filtros') }}</span>
            <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 1H18M0 6H12M0 11H18" stroke="currentColor" stroke-width="1.5" />
            </svg>
        </button>
    </div>

    {{-- Sort e Per Page (Direita) --}}
    <div class="flex-shrink-0">
        <form id="sortForm" method="GET" class="d-flex align-items-center gap-2 gap-md-4">
            <input type="hidden" name="search" value="{{ $request->search }}">
            <input type="hidden" name="brand" value="{{ $request->brand }}">
            <input type="hidden" name="category" value="{{ $request->category }}">
            <input type="hidden" name="min_price" value="{{ $request->min_price }}">
            <input type="hidden" name="max_price" value="{{ $request->max_price }}">

            {{-- Ordenar por --}}
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

            {{-- Mostrar --}}
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

{{-- Offcanvas de Filtros --}}
<div class="offcanvas offcanvas-end offcanvas-filter" tabindex="-1" id="modalFiltros">
    <div class="offcanvas-header border-bottom px-4 py-4">
        <h5 class="offcanvas-title text-uppercase fw-bold tracking-widest small">{{ __('messages.filtrar_por') }}</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body px-4 py-4">
        <form action="{{ route('search') }}" method="GET">
            <input type="hidden" name="search" value="{{ $request->search }}">
            <input type="hidden" name="sort_by" value="{{ $request->sort_by }}">
            <input type="hidden" name="per_page" value="{{ $request->per_page }}">

            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.marca') }}</label>
                <select name="brand" class="form-select sax-filter-input">
                    <option value="">{{ __('messages.todas') }}</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected($request->brand == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.categoria') }}</label>
                <select name="category" class="form-select sax-filter-input">
                    <option value="">{{ __('messages.todas') }}</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected($request->category == $category->id)>
                            {{ $category->name ?? $category->slug }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">{{ __('messages.preco') }} ({{ $currencySign }})</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" name="min_price" class="form-control sax-filter-input" placeholder="{{ __('messages.placeholder_min') }}"
                        value="{{ $request->min_price }}">
                    <input type="number" name="max_price" class="form-control sax-filter-input" placeholder="{{ __('messages.placeholder_max') }}"
                        value="{{ $request->max_price }}">
                </div>
            </div>

            <div class="d-grid gap-2 mt-5">
                <button type="submit" class="btn btn-dark rounded-0 py-3 text-uppercase fw-bold x-small tracking-widest">
                    {{ __('messages.aplicar_filtros') }}
                </button>
                <a href="{{ route('search', ['search' => $request->search]) }}"
                    class="btn btn-link text-dark text-decoration-none text-center x-small fw-bold text-uppercase mt-2">
                    {{ __('messages.limpar_tudo') }}
                </a>
            </div>
        </form>
    </div>
</div>