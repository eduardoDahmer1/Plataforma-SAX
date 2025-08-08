@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>{{ $childcategory->name ?? $childcategory->slug }}</h1>

    <div class="text-center mb-3">
        @if($childcategory->photo && Storage::disk('public')->exists($childcategory->photo))
            <img src="{{ Storage::url($childcategory->photo) }}" alt="{{ $childcategory->name }}" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <p><strong>ID:</strong> {{ $childcategory->id }}</p>
    <p><strong>Slug:</strong> {{ $childcategory->slug ?? 'N/A' }}</p>
    <p><strong>Subcategoria:</strong> {{ $childcategory->subcategory->name ?? 'N/A' }}</p>
    <p><strong>Categoria:</strong> {{ $childcategory->subcategory->category->name ?? 'N/A' }}</p>

    <div class="text-center mt-4">
        @if($childcategory->banner && Storage::disk('public')->exists($childcategory->banner))
            <img src="{{ Storage::url($childcategory->banner) }}" alt="Banner da Childcategory" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <a href="{{ route('childcategories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
