@extends('layout.layout')

@section('content')
<div class="sax-article-wrapper py-5">
    <div class="container">
        
        {{-- Navegação Superior Discreta --}}
        <div class="mb-5">
            <a href="{{ route('blogs.index') }}" class="sax-back-link">
                <i class="fas fa-long-arrow-alt-left me-2"></i> VOLVER A #SAXNEWS
            </a>
        </div>

        {{-- Cabeçalho do Artigo --}}
        <header class="text-center mb-5 blog-header-sax">
            @if($blog->category)
                <div class="sax-category-eyebrow mb-3">
                    {{ strtoupper($blog->category->name) }}
                </div>
            @endif

            <h1 class="sax-article-title mb-4">{{ $blog->title }}</h1>

            @if($blog->subtitle)
                <p class="sax-article-subtitle text-muted mx-auto">
                    {{ $blog->subtitle }}
                </p>
            @endif

            <div class="sax-meta-info mt-4">
                @if($blog->author)
                    <span class="me-3">POR <strong>{{ strtoupper($blog->author) }}</strong></span>
                @endif
                @if($blog->published_at)
                    <span>{{ \Carbon\Carbon::parse($blog->published_at)->translatedFormat('d \d\e F, Y') }}</span>
                @endif
            </div>
        </header>

        {{-- Imagem de Capa Imponente --}}
        <div class="sax-main-image-container mb-5">
            @php
                $imagePath = ($blog->image && Storage::disk('public')->exists($blog->image))
                    ? Storage::url($blog->image)
                    : asset('storage/uploads/noimage.webp');
            @endphp
            <img src="{{ $imagePath }}" alt="{{ $blog->title }}" class="img-fluid w-100">
        </div>

        {{-- Corpo do Texto --}}
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <article class="sax-article-body">
                    <div class="content-rich-text">
                        {!! $blog->content !!}
                    </div>
                </article>

                {{-- Rodapé do Artigo --}}
                <div class="sax-article-footer border-top pt-4 mt-5 d-flex justify-content-between align-items-center">
                    <div class="share-links">
                        <span class="small fw-bold me-3">COMPARTIR:</span>
                        <a href="#" class="me-2 text-dark"><i class="fab fa-facebook-f"></i></a>
                        <a href="#" class="me-2 text-dark"><i class="fab fa-instagram"></i></a>
                        <a href="#" class="text-dark"><i class="fab fa-whatsapp"></i></a>
                    </div>
                    <a href="{{ route('blogs.index') }}" class="sax-btn-dark">VOLVER</a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

<style>
    /* Container Principal */
.sax-article-wrapper {
    background-color: #fff;
    color: #000;
}

/* Link Voltar */
.sax-back-link {
    color: #999;
    text-decoration: none;
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 2px;
    transition: color 0.3s;
}
.sax-back-link:hover {
    color: #000;
}

/* Tipografia de Título */
.sax-article-title {
    font-family: 'Playfair Display', serif; /* Ou Helvetica Neue Bold */
    font-size: 3.5rem;
    font-weight: 900;
    line-height: 1.1;
    letter-spacing: -2px;
    max-width: 900px;
    margin-left: auto;
    margin-right: auto;
}

.sax-article-subtitle {
    font-size: 1.25rem;
    max-width: 700px;
    line-height: 1.6;
}

.sax-category-eyebrow {
    font-size: 12px;
    font-weight: 800;
    letter-spacing: 3px;
    color: #b2945e; /* Dourado SAX */
}

.sax-meta-info {
    font-size: 11px;
    letter-spacing: 1px;
    color: #666;
}

/* Imagem Central */
.sax-main-image-container img {
    max-height: 600px;
    object-fit: cover;
    border-radius: 0; /* Luxo usa linhas retas */
}

/* Corpo do Artigo */
.sax-article-body {
    font-family: 'Georgia', serif; /* Estilo editorial para leitura longa */
    font-size: 1.15rem;
    line-height: 1.8;
    color: #222;
}

/* Estilização para imagens ou elementos dentro do content do banco */
.content-rich-text img {
    max-width: 100%;
    height: auto;
    margin: 2rem 0;
}

.content-rich-text h2, .content-rich-text h3 {
    font-family: 'Helvetica Neue', Arial, sans-serif;
    font-weight: 700;
    margin-top: 2rem;
}

/* Botão Estilo SAX */
.sax-btn-dark {
    background: #000;
    color: #fff;
    padding: 10px 30px;
    text-decoration: none;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 1px;
    transition: opacity 0.3s;
}
.sax-btn-dark:hover {
    color: #fff;
    opacity: 0.8;
}

/* Responsividade */
@media (max-width: 991px) {
    .sax-article-title {
        font-size: 2.2rem;
        letter-spacing: -1px;
    }
}
</style>