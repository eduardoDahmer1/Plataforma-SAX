@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Voltar --}}
    <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>

    {{-- Cabeçalho --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold">{{ $category->name }}</h1>
        <p class="text-muted">Slug: {{ $category->slug }} | ID: {{ $category->id }}</p>
    </div>

    {{-- Logo / Foto --}}
    <div class="text-center mb-4">
        <div class="ratio ratio-16x9 mx-auto" style="max-width: 600px;">
            @if($category->photo && Storage::disk('public')->exists($category->photo))
                <img src="{{ Storage::url($category->photo) }}" alt="{{ $category->name }}"
                     class="img-fluid rounded-3 shadow-sm object-fit-contain">
            @else
                <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                     class="img-fluid rounded-3 shadow-sm object-fit-contain">
            @endif
        </div>
    </div>

    {{-- Banner --}}
    @if($category->banner && Storage::disk('public')->exists($category->banner))
    <div class="text-center mt-4">
        <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
            <img src="{{ Storage::url($category->banner) }}" alt="Banner da categoria"
                 class="img-fluid rounded-3 shadow-sm object-fit-cover">
        </div>
    </div>
    @endif

    {{-- Voltar --}}
    <div class="text-center mt-4">
        <a href="{{ route('categories.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

</div>
@endsection
