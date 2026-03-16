<section class="hero-slider">
    <div class="swiper mainSwiper">
        <div class="swiper-wrapper">
            @foreach($institucional->top_sliders as $slide)
            {{-- Tornamos o slide clicável envolvendo o conteúdo em um link --}}
            <a href="{{ route('categories.index') }}" class="swiper-slide">
                <div class="hero-overlay"></div>
                <img src="{{ asset('storage/' . $slide) }}" alt="SAX Experience">
                
                <div class="hero-content text-center">
                    <div class="container">
                        <span class="hero-subtitle" data-aos="fade-up">Exclusive Experience</span>
                        <h1 class="hero-title" data-aos="fade-up" data-aos-delay="200">
                            SAX <span class="text-gold">Department</span>
                        </h1>
                        <div class="hero-line" data-aos="zoom-in" data-aos-delay="400"></div>
                        <p class="hero-text" data-aos="fade-up" data-aos-delay="600">
                            Onde o luxo encontra a exclusividade e o design define o estilo.
                        </p>
                        <div class="hero-btn-wrapper" data-aos="fade-up" data-aos-delay="800">
                            <span class="btn-discover">Descobrir Coleção</span>
                        </div>
                    </div>
                </div>
            </a>
            @endforeach
        </div>

        {{-- Controles elegantes --}}
        <div class="swiper-nav-wrapper d-none d-md-flex">
            <div class="swiper-button-prev"></div>
            <div class="swiper-button-next"></div>
        </div>
        <div class="swiper-pagination"></div>
    </div>
</section>
