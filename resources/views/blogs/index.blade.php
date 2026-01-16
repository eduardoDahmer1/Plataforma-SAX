@extends('layout.layout')

@section('content')
<div class="sax-blog-container py-5">
    <div class="container">
        
        {{-- Título Editorial --}}
        <div class="text-center mb-5">
            <h1 class="sax-blog-title">#SAXNEWS</h1>
            <div class="sax-divider mx-auto"></div>
            <p class="text-muted mt-2 small text-uppercase letter-spacing-2">Tendências, Estilo e Lifestyle</p>
        </div>

        {{-- Filtros e Busca --}}
        <div class="row mb-5 align-items-center">
            <div class="col-lg-8 order-2 order-lg-1">
                <div class="sax-categories-filter d-flex flex-wrap gap-3">
                    <a href="{{ route('blogs.index') }}"
                       class="sax-filter-link {{ request('category') == '' ? 'active' : '' }}">
                        TODOS
                    </a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blogs.index', array_merge(request()->all(), ['category' => $cat->id])) }}"
                           class="sax-filter-link {{ request('category') == $cat->id ? 'active' : '' }}">
                           {{ strtoupper($cat->name) }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-lg-4 order-1 order-lg-2 mb-4 mb-lg-0">
                <form method="GET" class="sax-blog-search">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control"
                               placeholder="BUSCAR NO BLOG..."
                               value="{{ request('search') }}">
                        <button class="btn border-0"><i class="fas fa-search"></i></button>
                    </div>
                    @if(request('category'))
                        <input type="hidden" name="category" value="{{ request('category') }}">
                    @endif
                </form>
            </div>
        </div>

        {{-- Banner da categoria --}}
        @if(request('category'))
            @php $selectedCategory = $categories->firstWhere('id', request('category')); @endphp
            @if($selectedCategory && $selectedCategory->banner)
                <div class="sax-category-banner mb-5 overflow-hidden">
                    <img src="{{ Storage::url($selectedCategory->banner) }}" alt="{{ $selectedCategory->name }}" class="w-100">
                    <div class="banner-overlay text-white">
                        <h2 class="m-0">{{ strtoupper($selectedCategory->name) }}</h2>
                    </div>
                </div>
            @endif
        @endif

        {{-- Lista de Blogs --}}
        <div class="row g-5">
            @forelse ($blogs as $blog)
            <div class="col-md-6 col-lg-4">
                <article class="sax-post-card">
                    <a href="{{ route('blogs.show', $blog->slug) }}" class="text-decoration-none">
                        <div class="post-img-wrapper mb-3">
                            @php
                                $imagePath = ($blog->image && Storage::disk('public')->exists($blog->image))
                                    ? Storage::url($blog->image)
                                    : asset('storage/uploads/noimage.webp');
                            @endphp
                            <img src="{{ $imagePath }}" class="img-fluid" alt="{{ $blog->title }}">
                            <span class="post-category-tag">{{ $blog->category->name ?? 'STYLE' }}</span>
                        </div>
                        
                        <div class="post-content">
                            <h3 class="post-title">{{ $blog->title }}</h3>
                            <p class="post-excerpt text-muted">
                                {{ Str::limit($blog->subtitle, 100) }}
                            </p>
                            <span class="sax-read-more">CONTINUAR LENDO <i class="fas fa-arrow-right ms-2"></i></span>
                        </div>
                    </a>
                </article>
            </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">Nenhum artigo encontrado para esta busca.</p>
                </div>
            @endforelse
        </div>

        {{-- Paginação SAX Style --}}
        <div class="d-flex justify-content-center mt-5">
            {{ $blogs->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection
<style>
    /* Container Geral */
.sax-blog-container {
    background-color: #fff;
    color: #000;
}

/* Tipografia */
.sax-blog-title {
    font-family: 'Playfair Display', serif; /* Opcional, ou Helvetica bold */
    font-weight: 900;
    font-size: 3rem;
    letter-spacing: -1px;
    margin-bottom: 0;
}

.letter-spacing-2 { letter-spacing: 2px; }

.sax-divider {
    width: 60px;
    height: 3px;
    background-color: #000;
}

/* Busca */
.sax-blog-search {
    border-bottom: 1px solid #000;
}
.sax-blog-search .form-control {
    border: none !important;
    background: transparent !important;
    font-size: 12px;
    font-weight: 600;
    padding-left: 0;
}
.sax-blog-search .form-control:focus { box-shadow: none; }

/* Filtros */
.sax-filter-link {
    text-decoration: none;
    color: #999;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    transition: all 0.3s ease;
    border-bottom: 2px solid transparent;
    padding-bottom: 5px;
}
.sax-filter-link:hover, .sax-filter-link.active {
    color: #000;
    border-bottom-color: #000;
}

/* Banner de Categoria */
.sax-category-banner {
    position: relative;
    max-height: 350px;
}
.banner-overlay {
    position: absolute;
    top: 0; left: 0; width: 100%; height: 100%;
    background: rgba(0,0,0,0.3);
    display: flex;
    align-items: center;
    justify-content: center;
}
.banner-overlay h2 {
    font-size: 2.5rem;
    font-weight: 800;
    letter-spacing: 4px;
}

/* Card de Postagem */
.sax-post-card {
    transition: all 0.4s ease;
}

.post-img-wrapper {
    position: relative;
    overflow: hidden;
}

.post-img-wrapper img {
    width: 100%;
    height: 280px;
    object-fit: cover;
    transition: transform 0.8s ease;
}

.sax-post-card:hover img {
    transform: scale(1.05);
}

.post-category-tag {
    position: absolute;
    bottom: 0;
    left: 0;
    background: #000;
    color: #fff;
    font-size: 10px;
    font-weight: bold;
    padding: 5px 15px;
    text-transform: uppercase;
}

.post-title {
    font-size: 18px;
    font-weight: 700;
    margin: 15px 0 10px;
    color: #000;
    line-height: 1.3;
}

.post-excerpt {
    font-size: 14px;
    line-height: 1.6;
    margin-bottom: 15px;
}

.sax-read-more {
    font-size: 11px;
    font-weight: 800;
    color: #000;
    letter-spacing: 1px;
    position: relative;
}

.sax-read-more::after {
    content: "";
    position: absolute;
    width: 0;
    height: 1px;
    bottom: -2px;
    left: 0;
    background: #000;
    transition: width 0.3s ease;
}

.sax-post-card:hover .sax-read-more::after {
    width: 100%;
}

/* Customização Paginação (Bootstrap 5) */
.pagination .page-link {
    border: none;
    color: #000;
    font-weight: bold;
    margin: 0 5px;
}
.pagination .page-item.active .page-link {
    background-color: #000;
    border-radius: 50%;
}
</style>