@if($banners->isNotEmpty())
<section class="vista-editorials" aria-label="Destacados de Vista & Co">
    @foreach($banners as $banner)
        @php($title = $banner['title'] ?: $banner['label'])
        <article class="vista-editorial-card">
            @if(filled($banner['link']))<a href="{{ $banner['link'] }}" aria-label="{{ $title }}">@endif
                <picture>
                    <source media="(max-width: 767px)" srcset="{{ $banner['mobile_image_url'] }}">
                    <img src="{{ $banner['image_url'] }}" alt="{{ $title }}" loading="lazy" decoding="async">
                </picture>
                <span>{{ $title }}</span>
            @if(filled($banner['link']))</a>@endif
        </article>
    @endforeach
</section>
@endif
