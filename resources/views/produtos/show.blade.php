@extends('layout.layout')

@section('content')
    <div class="container mt-5">

        <div class="card shadow-lg rounded-4 border-0 overflow-hidden">
            <div class="row g-0">

                {{-- Imagem Principal + Galeria --}}
                <div class="col-lg-6 p-4 bg-light d-flex flex-column justify-content-center">
                    @php
                        $mainImage = $product->photo_url;

                        $gallery = [];
                        if ($product->gallery) {
                            $galleryImages = json_decode($product->gallery, true);
                            foreach ($galleryImages as $img) {
                                $gallery[] = Storage::url($img);
                            }
                        }
                    @endphp

                    {{-- Imagem Principal --}}
                    <div class="ratio ratio-1x1 mb-4">
                        <img src="{{ $mainImage }}" class="img-fluid rounded-3 shadow-sm object-fit-contain w-100 h-100"
                            alt="{{ $product->external_name }}">
                    </div>

                    {{-- Galeria (se existir) --}}
                    @if (count($gallery))
                        <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
                            <div class="carousel-inner rounded-3 shadow-sm">
                                @foreach ($gallery as $index => $img)
                                    <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                                        <img src="{{ $img }}" class="d-block w-100 object-fit-contain"
                                            style="max-height: 400px;" alt="Galeria {{ $index + 1 }}">
                                    </div>
                                @endforeach
                            </div>

                            @if (count($gallery) > 1)
                                <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel"
                                    data-bs-slide="prev">
                                    <span class="carousel-control-prev-icon"></span>
                                    <span class="visually-hidden">Anterior</span>
                                </button>
                                <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel"
                                    data-bs-slide="next">
                                    <span class="carousel-control-next-icon"></span>
                                    <span class="visually-hidden">Próximo</span>
                                </button>
                            @endif
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
                        <div class="d-flex flex-wrap align-items-center mb-3" style="gap: .5rem; font-size: 0.95rem;">

                            {{-- Categoria --}}
                            @if ($product->category)
                                <a href="{{ route('categories.show', $product->category->slug) }}"
                                    class="text-dark text-decoration-none fw-semibold">
                                    {{ $product->category->name }}
                                </a>
                            @else
                                <span class="text-muted">Sem Categoria</span>
                            @endif

                            @if ($product->subcategory)
                                <span class="text-dark">›</span>
                                <a href="{{ route('subcategories.show', $product->subcategory->id) }}"
                                    class="text-dark text-decoration-none fw-semibold">
                                    {{ $product->subcategory->name }}
                                </a>
                            @endif

                            @if ($product->childcategory)
                                <span class="text-dark">›</span>
                                <a href="{{ route('childcategories.show', $product->childcategory->slug) }}"
                                    class="text-dark text-decoration-none fw-semibold">
                                    {{ $product->childcategory->name }}
                                </a>
                            @endif
                        </div>


                        {{-- SKU e Estoque --}}
                        <p class="mb-2"><strong>SKU:</strong> {{ $product->sku }}</p>
                        <p class="mb-2"><strong>Estoque:</strong>
                            <span class="{{ $product->stock > 0 ? 'text-success' : 'text-danger' }}">
                                {{ $product->stock ?? 'Indisponível' }}
                            </span>
                        </p>

                        {{-- Preço --}}
                        <p class="mb-2"><strong>Preço:</strong>
                            @if ($product->price)
                                <span class="text-success fs-4 fw-semibold">
                                    {{ currency_format($product->price) }}
                                </span>
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
                    <div class="mt-4 d-flex gap-3 flex-wrap">
                        @auth
                            @php
                                $cart = session('cart', []);
                                $currentQty = $cart[$product->id]['quantity'] ?? 0;
                            @endphp

                            @if (in_array(auth()->user()->user_type, [0, 1, 2]))
                                <form action="{{ route('checkout.index') }}" method="GET" class="d-inline">
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-outline-primary px-4">
                                        <i class="fas fa-credit-card me-1"></i> Comprar Agora
                                    </button>
                                </form>

                                <form action="{{ route('cart.add') }}" method="POST" class="d-inline">
                                    @csrf
                                    <input type="hidden" name="product_id" value="{{ $product->id }}">
                                    <button type="submit" class="btn btn-success px-4"
                                        @if ($currentQty >= $product->stock) disabled @endif>
                                        <i class="fas fa-cart-plus me-1"></i> Adicionar ao Carrinho
                                    </button>
                                </form>
                            @endif
                        @else
                            <a href="#" class="btn btn-warning px-4" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-user-lock me-1"></i> Login para Comprar
                            </a>
                        @endauth

                        <a href="{{ url('/') }}" class="btn btn-outline-secondary px-4">
                            <i class="fas fa-arrow-left me-1"></i> Voltar
                        </a>
                    </div>
                </div>

            </div>
        </div>
    </div>
@endsection
