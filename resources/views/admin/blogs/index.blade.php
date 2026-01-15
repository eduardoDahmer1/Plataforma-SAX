@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Contenido Editorial</h1>
            <p class="small text-secondary mb-0">Gestión de artículos y noticias del blog</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-outline-dark btn-sm rounded-0 px-3 text-uppercase fw-bold x-small tracking-wider">
                Categorías
            </a>
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
                <i class="fa fa-plus me-2"></i> Nuevo Artículo
            </a>
        </div>
    </div>

    {{-- Tabela de Blogs --}}
    <div class="table-responsive">
        <table class="table align-middle">
            <thead class="bg-white">
                <tr class="text-uppercase x-small tracking-wider text-secondary">
                    <th class="py-3 border-0 fw-bold">Título del Artículo</th>
                    <th class="py-3 border-0 fw-bold">Estado</th>
                    <th class="py-3 border-0 fw-bold">Fecha de Publicación</th>
                    <th class="py-3 border-0 fw-bold text-end">Acciones</th>
                </tr>
            </thead>
            <tbody class="border-top-0">
                @forelse($blogs as $blog)
                <tr class="border-bottom clickable-row">
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            {{-- Miniatura discreta ou ícone --}}
                            <div class="blog-icon-box me-3">
                                <i class="far fa-file-alt text-muted"></i>
                            </div>
                            <div>
                                <span class="d-block fw-bold text-dark text-uppercase small tracking-tighter">{{ $blog->title }}</span>
                                <span class="x-small text-muted italic">slug: {{ $blog->slug }}</span>
                            </div>
                        </div>
                    </td>
                    <td class="py-4">
                        <div class="d-flex align-items-center">
                            <span class="status-dot {{ $blog->published_at ? 'published' : 'draft' }}"></span>
                            <span class="x-small text-uppercase fw-bold {{ $blog->published_at ? 'text-dark' : 'text-muted' }}">
                                {{ $blog->published_at ? 'Publicado' : 'Borrador' }}
                            </span>
                        </div>
                    </td>
                    <td class="py-4">
                        <span class="x-small text-secondary">
                            {{ $blog->published_at ? $blog->published_at->format('d M, Y') : '—' }}
                        </span>
                    </td>
                    <td class="py-4 text-end">
                        <div class="d-flex justify-content-end gap-3 align-items-center">
                            <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                VISTA PREVIA
                            </a>
                            <a href="{{ route('admin.blogs.edit', $blog) }}" class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                EDITAR
                            </a>
                            <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('¿Eliminar artículo?')" class="m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    ELIMINAR
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="4" class="text-center py-5 text-muted small italic">No hay artículos redactados todavía.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-5 d-flex justify-content-center">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
</div>

<style>
    /* Editorial Minimalist UI */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    
    .blog-icon-box {
        width: 40px; height: 40px;
        background: #f8f9fa;
        display: flex; align-items: center; justify-content: center;
        border-radius: 2px;
    }

    /* Status Dots */
    .status-dot { height: 6px; width: 6px; border-radius: 50%; display: inline-block; margin-right: 8px; }
    .status-dot.published { background: #10b981; } /* Emerald */
    .status-dot.draft { background: #dee2e6; }     /* Grey */

    /* Buttons and Links */
    .btn-clean { background: none; border: none; padding: 0; cursor: pointer; }
    .hover-underline:hover { text-decoration: underline !important; }
    
    .table td { border-bottom: 1px solid #f1f1f1; transition: 0.2s; }
    .clickable-row:hover td { background-color: #fafafa; }

    /* Custom Pagination Overrides */
    .pagination { --bs-pagination-border-radius: 0; --bs-pagination-color: #000; --bs-pagination-active-bg: #000; --bs-pagination-active-border-color: #000; }
</style>
@endsection