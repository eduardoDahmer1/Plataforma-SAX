<footer class="palace-footer">
    <div class="container">
        <div class="palace-footer__surface">
            <div class="row g-4 justify-content-between">
                <div class="col-lg-4 col-md-12 palace-reveal" data-aos="fade-up">
                    <a href="/" class="d-inline-block mb-4">
                        @if(isset($attributes) && $attributes->logo_palace)
                            <img src="{{ asset('storage/uploads/' . $attributes->logo_palace) }}" alt="SAX Logo" height="50" class="logo-footer">
                        @else
                            <img src="{{ asset('images/logo-sax-white.png') }}" alt="SAX Logo" height="50" class="logo-footer">
                        @endif
                    </a>
                    <p class="palace-footer__copy mb-4">
                        {{ $t->palace_hero_descricao ?? $palace->hero_descricao }}
                    </p>
                    <div class="d-flex flex-wrap gap-3 palace-footer__socials">
                        <a href="https://www.instagram.com/saxpalace" target="_blank" class="social-link-minimal">{{ __('messages.instagram') }}</a>
                        <a href="#" class="social-link-minimal">{{ __('messages.facebook') }}</a>
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="social-link-minimal">{{ __('messages.whatsapp') }}</a>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 palace-reveal" data-aos="fade-up" data-aos-delay="100">
                    <h6 class="text-uppercase tracking-widest fw-bold small mb-4 text-white">{{ __('messages.horarios_label') }}</h6>
                    <div class="palace-footer__list text-secondary small">
                        <div class="palace-footer__row">
                            <span>{{ __('messages.segunda_label') }}:</span>
                            <span>{{ $palace->contato_horario_segunda }}</span>
                        </div>
                        <div class="palace-footer__row">
                            <span>{{ __('messages.terca_sabado_label') }}:</span>
                            <span>{{ $palace->contato_horario_sabado }}</span>
                        </div>
                        <div class="palace-footer__row">
                            <span>{{ __('messages.domingo_label') }}:</span>
                            <span>{{ $palace->contato_horario_domingo }}</span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-md-6 palace-reveal" data-aos="fade-up" data-aos-delay="200">
                    <h6 class="text-uppercase tracking-widest fw-bold small mb-4 text-white">{{ __('messages.localizacao_label') }}</h6>
                    <p class="palace-footer__copy small mb-4">
                        {{ $t->palace_contato_endereco ?? $palace->contato_endereco }}
                    </p>
                    <div class="bg-gold-soft p-3 rounded-3 palace-footer__contact-card">
                        <span class="d-block x-small text-uppercase fw-bold text-secondary opacity-75">{{ __('messages.reservas_diretas_label') }}:</span>
                        <a href="tel:{{ $palace->contato_whatsapp }}" class="text-white fs-5 fw-bold text-decoration-none lh-1">
                            {{ $palace->contato_whatsapp }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="row mt-5 pt-4 border-top border-secondary border-opacity-10">
                <div class="col-md-6 text-center text-md-start">
                    <p class="x-small text-secondary mb-0 uppercase tracking-widest">
                        &copy; {{ date('Y') }} Sax Palace • {{ __('messages.direitos_reservados_label') }}
                    </p>
                </div>
                <div class="col-md-6 text-center text-md-end mt-2 mt-md-0">
                    <p class="x-small text-secondary mb-0">
                        {{ __('messages.made_by') }} <span class="text-white fw-bold">SAX FULL SERVICE</span>
                    </p>
                </div>
            </div>
        </div>
    </div>
</footer>