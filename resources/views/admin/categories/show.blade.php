@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2 class="mb-4"><i class="fas fa-folder-open me-2"></i>Detalhes da Categoria</h2>

    <div class="card shadow-sm rounded-4 border-0">
        <div class="card-body">
            <p><strong><i class="fas fa-hashtag me-1"></i>ID:</strong> {{ $category->id }}</p>
            <p><strong><i class="fas fa-tag me-1"></i>Nome:</strong> {{ $category->name ?? 'Sem Nome' }}</p>
            <p><strong><i class="fas fa-link me-1"></i>Slug:</strong> {{ $category->slug ?? 'Sem Slug' }}</p>

            {{-- FOTO --}}
            <p><strong><i class="fas fa-image me-1"></i>Foto:</strong></p>
            @if ($category->photo)
                <img src="{{ asset('storage/' . $category->photo) }}" alt="Foto" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem foto</p>
            @endif

            {{-- BANNER --}}
            <p><strong><i class="fas fa-images me-1"></i>Banner:</strong></p>
            @if ($category->banner)
                <img src="{{ asset('storage/' . $category->banner) }}" alt="Banner" class="img-thumbnail mb-3" style="max-width:150px;">
            @else
                <p class="text-muted"><i class="fas fa-ban me-1"></i>Sem banner</p>
            @endif

            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mt-3">
                <i class="fas fa-arrow-left me-1"></i>Voltar
            </a>
        </div>
    </div>
</div>
@endsection
