@php
    use Illuminate\Support\Str;
@endphp

<div class="row g-4" id="blog-cards-grid">
    @forelse ($blogs as $blog)
        @if ($featuredBlog && !$hasFilters && $blog->id === $featuredBlog->id)
            @continue
        @endif

        <div class="col-md-6 col-xl-4">
            <article class="blog-card-clean">
                <a href="{{ route('blogs.show', $blog->slug) }}" class="blog-card-clean__link">
                    <div class="blog-card-clean__media">
                        <img src="{{ $blog->image ? Storage::url($blog->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $blog->title }}">
                        <span class="blog-card-clean__tag">{{ $blog->category->name ?? 'Blog' }}</span>
                    </div>
                    <div class="blog-card-clean__body">
                        <div class="blog-meta">
                            <span>{{ optional($blog->published_at)->format('d.m.Y') }}</span>
                            @if ($blog->read_time)
                                <span>{{ $blog->read_time }} min</span>
                            @endif
                        </div>
                        <h3>{{ $blog->title }}</h3>
                        <p>{{ Str::limit($blog->subtitle, 110) }}</p>
                    </div>
                </a>
            </article>
        </div>
    @empty
        <div class="col-12 text-center py-5">
            <h4 class="text-muted">Nenhum artigo encontrado.</h4>
        </div>
    @endforelse
</div>
