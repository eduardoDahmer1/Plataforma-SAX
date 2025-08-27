@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Voltar --}}
    <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>

    {{-- Cabeçalho --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold">{{ $brand->name ?? $brand->slug ?? 'N/A' }}</h1>
        <p class="text-muted">Slug: {{ $brand->slug ?? 'N/A' }} | ID: {{ $brand->id }}</p>
    </div>

    {{-- Imagem principal --}}
    <div class="text-center mb-4">
        <div class="ratio ratio-16x9 mx-auto" style="max-width: 600px;">
            @if($brand->image && Storage::disk('public')->exists($brand->image))
                <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name ?? $brand->slug }}"
                     class="img-fluid rounded-3 shadow-sm object-fit-contain">
            @else
                <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                     class="img-fluid rounded-3 shadow-sm object-fit-contain">
            @endif
        </div>
    </div>

    {{-- Produtos da marca --}}
    <h4 class="mt-5 mb-3 text-center">
        <i class="fas fa-box me-2"></i> Produtos da marca {{ $brand->name }}
    </h4>

    @if($products->count())
        <div class="row">
            @foreach($products as $item)
            <div class="col-6 col-md-4 col-lg-3 mb-4">
                <div class="card h-100 shadow-sm border-0">

                    {{-- Imagem do produto --}}
                    <img src="{{ $item->photo_url }}" class="card-img-top img-fluid rounded-top"
                        alt="{{ $item->external_name }}" style="max-height: 200px; object-fit: scale-down;">

                    <div class="card-body d-flex flex-column">
                        {{-- Nome --}}
                        <h6 class="card-title mb-2">
                            <a href="{{ route('produto.show', $item->id) }}" class="text-decoration-none">
                                <i class="fas fa-tag me-1"></i> {{ $item->external_name ?? 'Sem nome' }}
                            </a>
                        </h6>

                        {{-- Infos --}}
                        <p class="card-text small text-muted mb-2">
                            <i class="fas fa-barcode me-1"></i> {{ $item->sku ?? 'Sem SKU' }}<br>
                            <i class="fas fa-dollar-sign me-1"></i>
                            {{ isset($item->price) ? 'R$ ' . number_format($item->price, 2, ',', '.') : 'Não informado' }}
                        </p>

                        {{-- Ações --}}
                        <div class="mt-auto d-flex flex-column">
                            <a href="{{ route('produto.show', $item->id) }}" class="btn btn-sm btn-info mb-2">
                                <i class="fas fa-eye me-1"></i> Ver Detalhes
                            </a>

                            @auth
                            @php $currentQty = $cartItems[$item->id] ?? 0; @endphp

                            @if(in_array(auth()->user()->user_type, [0,1,2]))
                                {{-- Adicionar ao carrinho --}}
                                <form action="{{ route('cart.add') }}" method="POST" class="d-flex mb-2">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-sm btn-success flex-grow-1"
                                        @if($currentQty >= $item->stock) disabled @endif>
                                        <i class="fas fa-cart-plus me-1"></i> Adicionar
                                    </button>
                                </form>

                                {{-- Comprar agora --}}
                                <form action="{{ route('checkout.index') }}" method="GET" class="d-flex">
                                    <input type="hidden" name="product_id" value="{{ $item->id }}">
                                    <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                        <i class="fas fa-bolt me-1"></i> Comprar Agora
                                    </button>
                                </form>
                            @endif
                            @else
                                <a href="#" class="btn btn-sm btn-warning mt-2" data-bs-toggle="modal"
                                    data-bs-target="#loginModal">
                                    <i class="fas fa-sign-in-alt me-1"></i> Login para Comprar
                                </a>
                            @endauth
                        </div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Paginação --}}
        <div class="d-flex justify-content-center mt-4">
            {{ $products->links('pagination::bootstrap-4') }}
        </div>
    @else
        <div class="alert alert-info text-center">
            <i class="fas fa-info-circle me-1"></i> Nenhum produto encontrado para esta marca.
        </div>
    @endif

    {{-- Banner (opcional) --}}
    @if($brand->banner && Storage::disk('public')->exists($brand->banner))
    <div class="text-center mt-4">
        <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
            <img src="{{ Storage::url($brand->banner) }}" alt="Banner da Marca"
                 class="img-fluid rounded-3 shadow-sm object-fit-coverr">
        </div>
    </div>
    @endif

    {{-- Voltar --}}
    <div class="text-center mt-4">
        <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

</div>
@endsection
