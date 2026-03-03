@extends('layout.layout')

@section('content')
<div class="sax-blog-container py-5">
    <div class="container">
        
        {{-- Cabeçalho Minimalista --}}
        <div class="text-center mb-5 pb-4">
            <h1 class="sax-editorial-title">SAX <span class="thin">News</span></h1>
            <p class="sax-subtitle">CURADORIA DE ESTILO, LUXO E LIFESTYLE</p>
        </div>

        {{-- Navegação de Categorias & Busca --}}
        <div class="row mb-5 pb-3 border-bottom align-items-center">
            <div class="col-md-8">
                <div class="sax-nav-wrapper">
                    <a href="{{ route('blogs.index') }}" class="sax-nav-item {{ !request('category') ? 'active' : '' }}">TODOS</a>
                    @foreach($categories as $cat)
                        <a href="{{ route('blogs.index', ['category' => $cat->id]) }}" 
                           class="sax-nav-item {{ request('category') == $cat->id ? 'active' : '' }}">
                           {{ strtoupper($cat->name) }}
                        </a>
                    @endforeach
                </div>
            </div>
            <div class="col-md-4">
                <form method="GET" class="sax-search-minimal">
                    <input type="text" name="search" placeholder="BUSCAR..." value="{{ request('search') }}">
                    <button type="submit"><i class="fas fa-search"></i></button>
                </form>
            </div>
        </div>

        {{-- SEÇÃO DE DESTAQUE (Apenas se não houver busca/filtro ativa) --}}
        @if(!request('search') && !request('category'))
            @php $featured = $blogs->where('featured', true)->first() ?? $blogs->first(); @endphp
            @if($featured)
                <section class="sax-featured-section mb-5">
                    <div class="row g-0 align-items-center bg-light">
                        <div class="col-lg-8">
                            <div class="featured-img-container">
                                <img src="{{ $featured->image ? Storage::url($featured->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $featured->title }}">
                            </div>
                        </div>
                        <div class="col-lg-4 p-5">
                            <span class="sax-badge-gold">DESTACADO</span>
                            <h2 class="featured-title mt-3">{{ $featured->title }}</h2>
                            <p class="featured-excerpt text-muted mt-3">{{ Str::limit($featured->subtitle, 150) }}</p>
                            <a href="{{ route('blogs.show', $featured->slug) }}" class="sax-btn-link mt-4">LEER ARTÍCULO</a>
                        </div>
                    </div>
                </section>
            @endif
        @endif

        {{-- GRID DE POSTS --}}
        <div class="row g-4">
            @forelse ($blogs as $blog)
                {{-- Pula o post que já apareceu no destaque se for a primeira página --}}
                @if(!request('search') && !request('category') && isset($featured) && $blog->id == $featured->id) @continue @endif

                <div class="col-md-6 col-lg-4">
                    <article class="sax-card-v2">
                        <a href="{{ route('blogs.show', $blog->slug) }}" class="text-decoration-none">
                            <div class="sax-card-img">
                                <img src="{{ $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $blog->title }}">
                                <div class="sax-card-cat">{{ $blog->category->name ?? 'STYLE' }}</div>
                            </div>
                            <div class="sax-card-body mt-3">
                                <div class="sax-card-meta">
                                    <span>{{ \Carbon\Carbon::parse($blog->published_at)->format('d.m.Y') }}</span>
                                    @if($blog->read_time)
                                        <span class="mx-2">•</span>
                                        <span>{{ $blog->read_time }} MIN LEITURA</span>
                                    @endif
                                </div>
                                <h3 class="sax-card-title">{{ $blog->title }}</h3>
                                <p class="sax-card-text">{{ Str::limit($blog->subtitle, 100) }}</p>
                            </div>
                        </a>
                    </article>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <h4 class="text-muted thin">Nenhum item encontrado.</h4>
                </div>
            @endforelse
        </div>

        {{-- PAGINAÇÃO --}}
        <div class="d-flex justify-content-center mt-5 pt-4">
            {{ $blogs->appends(request()->query())->links('pagination::bootstrap-5') }}
        </div>
    </div>
</div>
@endsection

<style>
@import url('https://fonts.googleapis.com/css2?family=Playfair+Display:wght@400;700;900&family=Montserrat:wght@300;400;700&display=swap');

:root {
    --sax-gold: #b2945e;
    --sax-black: #000;
}

/* Tipografia Editorial */
.sax-editorial-title {
    font-family: 'Playfair Display', serif;
    font-weight: 900;
    font-size: 3.5rem;
    letter-spacing: 5px;
    color: var(--sax-black);
}
.sax-editorial-title .thin { font-weight: 400; }
.sax-subtitle {
    font-family: 'Montserrat', sans-serif;
    font-size: 10px;
    letter-spacing: 4px;
    color: #888;
}

/* Navegação e Busca */
.sax-nav-wrapper {
    display: flex;
    gap: 25px;
    overflow-x: auto;
    padding-bottom: 5px;
}
.sax-nav-item {
    text-decoration: none !important;
    color: #999;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1.5px;
    transition: 0.3s;
}
.sax-nav-item.active, .sax-nav-item:hover { color: var(--sax-black); }

.sax-search-minimal {
    display: flex;
    border-bottom: 1px solid #eee;
    padding-bottom: 5px;
}
.sax-search-minimal input {
    border: none;
    outline: none;
    font-size: 11px;
    letter-spacing: 1px;
    width: 100%;
}
.sax-search-minimal button { border: none; background: none; font-size: 12px; }

/* Destaque (Featured) */
.featured-img-container {
    height: 500px;
    overflow: hidden;
}
.featured-img-container img {
    width: 100%; height: 100%; object-fit: cover;
}
.featured-title {
    font-family: 'Playfair Display', serif;
    font-size: 2.2rem;
    font-weight: 700;
    line-height: 1.1;
}
.sax-badge-gold {
    color: var(--sax-gold);
    font-size: 10px;
    font-weight: 700;
    letter-spacing: 2px;
}

/* Card V2 (Grid) */
.sax-card-v2 { transition: transform 0.3s; }
.sax-card-img {
    position: relative;
    height: 350px;
    overflow: hidden;
}
.sax-card-img img {
    width: 100%; height: 100%; object-fit: cover;
    transition: transform 0.6s;
}
.sax-card-v2:hover .sax-card-img img { transform: scale(1.08); }
.sax-card-cat {
    position: absolute;
    top: 20px; left: 20px;
    background: #fff;
    padding: 5px 12px;
    font-size: 9px;
    font-weight: 700;
    letter-spacing: 1px;
}
.sax-card-meta {
    font-size: 10px;
    color: #aaa;
    letter-spacing: 1px;
    margin-bottom: 8px;
}
.sax-card-title {
    font-family: 'Playfair Display', serif;
    font-size: 1.4rem;
    font-weight: 700;
    color: #000;
    line-height: 1.2;
}
.sax-card-text { font-size: 13px; color: #666; line-height: 1.5; }

/* Botões */
.sax-btn-link {
    display: inline-block;
    padding: 10px 25px;
    border: 1px solid #000;
    color: #000;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 1px;
    transition: 0.3s;
}
.sax-btn-link:hover { background: #000; color: #fff; }

@media (max-width: 991px) {
    .featured-img-container { height: 300px; }
}
</style>