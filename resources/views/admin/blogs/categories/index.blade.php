@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">

    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Categorías de Blog</h1>
            <p class="small text-secondary mb-0">Organización jerárquica de contenido editorial</p>
        </div>
        <a href="{{ route('admin.blog-categories.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
            <i class="fa fa-plus me-2"></i> Nueva Categoria
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-dark border-0 rounded-0 small mb-4 py-3 shadow-sm d-flex justify-content-between align-items-center" role="alert">
            <span><i class="fa fa-check me-2"></i> {{ session('success') }}</span>
            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if($categories->count())
    <div class="row g-4">
        @foreach($categories as $cat)
            <div class="col-sm-6 col-lg-3">
                <div class="sax-category-card border-0 bg-transparent h-100">
                    
                    {{-- Imagem em Aspect Ratio 1:1 ou 4:3 --}}
                    <div class="category-img-container mb-3 position-relative overflow-hidden">
                        @if($cat->banner && Storage::disk('public')->exists($cat->banner))
                            <img src="{{ Storage::url($cat->banner) }}" alt="{{ $cat->name }}" class="grayscale-hover">
                        @else
                            <div class="no-image-placeholder x-small text-uppercase fw-bold text-muted">
                                No Image
                            </div>
                        @endif
                        
                        {{-- Overlay de Ações (Aparece no hover) --}}
                        <div class="category-overlay">
                            <a href="{{ route('admin.blog-categories.edit', $cat) }}" class="btn btn-light btn-sm rounded-0 x-small fw-bold border">EDITAR</a>
                        </div>
                    </div>

                    <div class="category-info">
                        <h6 class="text-uppercase tracking-tighter fw-bold mb-1 fs-7">{{ $cat->name }}</h6>
                        <div class="d-flex justify-content-between align-items-center">
                            <span class="x-small text-secondary text-uppercase tracking-wider">ID #{{ $cat->id }}</span>
                            
                            <div class="d-flex gap-3 align-items-center">
                                <a href="{{ route('admin.blog-categories.show', $cat) }}" class="text-dark x-small fw-bold text-decoration-none hover-underline">VER</a>
                                
                                <form action="{{ route('admin.blog-categories.destroy', $cat) }}" method="POST" class="m-0" onsubmit="return confirm('¿Eliminar categoría?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">ELIMINAR</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @else
        <div class="py-5 text-center border">
            <p class="text-muted small italic mb-0">No se encontraron categorías registradas.</p>
        </div>
    @endif

</div>

<style>
    /* Minimalist Category UI */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .fs-7 { font-size: 0.8rem; }
    .italic { font-style: italic; }

    /* Container de Imagem */
    .category-img-container {
        aspect-ratio: 4 / 3;
        background: #f8f9fa;
        border: 1px solid #eee;
    }

    .category-img-container img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.5s ease, filter 0.5s ease;
    }

    .grayscale-hover {
        filter: grayscale(100%);
    }

    .sax-category-card:hover .grayscale-hover {
        filter: grayscale(0%);
        transform: scale(1.05);
    }

    /* Placeholder quando não há imagem */
    .no-image-placeholder {
        width: 100%;
        height: 100%;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f1f1f1;
    }

    /* Overlay Minimalista */
    .category-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background: rgba(255,255,255,0.1);
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0;
        transition: 0.3s ease;
    }

    .sax-category-card:hover .category-overlay {
        opacity: 1;
        background: rgba(255,255,255,0.4);
    }

    /* Utilitários */
    .btn-clean { background: none; border: none; padding: 0; cursor: pointer; }
    .hover-underline:hover { text-decoration: underline !important; }
</style>
@endsection