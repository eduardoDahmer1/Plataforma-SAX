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
                                <img src="{{ asset('storage/' . $promo['image']) }}" alt="{{ $promo['title'] }}" class="promo-img">
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

@push('styles')
<style>
    .promo-section {
        background: var(--bridal-white);
    }

    .promo-card {
        background: var(--bridal-cream);
        border-radius: 2px;
        overflow: hidden;
        transition: transform 0.4s ease;
    }

    .promo-card:hover {
        transform: translateY(-6px);
    }

    .promo-img-wrap {
        overflow: hidden;
        aspect-ratio: 4 / 5;
    }

    .promo-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 1.2s ease;
    }

    .promo-card:hover .promo-img {
        transform: scale(1.06);
    }

    .promo-body {
        padding: 28px 24px 32px;
        text-align: center;
    }

    .promo-title {
        font-family: var(--font-serif);
        font-size: 1.3rem;
        color: var(--bridal-dark);
        margin-bottom: 8px;
        font-weight: 400;
    }

    .promo-subtitle {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 16px;
        line-height: 1.6;
    }

    .promo-link {
        color: var(--bridal-gold);
        text-decoration: none;
        font-size: 0.72rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .promo-link:hover {
        color: var(--bridal-dark);
    }

    /* Swiper pagination — pill activo */
    .promo-pagination .swiper-pagination-bullet {
        background: var(--bridal-gold);
        opacity: 0.3;
        width: 8px;
        height: 8px;
        border-radius: 4px;
        transition: all 0.35s ease;
    }

    .promo-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        width: 28px;
        border-radius: 4px;
    }
</style>
@endpush
