@extends('layout.layout')  <!-- Usando o layout criado -->

@section('content')
<div class="container mt-5">
    <div class="product-card">
        <div class="product-card-header">
            <h1>{{ $product->external_name }}</h1>
        </div>
        <div class="product-card-body">
            <div class="row">
                <div class="col-md-6">
                    <!-- Exibe a imagem principal do produto, se existir -->
                    <div class="product-images">
                        @if($product->photo)
                            <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->external_name }}" class="img-fluid">
                        @endif
                    </div>
                </div>
                <div class="col-md-6">
                    <!-- Detalhes do produto -->
                    <p><strong>SKU:</strong> {{ $product->sku }}</p>
                    <p><strong>Preço:</strong> R$ {{ number_format($product->price, 2, ',', '.') }}</p>
                    <p><strong>Descrição:</strong> {{ $product->description }}</p>
                    <p><strong>Status:</strong> {{ $product->status }}</p>
                    <p><strong>Categoria:</strong> {{ $product->category_id }}</p>
                    <p><strong>Marca:</strong> {{ $product->brand_id }}</p>
                </div>
            </div>

            <!-- Exemplo de exibição de uploads relacionados, se necessário -->
            @if($uploads->isNotEmpty())
                <h3 class="mt-4">Arquivos Relacionados</h3>
                <ul class="list-group">
                    @foreach($uploads as $upload)
                        <li class="list-group-item">
                            <a href="{{ Storage::url($upload->file_path) }}" target="_blank">
                                {{ $upload->original_name }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            @endif
        </div>

        <div class="product-card-footer">
            <!-- Link para voltar à página inicial -->
            <a href="{{ url('/') }}" class="btn btn-secondary mt-3">Voltar para a Home</a>
        </div>
    </div>
</div>
@endsection
