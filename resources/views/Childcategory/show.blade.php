@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Voltar --}}
    <a href="{{ route('childcategories.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>

    {{-- Título --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold">{{ $childcategory->name }}</h1>
        <p class="text-muted">
            ID: {{ $childcategory->id }} | Slug: {{ $childcategory->slug ?? 'N/A' }}
        </p>
    </div>

    {{-- Foto --}}
    <div class="text-center mb-4">
        <div class="ratio ratio-16x9 mx-auto" style="max-width: 600px;">
            @if($childcategory->photo && Storage::disk('public')->exists($childcategory->photo))
                <img src="{{ Storage::url($childcategory->photo) }}" alt="{{ $childcategory->name }}"
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
            <p><strong>Subcategoria Pai:</strong> {{ $childcategory->subcategory->name ?? 'N/A' }}</p>
            <p><strong>Categoria Pai:</strong> {{ $childcategory->subcategory->category->name ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Produtos --}}
    @if($childcategory->products && $childcategory->products->count())
        <h3 class="mb-3 fw-semibold">Produtos desta Sub-Subcategoria</h3>
        <div class="row">
            @foreach($childcategory->products as $product)
            <div class="col-md-4 mb-4">
                <div class="card h-100 shadow-sm border-0">
                    <div class="card-img-top text-center p-3">
                        @if($product->photo && Storage::disk('public')->exists($product->photo))
                            <img src="{{ Storage::url($product->photo) }}" alt="{{ $product->name }}"
                                 class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                        @else
                            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Sem imagem"
                                 class="img-fluid rounded-3" style="max-height: 150px; object-fit: contain;">
                        @endif
                    </div>
                    <div class="card-body text-center d-flex flex-column">
                        <h5 class="fw-semibold">{{ $product->name ?? $product->slug }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fas fa-tag me-1"></i> {{ number_format($product->price, 2, ',', '.') }} GS$
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
            <i class="fas fa-info-circle me-1"></i> Nenhum produto encontrado nesta sub-subcategoria.
        </div>
    @endif

    {{-- Banner --}}
    @if($childcategory->banner && Storage::disk('public')->exists($childcategory->banner))
    <div class="text-center mt-4">
        <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
            <img src="{{ Storage::url($childcategory->banner) }}" alt="Banner da Sub-Subcategoria"
                 class="img-fluid rounded-3 shadow-sm object-fit-coverr">
        </div>
    </div>
    @endif

    {{-- Voltar --}}
    <div class="text-center mt-4">
        <a href="{{ route('childcategories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

</div>
@endsection
