<section class="py-5 py-lg-6 bg-dark text-white" id="contato">
    <div class="container py-4">
        <div class="row g-4 g-lg-5 align-items-stretch">
            
            <div class="col-lg-5" data-aos="fade-up">
                <div class="d-flex flex-column h-100">
                    <span class="text-gold fw-bold text-uppercase tracking-wider small mb-2">Localização</span>
                    <h2 class="display-5 font-serif mb-4">Visite o Palace</h2>
                    
                    <div class="mb-5">
                        <p class="lead opacity-75 mb-4">
                            <i class="bi bi-geo-alt text-gold me-2"></i>
                            {{ $palace->contato_endereco ?? 'Endereço não configurado' }}
                        </p>

                        <div class="p-4 border border-secondary border-opacity-25 rounded-3 bg-opacity-10">
                            <h6 class="text-gold text-uppercase fw-bold mb-3 small">Horário de Atendimento</h6>
                            <div class="d-flex justify-content-between mb-2 small border-bottom border-secondary border-opacity-25 pb-2">
                                <span>Segunda-feira</span>
                                <span class="fw-bold">{{ $palace->contato_horario_segunda }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2 small border-bottom border-secondary border-opacity-25 pb-2">
                                <span>Terça a Sábado</span>
                                <span class="fw-bold">{{ $palace->contato_horario_sabado }}</span>
                            </div>
                            <div class="d-flex justify-content-between small">
                                <span>Domingo</span>
                                <span class="fw-bold">{{ $palace->contato_horario_domingo }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="mt-auto">
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" 
                           target="_blank" 
                           class="btn btn-outline-gold w-100 py-3 rounded-0 text-uppercase fw-bold d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-whatsapp"></i>
                            Falar com Concierge
                        </a>
                    </div>
                </div>
            </div>

            <div class="col-lg-7" data-aos="fade-up" data-aos-delay="200">
                <div class="h-100 min-vh-40 shadow-lg rounded-3 overflow-hidden border border-secondary border-opacity-25 position-relative">
                    @if(!empty($palace->contato_mapa_iframe))
                        <div class="w-100 h-100 map-container">
                            {!! $palace->contato_mapa_iframe !!}
                        </div>
                    @else
                        <div class="bg-black d-flex flex-column align-items-center justify-content-center h-100 py-5 text-center">
                            <i class="bi bi-map-fill display-1 text-secondary opacity-25 mb-3"></i>
                            <p class="text-gold small text-uppercase tracking-widest">Localização Indisponível</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</section>

<style>
    /* Apenas o essencial que o Bootstrap não cobre nativamente */
    .btn-outline-gold {
        color: var(--palace-gold);
        border-color: var(--palace-gold);
    }
    .btn-outline-gold:hover {
        background-color: var(--palace-gold);
        color: #000;
        border-color: var(--palace-gold);
    }
    
    .tracking-wider { letter-spacing: 0.15rem; }
    
    .min-vh-40 { min-height: 400px; }

    /* Força o Iframe injetado a preencher o container pai */
    .map-container iframe {
        width: 100% !important;
        height: 100% !important;
        filter: grayscale(1) invert(0.9) contrast(1.2); /* Deixa o mapa no tom dark do site */
    }

    @media (max-width: 991px) {
        .min-vh-40 { min-height: 300px; }
    }
</style>