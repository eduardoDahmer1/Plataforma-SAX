@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Voltar --}}
    <a href="{{ route('childcategories.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>

    {{-- Cabeçalho --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold">{{ $childcategory->name ?? $childcategory->slug }}</h1>
        <p class="text-muted">
            ID: {{ $childcategory->id }} |
            Slug: {{ $childcategory->slug ?? 'N/A' }}
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
            <p><strong>Subcategoria:</strong> {{ $childcategory->subcategory->name ?? 'N/A' }}</p>
            <p><strong>Categoria:</strong> {{ $childcategory->subcategory->category->name ?? 'N/A' }}</p>
        </div>
    </div>

    {{-- Banner --}}
    @if($childcategory->banner && Storage::disk('public')->exists($childcategory->banner))
    <div class="text-center mt-4">
        <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
            <img src="{{ Storage::url($childcategory->banner) }}" alt="Banner da Childcategory"
                 class="img-fluid rounded-3 shadow-sm object-fit-cover">
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
