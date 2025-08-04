@extends('layout.layout')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg rounded-4 border-0">
        <div class="row g-0">
            <!-- Imagem do Produto -->
            <div class="col-md-6 p-4 text-center">
                @if($product->photo && Storage::disk('public')->exists($product->photo))
                <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->external_name }}"
                    class="img-fluid rounded-3 shadow-sm">
                @elseif(!empty($noimage) && Storage::disk('public')->exists('uploads/' . $noimage))
                <img src="{{ asset('storage/uploads/' . $noimage) }}" alt="Imagem padrão"
                    class="img-fluid rounded-3 shadow-sm">
                @else
                <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                    class="img-fluid rounded-3 shadow-sm">
                @endif
            </div>

            <!-- Detalhes do Produto -->
            <div class="col-md-6 p-4">
                <h1 class="h3 mb-3">{{ $product->external_name }}</h1>

                <p class="mb-2">
                    <strong>Marca:</strong>
                    @if ($product->brand)
                    <a href="{{ route('brands.show', $product->brand->id) }}">{{ $product->brand->name }}</a>
                    @else
                    Sem Marca
                    @endif
                </p>

                <p class="mb-2"><strong>Categoria:</strong> {{ $product->category->name ?? 'Sem Categoria' }}</p>
                <p class="mb-2"><strong>SKU:</strong> {{ $product->sku }}</p>
                <p class="mb-2"><strong>Status:</strong> {{ ucfirst($product->status) }}</p>
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

                <div class="mt-4 d-flex gap-3">
                    @auth
                        @if(in_array(auth()->user()->user_type, [0, 1, 2]))
                            <form action="{{ route('cart.add') }}" method="POST">
                                @csrf
                                <input type="hidden" name="product_id" value="{{ $product->id }}">
                                <button type="submit" class="btn btn-primary px-4">Comprar</button>
                            </form>
                        @endif
                    @else
                        <a href="#" class="btn btn-warning px-4" data-bs-toggle="modal" data-bs-target="#loginModal">Login para Comprar</a>
                    @endauth

                    <a href="{{ url('/') }}" class="btn btn-outline-secondary">Voltar para a Home</a>
                </div>
            </div>
        </div>

        <!-- Arquivos Relacionados -->
        @if($uploads->isNotEmpty())
        <div class="p-4 border-top">
            <h4 class="mb-3">Arquivos Relacionados</h4>
            <ul class="list-group list-group-flush">
                @foreach($uploads as $upload)
                <li class="list-group-item">
                    <a href="{{ Storage::url($upload->file_path) }}" target="_blank">
                        {{ $upload->original_name }}
                    </a>
                </li>
                @endforeach
            </ul>
        </div>
        @endif
    </div>
</div>
@endsection