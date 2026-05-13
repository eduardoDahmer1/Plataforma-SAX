@php
    $slides = array_filter([
        $banner1 ?? null,
        $banner2 ?? null,
        $banner3 ?? null,
        $banner4 ?? null,
        $banner5 ?? null,
    ]);
    $slides = array_slice($slides, 0, $limit ?? 5);
@endphp

@if(count($slides) > 0)
<section class="sax-main-slider">
    <div class="swiper-container mainSwiper">
        <div class="swiper-wrapper">
            @foreach($slides as $img)
                <div class="swiper-slide">
                    <div class="slide-inner">
                        <img src="{{ asset('storage/uploads/' . $img) }}" alt="Banner SAX">
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