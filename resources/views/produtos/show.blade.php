@extends('layout.layout')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg rounded-4 border-0">
        <div class="row g-0">
            <!-- Imagem Principal -->
            <div class="col-md-6 p-4 text-center">
                @php
                    // Foto principal
                    $mainImage = $product->photo_url;

                    // Galeria
                    $gallery = [];
                    if($product->gallery) {
                        $galleryImages = json_decode($product->gallery, true);
                        foreach($galleryImages as $img) {
                            $gallery[] = Storage::url($img);
                        }
                    }
                @endphp

                <img src="{{ $mainImage }}" class="img-fluid rounded-3 shadow-sm mb-4" alt="{{ $product->external_name }}">

                @if(count($gallery))
                <div id="galleryCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner">
                        @foreach($gallery as $index => $img)
                        <div class="carousel-item {{ $index === 0 ? 'active' : '' }}">
                            <img src="{{ $img }}" class="d-block w-100 rounded-3 shadow-sm" alt="Galeria {{ $index + 1 }}">
                        </div>
                        @endforeach
                    </div>

                    @if(count($gallery) > 1)
                    <button class="carousel-control-prev" type="button" data-bs-target="#galleryCarousel" data-bs-slide="prev">
                        <span class="carousel-control-prev-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Anterior</span>
                    </button>
                    <button class="carousel-control-next" type="button" data-bs-target="#galleryCarousel" data-bs-slide="next">
                        <span class="carousel-control-next-icon" aria-hidden="true"></span>
                        <span class="visually-hidden">Próximo</span>
                    </button>
                    @endif
                </div>
                @endif
            </div>

            <!-- Detalhes do Produto -->
            <div class="col-md-6 p-4">
                <h1 class="h3 mb-3">{{ $product->external_name }}</h1>

                <p class="mb-2"><strong>Marca:</strong>
                    @if ($product->brand)
                    <a href="{{ route('brands.show', $product->brand->id) }}">{{ $product->brand->name }}</a>
                    @else
                    Sem Marca
                    @endif
                </p>

                <p class="mb-2"><strong>Categoria:</strong> {{ $product->category->name ?? 'Sem Categoria' }}</p>
                <p class="mb-2"><strong>SKU:</strong> {{ $product->sku }}</p>
                <p class="mb-2"><strong>Estoque:</strong> {{ $product->stock ?? 'Indisponível' }}</p>
                <p class="mb-2"><strong>Preço:</strong>
                    @if($product->price)
                    <span class="text-success h5">R$ {{ number_format($product->price, 2, ',', '.') }}</span>
                    @else
                    <span class="text-muted">Não informado</span>
                    @endif
                </p>

                @if($product->description)
                <p class="mt-4"><strong>Descrição:</strong><br>{{ $product->description }}</p>
                @endif

                <div class="mt-4 d-flex gap-3 flex-wrap">
                    @auth
                    @if(in_array(auth()->user()->user_type, [0, 1, 2]))
                    <form action="{{ route('checkout.index') }}" method="GET">
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="btn btn-outline-secondary">Comprar</button>
                    </form>

                    <form action="{{ route('cart.add') }}" method="POST">
                        @csrf
                        <input type="hidden" name="product_id" value="{{ $product->id }}">
                        <button type="submit" class="btn btn-success">+ 🛒 Adicionar</button>
                    </form>
                    @endif
                    @else
                    <a href="#" class="btn btn-warning px-4" data-bs-toggle="modal" data-bs-target="#loginModal">Login para Comprar</a>
                    @endauth

                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Voltar para a Home</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
