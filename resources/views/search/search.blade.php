@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h2 class="mb-4"><i class="fas fa-search me-2"></i> Veja os produtos encontrados no catálogo.</h2>

    {{-- Alertas --}}
    <x-alert type="success" :message="session('success')" />

    <div class="row">
        {{-- Sidebar --}}
        <x-sidebar-filters 
            :brands="$brands" 
            :categories="$categories" 
            :subcategories="$subcategories" 
            :childcategories="$childcategories" 
        />

        {{-- Lista de produtos --}}
        <div class="col-md-9">
            <h4 class="mb-3"><i class="fas fa-box-open me-2"></i> Produtos</h4>

            
            {{-- Form de ordenar e mostrar --}}
            <x-product-sort-form :request="request()" />
            
            <div class="d-flex justify-content-center mt-4">
                <x-pagination :links="$paginated" />
            </div>
            
            @if ($paginated->count())
                <div class="row">
                    @foreach ($paginated as $item)
                        <x-product-card :item="$item" :cartItems="$cartItems" />
                    @endforeach
                </div>

                <div class="d-flex justify-content-center mt-4">
                    <x-pagination :links="$paginated" />
                </div>
            @else
                <p class="text-muted">Nenhum produto encontrado para "{{ $query }}".</p>
            @endif
        </div>
    </div>
</div>
@endsection
