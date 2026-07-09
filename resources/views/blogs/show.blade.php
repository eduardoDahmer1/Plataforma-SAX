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

                <div class="blog-article-hero__media">
                    <img src="{{ $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $blog->title }}">
                    @if ($blog->image_caption)
                        <span class="blog-caption">{{ $blog->image_caption }}</span>
                    @endif
                </div>
            </div>
        </div>
    </section>

    <section class="blog-article-content">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-8">
                    <article class="blog-article-body">
                        {!! $blog->content !!}
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

                <div class="col-lg-4">
                    <aside class="blog-sidebar">
                        <div class="blog-sidebar__box">
                            <h3 class="blog-sidebar__title">Categorias</h3>
                            <div class="blog-sidebar__links">
                                <a href="{{ route('blogs.index') }}" class="blog-sidebar__link">
                                    <span>Todos</span>
                                </a>
                                @foreach ($categories as $category)
                                    <a href="{{ route('blogs.index', ['category' => $category->id]) }}" class="blog-sidebar__link">
                                        <span class="d-flex align-items-center gap-2">
                                            @if ($category->banner)
                                                <img src="{{ Storage::url($category->banner) }}" alt="" class="blog-filter-chip__avatar">
                                            @endif
                                            {{ $category->name }}
                                        </span>
                                    </a>
                                @endforeach
                            </div>
                        </div>

                        <div class="blog-sidebar__box">
                            <h3 class="blog-sidebar__title">Leituras recentes</h3>
                            <div class="blog-mini-list">
                                @foreach ($latestPosts as $latestPost)
                                    <a href="{{ route('blogs.show', $latestPost->slug) }}" class="blog-mini-item">
                                        <img src="{{ $latestPost->image ? Storage::url($latestPost->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $latestPost->title }}">
                                        <div>
                                            <div class="blog-mini-item__meta">{{ optional($latestPost->published_at)->format('d.m.Y') }}</div>
                                            <strong>{{ Str::limit($latestPost->title, 60) }}</strong>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    </aside>
                </div>
            </div>

            @if ($relatedPosts->isNotEmpty())
                <section class="blog-related">
                    <div class="blog-related__heading">
                        <div>
                            <span class="blog-kicker">Continue lendo</span>
                            <h2>Mais histórias do blog</h2>
                        </div>
                        <a href="{{ route('blogs.index') }}" class="blog-cta-link">Ver blog completo</a>
                    </div>

                    <div class="row g-4">
                        @foreach ($relatedPosts as $relatedPost)
                            <div class="col-md-4">
                                <article class="blog-card-clean">
                                    <a href="{{ route('blogs.show', $relatedPost->slug) }}" class="blog-card-clean__link">
                                        <div class="blog-card-clean__media">
                                            <img src="{{ $relatedPost->image ? Storage::url($relatedPost->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $relatedPost->title }}">
                                            <span class="blog-card-clean__tag">{{ $relatedPost->category->name ?? 'Blog' }}</span>
                                        </div>
                                        <div class="blog-card-clean__body">
                                            <div class="blog-meta">
                                                <span>{{ optional($relatedPost->published_at)->format('d.m.Y') }}</span>
                                                @if ($relatedPost->read_time)
                                                    <span>{{ $relatedPost->read_time }} min</span>
                                                @endif
                                            </div>
                                            <h3>{{ $relatedPost->title }}</h3>
                                            <p>{{ Str::limit($relatedPost->subtitle, 90) }}</p>
                                        </div>
                                    </a>
                                </article>
                            </div>
                        @endforeach
                    </div>
                </section>
            @endif
        </div>
    </section>
</div>

<div id="blogLightbox" class="blog-lightbox">
    <button type="button" class="blog-lightbox__close" aria-label="Fechar">&times;</button>
    <img src="" alt="">
</div>
@endsection

@push('scripts')
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
