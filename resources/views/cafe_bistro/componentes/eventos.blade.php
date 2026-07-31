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
                                         alt="{{ __('messages.cafe_event_image_alt') }}"
                                         class="w-100 h-100"
                                         style="object-fit: cover;">
                                </div>
                            @endforeach
                        </div>
                    </div>
                @else
                    <div class="img-placeholder rounded" style="aspect-ratio: 4/5;">
                        {{ __('messages.eventos_label') }}
                    </div>
                @endif
            </div>

            {{-- Texto --}}
            <div class="col-lg-6" data-reveal="right">
                <span class="eyebrow">{{ $t?->cafe_eventos_subtitulo ?? $cafeBistro->eventos_subtitulo ?? __('messages.cafe_events_subtitle_fallback') }}</span>
                <div class="divider"></div>
                <h2 class="section-title mb-4">{{ $t?->cafe_eventos_titulo ?? $cafeBistro->eventos_titulo ?? __('messages.cafe_events_title_fallback') }}</h2>

                {!! nl2br(e($t?->cafe_eventos_texto ?? $cafeBistro->eventos_texto ?? __('messages.cafe_events_text_fallback'))) !!}

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
                    <i class="bi bi-whatsapp"></i> {{ __('messages.fale_conosco') }}
                </a>
            </div>

        </div>
    </div>
</section>
