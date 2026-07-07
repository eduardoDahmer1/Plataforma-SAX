<section class="palace-section palace-section--soft" id="eventos">
    <div class="container py-5 py-lg-6">
        <div class="row g-4 g-xl-5 align-items-stretch">
            <div class="col-lg-5 palace-reveal" data-aos="fade-right">
                <div class="palace-event-panel h-100 d-flex flex-column justify-content-center">
                    <span class="palace-eyebrow text-uppercase">{{ __('messages.eventos_label') ?? __('messages.localizacao_label') }}</span>
                    <h2 class="palace-section__title mt-3 mb-4">{{ $t->palace_eventos_titulo ?? $palace->eventos_titulo }}</h2>
                    <p class="palace-section__lead mb-4">{{ $t->palace_eventos_descricao ?? $palace->eventos_descricao }}</p>

                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" class="palace-inline-link">
                        {{ __('messages.solicitar_orcamento_btn') ?? 'SOLICITAR ORÇAMENTO' }}
                        <i class="bi bi-arrow-right ms-2"></i>
                    </a>
                </div>
            </div>
            <div class="col-lg-7 palace-reveal" data-aos="fade-left">
                @php
                    $galeria = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true);
                @endphp
                @if(!empty($galeria))
                    <div class="row g-3 g-lg-4 palace-events-grid">
                        @foreach(array_slice($galeria, 0, 4) as $foto)
                            <div class="col-6">
                                <div class="palace-gallery-card" data-palace-tilt>
                                    <img src="{{ asset('storage/' . $foto) }}" class="palace-gallery-card__img" alt="Evento Palace" loading="lazy">
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>