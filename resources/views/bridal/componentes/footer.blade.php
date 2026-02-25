{{-- SAX Bridal — Footer --}}
{{--
    Datos dinámicos opcionales (desde controlador):
    $bridal->descripcion, $bridal->instagram, $bridal->whatsapp,
    $bridal->horario_lunes, $bridal->horario_semana, $bridal->horario_domingo,
    $bridal->direccion, $bridal->telefono
--}}
<style>
    .footer-bridal-v2 {
        background: var(--bridal-white);
        color: var(--bridal-dark);
        padding: 80px 0 40px;
        border-top: 1px solid #f0ece6;
    }

    .footer-brand-v2 {
        display: inline-block;
        font-family: var(--font-display);
        font-size: 1.6rem;
        letter-spacing: 6px;
        color: var(--bridal-dark);
        text-decoration: none;
        font-weight: 400;
        margin-bottom: 20px;
    }

    .footer-brand-v2 span { font-weight: 200; }
    .footer-brand-v2:hover { color: var(--bridal-gold); }

    .footer-desc-v2 {
        font-size: 0.85rem;
        color: #888;
        line-height: 1.8;
        margin-bottom: 28px;
    }

    .footer-social-v2 {
        display: flex;
        gap: 20px;
    }

    .footer-social-link {
        font-size: 0.65rem;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: var(--bridal-gold);
        text-decoration: none;
        position: relative;
        transition: opacity 0.3s;
    }

    .footer-social-link::after {
        content: '';
        position: absolute;
        width: 0;
        height: 1px;
        bottom: -2px;
        left: 0;
        background: var(--bridal-gold);
        transition: width 0.3s;
    }

    .footer-social-link:hover::after { width: 100%; }
    .footer-social-link:hover { opacity: 0.75; color: var(--bridal-gold); }

    .footer-col-title {
        font-family: var(--font-sans);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-weight: 700;
        color: var(--bridal-dark);
        margin-bottom: 20px;
    }

    .footer-hours { font-size: 0.82rem; }

    .footer-hours-row {
        display: flex;
        justify-content: space-between;
        padding: 10px 0;
        border-bottom: 1px solid #f0ece6;
        color: #999;
    }

    .footer-hours-row.last { border-bottom: none; }
    .footer-hours-row span:last-child { color: var(--bridal-dark); }

    .footer-address {
        font-size: 0.83rem;
        color: #888;
        line-height: 1.7;
        margin-bottom: 20px;
    }

    .footer-contact-box {
        background: var(--bridal-cream);
        border: 1px solid rgba(201, 169, 97, 0.25);
        padding: 16px 20px;
    }

    .footer-contact-label {
        display: block;
        font-size: 0.6rem;
        text-transform: uppercase;
        letter-spacing: 2px;
        color: #aaa;
        margin-bottom: 6px;
    }

    .footer-contact-phone {
        font-size: 1.1rem;
        font-weight: 600;
        color: var(--bridal-dark);
        text-decoration: none;
        letter-spacing: 1px;
        transition: color 0.3s;
    }

    .footer-contact-phone:hover { color: var(--bridal-gold); }

    .footer-bottom-v2 {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-top: 60px;
        padding-top: 28px;
        border-top: 1px solid #f0ece6;
    }

    .footer-copy-v2 {
        font-size: 0.65rem;
        letter-spacing: 2px;
        color: #bbb;
        margin: 0;
        text-transform: uppercase;
    }

    .footer-made-by {
        font-size: 0.65rem;
        letter-spacing: 2px;
        color: #bbb;
        margin: 0;
        text-transform: uppercase;
    }

    .footer-made-by span {
        color: var(--bridal-dark);
        font-weight: 700;
    }

    @media (max-width: 991px) {
        .footer-bridal-v2 { text-align: center; }
        .footer-social-v2 { justify-content: center; }
        .footer-bottom-v2 { flex-direction: column; gap: 10px; text-align: center; }
    }
</style>

<footer class="footer-bridal-v2">
    <div class="container">
        <div class="row g-4 justify-content-between">

            {{-- Columna 1: Marca + descripción + redes --}}
            <div class="col-lg-4 col-md-12">
                <a href="/" class="footer-brand-v2">
                    SAX <span>BRIDAL</span>
                </a>
                <p class="footer-desc-v2">
                    {{ $bridal->descripcion ?? 'La mayor selección de alta costura nupcial, donde cada detalle es una invitación al sueño.' }}
                </p>
                <div class="footer-social-v2">
                    <a href="{{ $bridal->instagram ?? 'https://www.instagram.com/saxbridal' }}" target="_blank" rel="noopener" class="footer-social-link">INSTAGRAM</a>
                    <a href="#" class="footer-social-link">FACEBOOK</a>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $bridal->whatsapp ?? '1234567890') }}" target="_blank" rel="noopener" class="footer-social-link">WHATSAPP</a>
                </div>
            </div>

            {{-- Columna 2: Horarios --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-col-title">Horarios</h6>
                <div class="footer-hours">
                    <div class="footer-hours-row">
                        <span>Lunes</span>
                        <span>{{ $bridal->horario_lunes ?? '08:30 a 17:00' }}</span>
                    </div>
                    <div class="footer-hours-row">
                        <span>Martes – Sábado</span>
                        <span>{{ $bridal->horario_semana ?? '10:00 – 19:00' }}</span>
                    </div>
                    <div class="footer-hours-row last">
                        <span>Domingo</span>
                        <span>{{ $bridal->horario_domingo ?? '09:00 a 16:00' }}</span>
                    </div>
                </div>
            </div>

            {{-- Columna 3: Ubicación + contacto --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-col-title">Ubicación</h6>
                <p class="footer-address">
                    {{ $bridal->direccion ?? 'Calle del Lujo 123, Bogotá, Colombia' }}
                </p>
                <div class="footer-contact-box">
                    <span class="footer-contact-label">Reservas directas</span>
                    <a href="tel:{{ $bridal->telefono ?? '+5712345678' }}" class="footer-contact-phone">
                        {{ $bridal->telefono ?? '+57 (1) 234-5678' }}
                    </a>
                </div>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="footer-bottom-v2">
            <p class="footer-copy-v2">
                &copy; {{ date('Y') }} SAX GROUP &mdash; LA DEFINICI&Oacute;N DEL LUJO.
            </p>
            <p class="footer-made-by">
                MADE BY <span>SAX FULL SERVICE</span>
            </p>
        </div>
    </div>
</footer>
