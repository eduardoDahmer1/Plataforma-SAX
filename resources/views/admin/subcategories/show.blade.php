@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Detalhes da Subcategoria</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $subcategory->id }}</p>
            <p><strong>Nome:</strong> {{ $subcategory->name ?? 'Sem Nome' }}</p>
            <p><strong>Categoria Pai:</strong> {{ $subcategory->category->name ?? $subcategory->category->slug ?? 'Sem Categoria' }}</p>

            {{-- FOTO --}}
            <p><strong>Foto:</strong></p>
            @if ($subcategory->photo)
                <img src="{{ asset('storage/' . $subcategory->photo) }}" alt="Foto" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem foto</p>
            @endif

            {{-- BANNER --}}
            <p><strong>Banner:</strong></p>
            @if ($subcategory->banner)
                <img src="{{ asset('storage/' . $subcategory->banner) }}" alt="Banner" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem banner</p>
            @endif

            <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
        </div>
    </div>
</div>
@endsection
