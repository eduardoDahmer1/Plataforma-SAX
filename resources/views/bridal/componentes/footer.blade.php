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

    .footer-brand-v2 img { 
        height: 2.5em;
        width: auto;
    }

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
        font-size: 1.2rem;
        color: #888;
        text-decoration: none;
        transition: opacity 0.3s, transform 0.3s;
        display: inline-flex;
        align-items: center;
        justify-content: center;
    }

    .footer-social-link:hover { opacity: 0.75; color: var(--bridal-dark); transform: translateY(-2px); }

    .footer-col-title {
        font-family: var(--font-sans);
        font-size: 0.65rem;
        text-transform: uppercase;
        letter-spacing: 3px;
        font-weight: 600;
        color: var(--bridal-dark);
        margin-bottom: 20px;
    }

    .footer-links-list {
        list-style: none;
        padding: 0;
        margin: 0;
    }

    .footer-links-list li {
        margin-bottom: 12px;
    }

    .footer-link {
        font-size: 0.83rem;
        color: #888;
        text-decoration: none;
        transition: color 0.3s;
    }

    .footer-link:hover {
        color: var(--bridal-gold);
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

    .footer-copyright {
        margin-top: 80px;
        padding-top: 30px;
        text-align: center;
        border-top: 1px solid #f0ece6;
    }

    .footer-copyright p {
        font-size: 0.83rem;
        color: #888;
        margin: 0;
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
             @if(isset($attributes) && $attributes->logo_bridal)
                <img src="{{ asset('storage/uploads/' . $attributes->logo_bridal) }}" alt="SAX Bridal"
                     loading="lazy" decoding="async">
            @else
                SAX <span>BRIDAL</span>
            @endif
            </a>
    
                <p class="footer-desc-v2">
                    {{ $bridal->descripcion ?? 'La mayor selección de alta costura nupcial, donde cada detalle es una invitación al sueño.' }}
                </p>
                <div class="footer-social-v2">
                    <a href="{{ $bridal->instagram ?? 'https://www.instagram.com/saxbridal' }}" target="_blank" rel="noopener" class="footer-social-link" title="Instagram">
                        <i class="fab fa-instagram"></i>
                    </a>
                    <a href="https://www.facebook.com/profile.php?id=61553961935895" class="footer-social-link" title="Facebook">
                        <i class="fab fa-facebook-f"></i>
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $bridal->whatsapp ?? '595981527848') }}" target="_blank" rel="noopener" class="footer-social-link" title="WhatsApp">
                        <i class="fab fa-whatsapp"></i>
                    </a>
                </div>
            </div>

            {{-- Columna 2: Horarios --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-col-title">Horarios</h6>
                <div class="footer-hours">
                    <div class="footer-hours-row">
                        <span>Lunes – Sábado</span>
                        <span>{{ $bridal->horario_semana ?? '08:30 – 17:00' }}</span>
                    </div>
                    <div class="footer-hours-row last">
                        <span>Domingo</span>
                        <span>{{ $bridal->horario_domingo ?? '09:00 a 13:00' }}</span>
                    </div>
                </div>
            </div>

            {{-- Columna 3: Enlaces --}}
            <div class="col-lg-3 col-md-6">
                <h6 class="footer-col-title">Sobre S.A.X</h6>
                <ul class="footer-links-list">
                    <li><a href="{{ route('brands.index') }}" class="footer-link">Nuestras Marcas</a></li>
                    <li><a href="{{ route('blogs.index') }}" class="footer-link">#SAXNEWS</a></li>
                    <li><a href="{{ route('palace.index') }}" class="footer-link">SAX Palace</a></li>
                    <li><a href="{{ route('bridal.index') }}" class="footer-link">SAX Bridal</a></li>
                    <li><a href="{{ route('contact.form') }}" class="footer-link">Trabaja con nosotros</a></li>
                </ul>
            </div>

        </div>

        {{-- Bottom bar --}}
        <div class="footer-copyright">
            <p>{{ date('Y') }}. Todos los derechos reservados. SAX E-commerce.</p>
        </div>
    </div>
</footer>
