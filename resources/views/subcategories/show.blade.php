@extends('layout.layout')

@section('content')
    <div class="container py-4">

        {{-- Voltar --}}
        <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary mb-3">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>

        {{-- Título --}}
        <div class="text-center mb-4">
            <h1 class="fw-bold">{{ $subcategory->name }}</h1>
            <p class="text-muted">
                ID: {{ $subcategory->id }} | Slug: {{ $subcategory->slug ?? 'N/A' }}
            </p>
        </div>

        {{-- Foto da Subcategoria --}}
        <div class="text-center mb-4">
            <div class="ratio ratio-16x9 mx-auto" style="max-width: 600px;">
                @if ($subcategory->photo && Storage::disk('public')->exists($subcategory->photo))
                    <img src="{{ Storage::url($subcategory->photo) }}" alt="{{ $subcategory->name }}"
                        class="img-fluid rounded-3 shadow-sm object-fit-contain">
                @else
                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                        class="img-fluid rounded-3 shadow-sm object-fit-contain">
                @endif
            </div>
        </div>

        {{-- Infos --}}
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-body">
                <p><strong>Categoria Pai:</strong> {{ $subcategory->category->name ?? 'N/A' }}</p>

                @if ($subcategory->childcategories && $subcategory->childcategories->count())
                    <p><strong>Childcategories:</strong></p>
                    <ul>
                        @foreach ($subcategory->childcategories as $child)
                            <li>{{ $child->name ?? $child->slug }}</li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>

        {{-- Produtos --}}
        @if ($subcategory->products && $subcategory->products->count())
            <h3 class="mb-3 fw-semibold">Produtos desta Subcategoria</h3>
            <div class="row">
                @foreach ($subcategory->products as $product)
                    <div class="col-6 col-md-4 col-lg-3 mb-4">
                        <div class="card h-100 shadow-sm border-0 position-relative">

                            {{-- Imagem --}}
                            <div class="card-img-top text-center p-3">
                                @if ($product->photo && Storage::disk('public')->exists($product->photo))
                                    <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: scale-down;">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: scale-down;">
                                @endif
                            </div>

                            @auth
                                @php
                                    // Pega a quantidade do produto específico no carrinho
                                    $currentQty = $cartItems[$product->id] ?? 0;
                                @endphp

                                <div class="mb-2">
                                    <span class="badge bg-info">No carrinho: {{ $currentQty }}</span>
                                </div>

                            @endauth

                            <div class="card-body d-flex flex-column">
                                {{-- Nome --}}
                                <h6 class="card-title mb-2">
                                    <a href="{{ route('products.show', $product->id) }}" class="text-decoration-none">
                                        <i class="fas fa-tag me-1"></i> {{ $product->name ?? $product->slug }}
                                    </a>
                                </h6>

                                {{-- Preço --}}
                                <p class="text-success fw-semibold mb-1">
                                    {{ isset($product->price) ? currency_format($product->price) : 'Não informado' }}
                                </p>

                                {{-- Estoque --}}
                                <p class="mb-2">
                                    @if ($product->stock > 0)
                                        <span class="badge bg-success"><i class="fas fa-box me-1"></i>
                                            {{ $product->stock }} em estoque
                                        </span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sem
                                            estoque</span>
                                    @endif
                                </p>

                                {{-- Botões --}}
                                <div class="mt-auto d-flex flex-column gap-2">
                                    <a href="{{ route('products.show', $product->id) }}" class="btn btn-sm btn-info">
                                        <i class="fas fa-eye me-1"></i> Ver Produto
                                    </a>

                                    @auth
                                        @php $currentQty = $cartItems[$product->id] ?? 0; @endphp

                                        @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                            <form action="{{ route('cart.addAndCheckout') }}" method="POST" class="d-flex">
                                                @csrf
                                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                                <button type="submit" class="btn btn-sm btn-primary flex-grow-1">
                                                    <i class="fas fa-bolt me-1"></i> Comprar Agora
                                                </button>
                                            </form>
                                        @endif
                                    @else
                                        <a href="#" class="btn btn-sm btn-warning" data-bs-toggle="modal"
                                            data-bs-target="#loginModal">
                                            <i class="fas fa-sign-in-alt me-1"></i> Login para Favoritar
                                        </a>
                                    @endauth
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="alert alert-info text-center">
                <i class="fas fa-info-circle me-1"></i> Nenhum produto encontrado nesta subcategoria.
            </div>
        @endif

        {{-- Banner --}}
        @if ($subcategory->banner && Storage::disk('public')->exists($subcategory->banner))
            <div class="text-center mt-4">
                <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
                    <img src="{{ Storage::url($subcategory->banner) }}" alt="Banner da Subcategoria"
                        class="img-fluid rounded-3 shadow-sm object-fit-cover">
                </div>
            </div>
        @endif

        {{-- Voltar --}}
        <div class="text-center mt-4">
            <a href="{{ route('subcategories.index') }}" class="btn btn-outline-secondary">
                <i class="fas fa-arrow-left me-1"></i> Voltar
            </a>
        </div>

    </div>
@endsection
