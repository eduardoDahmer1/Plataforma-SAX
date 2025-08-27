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

        {{-- Foto --}}
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

                {{-- Childcategories --}}
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
                    <div class="col-md-4 mb-4">
                        <div class="card h-100 shadow-sm border-0">
                            <div class="card-img-top text-center p-3">
                                @if ($product->photo && Storage::disk('public')->exists($product->photo))
                                    <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                                @else
                                    <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem"
                                        class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                                @endif
                            </div>
                            <div class="card-body text-center d-flex flex-column">
                                <h5 class="fw-semibold">{{ $product->name ?? $product->slug }}</h5>

                                {{-- Preço --}}
                                <p class="text-muted mb-1">
                                    <i class="fas fa-tag me-1"></i> {{ number_format($product->price, 2, ',', '.') }} GS$
                                </p>

                                {{-- Estoque --}}
                                <p class="mb-2">
                                    @if ($product->stock > 0)
                                        <span class="badge bg-success"><i class="fas fa-box me-1"></i>
                                            {{ $product->stock }} em estoque</span>
                                    @else
                                        <span class="badge bg-danger"><i class="fas fa-times-circle me-1"></i> Sem
                                            estoque</span>
                                    @endif
                                </p>

                                <a href="{{ route('products.show', $product->id) }}"
                                    class="btn btn-outline-primary btn-sm mt-auto">
                                    <i class="fas fa-eye me-1"></i> Ver produto
                                </a>
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
                        class="img-fluid rounded-3 shadow-sm object-fit-coverr">
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
