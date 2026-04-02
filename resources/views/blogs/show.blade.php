@extends('layout.layout')

@section('meta_tags')
    <meta name="description" content="{{ $blog->meta_description ?? Str::limit(strip_tags($blog->subtitle), 160) }}">
    <meta property="og:title" content="{{ $blog->title }}">
    <meta property="og:image" content="{{ asset('storage/' . $blog->image) }}">
    <meta property="og:type" content="article">
@endsection

@section('content')
<div class="sax-reading-env">
    {{-- Barra de Progresso --}}
    <div class="reading-progress-bar"></div>

    {{-- Hero Section Editorial --}}
    <header class="sax-hero-header">
        <div class="container-fluid px-md-5">
            <div class="row align-items-center min-vh-75">
                <div class="col-lg-6 order-2 order-lg-1 py-5">
                    <nav class="sax-breadcrumb-minimal mb-4">
                        <a href="{{ route('blogs.index') }}">JOURNAL</a> 
                        <span class="sep">/</span> 
                        <span class="curr">{{ $blog->category->name ?? 'STYLE' }}</span>
                    </nav>

                    <h1 class="sax-main-title animate-up">{{ $blog->title }}</h1>
                    
                    @if($blog->subtitle)
                        <p class="sax-main-subtitle animate-up-delayed">{{ $blog->subtitle }}</p>
                    @endif

                    <div class="sax-author-box d-flex align-items-center mt-5">
                        <div class="author-info">
                            <span class="author-name">POR {{ strtoupper($blog->author ?? 'SAX STYLE') }}</span>
                            <span class="post-date">{{ \Carbon\Carbon::parse($blog->published_at)->translatedFormat('d M, Y') }}</span>
                        </div>
                        @if($blog->read_time)
                            <div class="ms-auto">
                                <span class="read-time-pill"><i class="far fa-clock me-2"></i>{{ $blog->read_time }} MIN LECTURA</span>
                            </div>
                        @endif
                    </div>
                </div>
                <div class="col-lg-6 order-1 order-lg-2 p-0">
                    <div class="hero-image-wrapper">
                        <img src="{{ $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $blog->title }}" class="img-fluid hero-parallax">
                        @if($blog->image_caption)
                            <span class="img-credit">{{ $blog->image_caption }}</span>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </header>

    {{-- Corpo do Artigo com Sidebar --}}
    <div class="container mt-lg-5">
        <div class="row justify-content-center">
            {{-- Conteúdo Principal --}}
            <div class="col-lg-7">
                <div class="sax-article-body drop-cap mb-5">
                    {!! $blog->content !!}
                </div>

                {{-- Footer do Post --}}
                <div class="sax-post-footer-minimal border-top pt-5">
                    <div class="d-flex flex-wrap justify-content-between align-items-center gap-4">
                        <div class="sax-tags">
                            <span class="x-small fw-bold me-3">TAGS:</span>
                            <a href="#" class="tag-item">#LUXURY</a>
                            <a href="#" class="tag-item">#SAXESTILO</a>
                            <a href="#" class="tag-item">#TRENDS</a>
                        </div>
                        <div class="sax-social-share">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" target="_blank" class="share-btn"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://api.whatsapp.com/send?text={{ rawurlencode($blog->title) }}%20{{ url()->current() }}" target="_blank" class="share-btn"><i class="fab fa-whatsapp"></i></a>
                            <a href="javascript:void(0)" onclick="copyToClipboard()" class="share-btn"><i class="fas fa-link"></i></a>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Sidebar Refinada (Desktop Only) --}}
            <div class="col-lg-3 offset-lg-1 d-none d-lg-block">
                <aside class="sax-sidebar-sticky">
                    <div class="sidebar-widget mb-5">
                        <h4 class="widget-title">CATEGORÍAS</h4>
                        <ul class="list-unstyled sax-cat-list">
                            <li><a href="{{ route('blogs.index') }}">TODOS</a></li>
                            {{-- Se você passar $categories na controller, o loop entra aqui --}}
                            <li class="active"><a href="#">{{ $blog->category->name ?? 'Style' }}</a></li>
                        </ul>
                    </div>

                    <div class="sidebar-widget">
                        <h4 class="widget-title">NEWSLETTER</h4>
                        <p class="x-small text-muted">Subscríbete para recibir tendencias exclusivas.</p>
                        <form class="sax-mini-form">
                            <input type="email" placeholder="TU EMAIL" class="form-control mb-2">
                            <button class="btn btn-dark w-100 btn-sm rounded-0">UNIRSE</button>
                        </form>
                    </div>
                </aside>
            </div>
        </div>
    </div>

    {{-- Seção: Outros Artigos (Simulado) --}}
    <section class="sax-related-posts py-5 mt-5 bg-light">
        <div class="container">
            <h3 class="text-center fw-900 mb-5 tracking-tighter">CONTINUAR LEYENDO</h3>
            <div class="row g-4">
                <div class="col-md-4">
                    <div class="mini-post-card">
                        <a href="#" class="text-decoration-none text-dark">
                            <div class="ratio ratio-1x1 overflow-hidden mb-3">
                                <img src="https://images.unsplash.com/photo-1490481651871-ab68de25d43d?q=80&w=500" class="img-hover" alt="">
                            </div>
                            <span class="x-small fw-bold text-gold">LIFESTYLE</span>
                            <h5 class="fw-bold small mt-2">Nuevas tendencias de Milán 2026</h5>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mini-post-card">
                        <a href="#" class="text-decoration-none text-dark">
                            <div class="ratio ratio-1x1 overflow-hidden mb-3">
                                <img src="https://images.unsplash.com/photo-1445205170230-053b83016050?q=80&w=500" class="img-hover" alt="">
                            </div>
                            <span class="x-small fw-bold text-gold">FASHION</span>
                            <h5 class="fw-bold small mt-2">El arte de la sastrería moderna</h5>
                        </a>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="mini-post-card text-center d-flex flex-column justify-content-center border p-4">
                        <h5 class="fw-light italic">¿Te gusta lo que lees?</h5>
                        <a href="{{ route('blogs.index') }}" class="btn btn-outline-dark btn-sm rounded-0 mt-3">VER TODO EL BLOG</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
</div>

@endsection
