<section class="section-padding container" id="contato">
    <div class="row g-5">
        <div class="col-md-6" data-aos="fade-up">
            <h2 class="display-5 mb-4 font-serif">Onde Estamos</h2>
            <div class="gold-divider ms-0 mb-4"></div>
            
            {{-- Endereço Dinâmico --}}
            <p class="mb-4">{{ $palace->contato_endereco ?? 'Endereço não configurado' }}</p>
            
            <div class="contact-box p-4 border border-secondary mb-4">
                <h5 class="gold-text mb-3">Horário de Atendimento</h5>
                {{-- Horários Dinâmicos --}}
                <p class="small mb-2">Segunda-feira: {{ $palace->contato_horario_segunda }}</p>
                <p class="small mb-2">Terça a Sábado: {{ $palace->contato_horario_sabado }}</p>
                <p class="small">Domingo: {{ $palace->contato_horario_domingo }}</p>
            </div>

            <div class="d-flex align-items-center">
                <i class="fab fa-whatsapp fs-3 gold-text me-3"></i>
                <div>
                    <span class="d-block small text-uppercase">Reservas WhatsApp</span>
                    {{-- Link Dinâmico para WhatsApp Limpo --}}
                    <a href="https://wa.me/{{ preg_replace('/\D/', '', $palace->contato_whatsapp) }}" target="_blank" class="text-decoration-none text-white">
                        <strong>{{ $palace->contato_whatsapp ?? '+595 981 528186' }}</strong>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-6" data-aos="fade-up" data-aos-delay="200">
            <div class="ratio ratio-16x9 rounded overflow-hidden shadow border border-secondary bg-dark">
                {{-- Verifica se o Iframe existe no banco (está NULL na sua foto) --}}
                @if(!empty($palace->contato_mapa_iframe))
                    <div class="map-wrapper">
                        {!! $palace->contato_mapa_iframe !!}
                    </div>
                @else
                    {{-- Fallback caso o mapa não esteja preenchido --}}
                    <div class="d-flex align-items-center justify-content-center h-100">
                        <div class="text-center">
                            <i class="fas fa-map-marked-alt fs-1 text-gold mb-2"></i>
                            <p class="text-gold small mb-0">Mapa sendo configurado</p>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
</section>

<style>
    /* Força o Iframe colado via Admin a se comportar 
       como responsivo dentro da proporção 16x9 
    */
    .map-wrapper iframe {
        width: 100% !important;
        height: 100% !important;
        border: 0 !important;
    }
    
    .contact-box {
        background: rgba(255, 255, 255, 0.03);
        border-radius: 8px;
    }
</style>