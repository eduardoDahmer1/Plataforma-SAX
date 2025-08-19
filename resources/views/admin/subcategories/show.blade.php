@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-folder-open me-2"></i>Detalhes da Subcategoria</h2>

    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body">
            <p><strong><i class="fas fa-hashtag me-1"></i>ID:</strong> {{ $subcategory->id }}</p>
            <p><strong><i class="fas fa-tag me-1"></i>Nome:</strong> {{ $subcategory->name ?? 'Sem Nome' }}</p>
            <p><strong><i class="fas fa-sitemap me-1"></i>Categoria Pai:</strong> {{ $subcategory->category->name ?? $subcategory->category->slug ?? 'Sem Categoria' }}</p>

            {{-- FOTO --}}
            <p><strong><i class="fas fa-image me-1"></i>Foto:</strong></p>
            @if ($subcategory->photo)
                <img src="{{ asset('storage/' . $subcategory->photo) }}" alt="Foto" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem foto</p>
            @endif

            {{-- BANNER --}}
            <p><strong><i class="fas fa-images me-1"></i>Banner:</strong></p>
            @if ($subcategory->banner)
                <img src="{{ asset('storage/' . $subcategory->banner) }}" alt="Banner" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem banner</p>
            @endif

            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>
</div>
@endsection
