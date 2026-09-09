@if ($stackedBanners->isNotEmpty())
    <section class="sax-stacked-banners py-5" aria-label="Destaques editoriais SAX">
        <div class="container-fluid px-lg-5">
            @if(filled($sectionContent['title']) || filled($sectionContent['description']) || $stackedBanners->count() > 1)
            <div class="stacked-banners-heading">
                @if(filled($sectionContent['title']) || filled($sectionContent['description']))
                    <div>
                        <span>{{ __('messages.curadoria_sax') }}</span>
                        @if(filled($sectionContent['title']))<h2>{{ $sectionContent['title'] }}</h2>@endif
                        @if(filled($sectionContent['description']))<p class="sax-editorial-description">{{ $sectionContent['description'] }}</p>@endif
                    </div>
                @endif
                @if($stackedBanners->count() > 1)
                    <div class="stacked-banner-controls">
                        <button type="button" class="editorial-prev" aria-label="Destaque anterior"><i class="fa-solid fa-arrow-left"></i></button>
                        <button type="button" class="editorial-next" aria-label="Próximo destaque"><i class="fa-solid fa-arrow-right"></i></button>
                    </div>
                @endif
            </div>
            @endif
            <div class="swiper editorialBannerSwiper">
                <div class="swiper-wrapper">
                    @foreach ($stackedBanners as $banner)
                        @php $isExternal = filled($banner['link']) && !str_starts_with($banner['link'], url('/')) && !str_starts_with($banner['link'], '/'); @endphp
                        <div class="swiper-slide">
                            <article class="stacked-banner-card">
                                @if (!empty($banner['link']))
                                    <a href="{{ $banner['link'] }}" @if($isExternal) target="_blank" rel="noopener noreferrer" @endif aria-label="Abrir {{ $banner['label'] }}">
                                @endif
                                    <img src="{{ $banner['image_url'] }}" alt="{{ $banner['label'] }}" width="1600" height="760" loading="lazy" decoding="async">
                                    <span class="stacked-banner-card__index">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</span>
                                    <span class="stacked-banner-card__overlay"><small>{{ __('messages.curadoria_sax') }}</small><strong>{{ $banner['label'] }}</strong>@if (!empty($banner['link']))<b>{{ __('messages.descobrir_btn') }} <i class="fa-solid fa-arrow-right"></i></b>@endif</span>
                                @if (!empty($banner['link']))</a>@endif
                            </article>
                        </div>
                    @endforeach
                </div>
                <div class="editorial-pagination"></div>
            </div>
        </div>
    </section>
@endif
