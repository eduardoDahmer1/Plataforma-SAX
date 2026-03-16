{{-- Tarefa 7: Eventos --}}
<section id="eventos" class="section-padding" style="background: var(--azul-navy);">
    <div class="container">
        <div class="row align-items-center gy-5">

            {{-- Carrusel de eventos --}}
            <div class="col-lg-6" data-reveal="left">
                @if(!empty($cafeBistro->eventos_galeria))
                    <div class="swiper eventosSwiper rounded" style="aspect-ratio: 4/5;">
                        <div class="swiper-wrapper">
                            @foreach($cafeBistro->eventos_galeria as $foto)
                                <div class="swiper-slide">
                                    <img src="{{ asset('storage/' . $foto) }}"
                                         alt="Evento"
                                         class="w-100 h-100"
                                         style="object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="img-placeholder rounded" style="aspect-ratio: 4/5;">
                        EVENTOS
                    </div>
                @endif
            </div>

            {{-- Texto --}}
            <div class="col-lg-6" data-reveal="right">
                <span class="eyebrow">{{ $cafeBistro->eventos_subtitulo ?? 'Celebrações' }}</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">{{ $cafeBistro->eventos_titulo ?? 'O lugar perfeito para as suas celebrações' }}</h2>

                {!! nl2br(e($cafeBistro->eventos_texto ?? 'Um espaço onde as ideias ganham vida e cada celebração se transforma em uma experiência única.')) !!}

                {{-- Tipos de evento --}}
                @if($cafeBistro->eventos_tipos)
                    <ul class="eventos-tipos-list">
                        @foreach($cafeBistro->eventos_tipos as $tipo)
                            <li>{{ $tipo }}</li>
                        @endforeach
                    </ul>
                @endif

                {{-- CTA WhatsApp --}}
                <a href="{{ $cafeBistro->whatsapp_link }}" target="_blank" rel="noopener" class="btn-cafe-white btn-cafe-white--whatsapp">
                    <i class="bi bi-whatsapp"></i> Fale Conosco
                </a>
            </div>

        </div>
    </div>
</section>
