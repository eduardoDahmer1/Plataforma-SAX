@extends('layout.layout')

@section('content')
    <div class="container mt-5">

        <div class="card shadow-lg rounded-4 border-0 overflow-hidden">
            <div class="row g-0">

                {{-- Imagem Principal + Galeria --}}
                <div class="col-lg-6 p-4 bg-light d-flex flex-column justify-content-center">
                    @php
                        $mainImage = $product->photo_url;

                        // Garante que $gallery seja sempre um array
                        if (is_string($product->gallery)) {
                            $gallery = is_array($product->gallery) ? $product->gallery : json_decode($product->gallery, true);
                        } elseif (is_array($product->gallery)) {
                            $gallery = $product->gallery;
                        } else {
                            $gallery = [];
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
                                <img src="{{ Storage::url($img) }}" class="img-thumbnail rounded-3"
                                    style="height: 60px; cursor: pointer;"
                                    onclick="document.querySelector('.ratio img').src='{{ Storage::url($img) }}'">
                            @endforeach
                        </div>
                    @endif
                </div>

                {{-- Detalhes do Produto --}}
                <div class="col-lg-6 p-4 d-flex flex-column justify-content-between">
                    <div>
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

                        {{-- Breadcrumb de categorias --}}
                        <div class="d-flex flex-wrap align-items-center mb-3 gap-1 small">
                            @if ($product->category)
                                <a href="{{ route('categories.show', $product->category->slug) }}"
                                    class="text-dark fw-semibold text-decoration-none">{{ $product->category->name }}</a>
                            @else
                                <span class="text-muted">Sem Categoria</span>
                            @endif

                            @if ($product->subcategory)
                                <span class="text-dark">›</span>
                                <a href="{{ route('subcategories.show', $product->subcategory->id) }}"
                                    class="text-dark fw-semibold text-decoration-none">{{ $product->subcategory->name }}</a>
                            @endif

                            @if ($product->childcategory)
                                <span class="text-dark">›</span>
                                <a href="{{ route('childcategories.show', $product->childcategory->slug) }}"
                                    class="text-dark fw-semibold text-decoration-none">{{ $product->childcategory->name }}</a>
                            @endif
                        </div>

                        {{-- Preparar IDs das cores --}}
                        @php
                            $colorIds = [];
                            if ($product->color_parent_id) {
                                if (is_array($product->color_parent_id)) {
                                    $colorIds = $product->color_parent_id;
                                } elseif (is_string($product->color_parent_id)) {
                                    $colorIds = explode(',', $product->color_parent_id);
                                }
                            }
                        @endphp

                        {{-- Produtos Relacionados --}}
                        @if ($product->children->count() || count($colorIds))
                            <div class="container mt-5">
                                {{-- Tamanhos --}}
                                @if ($siblings->count())
                                    <h5 class="fw-bold mb-3">Tamanhos Disponíveis</h5>
                                    <div class="row g-3 mb-4">
                                        @foreach ($siblings as $child)
                                            <div class="col-6 col-md-2">
                                                <a href="{{ route('produto.show', $child) }}"
                                                    class="text-decoration-none text-dark">
                                                    <div class="card-body p-2 border rounded text-center">
                                                        <p class="mb-1 small">
                                                            {{ $child->size ?? $child->external_name }}
                                                        </p>
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif

                                {{-- Cores --}}
                                @if (count($colorIds))
                                    <h5 class="fw-bold mb-3">Cores Disponíveis</h5>
                                    <div class="row g-3">
                                        @foreach (App\Models\Product::whereIn('id', $colorIds)->get() as $color)
                                            <div class="col-6 col-md-2">
                                                <a href="{{ route('produto.show', $color->id) }}"
                                                    class="text-decoration-none text-dark">
                                                    <div class="card-body p-2 d-flex align-items-center gap-2">
                                                        @if ($color->color)
                                                            <span
                                                                style="display:inline-block;width:16px;height:16px;background:{{ $color->color }};border:1px solid #ccc;"></span>
                                                        @else
                                                            {{ $color->external_name }}
                                                        @endif
                                                    </div>
                                                </a>
                                            </div>
                                        @endforeach
                                    </div>
                                @endif
                            </div>
                        @endif

                        {{-- SKU, Estoque e Preço --}}
                        <p class="mb-2"><strong>SKU:</strong> {{ $product->sku }}</p>
                        <p class="mb-2"><strong>Estoque:</strong>
                            <span class="{{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $product->stock ?? 'Indisponível' }}
                            </span>
                        </p>
                        <p class="mb-2"><strong>Preço:</strong>
                            @if ($product->price)
                                <span class="text-success fs-4 fw-bold">{{ currency_format($product->price) }}</span>
                            @else
                                <span class="text-muted">Não informado</span>
                            @endif
                        </p>

                        {{-- Descrição --}}
                        @if ($product->description)
                            <div class="mt-4">
                                <h6 class="fw-bold">Descrição</h6>
                                <p class="text-muted">{{ $product->description }}</p>
                            </div>
                        @endif
                    </div>

                    {{-- Botões de Ação --}}
                    <div class="mt-4 d-flex flex-wrap gap-2">
                        @auth
                            @php
                                $cart = session('cart', []);
                                $currentQty = $cart[$product->id]['quantity'] ?? 0;
                                $isFavorite = in_array($product->id, $favoriteProductIds ?? []);
                            @endphp

                            {{-- Comprar Agora --}}
                            <form action="{{ route('checkout.index') }}" method="GET" class="d-inline">
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-primary px-4 shadow-sm">
                                    <i class="fas fa-credit-card me-1"></i> Comprar Agora
                                </button>
                            </form>

                            {{-- Adicionar ao Carrinho --}}
                            <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-success px-4 shadow-sm"
                                    {{ $product->stock <= 0 || $currentQty >= $product->stock ? 'disabled' : '' }}>
                                    <i class="fas fa-cart-plus me-1"></i> Adicionar ao Carrinho
                                </button>
                            </form>

                            {{-- Favorito --}}
                            <form action="{{ route('user.preferences.toggle') }}" method="POST" class="d-inline">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit"
                                    class="btn {{ $isFavorite ? 'btn-danger' : 'btn-outline-danger' }} px-4 shadow-sm">
                                    <i class="fas {{ $isFavorite ? 'fa-heart-broken' : 'fa-heart' }} me-1"></i>
                                    {{ $isFavorite ? 'Remover dos Favoritos' : 'Adicionar aos Favoritos' }}
                                </button>
                            </form>
                        @else
                            <a href="#" class="btn btn-warning px-4 shadow-sm" data-bs-toggle="modal"
                                data-bs-target="#loginModal">
                                <i class="fas fa-user-lock me-1"></i> Login para Comprar
                            </a>
                        @endauth

                        {{-- Voltar --}}
                        <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4 shadow-sm">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
