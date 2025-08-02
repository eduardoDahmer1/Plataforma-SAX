@extends('layout.admin')

@section('content')
<div class="container">
    <h2>Detalhes da Subcategoria</h2>

    <p><strong>Nome:</strong> {{ $subcategory->name }}</p>
    <p><strong>Categoria Pai:</strong> {{ $subcategory->category->name ?? $subcategory->category->slug ?? 'Sem Categoria' }}</p>

    <a href="{{ route('admin.subcategories.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
