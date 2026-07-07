<section class="palace-section palace-section--dark" id="contato">
    <div class="container py-5 py-lg-6">
        <div class="row g-4 g-lg-5 align-items-stretch">
            <div class="col-lg-5 palace-reveal" data-aos="fade-up">
                <div class="palace-contact-panel h-100 d-flex flex-column">
                    <span class="palace-eyebrow text-uppercase">{{ __('messages.localizacao_label') }}</span>
                    <h2 class="palace-section__title mt-3 mb-4">{{ __('messages.visite_palace_title') }}</h2>

                    <div class="mb-5">
                        <p class="palace-contact-panel__address mb-4">
                            <i class="bi bi-geo-alt text-gold me-2"></i>
                            {{ $t->palace_contato_endereco ?? $palace->contato_endereco ?? __('messages.endereco_nao_configurado') }}
                        </p>

                        <div class="palace-hours-card">
                            <h6 class="palace-hours-card__title text-uppercase fw-bold mb-3 small">{{ __('messages.horarios_label') }}</h6>

                            <div class="palace-hours-card__row">
                                <span>{{ __('messages.segunda_label') }}</span>
                                <span>{{ $palace->contato_horario_segunda }}</span>
                            </div>
                            <div class="palace-hours-card__row">
                                <span>{{ __('messages.terca_sabado_label') }}</span>
                                <span>{{ $palace->contato_horario_sabado }}</span>
                            </div>
                            <div class="palace-hours-card__row">
                                <span>{{ __('messages.domingo_label') }}</span>
                                <span>{{ $palace->contato_horario_domingo }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}"
                           target="_blank"
                           class="btn palace-btn palace-btn--ghost w-100 py-3 text-uppercase fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-whatsapp"></i>
                            {{ __('messages.falar_concierge_btn') }}
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7 palace-reveal" data-aos="fade-up" data-aos-delay="200">
                <div class="palace-map-shell h-100 position-relative overflow-hidden">
                    @if(!empty($palace->contato_mapa_iframe))
                        <div class="w-100 h-100 map-container">
                            {!! $palace->contato_mapa_iframe !!}
                        </div>
                    @else
                        <div class="palace-map-shell__empty d-flex flex-column align-items-center justify-content-center h-100 py-5 text-center">
                            <i class="bi bi-map-fill display-1 text-secondary opacity-25 mb-3"></i>
                            <p class="text-gold small text-uppercase tracking-widest">{{ __('messages.localizacao_indisponivel_status') }}</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>