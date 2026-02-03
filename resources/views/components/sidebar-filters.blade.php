@props(['request', 'brands' => [], 'categories' => [], 'subcategories' => [], 'childcategories' => []])

@php
    use App\Models\Currency;
    $currentCurrencyId = session('currency', Currency::where('is_default', 1)->first()?->id);
    $currentCurrency = Currency::find($currentCurrencyId);
    $currencySign = $currentCurrency?->sign ?? '$';
    $currencyRate = $currentCurrency?->rate ?? 1;
@endphp

<style>
    .tracking-widest {
        letter-spacing: 0.15em;
    }

    .x-small {
        font-size: 0.65rem;
    }

    /* Botão de Filtro Estilo Figma */
    .btn-filter-trigger {
        border: 1px solid #000;
        background: #fff;
        border-radius: 0;
        padding: 10px 18px;
        display: flex;
        align-items: center;
        gap: 12px;
        transition: all 0.2s ease;
        cursor: pointer;
        height: 42px;
        color: #000;
        text-decoration: none;
    }

    .btn-filter-trigger:hover {
        background: #000;
        color: #fff;
    }

    /* Estilo dos Selects na Toolbar */
    .toolbar-label {
        font-size: 0.7rem;
        font-weight: 800;
        text-transform: uppercase;
        letter-spacing: 0.05em;
    }

    .toolbar-select {
        border-radius: 0 !important;
        border: 1px solid #000 !important;
        font-size: 0.75rem !important;
        text-transform: uppercase;
        font-weight: 700;
        padding: 8px 30px 8px 12px !important;
        height: 42px;
        background-color: #fff;
    }

    /* Modal / Offcanvas */
    .sax-filter-input {
        border-radius: 0 !important;
        border: 1px solid #e0e0e0 !important;
        font-size: 0.75rem;
        padding: 12px;
        text-transform: uppercase;
    }

    .offcanvas-filter {
        width: 350px !important;
        border-left: 1px solid #000;
    }
</style>

<div class="d-flex justify-content-between align-items-center px-0 py-3 border-bottom border-top mb-4">

    <div class="col-auto">
        <button class="btn-filter-trigger" type="button" data-bs-toggle="offcanvas" data-bs-target="#modalFiltros">
            <span class="fw-bold text-uppercase x-small tracking-widest">Todos los filtros</span>
            <svg width="18" height="12" viewBox="0 0 18 12" fill="none" xmlns="http://www.w3.org/2000/svg">
                <path d="M0 1H18M0 6H12M0 11H18" stroke="currentColor" stroke-width="1.5" />
            </svg>
        </button>
    </div>

    <div class="col-auto">
        <form id="sortForm" method="GET" class="d-flex align-items-center gap-3 gap-md-4">
            {{-- Preservar os valores dos filtros do Modal no envio do Sort --}}
            <input type="hidden" name="search" value="{{ $request->search }}">
            <input type="hidden" name="brand" value="{{ $request->brand }}">
            <input type="hidden" name="category" value="{{ $request->category }}">
            <input type="hidden" name="min_price" value="{{ $request->min_price }}">
            <input type="hidden" name="max_price" value="{{ $request->max_price }}">

            {{-- Ordenar por --}}
            <div class="d-flex align-items-center gap-2">
                <label class="toolbar-label d-none d-md-block mb-0">Ordenar por:</label>
                <select name="sort_by" class="form-select toolbar-select" onchange="this.form.submit()">
                    <option value="">Padrão</option>
                    <option value="latest" @selected($request->sort_by == 'latest')>Último</option>
                    <option value="price_low" @selected($request->sort_by == 'price_low')>Menor precio</option>
                    <option value="price_high" @selected($request->sort_by == 'price_high')>Mayor precio</option>
                    <option value="name_az" @selected($request->sort_by == 'name_az')>A-Z</option>
                </select>
            </div>

            {{-- Mostrar --}}
            <div class="d-flex align-items-center gap-2 border-start ps-3 ps-md-4">
                <label class="toolbar-label d-none d-md-block mb-0">Mostrar:</label>
                <select name="per_page" class="form-select toolbar-select" style="width: 80px;"
                    onchange="this.form.submit()">
                    <option value="35" @selected($request->per_page == 35)>35</option>
                    <option value="70" @selected($request->per_page == 70)>70</option>
                    <option value="100" @selected($request->per_page == 100)>100</option>
                </select>
            </div>
        </form>
    </div>
</div>

<div class="offcanvas offcanvas-end offcanvas-filter" tabindex="-1" id="modalFiltros">
    <div class="offcanvas-header border-bottom px-4 py-4">
        <h5 class="offcanvas-title text-uppercase fw-bold tracking-widest small">Filtrar por</h5>
        <button type="button" class="btn-close shadow-none" data-bs-dismiss="offcanvas"></button>
    </div>

    <div class="offcanvas-body px-4 py-4">
        <form action="{{ route('search') }}" method="GET">
            {{-- Preservar o termo de busca e ordenação atual no modal --}}
            <input type="hidden" name="search" value="{{ $request->search }}">
            <input type="hidden" name="sort_by" value="{{ $request->sort_by }}">
            <input type="hidden" name="per_page" value="{{ $request->per_page }}">

            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">Marca</label>
                <select name="brand" class="form-select sax-filter-input">
                    <option value="">Todas</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" @selected($request->brand == $brand->id)>{{ $brand->name }}</option>
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">Categoría</label>
                <select name="category" class="form-select sax-filter-input">
                    <option value="">Todas</option>
                    @php
                        // Lista de IDs permitidos conforme sua solicitação
                        $allowedCategoryIds = [
                            91,
                            115,
                            139,
                            140,
                            141,
                            143,
                            144,
                            146,
                            149,
                            150,
                            152,
                            168,
                            169,
                            170,
                            158,
                        ];
                    @endphp

                    @foreach ($categories as $category)
                        {{-- Verifica se o ID da categoria atual está na lista permitida --}}
                        @if (in_array($category->id, $allowedCategoryIds))
                            <option value="{{ $category->id }}" @selected($request->category == $category->id)>
                                {{ $category->name ?? $category->slug }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            <div class="mb-4">
                <label class="toolbar-label d-block mb-2">Precio ({{ $currencySign }})</label>
                <div class="d-flex align-items-center gap-2">
                    <input type="number" name="min_price" class="form-control sax-filter-input" placeholder="Min"
                        value="{{ $request->min_price }}">
                    <input type="number" name="max_price" class="form-control sax-filter-input" placeholder="Max"
                        value="{{ $request->max_price }}">
                </div>
            </div>

            <div class="d-grid gap-2 mt-5">
                <button type="submit"
                    class="btn btn-dark rounded-0 py-3 text-uppercase fw-bold x-small tracking-widest">
                    Aplicar Filtros
                </button>
                <a href="{{ route('search', ['search' => $request->search]) }}"
                    class="btn btn-link text-dark text-decoration-none text-center x-small fw-bold text-uppercase mt-2">
                    Limpiar todo
                </a>
            </div>
        </form>
    </div>
</div>
