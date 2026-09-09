@php
    $slides = collect($homeMainBanners ?? [])->values()->map(fn ($banner, $index) => [
        'image' => $banner->image,
        'image_url' => $banner->image_url,
        'link' => $banner->link,
        'title' => $banner->translated('title'),
        'description' => $banner->translated('description'),
    ])->take($limit ?? 20);
@endphp

@if($slides->isNotEmpty())
<section class="sax-main-slider" aria-label="Campanhas em destaque">
    <div class="swiper mainSwiper" data-slide-count="{{ $slides->count() }}">
        <div class="swiper-wrapper">
            @foreach($slides as $slide)
                @php
                    $isExternal = filled($slide['link']) && !str_starts_with($slide['link'], url('/')) && !str_starts_with($slide['link'], '/');
                @endphp
                <div class="swiper-slide">
                    <article class="sax-luxury-slide">
                        @if(filled($slide['link']))
                            <a href="{{ $slide['link'] }}" class="sax-luxury-slide__link" @if($isExternal) target="_blank" rel="noopener noreferrer" @endif aria-label="Abrir campanha {{ $loop->iteration }}">
                        @endif
                            <img src="{{ $slide['image_url'] }}" alt="Campanha SAX {{ $loop->iteration }}"
                                width="1920" height="720" @if($loop->first) fetchpriority="high" @else loading="lazy" @endif decoding="async">
                            <span class="sax-luxury-slide__shade" aria-hidden="true"></span>
                            @if(filled($slide['title']) || filled($slide['description']) || filled($slide['link']))
                                <span class="sax-luxury-slide__caption">
                                    @if(filled($slide['title']) || filled($slide['description']))
                                        <span class="sax-slider-copy">
                                            @if(filled($slide['title']))<small>{{ $slide['title'] }}</small>@endif
                                            @if(filled($slide['description']))<em>{{ $slide['description'] }}</em>@endif
                                        </span>
                                    @endif
                                    @if(filled($slide['link']))<strong>{{ __('messages.explorar_colecao') }} <i class="fa-solid fa-arrow-right"></i></strong>@endif
                                </span>
                            @endif
                        @if(filled($slide['link']))</a>@endif
                    </article>
                </div>
            @endforeach
        </div>

        @if($slides->count() > 1)
            <div class="sax-slider-ui">
                <div class="sax-slider-count"><span data-slider-current>01</span><i></i><span>{{ str_pad($slides->count(), 2, '0', STR_PAD_LEFT) }}</span></div>
                <div class="swiper-pagination"></div>
                <div class="sax-slider-controls">
                    <button type="button" class="swiper-nav-click prev" aria-label="Campanha anterior"><i class="fa-solid fa-arrow-left"></i></button>
                    <button type="button" class="swiper-nav-click next" aria-label="Próxima campanha"><i class="fa-solid fa-arrow-right"></i></button>
                </div>
            </div>
        @endif
    </div>
</section>
@endif
