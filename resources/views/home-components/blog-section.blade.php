{{-- resources/views/home-components/blog-section.blade.php --}}
<h4 class="mt-5 mb-3 fw-bold d-flex align-items-center">
    <i class="fas fa-blog me-2 text-primary"></i> Últimos Artigos do Blog
</h4>

<div class="swiper blogSwiper mb-4">
    <div class="swiper-wrapper">
        @foreach ($blogs as $blog)
            <div class="swiper-slide">
                <div class="card border-0 rounded-4 shadow-sm h-100 overflow-hidden blog-card">
                    <div class="position-relative">
                        @php
                            $img = $blog->image && Storage::disk('public')->exists($blog->image)
                                ? Storage::url($blog->image)
                                : asset('storage/uploads/noimage.webp');
                        @endphp
                        <img src="{{ $img }}" alt="{{ $blog->title }}" class="w-100 object-fit-cover" style="height: 170px;">
                        <span class="position-absolute top-0 start-0 m-2 px-3 py-1 rounded-pill bg-dark text-white small fw-semibold">
                            {{ $blog->category->name ?? 'Sem categoria' }}
                        </span>
                    </div>

                    <div class="card-body d-flex flex-column p-3">
                        <h5 class="fw-bold mb-2" style="line-height: 1.3">{{ Str::limit($blog->title, 60) }}</h5>
                        <p class="text-muted small flex-grow-1 mb-3">{{ Str::limit($blog->subtitle, 90) }}</p>
                        <a href="{{ route('blogs.show', $blog->slug) }}" class="btn btn-primary w-100 fw-semibold py-2 rounded-3 small">
                            <i class="fas fa-arrow-right me-1"></i> Ler artigo
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <div class="swiper-button-prev"></div>
    <div class="swiper-button-next"></div>
</div>

<style>
    .blog-card { transition: .25s ease; }
    .blog-card:hover { transform: translateY(-5px); box-shadow: 0 8px 25px rgba(0, 0, 0, 0.14) !important; }
    .blog-card img { transition: .3s ease; }
    .blog-card:hover img { filter: brightness(0.9); }
</style>