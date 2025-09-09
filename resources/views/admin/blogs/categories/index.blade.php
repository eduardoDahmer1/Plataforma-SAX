@extends('layout.admin')
@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Gerenciar Categorias</h1>
        <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-success">
            <i class="fa fa-plus me-1"></i> Nova Categoria
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif

    @if($categories->count())
        <div class="row g-3">
            @foreach($categories as $cat)
                <div class="col-md-6 col-lg-4">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column">
                            @if($cat->banner && Storage::disk('public')->exists($cat->banner))
                                <img src="{{ Storage::url($cat->banner) }}" class="img-fluid mb-2 rounded" alt="{{ $cat->name }}">
                            @endif
                            <h5 class="card-title">{{ $cat->name }}</h5>

                            <div class="mt-auto d-flex flex-column flex-md-row gap-2">
                                <a href="{{ route('admin.blog-categories.edit', $cat) }}" class="btn btn-warning btn-sm flex-fill">
                                    <i class="fa fa-edit me-1"></i> Editar
                                </a>
                                <a href="{{ route('admin.blog-categories.show', $cat) }}" class="btn btn-info btn-sm flex-fill">
                                    <i class="fa fa-eye me-1"></i> Visualizar
                                </a>
                                <form action="{{ route('admin.blog-categories.destroy', $cat) }}" method="POST" class="flex-fill m-0" onsubmit="return confirm('Excluir categoria?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-danger btn-sm w-100">
                                        <i class="fa fa-trash me-1"></i> Excluir
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @else
        <p class="text-muted">Nenhuma categoria encontrada.</p>
    @endif
</div>
@endsection
