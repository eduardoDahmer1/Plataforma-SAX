@extends('layout.admin')
@section('content')
<div class="container py-4">

    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center mb-3 gap-2">
        <h1 class="mb-2 mb-md-0">Gerenciar Blogs</h1>
        <div class="d-flex gap-2 flex-wrap">
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-success">
                <i class="fa fa-plus me-1"></i> Novo Blog
            </a>
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-primary">
                <i class="fa fa-folder me-1"></i> Gerenciar Categorias
            </a>
        </div>
    </div>

    <!-- Lista de Blogs -->
    <div class="row g-3">
        @forelse($blogs as $blog)
            <div class="col-md-6 col-lg-4">
                <div class="card shadow-sm h-100">
                    <div class="card-body d-flex flex-column">
                        <h5 class="card-title">{{ $blog->title }}</h5>
                        <p class="text-muted mb-2">
                            <i class="fa fa-calendar me-1"></i>
                            {{ $blog->published_at ? $blog->published_at->format('d/m/Y') : 'Não publicado' }}
                        </p>
                        <div class="mt-auto d-flex flex-column flex-md-row gap-2">
                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="btn btn-warning btn-sm flex-fill">
                                <i class="fa fa-edit me-1"></i> Editar
                            </a>
                            <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="btn btn-success btn-sm flex-fill">
                                <i class="fa fa-eye me-1"></i> Ver
                            </a>
                            <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" class="flex-fill m-0" onsubmit="return confirm('Excluir blog?')">
                                @csrf @method('DELETE')
                                <button class="btn btn-danger btn-sm w-100">
                                    <i class="fa fa-trash me-1"></i> Excluir
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <p class="text-muted text-center">Nenhum blog encontrado.</p>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="d-flex justify-content-center mt-4">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
</div>
@endsection
