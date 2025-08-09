@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Detalhes da Categoria</h2>

    <div class="card">
        <div class="card-body">
            <p><strong>ID:</strong> {{ $category->id }}</p>
            <p><strong>Nome:</strong> {{ $category->name ?? 'Sem Nome' }}</p>
            <p><strong>Slug:</strong> {{ $category->slug ?? 'Sem Slug' }}</p>

            {{-- FOTO --}}
            <p><strong>Foto:</strong></p>
            @if ($category->photo)
                <img src="{{ asset('storage/' . $category->photo) }}" alt="Foto" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem foto</p>
            @endif

            {{-- BANNER --}}
            <p><strong>Banner:</strong></p>
            @if ($category->banner)
                <img src="{{ asset('storage/' . $category->banner) }}" alt="Banner" width="150" class="img-thumbnail mb-3">
            @else
                <p class="text-muted">Sem banner</p>
            @endif

            <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mt-3">Voltar</a>
        </div>
    </div>
</div>
@endsection
