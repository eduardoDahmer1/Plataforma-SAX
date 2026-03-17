{{-- SAX Bridal — Promotional Carousel (Swiper.js) --}}
<section class="promo-section section-padding">
    <div class="container">
        <div class="text-center mb-5">
            <span class="title-gold">COLECCION EXCLUSIVA</span>
            <h2 class="section-title">La elegancia en cada momento</h2>
        </div>
        <div class="swiper promoSwiper">
            <div class="swiper-wrapper">
                @foreach($promos as $promo)
                    <div class="swiper-slide">
                        <div class="promo-card" data-reveal="up">
                            <div class="promo-img-wrap">
                                <img src="{{ asset('storage/' . $promo['image']) }}" alt="{{ $promo['title'] }}" class="promo-img"
                                     loading="lazy" decoding="async" >
                            </div>
                            <div class="promo-body">
                                <h3 class="promo-title">{{ $promo['title'] }}</h3>
                                <p class="promo-subtitle">{{ $promo['subtitle'] }}</p>
                                @if(!empty($promo['button']))
                                    <a href="{{ $promo['link'] }}" class="promo-link">{{ $promo['button'] }} &rarr;</a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="promo-pagination swiper-pagination mt-4"></div>
        </div>
    </div>
</section>

