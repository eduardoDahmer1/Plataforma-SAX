{{-- SAX Bridal — Testimonials Carousel (Swiper.js) — Polaroid Style --}}
<section class="testimonials-section section-padding">
    <div class="container">
        <div class="text-center mb-5" data-reveal="up">
            <span class="title-gold">{{ $sectionLabel }}</span>
            <h2 class="section-title">{!! $sectionTitle !!}</h2>
        </div>

        <div class="testimonial-carousel-wrapper" data-reveal="up">
            <div class="swiper testimonialSwiper">
                <div class="swiper-wrapper">
                    @foreach($testimonials as $testimonial)
                        <div class="swiper-slide">
                            <div class="testimonial-card">
                                {{-- Polaroid a la izquierda --}}
                                <div class="tq-polaroid {{ $loop->index % 2 === 0 ? 'tq-rotate-left' : 'tq-rotate-right' }}">
                                    @if(!empty($testimonial['foto']))
                                        <img
                                            src="{{ asset('storage/' . $testimonial['foto']) }}"
                                            alt="{{ $testimonial['author'] }}"
                                            class="tq-polaroid-img"
                                            loading="lazy" decoding="async"
                                        >
                                    @else
                                        <div class="tq-polaroid-placeholder">
                                            <i class="fas fa-camera"></i>
                                        </div>
                                    @endif
                                    <span class="tq-polaroid-caption">{{ $testimonial['author'] }}</span>
                                </div>

                                {{-- Texto a la derecha --}}
                                <div class="tq-content">
                                    <div class="tq-mark">&ldquo;</div>
                                    <p class="tq-quote">{{ $testimonial['quote'] }}</p>
                                    <p class="tq-author">&mdash; {{ $testimonial['author'] }}</p>
                                    @if(!empty($testimonial['ubicacion']))
                                        <p class="tq-location">{{ $testimonial['ubicacion'] }}</p>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="testimonial-pagination swiper-pagination"></div>
            </div>

            {{-- Flechas de navegación --}}
            <button class="tq-nav tq-nav-prev" aria-label="Anterior">
                <i class="fa-solid fa-chevron-left"></i>
            </button>
            <button class="tq-nav tq-nav-next" aria-label="Siguiente">
                <i class="fa-solid fa-chevron-right"></i>
            </button>
        </div>
    </div>
</section>

