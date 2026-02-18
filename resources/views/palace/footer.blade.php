<footer class="bg-black text-white pt-5 pb-4 border-top border-gold-subtle">
    <div class="container">
        <div class="row g-4 justify-content-between">
            
            <div class="col-lg-4 col-md-12" data-aos="fade-up">
                <a href="/" class="d-inline-block mb-4">
                    @if(isset($attributes) && $attributes->logo_palace)
                        <img src="{{ asset('storage/uploads/' . $attributes->logo_palace) }}" alt="SAX Logo" height="50" class="logo-footer">
                    @else
                        <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Logo" height="50" class="logo-footer">
                    @endif
                </a>
                <p class="text-secondary small lh-lg mb-4 opacity-75">
                    {{ $palace->hero_descricao }}
                </p>
                <div class="d-flex gap-3">
                    <a href="https://www.instagram.com/saxpalace" target="_blank" class="text-gold social-link-minimal">INSTAGRAM</a>
                    <a href="#" class="text-gold social-link-minimal">FACEBOOK</a>
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="text-gold social-link-minimal">WHATSAPP</a>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="100">
                <h6 class="text-uppercase tracking-widest fw-bold small mb-4 text-white">Horários</h6>
                <div class="text-secondary small">
                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10 py-2">
                        <span>Segunda:</span>
                        <span class="text-white">{{ $palace->contato_horario_segunda }}</span>
                    </div>
                    <div class="d-flex justify-content-between border-bottom border-secondary border-opacity-10 py-2">
                        <span>Terça a Sábado:</span>
                        <span class="text-white">{{ $palace->contato_horario_sabado }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span>Domingo:</span>
                        <span class="text-white">{{ $palace->contato_horario_domingo }}</span>
                    </div>
                </div>
            </div>

            <div class="col-lg-3 col-md-6" data-aos="fade-up" data-aos-delay="200">
                <h6 class="text-uppercase tracking-widest fw-bold small mb-4 text-white">Localização</h6>
                <p class="text-secondary small mb-4">
                    {{ $palace->contato_endereco }}
                </p>
                <div class="bg-gold-soft p-3 rounded-1">
                    <span class="d-block x-small text-uppercase fw-bold text-secondary opacity-75">Reservas Diretas:</span>
                    <a href="tel:{{ $palace->contato_whatsapp }}" class="text-secondary fs-5 fw-bold text-decoration-none lh-1">
                        {{ $palace->contato_whatsapp }}
                    </a>
                </div>
            </div>

        </div>

        <div class="row mt-5 pt-4 border-top border-secondary border-opacity-10">
            <div class="col-md-6 text-center text-md-start">
                <p class="x-small text-secondary mb-0 uppercase tracking-widest">
                    &copy; {{ date('Y') }} Sax Palace • Todos os direitos reservados
                </p>
            </div>
            <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                <p class="x-small text-secondary mb-0">
                    MADE BY <span class="text-white fw-bold">SAX FULL SERVICE</span>
                </p>
            </div>
        </div>
    </div>
</footer>
<style>
/* Utilitários Gerais */
.text-gold { color: #c5a059; }
.bg-gold-soft { background-color: #ffffff; }
.border-gold-subtle { border-top: 1px solid rgba(197, 160, 89, 0.3) !important; }
.tracking-widest { letter-spacing: 0.2rem; }
.x-small { font-size: 0.65rem; }

/* Logo */
.logo-footer {
    filter: brightness(0) invert(1);
    transition: transform 0.3s ease;
}

.logo-footer:hover {
    transform: scale(1.05);
}

/* Redes Sociais Estilo Minimal */
.social-link-minimal {
    font-size: 0.7rem;
    font-weight: 700;
    letter-spacing: 1px;
    text-decoration: none;
    position: relative;
    transition: opacity 0.3s;
}

.social-link-minimal::after {
    content: '';
    position: absolute;
    width: 0;
    height: 1px;
    bottom: -2px;
    left: 0;
    background-color: #c5a059;
    transition: width 0.3s;
}

.social-link-minimal:hover::after {
    width: 100%;
}

.social-link-minimal:hover {
    opacity: 0.8;
    color: #c5a059;
}

/* Responsividade Mobile */
@media (max-width: 991px) {
    footer { text-align: center; }
    .d-flex.justify-content-between { justify-content: center !important; gap: 20px; }
    .gap-3 { justify-content: center; }
    .bg-gold-soft { display: inline-block; width: 100%; }
}
</style>