@extends('layout.layout')

@section('content')
    <div class="container-fluid py-4">

        {{-- Alertas --}}
        <x-alert type="success" :message="session('success')" />

        <div class="row">

            {{-- Lista de produtos --}}
            <div class="col-md-12">
                <h4 class="mb-3"><i class="fas fa-box-open me-2"></i> Produtos</h4>

                <x-sidebar-filters :request="request()" :brands="$brands" :categories="$categories" :subcategories="$subcategories" :childcategories="$childcategories" />

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
