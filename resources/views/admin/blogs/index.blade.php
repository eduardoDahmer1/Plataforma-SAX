@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Conteúdo Editorial"
        description="Gestão de artigos e notícias do blog">
        <x-slot:actions>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.blog-categories.index') }}" class="btn btn-white border rounded-0 btn-sm fw-bold x-small tracking-wider px-3">
                    CATEGORIAS
                </a>
                <a href="{{ route('admin.blogs.create') }}" class="btn btn-dark rounded-0 btn-sm fw-bold x-small tracking-wider px-3">
                    <i class="fa fa-plus me-1"></i> NOVO ARTIGO
                </a>
            </div>
        </x-slot:actions>
    </x-admin.page-header>

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
                            {{ $blog->is_active ? 'PUBLICADO' : 'RASCUNHO' }}
                        </span>
                    </div>
                </div>

                {{-- Data --}}
                <div class="col-auto px-4 d-none d-lg-block border-start h-100">
                    <div class="text-center">
                        <span class="d-block x-small-7 text-muted fw-bold tracking-wider">PUBLICAÇÃO</span>
                        <span class="d-block small fw-bold">{{ $blog->published_at ? $blog->published_at->format('d M, Y') : '—' }}</span>
                    </div>
                </div>

                {{-- Ações --}}
                <div class="col-auto px-4 border-start">
                    <div class="d-flex gap-3">
                        <a href="{{ route('blogs.show', $blog->slug) }}" target="_blank" class="action-icon" title="Vista Prévia">
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
            <p class="text-muted mb-0">Não há artigos para mostrar.</p>
        </div>
        @endforelse
    </div>

    <div class="mt-4">
        {{ $blogs->links('pagination::bootstrap-4') }}
    </div>
</x-admin.card>
@endsection
