@props([
    'brands' => [],
    'categories' => [],
    'subcategories' => [],
    'childcategories' => []
])

@php
use App\Models\Currency;

$currentCurrencyId = session('currency', Currency::where('is_default', 1)->first()?->id);
$currentCurrency = Currency::find($currentCurrencyId);
$currencySign = $currentCurrency?->sign ?? '$';
$currencyRate = $currentCurrency?->rate ?? 1;
@endphp

{{-- Botão fixo no canto inferior direito apenas no mobile --}}
<button class="btn btn-primary d-md-none position-fixed" 
        style="bottom: 5em;right: 1rem; z-index: 1055; width: 3em;" 
        type="button" data-bs-toggle="offcanvas" data-bs-target="#mobileFilter" aria-controls="mobileFilter">
    <i class="fas fa-filter"></i>
</button>

{{-- Offcanvas para mobile --}}
<div class="offcanvas offcanvas-end" tabindex="-1" id="mobileFilter" aria-labelledby="mobileFilterLabel">
    <div class="offcanvas-header">
        <h5 class="offcanvas-title" id="mobileFilterLabel"><i class="fas fa-filter me-2"></i> Filtrar Resultados</h5>
        <button type="button" class="btn-close text-reset" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body">
        <form action="{{ route('search') }}" method="GET">
            <input type="hidden" name="search" value="{{ request('search') }}">

            {{-- Marca --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Marca</label>
                <select name="brand" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($brands as $brand)
                        <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                            {{ $brand->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Categoria --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Categoria</label>
                <select name="category" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($categories as $category)
                        @if (!empty($category->name))
                            <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Subcategoria --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Subcategoria</label>
                <select name="subcategory" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($subcategories as $subcategory)
                        @if (!empty($subcategory->name))
                            <option value="{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>
                                {{ $subcategory->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Categoria filha --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Categoria filha</label>
                <select name="childcategory" class="form-select">
                    <option value="">Todas</option>
                    @foreach ($childcategories as $childcategory)
                        @if (!empty($childcategory->name))
                            <option value="{{ $childcategory->id }}" {{ request('childcategory') == $childcategory->id ? 'selected' : '' }}>
                                {{ $childcategory->name }}
                            </option>
                        @endif
                    @endforeach
                </select>
            </div>

            {{-- Preço --}}
            <div class="mb-3">
                <label class="form-label fw-bold">Preço ({{ $currencySign }})</label>
                <div class="d-flex gap-2">
                    <input type="number" name="min_price" class="form-control" placeholder="Mín"
                           min="0" step="0.01" value="{{ request('min_price', 5 * $currencyRate) }}">
                    <input type="number" name="max_price" class="form-control" placeholder="Máx"
                           min="0" step="0.01" value="{{ request('max_price', 1000 * $currencyRate) }}">
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 mt-2">
                <i class="fas fa-search me-1"></i> Aplicar Filtros
            </button>
        </form>
    </div>
</div>

{{-- Filtro lateral para desktop --}}
<div class="d-none d-md-block col-md-3 mb-4">
    <div class="card shadow-sm">
        <div class="card-header bg-light">
            <i class="fas fa-filter me-2"></i> Filtrar Resultados
        </div>
        <div class="card-body">
            <form action="{{ route('search') }}" method="GET" id="filterForm">
                <input type="hidden" name="search" value="{{ request('search') }}">

                {{-- Marca --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Marca</label>
                    <select name="brand" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($brands as $brand)
                            <option value="{{ $brand->id }}" {{ request('brand') == $brand->id ? 'selected' : '' }}>
                                {{ $brand->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                {{-- Categoria --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Categoria</label>
                    <select name="category" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($categories as $category)
                            @if (!empty($category->name))
                                <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>
                                    {{ $category->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Subcategoria --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Subcategoria</label>
                    <select name="subcategory" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($subcategories as $subcategory)
                            @if (!empty($subcategory->name))
                                <option value="{{ $subcategory->id }}" {{ request('subcategory') == $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Categoria filha --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Categoria filha</label>
                    <select name="childcategory" class="form-select">
                        <option value="">Todas</option>
                        @foreach ($childcategories as $childcategory)
                            @if (!empty($childcategory->name))
                                <option value="{{ $childcategory->id }}" {{ request('childcategory') == $childcategory->id ? 'selected' : '' }}>
                                    {{ $childcategory->name }}
                                </option>
                            @endif
                        @endforeach
                    </select>
                </div>

                {{-- Preço --}}
                <div class="mb-3">
                    <label class="form-label fw-bold">Preço ({{ $currencySign }})</label>
                    <div class="d-flex gap-2">
                        <input type="number" name="min_price" class="form-control" placeholder="Mín"
                               min="0" step="0.01" value="{{ request('min_price', 5 * $currencyRate) }}">
                        <input type="number" name="max_price" class="form-control" placeholder="Máx"
                               min="0" step="0.01" value="{{ request('max_price', 1000 * $currencyRate) }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-primary w-100 mt-2">
                    <i class="fas fa-search me-1"></i> Aplicar Filtros
                </button>
            </form>
        </div>
    </div>
</div>
