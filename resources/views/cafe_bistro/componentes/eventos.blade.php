{{-- Tarefa 7: Eventos --}}
<section id="eventos" class="section-padding" style="background: var(--azul-navy);">
    <div class="container">
        <div class="row align-items-center gy-5">

            {{-- Foto placeholder --}}
            <div class="col-lg-6" data-reveal="left">
                <div class="img-placeholder rounded" style="height: 28rem;">
                    EVENTOS
                </div>
            </div>

            {{-- Texto --}}
            <div class="col-lg-6" data-reveal="right">
                <span class="eyebrow">{{ $cafeBistro->eventos_subtitulo ?? 'Celebrações' }}</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">{{ $cafeBistro->eventos_titulo ?? 'O lugar perfeito para as suas celebrações' }}</h2>

                {!! nl2br(e($cafeBistro->eventos_texto ?? 'Um espaço onde as ideias ganham vida e cada celebração se transforma em uma experiência única.')) !!}

                {{-- Separador de servicios --}}
                @if($cafeBistro->eventos_tipos)
                    <p class="eventos-separador">
                        {{ implode(' • ', $cafeBistro->eventos_tipos) }}
                    </p>
                @endif

                {{-- CTA WhatsApp --}}
                <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" rel="noopener" class="btn-cafe-white btn-cafe-white--whatsapp">
                    <i class="bi bi-whatsapp"></i> Fale Conosco
                </a>
            </div>

        </div>
    </div>
</section>
