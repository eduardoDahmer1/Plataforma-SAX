@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-3">
        <h2>Categorias</h2>
        <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">Nova Categoria</a>
        <a href="{{ route('admin.index') }}" class="btn btn-primary">Admin</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    <div class="row">
        @foreach($categories as $category)
            <div class="col-md-4 mb-3">
                <div class="card shadow-sm p-3">
                    <h5>{{ $category->name }}</h5>
                    <p class="text-muted">Slug: {{ $category->slug }}</p>
                    <div class="d-flex justify-content-between">
                        <a href="{{ route('admin.categories.show', $category) }}" class="btn btn-info btn-sm">Ver</a>
                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-warning btn-sm">Editar</a>
                        <form action="{{ route('admin.categories.destroy', $category) }}" method="POST" onsubmit="return confirm('Tem certeza?')">
                            @csrf
                            @method('DELETE')
                            <button class="btn btn-danger btn-sm">Excluir</button>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="d-flex justify-content-center mt-4">
        {{ $categories->links() }}
    </div>
</div>
@endsection
