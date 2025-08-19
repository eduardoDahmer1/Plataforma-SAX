@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-folder-open me-2"></i>Detalhes da Sub-Subcategoria</h2>

    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body">
            <p><strong><i class="fas fa-hashtag me-1"></i>ID:</strong> {{ $childcategory->id }}</p>
            <p><strong><i class="fas fa-tag me-1"></i>Nome:</strong> {{ $childcategory->name }}</p>
            <p><strong><i class="fas fa-link me-1"></i>Slug:</strong> {{ $childcategory->slug ?? 'Sem Slug' }}</p>
            <p><strong><i class="fas fa-sitemap me-1"></i>Subcategoria Pai:</strong> 
                {{ $childcategory->subcategory->name ?? $childcategory->subcategory->slug ?? 'Sem Subcategoria' }}
            </p>
            <p><strong><i class="fas fa-sitemap me-1"></i>Categoria Pai:</strong>
                {{ $childcategory->subcategory->category->name ?? $childcategory->subcategory->category->slug ?? 'Sem Categoria' }}
            </p>

            {{-- FOTO --}}
            <p><strong><i class="fas fa-image me-1"></i>Foto:</strong></p>
            @if ($childcategory->photo)
                <img src="{{ asset('storage/' . $childcategory->photo) }}" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem foto</p>
            @endif

            {{-- BANNER --}}
            <p><strong><i class="fas fa-images me-1"></i>Banner:</strong></p>
            @if ($childcategory->banner)
                <img src="{{ asset('storage/' . $childcategory->banner) }}" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem banner</p>
            @endif

            <a href="{{ route('admin.childcategories.index') }}" class="btn btn-secondary mt-3"><i class="fas fa-arrow-left me-1"></i>Voltar</a>
        </div>
    </div>
</div>
@endsection
