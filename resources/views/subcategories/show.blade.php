@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>{{ $subcategory->name }}</h1>

    <div class="text-center mb-3">
        @if($subcategory->photo && Storage::disk('public')->exists($subcategory->photo))
            <img src="{{ Storage::url($subcategory->photo) }}" alt="{{ $subcategory->name }}" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <p><strong>ID:</strong> {{ $subcategory->id }}</p>
    <p><strong>Slug:</strong> {{ $subcategory->slug ?? 'N/A' }}</p>
    <p><strong>Categoria Pai:</strong> {{ $subcategory->category->name ?? 'N/A' }}</p>

    <div class="text-center mt-4">
        @if($subcategory->banner && Storage::disk('public')->exists($subcategory->banner))
            <img src="{{ Storage::url($subcategory->banner) }}" alt="Banner da Subcategoria" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <a href="{{ route('subcategories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
