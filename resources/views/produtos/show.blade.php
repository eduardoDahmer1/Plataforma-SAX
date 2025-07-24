@extends('layout.layout')

@section('content')
<div class="container mt-5">
    <div class="card shadow-lg rounded-4 border-0">
        <div class="row g-0">
            <!-- Imagem do Produto -->
            <div class="col-md-6 p-4 text-center">
                @if($product->photo)
                    <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->external_name }}" class="img-fluid rounded-3 shadow-sm">
                @else
                    <img src="https://via.placeholder.com/400x400?text=Sem+Imagem" alt="Sem Imagem" class="img-fluid rounded-3">
                @endif
            </div>

            <!-- Detalhes do Produto -->
            <div class="col-md-6 p-4">
                <h1 class="h3 mb-3">{{ $product->external_name }}</h1>

                <p class="mb-2"><strong>Marca:</strong> {{ $product->brand->name ?? 'Sem Marca' }}</p>
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
                    <a href="#" class="btn btn-primary px-4">Comprar</a>
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
