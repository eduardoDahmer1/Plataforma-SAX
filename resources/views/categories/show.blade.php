@extends('layout.layout')

@section('content')
<div class="container py-4">
    <h1>{{ $category->name }}</h1>

    <!-- Exibindo a imagem da categoria (logo) -->
    <div class="text-center mb-3">
        @if($category->photo && Storage::disk('public')->exists($category->photo))
            <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <!-- Exibindo detalhes da categoria -->
    <p><strong>ID:</strong> {{ $category->id }}</p>
    <p><strong>Slug:</strong> {{ $category->slug }}</p>

    <!-- Exibindo o banner da categoria -->
    <div class="text-center mt-4">
        @if($category->banner && Storage::disk('public')->exists($category->banner))
            <img src="{{ Storage::url($category->banner) }}" alt="Banner da categoria" class="img-fluid rounded-3 shadow-sm">
        @elseif(Storage::disk('public')->exists('uploads/noimage.webp'))
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @else
            <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão" class="img-fluid rounded-3 shadow-sm">
        @endif
    </div>

    <a href="{{ route('categories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
</div>
@endsection
