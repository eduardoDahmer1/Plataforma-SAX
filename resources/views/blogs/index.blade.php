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
