@extends('layout.layout')

@section('title', $blog->title)
@section('meta_description', $blog->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($blog->subtitle), 160))

@section('meta_tags')
    <meta name="description" content="{{ $blog->meta_description ?? \Illuminate\Support\Str::limit(strip_tags($blog->subtitle), 160) }}">
    <meta property="og:title" content="{{ $blog->title }}">
    <meta property="og:image" content="{{ $blog->image ? asset('storage/' . $blog->image) : asset('storage/uploads/noimage.webp') }}">
    <meta property="og:type" content="article">
@endsection

@section('content')
@php
    $coverUrl = $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp');
@endphp
<div class="reading-progress-bar"></div>

<div class="blog-article-page">
    <section class="blog-article-hero">
        <div class="container">
            <nav class="blog-breadcrumb">
                <a href="{{ route('blogs.index') }}">Blog</a>
                <span>/</span>
                <span>{{ $blog->category->name ?? 'SAX News' }}</span>
            </nav>

            <div class="blog-article-hero__grid">
                <div>
                    <span class="blog-kicker">{{ $blog->category->name ?? 'Blog' }}</span>
                    <h1 class="blog-article-title mt-3">{{ $blog->title }}</h1>

                    @if ($blog->subtitle)
                        <p class="blog-article-lead mt-3">{{ $blog->subtitle }}</p>
                    @endif

                    <div class="blog-article-meta">
                        <span class="blog-article-meta__item">{{ strtoupper($blog->author ?? 'SAX') }}</span>
                        <span class="blog-article-meta__item">{{ optional($blog->published_at)->translatedFormat('d M, Y') }}</span>
                        @if ($blog->read_time)
                            <span class="blog-article-meta__item">{{ $blog->read_time }} min</span>
                        @endif
                    </div>
                </div>

                <figure class="blog-article-hero__media">
                    <img src="{{ $coverUrl }}" alt="{{ $blog->title }}" fetchpriority="high" decoding="async">
                    @if ($blog->image_caption)
                        <figcaption class="blog-caption">{{ $blog->image_caption }}</figcaption>
                    @endif
                </figure>
            </div>
        </div>
    </section>

    <section class="blog-article-content">
        <div class="container">
            <div class="blog-article-main">
                    <article class="blog-article-body">
                        {!! $articleContent !!}
                    </article>

                    @if (!empty($blog->gallery))
                        <div class="blog-gallery">
                            <h3 class="blog-gallery__title">Galeria <span class="blog-gallery__count">{{ count($blog->gallery) }}</span></h3>
                            <div class="blog-gallery__grid">
                                @foreach ($blog->gallery as $img)
                                    <a href="{{ Storage::url($img) }}" class="blog-gallery__item" data-lightbox>
                                        <img src="{{ Storage::url($img) }}" alt="{{ $blog->title }}" loading="lazy">
                                        <span class="blog-gallery__zoom"><i class="fas fa-expand"></i></span>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div class="blog-share-panel">
                        <div class="blog-chip">Compartilhar</div>
                        <div class="sax-social-share">
                            <a href="https://www.facebook.com/sharer/sharer.php?u={{ url()->current() }}" target="_blank" rel="noopener" class="share-btn"><i class="fab fa-facebook-f"></i></a>
                            <a href="https://api.whatsapp.com/send?text={{ rawurlencode($blog->title) }}%20{{ url()->current() }}" target="_blank" rel="noopener" class="share-btn"><i class="fab fa-whatsapp"></i></a>
                            <a href="https://twitter.com/intent/tweet?text={{ rawurlencode($blog->title) }}&url={{ rawurlencode(url()->current()) }}" target="_blank" rel="noopener" class="share-btn"><i class="fab fa-x-twitter"></i></a>
                            <button type="button" class="share-btn" id="blog-copy-link" data-url="{{ url()->current() }}" title="Copiar link">
                                <i class="fas fa-link"></i>
                            </button>
                        </div>
                    </div>
            </div>

            @php
                $recentPosts = $latestPosts->reject(fn ($post) => $post->id === $blog->id || $relatedPosts->contains('id', $post->id))->take(3);
                $readingSections = [
                    ['title' => 'Mais histórias do blog', 'posts' => $relatedPosts],
                    ['title' => 'Leituras recentes', 'posts' => $recentPosts],
                ];
            @endphp
            @foreach ($readingSections as $readingSection)
                @if ($readingSection['posts']->isNotEmpty())
                    <section class="blog-article-reading" aria-labelledby="blog-reading-{{ $loop->index }}">
                        <div class="blog-related__heading">
                            <h2 id="blog-reading-{{ $loop->index }}">{{ $readingSection['title'] }}</h2>
                            <a href="{{ route('blogs.index') }}" class="blog-cta-link">Ver blog completo</a>
                        </div>
                        <div class="blog-article-reading__grid">
                            @foreach ($readingSection['posts'] as $post)
                                <article class="blog-story-card">
                                    <a href="{{ route('blogs.show', $post->slug) }}" class="blog-story-card__link">
                                        <div class="blog-story-card__media">
                                            <img src="{{ $post->image ? Storage::url($post->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $post->title }}" loading="lazy" decoding="async">
                                        </div>
                                        <div class="blog-story-card__body">
                                            <span class="blog-kicker">{{ $post->category->name ?? 'Blog' }}</span>
                                            <h3>{{ $post->title }}</h3>
                                            @if ($post->subtitle)
                                                <p>{{ Str::limit($post->subtitle, 100) }}</p>
                                            @endif
                                            <div class="blog-meta">
                                                <span>{{ optional($post->published_at)->format('d.m.Y') }}</span>
                                                @if ($post->read_time)
                                                    <span>{{ $post->read_time }} min</span>
                                                @endif
                                            </div>
                                        </div>
                                    </a>
                                </article>
                            @endforeach
                        </div>
                    </section>
                @endif
            @endforeach
        </div>
    </section>
</div>

<div id="blogLightbox" class="blog-lightbox">
    <button type="button" class="blog-lightbox__close" aria-label="Fechar">&times;</button>
    <img src="" alt="">
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/blog-product-carousel.js') }}?v={{ filemtime(public_path('js/blog-product-carousel.js')) }}" defer></script>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const lightbox = document.getElementById('blogLightbox');
    if (!lightbox) return;

    const lightboxImg = lightbox.querySelector('img');
    const closeBtn = lightbox.querySelector('.blog-lightbox__close');

    const open = (src, alt) => {
        lightboxImg.src = src;
        lightboxImg.alt = alt || '';
        lightbox.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    };

    const close = () => {
        lightbox.classList.remove('is-open');
        document.body.style.overflow = '';
    };

    document.querySelectorAll('[data-lightbox]').forEach((link) => {
        link.addEventListener('click', function (e) {
            e.preventDefault();
            open(this.href, this.querySelector('img')?.alt);
        });
    });

    closeBtn.addEventListener('click', close);
    lightbox.addEventListener('click', function (e) {
        if (e.target === lightbox) close();
    });
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') close();
    });

    const copyBtn = document.getElementById('blog-copy-link');
    if (copyBtn) {
        copyBtn.addEventListener('click', function () {
            navigator.clipboard.writeText(this.dataset.url).then(() => {
                const icon = this.querySelector('i');
                icon.className = 'fas fa-check';
                setTimeout(() => { icon.className = 'fas fa-link'; }, 1500);
            });
        });
    }
});
</script>
@endpush
