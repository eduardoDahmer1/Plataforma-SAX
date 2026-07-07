<section class="palace-section palace-section--dark" id="gastronomia">
    <div class="container py-5 py-lg-6">
        <div class="palace-section__header text-center mb-5 palace-reveal" data-aos="fade-up">
            <span class="palace-eyebrow text-uppercase">{{ __('messages.a_arte_de_servir') }}</span>
            <h2 class="palace-section__title mt-3">{{ $t->palace_gastronomia_titulo ?? $palace->gastronomia_titulo ?? __('messages.a_arte_de_servir') }}</h2>
            <div class="palace-divider mx-auto mt-3"></div>
        </div>

        <div class="row g-4 mb-5">
            @php
                // Mapeamento usando os dados traduzidos ($t)
                $refeicoes = [
                    [
                        'tit' => __('messages.meal_timeline_cafe'), 
                        'img' => $palace->bar_imagem_1 ? asset('storage/' . $palace->bar_imagem_1) : 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 
                        'desc' => $t->palace_gastronomia_cafe_desc ?? $palace->gastronomia_cafe_desc
                    ],
                    [
                        'tit' => __('messages.meal_timeline_almoco'), 
                        'img' => $palace->bar_imagem_2 ? asset('storage/' . $palace->bar_imagem_2) : 'https://images.unsplash.com/photo-1544025162-d76694265947', 
                        'desc' => $t->palace_gastronomia_almoco_desc ?? $palace->gastronomia_almoco_desc
                    ],
                    [
                        'tit' => __('messages.meal_timeline_jantar'), 
                        'img' => $palace->bar_imagem_3 ? asset('storage/' . $palace->bar_imagem_3) : 'https://images.unsplash.com/photo-1559339352-11d035aa65de', 
                        'desc' => $t->palace_gastronomia_jantar_desc ?? $palace->gastronomia_jantar_desc
                    ]
                ];
            @endphp

            @foreach($refeicoes as $item)
            <div class="col-md-4 palace-reveal" data-aos="fade-up">
                <div class="palace-card palace-card--menu h-100" data-palace-tilt>
                    <div class="palace-card__media ratio ratio-4x3">
                        <img src="{{ $item['img'] }}" class="palace-card__img" alt="{{ $item['tit'] }}" loading="lazy">
                        <div class="palace-card__overlay">
                            <h4 class="palace-card__title text-uppercase">{{ $item['tit'] }}</h4>
                        </div>
                    </div>
                    <div class="palace-card__body">
                        <p class="palace-card__text">{{ $item['desc'] ?? __('messages.experiencia_gastronomica_placeholder') }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        @if($palace->gastronomia_menu_pdf)
            <div class="row mt-5" data-aos="fade-up">
                <div class="col-12 text-center d-flex flex-column flex-sm-row justify-content-center gap-3 palace-menu-actions">
                    <button type="button"
                            class="btn palace-btn palace-btn--ghost text-uppercase px-5 py-3 fw-bold tracking-wider btn-view-pdf"
                            data-bs-toggle="modal"
                            data-bs-target="#pdfMenuModal">
                        <i class="fas fa-eye me-2"></i> {{ __('messages.visualizar_cardapio_btn') }}
                    </button>

                    <a href="{{ asset('storage/' . $palace->gastronomia_menu_pdf) }}"
                       download="Cardapio_SAX_Palace.pdf"
                       class="btn palace-btn palace-btn--gold text-uppercase px-5 py-3 fw-bold tracking-wider btn-download-pdf">
                        <i class="fas fa-download me-2"></i> {{ __('messages.baixar_pdf_btn') }}
                    </a>
                </div>
            </div>

            <div class="modal fade palace-modal" id="pdfMenuModal" tabindex="-1" aria-labelledby="pdfMenuModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered palace-modal__dialog">
                    <div class="modal-content palace-modal__content">
                        <div class="modal-header palace-modal__header">
                            <h5 class="modal-title palace-modal__title text-uppercase" id="pdfMenuModalLabel">
                                <i class="fas fa-utensils me-2"></i> {{ __('messages.cardapio_sax_palace_title') }}
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="{{ __('messages.fechar') }}"></button>
                        </div>
                        <div class="modal-body p-0 palace-modal__body">
                            <div class="ratio ratio-16x9 palace-pdf-frame">
                                <iframe src="{{ asset('storage/' . $palace->gastronomia_menu_pdf) }}#toolbar=1"
                                        frameborder="0"
                                        allow="autoplay">
                                </iframe>
                            </div>
                        </div>
                        <div class="modal-footer palace-modal__footer d-flex justify-content-between">
                            <small class="text-muted fst-italic">{{ __('messages.controles_leitor_info') }}</small>
                            <button type="button" class="btn btn-secondary rounded-0 text-uppercase px-4" data-bs-dismiss="modal">{{ __('messages.fechar_btn') }}</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>