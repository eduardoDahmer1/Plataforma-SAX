{{-- SAX Bridal — Sucursales / Contacto (Swiper Carousel) --}}
<section class="locations-section section-padding" id="contact">
    <div class="container">

        <div class="text-center mb-5" data-reveal="up">
            <span class="title-gold">ENCUÉNTRANOS</span>
            <h2 class="section-title">Nuestras Sucursales</h2>
        </div>

        <div class="position-relative locations-swiper-wrap">
            <div class="swiper locationSwiper">
            <div class="swiper-wrapper">
                @foreach($locations as $location)
                    <div class="swiper-slide">
                        <div class="location-card" data-reveal="up">
                            <div class="location-img-wrap">
                                @if(!empty($location['image']))
                                    <img src="{{ asset('storage/' . $location['image']) }}"
                                         alt="Sucursal {{ $location['name'] }}"
                                         class="location-img"
                                         loading="lazy" decoding="async">
                                @endif
                            </div>
                            <div class="location-body">
                                <h3 class="location-name">{{ $location['name'] }}</h3>
                                <div class="location-divider"></div>
                                @if(!empty($location['address']))
                                    <p class="location-info">
                                        <i class="fas fa-map-marker-alt location-icon"></i>
                                        {{ $location['address'] }}
                                    </p>
                                @endif
                                @if(!empty($location['whatsapp_url']))
                                    <a href="{{ $location['whatsapp_url'] }}" target="_blank" rel="noopener" class="location-wa-btn">
                                        <i class="fab fa-whatsapp"></i>
                                        Escribinos por WhatsApp
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="location-pagination swiper-pagination mt-4"></div>
        </div>

        </div>

    </div>
</section>

@push('styles')
<style>
    .locations-section {
        background: var(--bridal-white);
        position: relative; /* Importante para que las flechas no se escapen */
    }

    .location-card {
        background: var(--bridal-cream);
        border-radius: 2px;
        overflow: hidden;
    }

    @media (hover: hover) {
        .location-card {
            transition: transform 0.4s ease;
        }
        .location-card:hover {
            transform: translateY(-6px);
        }
    }

    .location-img-wrap {
        overflow: hidden;
        aspect-ratio: 4 / 5;
    }

    .location-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
    }

    @media (hover: hover) {
        .location-img {
            transition: transform 1.2s ease;
        }
        .location-card:hover .location-img {
            transform: scale(1.06);
        }
    }

    .location-body {
        padding: 1.75rem 1.5rem 2rem;
        text-align: center;
        display: flex;
        flex-direction: column;
        align-items: center;
    }

    .location-name {
        font-family: var(--font-serif);
        font-size: 1.3rem;
        color: var(--bridal-dark);
        margin-bottom: 0.5rem;
        font-weight: 400;
    }

    .location-divider {
        width: 1.875rem;
        height: 2px;
        background: var(--bridal-gold);
        margin-bottom: 1.25rem;
    }

    .location-info {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 0.625rem;
        display: flex;
        align-items: flex-start;
        gap: 0.35rem;
        line-height: 1.6;
    }

    .location-icon {
        color: var(--bridal-gold);
        margin-top: 0.1875rem;
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .location-wa-btn {
        display: inline-flex;
        align-items: center;
        gap: 0.5rem;
        margin-top: 1.25rem;
        color: var(--bridal-gold);
        text-decoration: none;
        font-size: 0.72rem;
        letter-spacing: 2px;
        text-transform: uppercase;
        font-weight: 500;
        transition: color 0.3s ease;
    }

    .location-wa-btn i {
        font-size: 1rem;
    }

    .location-wa-btn:hover {
        color: var(--bridal-dark);
    }

    /* --- Pagination --- */
    .location-pagination {
        position: relative;
        margin-top: 2rem;
        text-align: center;
    }

    .location-pagination .swiper-pagination-bullet {
        background: var(--bridal-gold);
        opacity: 0.3;
        width: 0.5rem;
        height: 0.5rem;
        border-radius: 0.25rem;
        transition: all 0.35s ease;
    }

    .location-pagination .swiper-pagination-bullet-active {
        opacity: 1;
        width: 1.75rem;
        border-radius: 0.25rem;
    }

</style>
@endpush