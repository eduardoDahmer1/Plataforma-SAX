@php
    $slides = array_filter([
        ['image' => $banner1 ?? null, 'link' => $banner1_link ?? null],
        ['image' => $banner2 ?? null, 'link' => $banner2_link ?? null],
        ['image' => $banner3 ?? null, 'link' => $banner3_link ?? null],
        ['image' => $banner4 ?? null, 'link' => $banner4_link ?? null],
        ['image' => $banner5 ?? null, 'link' => $banner5_link ?? null],
    ], fn ($slide) => filled($slide['image']));
    $slides = array_slice($slides, 0, $limit ?? 5);
@endphp

@if(count($slides) > 0)
<section class="sax-main-slider">
    <div class="swiper-container mainSwiper">
        <div class="swiper-wrapper">
            @foreach($slides as $slide)
                <div class="swiper-slide">
                    <div class="slide-inner">
                        @if(!empty($slide['link']))
                            <a href="{{ $slide['link'] }}" >
                                <img src="{{ asset('storage/uploads/' . $slide['image']) }}" alt="Banner SAX">
                            </a>
                        @else
                            <img src="{{ asset('storage/uploads/' . $slide['image']) }}" alt="Banner SAX">
                        @endif
                        <div class="slide-overlay"></div>
                    </div>
                </div>
            @endforeach
        </div>
        
        <div class="swiper-nav-click prev"></div>
        <div class="swiper-nav-click next"></div>
        
        <div class="swiper-pagination"></div>
    </div>
</section>

{{-- JS migrado a home.js --}}
@endif