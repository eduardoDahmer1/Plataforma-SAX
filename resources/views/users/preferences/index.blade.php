@extends('layout.dashboard')

@section('content')
    <h1 class="mb-4"><i class="fas fa-heart me-2"></i> Meus Produtos Favoritos</h1>

    @if (session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if ($favoriteProducts->count() == 0)
        <p class="text-muted">Você ainda não adicionou nenhum produto aos favoritos.</p>
    @else
        <div class="row">
            @foreach ($favoriteProducts as $product)
                <div class="col-md-4 mb-4">
                    <div class="card h-100 shadow-sm border-0 rounded-3 overflow-hidden">
                        <a href="{{ route('products.show', $product->id) }}">
                            <div class="ratio ratio-16x9 bg-light">
                                <img src="{{ $product->photo ? asset('storage/' . $product->photo) : asset('storage/uploads/noimage.webp') }}"
                                    alt="{{ $product->external_name }}" class="img-fluid object-fit-contain">
                            </div>
                        </a>

                        <div class="card-body d-flex flex-column">
                            <h5 class="card-title">
                                <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none text-dark">
                                    {{ $product->external_name }}
                                </a>
                            </h5>

                            <p class="card-text text-muted flex-grow-1">SKU: {{ $product->sku }}</p>
                            <p class="card-text fw-bold">R$ {{ number_format($product->price, 2, ',', '.') }}</p>

                            <form action="{{ route('user.preferences.toggle') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-danger w-100">
                                    <i class="fas fa-heart-broken me-1"></i> Remover dos Favoritos
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-4">
            {{ $favoriteProducts->links('pagination::bootstrap-4') }}
        </div>
    @endif
@endsection
