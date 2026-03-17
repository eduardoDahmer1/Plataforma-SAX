{{-- SAX Bridal — Footer --}}
{{--
    Datos dinámicos opcionales (desde controlador):
    $bridal->descripcion, $bridal->instagram, $bridal->whatsapp,
    $bridal->horario_lunes, $bridal->horario_semana, $bridal->horario_domingo,
    $bridal->direccion, $bridal->telefono
--}}
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
