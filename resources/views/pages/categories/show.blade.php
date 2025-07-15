@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <h2>Detalhes da Categoria</h2>

    <div class="card p-4 shadow-sm">
        <p><strong>Nome:</strong> {{ $category->name }}</p>
        <p><strong>Slug:</strong> {{ $category->slug }}</p>
    </div>

    <a href="{{ route('admin.categories.index') }}" class="btn btn-secondary mt-3">Voltar para Lista</a>
    <a href="{{ route('admin.index') }}" class="btn btn-secondary mt-3">Admin</a>
</div>
@endsection
