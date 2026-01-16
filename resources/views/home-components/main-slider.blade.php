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
        
        <div class="swiper-button-next"></div>
        <div class="swiper-button-prev"></div>
        <div class="swiper-pagination"></div>
    </div>
</section>

<style>
    .sax-main-slider {
        width: 100%;
        /* AJUSTE AQUI: Diminuído de 80vh para 55vh */
        height: 55vh; 
        background: #000;
        overflow: hidden;
    }

    .mainSwiper {
        width: 100%;
        height: 100%;
    }

    .slide-inner {
        width: 100%;
        height: 100%;
        position: relative;
    }

    .slide-inner img {
        width: 100%;
        height: 100%;
        /* object-position: center 20%; ajuda a não cortar cabeças em banners horizontais */
        object-fit: cover; 
        object-position: center;
    }

    .slide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.2));
    }

    /* Estilo das Setas */
    .swiper-button-next, .swiper-button-prev {
        color: #fff !important;
        transform: scale(0.6);
        transition: 0.3s;
    }

    /* Paginação */
    .swiper-pagination-bullet {
        background: #fff !important;
    }

    @media (max-width: 768px) {
        .sax-main-slider {
            /* AJUSTE MOBILE: Proporção mais quadrada para celulares */
            height: 40vh; 
        }
    }
</style>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        if (typeof Swiper !== 'undefined') {
            new Swiper('.mainSwiper', {
                loop: true,
                speed: 800,
                autoplay: {
                    delay: 5000,
                    disableOnInteraction: false,
                },
                pagination: {
                    el: '.swiper-pagination',
                    clickable: true,
                },
                navigation: {
                    nextEl: '.swiper-button-next',
                    prevEl: '.swiper-button-prev',
                },
                effect: 'fade',
                fadeEffect: {
                    crossFade: true
                },
            });
        }
    });
</script>
@endif