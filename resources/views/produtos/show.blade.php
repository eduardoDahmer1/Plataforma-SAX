@extends('layout.layout')

@section('content')
    <div class="container mt-5">

        <div class="card shadow-lg rounded-4 border-0 overflow-hidden">
            <div class="row g-0">

                {{-- Imagem Principal + Galeria --}}
                <div class="col-lg-6 p-4 bg-light d-flex flex-column justify-content-center">
                    @php
                        $mainImage =
                            $product->photo && \Storage::disk('public')->exists($product->photo)
                                ? Storage::url($product->photo)
                                : asset('storage/uploads/noimage.webp');

                        $gallery = [];
                        if (is_string($product->gallery)) {
                            $gallery = json_decode($product->gallery, true) ?: [];
                        } elseif (is_array($product->gallery)) {
                            $gallery = $product->gallery;
                        }
                    @endphp

                    {{-- Imagem Principal --}}
                    <div class="ratio ratio-1x1 mb-3 rounded-3 shadow-sm overflow-hidden">
                        <img src="{{ $mainImage }}" class="img-fluid w-100 h-100 object-fit-contain"
                            alt="{{ $product->external_name }}">
                    </div>

                    {{-- Miniaturas da Galeria --}}
                    @if (count($gallery))
                        <div class="d-flex gap-2 overflow-auto py-2">
                            @foreach ($gallery as $img)
                                @if (\Storage::disk('public')->exists($img))
                                    <img src="{{ Storage::url($img) }}" class="img-thumbnail rounded-3"
                                        style="height:60px; cursor:pointer;"
                                        onclick="document.querySelector('.ratio img').src='{{ Storage::url($img) }}'">
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Detalhes do Produto --}}
                <div class="col-lg-6 p-4 d-flex flex-column justify-content-between">

                    <div>
                        {{-- Nome do Produto --}}
                        <h1 class="h3 fw-bold mb-3">{{ $product->external_name }}</h1>

                        {{-- Marca --}}
                        <p class="mb-2">
                            <strong>Marca:</strong>
                            @if ($product->brand)
                                <a href="{{ route('brands.show', $product->brand->slug) }}" class="text-decoration-none">
                                    {{ $product->brand->name }}
                                </a>
                            @else
                                <span class="text-muted">Sem Marca</span>
                            @endif
                        </p>

                        {{-- Breadcrumb --}}
                        <div class="d-flex flex-wrap align-items-center mb-3 gap-1 small">
                            @if ($product->category)
                                <a href="{{ route('categories.show', $product->category->slug) }}"
                                    class="text-dark fw-semibold text-decoration-none">{{ $product->category->name }}</a>
                            @endif
                            @if ($product->subcategory)
                                <span class="text-dark">›</span>
                                <a href="{{ route('subcategories.show', $product->subcategory->slug) }}"
                                    class="text-dark fw-semibold text-decoration-none">{{ $product->subcategory->name }}</a>
                            @endif
                            @if ($product->childcategory)
                                <span class="text-dark">›</span>
                                <a href="{{ route('childcategories.show', $product->childcategory->slug) }}"
                                    class="text-dark fw-semibold text-decoration-none">{{ $product->childcategory->name }}</a>
                            @endif
                        </div>

                        {{-- Informações Importantes --}}
                        <div class="row g-3 mb-4">

                            {{-- Preço --}}
                            <div class="col-6 col-md-4">
                                <div
                                    class="p-3 border rounded h-100 d-flex flex-column justify-content-center text-center bg-white shadow-sm">
                                    <small class="text-muted">Preço</small>
                                    <span class="fs-4 fw-bold text-success mt-1">
                                        {{ currency_format($product->price) }}
                                    </span>
                                </div>
                            </div>

                            {{-- Estoque --}}
                            <div class="col-6 col-md-4">
                                <div
                                    class="p-3 border rounded h-100 d-flex flex-column justify-content-center text-center bg-white shadow-sm">
                                    <small class="text-muted">Estoque</small>
                                    <span class="mt-1 {{ $product->stock > 0 ? 'text-success' : 'text-danger' }} fw-bold">
                                        {{ $product->stock > 0 ? $product->stock . ' disponíveis' : 'Indisponível' }}
                                    </span>
                                </div>
                            </div>

                            {{-- SKU --}}
                            <div class="col-6 col-md-4">
                                <div
                                    class="p-3 border rounded h-100 d-flex flex-column justify-content-center text-center bg-white shadow-sm">
                                    <small class="text-muted">SKU</small>
                                    <span class="mt-1">{{ $product->sku ?? 'N/A' }}</span>
                                </div>
                            </div>

                        </div>

                        {{-- Descrição --}}
                        @if ($product->description)
                            <div class="mt-4">
                                <h5 class="fw-bold mb-2">Descrição</h5>
                                <p class="text-muted">{{ $product->description }}</p>
                            </div>
                        @endif

                        {{-- Produtos Filhos: Tamanhos e Cores --}}
                        @php
                            $siblings = $product->children ?? collect();
                            $colorIds = is_array($product->color_parent_id)
                                ? $product->color_parent_id
                                : (is_string($product->color_parent_id)
                                    ? explode(',', $product->color_parent_id)
                                    : []);
                        @endphp

                        @if ($siblings->count() || count($colorIds))
                            <div class="mt-4">

                                {{-- Tamanhos --}}
                                @if ($siblings->count())
                                    <h5 class="fw-bold mb-2">Tamanhos Disponíveis</h5>
                                    <div class="d-flex flex-wrap gap-2 mb-3">
                                        @foreach ($siblings as $child)
                                            <a href="{{ route('produto.show', $child) }}"
                                                class="btn btn-outline-secondary btn-sm flex-grow-1 flex-md-grow-0">
                                                {{ $child->size ?? $child->external_name }}
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Cores --}}
                                @if (count($colorIds))
                                    <h5 class="fw-bold mb-2">Cores Disponíveis</h5>
                                    <div class="d-flex flex-wrap gap-2">
                                        @foreach (App\Models\Product::whereIn('id', $colorIds)->get() as $color)
                                            <a href="{{ route('produto.show', $color->id) }}"
                                                class="btn btn-outline-secondary btn-sm d-flex align-items-center justify-content-center gap-1"
                                                style="min-width:50px;">
                                                @if ($color->color)
                                                    <span
                                                        style="display:inline-block;width:16px;height:16px;background:{{ $color->color }};border:1px solid #ccc;"></span>
                                                @else
                                                    {{ $color->external_name }}
                                                @endif
                                            </a>
                                        @endforeach
                                    </div>
                                @endif

                            </div>
                        @endif

                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-4 d-flex flex-wrap gap-2 justify-content-between">
                        @auth
                            @php
                                $cart = session('cart', []);
                                $currentQty = $cart[$product->id]['quantity'] ?? 0;
                                $isFavorite = in_array($product->id, $favoriteProductIds ?? []);
                            @endphp

                            <form action="{{ route('checkout.index') }}" method="GET" class="flex-fill">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-primary w-100 w-md-auto px-4 shadow-sm">
                                    <i class="fas fa-credit-card me-1"></i> Comprar Agora
                                </button>
                            </form>

                            <form action="{{ route('cart.add') }}" method="POST" class="flex-fill">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-success w-100 w-md-auto px-4 shadow-sm"
                                    {{ $product->stock <= 0 || $currentQty >= $product->stock ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus me-1"></i> Adicionar ao Carrinho
                                </button>
                            </form>

                            <form action="{{ route('user.preferences.toggle') }}" method="POST" class="flex-fill">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit"
                                    class="btn {{ $isFavorite ? 'btn-danger' : 'btn-outline-danger' }} w-100 w-md-auto px-4 shadow-sm">
                                    <i class="fas {{ $isFavorite ? 'fa-heart-broken' : 'fa-heart' }} me-1"></i>
                                    {{ $isFavorite ? 'Remover dos Favoritos' : 'Adicionar aos Favoritos' }}
                                </button>
                            </form>
                        @else
                            <a href="#" class="btn btn-warning w-100 w-md-auto px-4 shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#loginModal">
                                <i class="fas fa-user-lock me-1"></i> Login para Comprar
                            </a>
                        @endauth

                        <a href="{{ url('/') }}" class="btn btn-outline-secondary w-100 w-md-auto px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
