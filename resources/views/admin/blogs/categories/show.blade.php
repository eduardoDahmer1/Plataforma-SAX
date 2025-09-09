@extends('layout.admin')

@section('content')
<div class="container py-4">
    <h1 class="mb-4">Detalhes da Categoria</h1>

    <div class="mb-3">
        <strong>Nome:</strong> {{ $category->name }}
    </div>

    <div class="mb-3">
        <strong>Banner:</strong><br>
        @if($category->banner && file_exists(storage_path('app/public/' . $category->banner)))
            <img src="{{ asset('storage/' . $category->banner) }}" class="img-fluid rounded" style="max-height:200px;">
        @else
            <span class="text-muted">Nenhum banner enviado</span>
        @endif
    </div>

    <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-secondary">Voltar</a>
</div>
@endsection
