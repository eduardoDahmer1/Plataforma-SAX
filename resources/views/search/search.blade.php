@extends('layout.layout')

@section('content')
    <div class="container-fluid py-4">

        {{-- Alertas --}}
        <x-alert type="success" :message="session('success')" />

        <div class="row">

            {{-- Lista de produtos --}}
            <div class="col-md-12">
                <h4 class="mb-3"><i class="fas fa-box-open me-2"></i> Produtos</h4>

                {{-- Ajustado para passar a variável categoriasfilhas corretamente --}}
                <x-sidebar-filters 
                    :request="request()" 
                    :brands="$brands" 
                    :categories="$categories" 
                    :subcategories="$subcategories" 
                    :categoriasfilhas="$categoriasfilhas" 
                />

                @if ($paginated->count())
                    <div class="row">
                        @foreach ($paginated as $item)
                            <x-product-card :item="$item" :cartItems="$cartItems ?? []" />
                        @endforeach
                    </div>

                    <div class="d-flex justify-content-center mt-4 pagination-sax">
                        {{ $paginated->links() }}
                    </div>
                @else
                    <div class="text-center py-5">
                        <p class="text-muted">Nenhum produto encontrado para "<strong>{{ $query }}</strong>".</p>
                        <a href="{{ route('search') }}" class="btn btn-outline-dark btn-sm">Limpar busca</a>
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection