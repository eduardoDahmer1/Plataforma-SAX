@extends('layout.layout')

@section('title', $collectionTitle ?? __('messages.buscar'))

@section('content')
<x-alert type="success" :message="session('success')" />

<div class="container-fluid px-3 px-lg-5 py-4 py-lg-5 search-page">
    @if ($collectionTitle)
        <div class="search-query-heading search-collection-heading">
            <p>Vista &amp; Co</p>
            <h1>{{ $collectionTitle }}</h1>
            @if($collectionDescription)<div>{{ $collectionDescription }}</div>@endif
        </div>
    @elseif ($query)
        <div class="search-query-heading">
            <p>{{ __('messages.results_for') }}</p>
            <h1>“{{ $query }}”</h1>
        </div>
    @endif

    <x-sidebar-filters
        :request="request()"
        :brands="$brands"
        :categories="$categories"
        :subcategories="$subcategories"
        :categoriasfilhas="$categoriasfilhas"
        :sizes="$sizes"
        :size-groups="$sizeGroups"
        :colors="$colors"
        :coupon-product-count="$couponProductCount"
        :price-bounds="$priceBounds"
        :currency-context="$currencyContext"
    />

    @if(filled($query) && $suggestedProducts->isNotEmpty())
        <section class="search-product-suggestions" aria-labelledby="suggested-products-title">
            <div class="search-product-suggestions__heading">
                <div>
                    <span>Sugestões</span>
                    <h2 id="suggested-products-title">Produtos que combinam com sua busca</h2>
                </div>
                <small>Ordenados pela procura no catálogo</small>
            </div>
            <div class="search-product-suggestions__rail">
                @foreach($suggestedProducts as $suggested)
                    <a href="{{ route('produto.show', $suggested->slug) }}" class="search-product-suggestion">
                        <img src="{{ $suggested->photo_url ?? asset('storage/uploads/noimage.webp') }}" alt="" loading="lazy">
                        <span>
                            <small>{{ $suggested->brand?->name ?? 'SAX' }}</small>
                            <b>{{ $suggested->external_name ?: $suggested->name }}</b>
                            <strong>{{ currency_format($suggested->price) }}</strong>
                        </span>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    <div id="search-status" class="search-results-status" role="status" aria-live="polite">
        <div>
            <span id="search-total">{{ number_format($paginated->total(), 0, ',', '.') }}</span>
            <span>produtos encontrados</span>
        </div>
        <div id="search-spinner" class="spinner-border spinner-border-sm d-none" aria-hidden="true"></div>
    </div>

    <div id="search-grid" class="row g-2 g-md-3">
        @include('search.partials.grid', ['paginated' => $paginated])
    </div>

    <div id="search-pagination" class="d-flex justify-content-center mt-5 pagination-sax">
        @include('search.partials.pagination', ['paginated' => $paginated])
    </div>
</div>

@include('search.partials.filter-runtime', ['ajaxUrl' => route('search.ajax')])
@endsection
