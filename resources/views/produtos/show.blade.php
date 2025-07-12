@extends('layout.layout')  <!-- Aqui estamos dizendo que queremos usar o layout criado -->

@section('content')
    <div class="container">
        <h1>{{ $product->external_name }}</h1>

        <p><strong>SKU:</strong> {{ $product->sku }}</p>
        <p><strong>Preço:</strong> R$ {{ number_format($product->price, 2, ',', '.') }}</p>
        <p><strong>Descrição:</strong> {{ $product->description }}</p>
        <p><strong>Status:</strong> {{ $product->status }}</p>
        <p><strong>Categoria:</strong> {{ $product->category_id }}</p>
        <p><strong>Marca:</strong> {{ $product->brand_id }}</p>

        <!-- Exibe a imagem principal do produto, se existir -->
        <div class="product-images">
            @if($product->photo)
                <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->external_name }}">
            @endif
        </div>

        <!-- Exemplo de exibição de uploads relacionados, se necessário -->
        @if($uploads->isNotEmpty())
            <h3>Arquivos Relacionados</h3>
            <ul>
                @foreach($uploads as $upload)
                    <li>
                        <a href="{{ Storage::url($upload->file_path) }}" target="_blank">
                            {{ $upload->original_name }}
                        </a>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
