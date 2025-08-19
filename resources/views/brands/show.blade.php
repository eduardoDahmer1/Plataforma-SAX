@extends('layout.layout')

@section('content')
<div class="container py-4">

    {{-- Voltar --}}
    <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary mb-3">
        <i class="fas fa-arrow-left me-1"></i> Voltar
    </a>

    {{-- Cabeçalho --}}
    <div class="text-center mb-4">
        <h1 class="fw-bold">{{ $brand->name ?? $brand->slug ?? 'N/A' }}</h1>
        <p class="text-muted">Slug: {{ $brand->slug ?? 'N/A' }} | ID: {{ $brand->id }}</p>
    </div>

    {{-- Imagem principal --}}
    <div class="text-center mb-4">
        <div class="ratio ratio-16x9 mx-auto" style="max-width: 600px;">
            @if($brand->image && Storage::disk('public')->exists($brand->image))
                <img src="{{ Storage::url($brand->image) }}" alt="{{ $brand->name ?? $brand->slug }}"
                     class="img-fluid rounded-3 shadow-sm object-fit-contain">
            @else
                <img src="{{ asset('storage/uploads/noimage.webp') }}" alt="Imagem padrão"
                     class="img-fluid rounded-3 shadow-sm object-fit-contain">
            @endif
        </div>
    </div>

    {{-- Banner (opcional) --}}
    @if($brand->banner && Storage::disk('public')->exists($brand->banner))
    <div class="text-center mt-4">
        <div class="ratio ratio-21x9 mx-auto" style="max-width: 900px;">
            <img src="{{ Storage::url($brand->banner) }}" alt="Banner da Marca"
                 class="img-fluid rounded-3 shadow-sm object-fit-cover">
        </div>
    </div>
    @endif

    {{-- Voltar --}}
    <div class="text-center mt-4">
        <a href="{{ route('brands.index') }}" class="btn btn-outline-secondary">
            <i class="fas fa-arrow-left me-1"></i> Voltar
        </a>
    </div>

</div>
@endsection
