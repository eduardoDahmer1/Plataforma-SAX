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
            @if($subcategory->photo && Storage::disk('public')->exists($subcategory->photo))
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
        </div>
    </div>

    {{-- Banner --}}
    @if($subcategory->banner && Storage::disk('public')->exists($subcategory->banner))
    <div class="text-center mt-4">
        <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
            <img src="{{ Storage::url($subcategory->banner) }}" alt="Banner da Subcategoria"
                 class="img-fluid rounded-3 shadow-sm object-fit-cover">
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
