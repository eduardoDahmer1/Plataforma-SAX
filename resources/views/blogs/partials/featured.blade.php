@php
    use Illuminate\Support\Str;
@endphp

@if ($featuredBlog && !$hasFilters)
    <article class="blog-featured mb-5">
        <div class="blog-featured__media">
            <img src="{{ $featuredBlog->image ? Storage::url($featuredBlog->image) : asset('storage/uploads/noimage.webp') }}" alt="{{ $featuredBlog->title }}">
        </div>
        <div class="blog-featured__body">
            <span class="blog-kicker">{{ $featuredBlog->category->name ?? 'Blog' }}</span>
            <h2 class="blog-title mt-3">{{ $featuredBlog->title }}</h2>
            @if ($featuredBlog->subtitle)
                <p class="blog-excerpt mt-3">{{ Str::limit($featuredBlog->subtitle, 180) }}</p>
            @endif
            <div class="blog-meta mt-4">
                <span>{{ optional($featuredBlog->published_at)->format('d.m.Y') }}</span>
                @if ($featuredBlog->read_time)
                    <span>{{ $featuredBlog->read_time }} min</span>
                @endif
                @if ($featuredBlog->featured)
                    <span>Em destaque</span>
                @endif
            </div>
            <a href="{{ route('blogs.show', $featuredBlog->slug) }}" class="blog-cta-link mt-4">Ler artigo</a>
        </div>
    </article>
@endif
