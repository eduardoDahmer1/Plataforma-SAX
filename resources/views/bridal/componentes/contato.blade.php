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

