@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header com Estilo SAX Admin --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 fw-bold text-uppercase tracking-tighter mb-0">Contenido Editorial</h1>
            <p class="small text-muted mb-0">Gestión de artículos y noticias del blog</p>
        </div>
        <div class="d-flex gap-2">
            <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-white border rounded-0 btn-sm fw-bold x-small tracking-wider px-3">
                CATEGORÍAS
            </a>
            <a href="{{ route('admin.blogs.create') }}" class="btn btn-dark rounded-0 btn-sm fw-bold x-small tracking-wider px-3">
                <i class="fa fa-plus me-1"></i> NUEVO ARTÍCULO
            </a>
        </div>
    </div>

    {{-- Abas de Filtro --}}
    <div class="d-flex gap-4 mb-4 border-bottom pb-2">
        <a href="#" class="filter-tab active">TODOS ({{ $blogs->total() }})</a>
        <a href="#" class="filter-tab">PUBLICADOS</a>
        <a href="#" class="filter-tab">BORRADORES</a>
    </div>

    {{-- Lista de Cards --}}
    <div class="sax-admin-list">
        @forelse($blogs as $blog)
        <div class="sax-admin-card mb-3">
            <div class="row align-items-center g-0">
                {{-- Imagem --}}
                <div class="col-auto">
                    <div class="sax-card-img-box">
                        <img src="{{ $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp') }}" alt="">
                    </div>
                </div>

                {{-- Conteúdo --}}
                <div class="col ps-3">
                    <div class="mb-1">
                        <span class="badge bg-light text-muted border rounded-0 x-small-7 fw-bold">{{ $blog->category->name ?? 'STYLE' }}</span>
                    </div>
                    <h6 class="fw-bold mb-1 text-uppercase small tracking-tight">{{ $blog->title }}</h6>
                    <div class="d-flex align-items-center gap-2">
                        <span class="x-small text-muted italic">/{{ $blog->slug }}</span>
                        <span class="status-pill {{ $blog->is_active ? 'active' : 'draft' }}">
                            {{ $blog->is_active ? 'PUBLICADO' : 'BORRADOR' }}
                        </span>
                    </div>
                </div>

                {{-- Data --}}
                <div class="col-auto px-4 d-none d-lg-block border-start h-100">
                    <div class="text-center">
                        <span class="d-block x-small-7 text-muted fw-bold tracking-wider">PUBLICACIÓN</span>
                        <span class="d-block small fw-bold">{{ $blog->published_at ? $blog->published_at->format('d M, Y') : '—' }}</span>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="col-auto px-4 border-start">
                    <div class="d-flex gap-3">
                        <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="action-icon" title="Vista Previa">
                            <i class="far fa-eye"></i>
                        </a>
                        <a href="{{ route('admin.blogs.edit', $blog) }}" class="action-icon" title="Editar">
                            <i class="far fa-edit"></i>
                        </a>
                        <form action="{{ route('admin.blogs.destroy', $blog) }}" method="POST" onsubmit="return confirm('¿Eliminar?')" class="m-0">
                            @csrf @method('DELETE')
                            <button type="submit" class="action-icon text-danger">
                                <i class="far fa-trash-alt"></i>
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        @empty
        <div class="bg-white border p-5 text-center">
            <p class="text-muted mb-0">No hay artículos para mostrar.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
</div>

<style>
    /* Admin Editorial UI Overrides */
    .x-small { font-size: 0.65rem; }
    .x-small-7 { font-size: 0.6rem; }
    .tracking-wider { letter-spacing: 0.1em; }
    .italic { font-style: italic; }
    
    /* Tabs */
    .filter-tab { font-size: 0.65rem; font-weight: 800; text-decoration: none; color: #999; letter-spacing: 1px; padding-bottom: 8px; border-bottom: 2px solid transparent; transition: 0.3s; }
    .filter-tab:hover, .filter-tab.active { color: #000; border-bottom-color: #000; }

    /* Card Styling */
    .sax-admin-card { background: #fff; border: 1px solid #eee; transition: 0.2s; }
    .sax-admin-card:hover { border-color: #ccc; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }

    .sax-card-img-box { width: 80px; height: 80px; overflow: hidden; }
    .sax-card-img-box img { width: 100%; height: 100%; object-fit: cover; }

    /* Pill */
    .status-pill { font-size: 0.55rem; font-weight: 900; padding: 2px 8px; border-radius: 50px; }
    .status-pill.active { background: #dcfce7; color: #15803d; }
    .status-pill.draft { background: #f3f4f6; color: #6b7280; }

    /* Icons */
    .action-icon { color: #888; text-decoration: none; font-size: 0.9rem; transition: 0.2s; background: none; border: none; padding: 0; }
    .action-icon:hover { color: #000; }
    .action-icon.text-danger:hover { color: #dc3545; }

    /* Pagination */
    .pagination { --bs-pagination-border-radius: 0; --bs-pagination-color: #000; --bs-pagination-active-bg: #000; }
</style>
@endsection