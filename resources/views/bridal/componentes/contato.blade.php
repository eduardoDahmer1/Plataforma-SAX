{{-- SAX Bridal — Sucursales / Contacto --}}
<section class="branches-section section-padding" id="contact">
    <div class="container">

        <div class="text-center mb-5" data-reveal="up">
            <span class="title-gold">{{ $sectionLabel }}</span>
            <h2 class="section-title">{!! $sectionTitle !!}</h2>
        </div>

        <div class="row g-4 justify-content-center">

            {{-- Tarjeta Ciudad del Este --}}
            <div class="col-12 col-md-6 col-lg-5" data-reveal="right">
                <div class="branch-card">
                    <div class="branch-img-wrap">
                        @if(!empty($cde_image))
                            <img src="{{ asset('storage/' . $cde_image) }}" alt="Sucursal {{ $cde_name }}" class="branch-img">
                        @else
                            <img src="https://images.unsplash.com/photo-1441986300917-64674bd600d8?w=800&q=80&fit=crop" alt="Sucursal {{ $cde_name }}" class="branch-img">
                        @endif
                    </div>
                    <div class="branch-body">
                        <h3 class="branch-name">{{ $cde_name }}</h3>
                        <div class="branch-divider"></div>
                        @if(!empty($cde_address))
                        <p class="branch-info">
                            <i class="fas fa-map-marker-alt branch-icon"></i>
                            {{ $cde_address }}
                        </p>
                        @endif
                        @if(!empty($cde_phone))
                        <p class="branch-info">
                            <i class="fas fa-phone branch-icon"></i>
                            {{ $cde_phone }}
                        </p>
                        @endif
                        <a href="{{ route('contact.form') }}" class="btn-sax branch-btn">
                            Ir a Contacto
                        </a>
                    </div>
                </div>
            </div>

            {{-- Tarjeta Asunción --}}
            <div class="col-12 col-md-6 col-lg-5" data-reveal="left">
                <div class="branch-card">
                    <div class="branch-img-wrap">
                        @if(!empty($asuncion_image))
                            <img src="{{ asset('storage/' . $asuncion_image) }}" alt="Sucursal {{ $asuncion_name }}" class="branch-img">
                        @else
                            <img src="https://images.unsplash.com/photo-1555529669-e69e7aa0ba9a?w=800&q=80&fit=crop" alt="Sucursal {{ $asuncion_name }}" class="branch-img">
                        @endif
                    </div>
                    <div class="branch-body">
                        <h3 class="branch-name">{{ $asuncion_name }}</h3>
                        <div class="branch-divider"></div>
                        @if(!empty($asuncion_address))
                        <p class="branch-info">
                            <i class="fas fa-map-marker-alt branch-icon"></i>
                            {{ $asuncion_address }}
                        </p>
                        @endif
                        @if(!empty($asuncion_phone))
                        <p class="branch-info">
                            <i class="fas fa-phone branch-icon"></i>
                            {{ $asuncion_phone }}
                        </p>
                        @endif
                        <a href="{{ route('contact.form') }}" class="btn-sax branch-btn">
                            Ir a Contacto
                        </a>
                    </div>
                </div>
            </div>

        </div>
    </div>
</section>

@push('styles')
<style>
    .branches-section {
        background: var(--bridal-white);
    }

    .branch-card {
        display: flex;
        flex-direction: column;
        border: 1px solid #f0ece6;
        height: 100%;
        transition: box-shadow 0.4s ease, transform 0.4s ease;
    }

    .branch-card:hover {
        box-shadow: 0 12px 32px rgba(0, 0, 0, 0.07);
        transform: translateY(-4px);
    }

    /* Imagen fachada */
    .branch-img-wrap {
        width: 100%;
        height: 280px;
        overflow: hidden;
        flex-shrink: 0;
    }

    .branch-img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        display: block;
        transition: transform 0.7s ease;
    }

    .branch-card:hover .branch-img {
        transform: scale(1.04);
    }

    .branch-img-placeholder {
        width: 100%;
        height: 100%;
        background: var(--bridal-cream);
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .branch-placeholder-name {
        font-family: var(--font-display);
        font-size: 1.4rem;
        letter-spacing: 4px;
        color: var(--bridal-gold-light);
        text-transform: uppercase;
    }

    /* Cuerpo de la tarjeta */
    .branch-body {
        padding: 32px 28px 28px;
        display: flex;
        flex-direction: column;
        flex: 1;
    }

    .branch-name {
        font-family: var(--font-serif);
        font-size: 1.5rem;
        font-weight: 400;
        color: var(--bridal-dark);
        margin-bottom: 12px;
    }

    .branch-divider {
        width: 30px;
        height: 2px;
        background: var(--bridal-gold);
        margin-bottom: 20px;
    }

    .branch-info {
        font-size: 0.85rem;
        color: #888;
        margin-bottom: 10px;
        display: flex;
        align-items: flex-start;
        gap: 10px;
        line-height: 1.6;
    }

    .branch-icon {
        color: var(--bridal-gold);
        margin-top: 3px;
        flex-shrink: 0;
        font-size: 0.8rem;
    }

    .branch-btn {
        margin-top: auto;
        padding-top: 20px;
        align-self: flex-start;
    }

    @media (max-width: 767px) {
        .branch-img-wrap {
            height: 220px;
        }

        .branch-body {
            padding: 24px 20px 20px;
        }
    }
</style>
@endpush
