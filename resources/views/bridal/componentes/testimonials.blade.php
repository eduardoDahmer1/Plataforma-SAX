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

@push('styles')
<style>
    .testimonials-section {
        background: var(--bridal-white);
    }

    .testimonial-card {
        display: flex;
        align-items: flex-start;
        gap: 40px;
        padding: 40px;
        background: var(--bridal-cream);
        border-radius: 2px;
        box-shadow: 0 2px 14px rgba(0, 0, 0, 0.04);
        min-height: 320px;
    }

    /* — Polaroid — */
    .tq-polaroid {
        flex-shrink: 0;
        background: #fff;
        padding: 10px 10px 40px;
        box-shadow: 0 4px 20px rgba(0, 0, 0, 0.1);
        transition: transform 0.3s ease;
    }

    .tq-rotate-left {
        transform: rotate(-2deg);
    }

    .tq-rotate-right {
        transform: rotate(2deg);
    }

    .tq-polaroid:hover {
        transform: rotate(0deg) scale(1.02);
    }

    .tq-polaroid-img {
        width: 220px;
        aspect-ratio: 4 / 5;
        object-fit: cover;
        display: block;
    }

    .tq-polaroid-placeholder {
        width: 220px;
        aspect-ratio: 4 / 5;
        background: #f0ece6;
        display: flex;
        align-items: center;
        justify-content: center;
        color: var(--bridal-gold-light);
        font-size: 2.5rem;
    }

    .tq-polaroid-caption {
        display: block;
        text-align: center;
        font-family: 'Dancing Script', 'Segoe Script', cursive;
        font-size: 1rem;
        color: var(--bridal-dark);
        margin-top: 10px;
        opacity: 0.7;
    }

    /* — Contenido del testimonio — */
    .tq-content {
        flex: 1;
        min-width: 0;
    }

    .tq-mark {
        font-family: var(--font-serif);
        font-size: 3.5rem;
        color: var(--bridal-gold);
        line-height: 1;
        margin-bottom: 12px;
        opacity: 0.35;
    }

    .tq-quote {
        font-family: var(--font-serif);
        font-size: 0.95rem;
        color: var(--bridal-dark);
        font-style: italic;
        margin-bottom: 24px;
        line-height: 1.6;
    }

    .tq-author {
        font-size: 0.78rem;
        color: var(--bridal-gold);
        font-weight: 500;
        letter-spacing: 2px;
        text-transform: uppercase;
        margin-bottom: 4px;
    }

    .tq-location {
        font-size: 0.72rem;
        color: #999;
        margin-bottom: 0;
    }

    /* Swiper pagination — pill activo */
    .testimonial-pagination {
        position: relative;
        margin-top: 28px;
    }

    .testimonial-pagination .swiper-pagination-bullet {
        background: var(--bridal-gold);
        opacity: 0.3;
        width: 8px;
        height: 8px;
        border-radius: 4px;
        transition: all 0.35s ease;
    }

    .testimonial-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        width: 28px;
        border-radius: 4px;
    }

    /* — Flechas de navegación — */
    .testimonial-carousel-wrapper {
        position: relative;
    }

    .tq-nav {
        position: absolute;
        top: 50%;
        transform: translateY(-50%);
        width: 44px;
        height: 44px;
        border-radius: 50%;
        border: 1px solid var(--bridal-gold-light);
        background: rgba(255, 255, 255, 0.9);
        color: var(--bridal-gold);
        font-size: 0.85rem;
        cursor: pointer;
        z-index: 10;
        display: flex;
        align-items: center;
        justify-content: center;
        opacity: 0.6;
        transition: var(--transition);
    }

    .tq-nav:hover {
        opacity: 1;
        background: var(--bridal-gold);
        color: #fff;
        border-color: var(--bridal-gold);
    }

    .tq-nav-prev {
        left: -22px;
    }

    .tq-nav-next {
        right: -22px;
    }

    @media (max-width: 991px) {
        .testimonial-card {
            flex-direction: column;
            align-items: center;
            padding: 20px 16px 24px;
            gap: 16px;
        }

        .tq-polaroid {
            padding: 8px 8px 28px;
            width: 60%;
            max-width: 200px;
        }

        .tq-polaroid-img,
        .tq-polaroid-placeholder {
            width: 100%;
        }

        .tq-content {
            text-align: center;
            width: 100%;
        }

        .tq-mark {
            font-size: 2rem;
            margin-bottom: 6px;
        }

        .tq-quote {
            font-size: 0.88rem;
            margin-bottom: 14px;
        }

        .tq-nav {
            width: 36px;
            height: 36px;
            font-size: 0.75rem;
        }

        .tq-nav-prev {
            left: -8px;
        }

        .tq-nav-next {
            right: -8px;
        }
    }
</style>
@endpush
