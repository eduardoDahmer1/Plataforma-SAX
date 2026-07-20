@extends('layout.dashboard')

@section('content')
<div class="sax-wishlist-wrapper">
    <div class="dashboard-header mb-5">
        <h2 class="sax-title text-uppercase letter-spacing-2">{{ __('messages.wishlist_titulo') }}</h2>
        <p class="text-muted mb-2">Seus produtos favoritos reunidos em um só lugar.</p>
        <div class="sax-divider-dark"></div>
    </div>

    @if (session('success'))
        <div class="alert alert-dark border-0 rounded-0 x-small letter-spacing-1 mb-4">
            {{ session('success') }}
        </div>
    @endif

    @if ($favoriteProducts->isEmpty())
        <div class="empty-wishlist text-center py-5 border rounded-3 bg-white">
            <i class="far fa-heart fa-3x mb-3 opacity-25"></i>
            <p class="text-muted text-uppercase letter-spacing-1 small">{{ __('messages.lista_vazia') }}</p>
            <a href="{{ route('home') }}" class="btn btn-dark rounded-0 px-5 mt-3 x-small fw-bold letter-spacing-2">
                {{ __('messages.explorar_produtos') }}
            </a>
        </div>
    @else
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h6 class="sax-section-title m-0">Itens favoritados</h6>
            <span class="badge bg-light text-dark border">{{ $favoriteProducts->total() }} item(ns)</span>
        </div>

        <div class="row g-1">
            @foreach ($favoriteProducts as $product)
                <x-product-card :item="$product" gridClass="col-6 col-md-4 col-lg-3" />
            @endforeach
        </div>

        <div class="d-flex justify-content-center mt-5">
            {{ $favoriteProducts->links() }}
        </div>
    @endif
</div>
@endsection
