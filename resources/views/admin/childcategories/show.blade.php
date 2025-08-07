@extends('layout.admin') {{-- Ajuste se o layout correto tiver outro nome --}}

@section('content')
<div class="container mt-4">
    <h2>Detalhes da Subsubcategoria</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $childcategory->id }}</p>

            <p><strong>Nome:</strong> {{ $childcategory->name }}</p>

            <p><strong>Slug:</strong> {{ $childcategory->slug ?? 'Sem Slug' }}</p>

            <p><strong>Subcategoria:</strong> 
                {{ $childcategory->subcategory->name ?? $childcategory->subcategory->slug ?? 'Sem Subcategoria' }}
            </p>

            <p><strong>Categoria Pai:</strong>
                {{ $childcategory->subcategory->category->name ?? $childcategory->subcategory->category->slug ?? 'Sem Categoria' }}
            </p>

            {{-- FOTO --}}
            <p><strong>Foto:</strong></p>
            @if ($childcategory->photo)
                <img src="{{ asset('storage/' . $childcategory->photo) }}" alt="Foto" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem foto</p>
            @endif

            {{-- BANNER --}}
            <p><strong>Banner:</strong></p>
            @if ($childcategory->banner)
                <img src="{{ asset('storage/' . $childcategory->banner) }}" alt="Banner" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem banner</p>
            @endif

            <a href="{{ route('admin.childcategories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
        </div>
    </div>
</div>
@endsection
