@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Detalhes da Subcategoria</h2>

    <p><strong>Nome:</strong> {{ $subcategory->name }}</p>
    <p><strong>Categoria Pai:</strong> {{ $subcategory->category->name ?? $subcategory->category->slug ?? 'Sem Categoria' }}</p>

    @if($subcategory->photo)
    <div class="mb-3">
        <strong>Foto:</strong><br>
        <img src="{{ asset('storage/' . $subcategory->photo) }}" alt="Foto da Subcategoria" style="max-width: 200px;">
    </div>
    @endif

    @if($subcategory->banner)
    <div class="mb-3">
        <strong>Banner:</strong><br>
        <img src="{{ asset('storage/' . $subcategory->banner) }}" alt="Banner da Subcategoria" style="max-width: 300px;">
    </div>
    @endif

    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
