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

<style>
    .sax-main-slider {
        width: 100%;
        height: 55vh; 
        background: #000;
        position: relative;
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
        object-fit: cover; 
        object-position: center;
    }

    .slide-overlay {
        position: absolute;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: linear-gradient(to bottom, transparent, rgba(0,0,0,0.1));
    }

    /* Estilização das áreas de clique */
    .swiper-nav-click {
        position: absolute;
        top: 0;
        z-index: 20;
        height: 100%;
        width: 20%; /* 20% da tela em cada lado troca o slide */
        cursor: pointer;
    }

    .swiper-nav-click.prev { left: 0; }
    .swiper-nav-click.next { right: 0; }

    /* Paginação */
    .swiper-pagination-bullet {
        background: #fff !important;
        opacity: 0.6;
    }
    .swiper-pagination-bullet-active {
        opacity: 1;
    }

    @media (max-width: 768px) {
        .sax-main-slider {
            height: 40vh; 
        }
        .swiper-nav-click {
            width: 25%; /* Área um pouco maior no mobile para facilitar o toque */
        }
    }
</style>
{{-- JS migrado a home.js --}}
@endif