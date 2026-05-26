<section class="py-5 bg-black text-white">
    <div class="container py-5">
        <div class="text-center mb-5">
            <h2 class="display-6 font-serif text-uppercase tracking-wider">{{ $palace->gastronomia_titulo ?? 'A Arte de Servir' }}</h2>
            <div class="bg-gold mx-auto mt-3" style="width: 50px; height: 2px;"></div>
        </div>
        
        <div class="row g-4 mb-5">
            @php
                // Mapeamento das imagens do banco para cada categoria
                $refeicoes = [
                    [
                        'tit' => 'Café da Manhã', 
                        'img' => $palace->bar_imagem_1 ? asset('storage/' . $palace->bar_imagem_1) : 'https://images.unsplash.com/photo-1495474472287-4d71bcdd2085', 
                        'desc' => $palace->gastronomia_cafe_desc
                    ],
                    [
                        'tit' => 'Almoço', 
                        'img' => $palace->bar_imagem_2 ? asset('storage/' . $palace->bar_imagem_2) : 'https://images.unsplash.com/photo-1544025162-d76694265947', 
                        'desc' => $palace->gastronomia_almoco_desc
                    ],
                    [
                        'tit' => 'Jantar', 
                        'img' => $palace->bar_imagem_3 ? asset('storage/' . $palace->bar_imagem_3) : 'https://images.unsplash.com/photo-1559339352-11d035aa65de', 
                        'desc' => $palace->gastronomia_jantar_desc
                    ]
                ];
            @endphp

            @foreach($refeicoes as $item)
            <div class="col-md-4" data-aos="fade-up">
                <div class="card border-0 h-100 bg-transparent overflow-hidden group style-palace-card">
                    <div class="ratio ratio-4x3 overflow-hidden position-relative">
                        <img src="{{ $item['img'] }}" class="card-img-top object-fit-cover transition-scale" alt="{{ $item['tit'] }}">
                        <div class="card-img-overlay d-flex align-items-end p-0">
                            <div class="w-100 p-4 text-center text-white card-overlay-gradient">
                                <h4 class="font-serif m-0 text-uppercase tracking-wide fs-5">{{ $item['tit'] }}</h4>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4 text-center bg-white text-dark d-flex flex-column justify-content-center">
                        <p class="text-secondary small m-0 italic-desc">{{ $item['desc'] ?? 'Experiência gastronômica exclusiva.' }}</p>
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Seção de Ações do Cardápio em PDF --}}
        @if($palace->gastronomia_menu_pdf)
            <div class="row mt-5" data-aos="fade-up">
                <div class="col-12 text-center d-flex flex-column flex-sm-row justify-content-center gap-3">
                    {{-- Botão para Abrir o Modal --}}
                    <button type="button" 
                            class="btn btn-gold-outline text-uppercase px-5 py-3 fw-bold tracking-wider rounded-0 btn-view-pdf"
                            data-bs-toggle="modal" 
                            data-bs-target="#pdfMenuModal">
                        <i class="fas fa-eye me-2"></i> Visualizar Cardápio
                    </button>

                    {{-- Botão para Download Direto --}}
                    <a href="{{ asset('storage/' . $palace->gastronomia_menu_pdf) }}" 
                       download="Cardapio_SAX_Palace.pdf" 
                       class="btn btn-gold-solid text-uppercase px-5 py-3 fw-bold tracking-wider rounded-0 btn-download-pdf">
                        <i class="fas fa-download me-2"></i> Baixar PDF
                    </a>
                </div>
            </div>

            {{-- Estrutura do Modal Bootstrap 5 --}}
            <div class="modal fade" id="pdfMenuModal" tabindex="-1" aria-labelledby="pdfMenuModalLabel" aria-hidden="true">
                <div class="modal-dialog modal-xl modal-dialog-centered">
                    <div class="modal-content bg-dark border-gold rounded-0">
                        <div class="modal-header border-bottom-gold">
                            <h5 class="modal-title font-serif text-uppercase text-gold tracking-wide" id="pdfMenuModalLabel">
                                <i class="fas fa-utensils me-2"></i> Cardápio SAX Palace
                            </h5>
                            <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                        </div>
                        <div class="modal-body p-0 bg-black">
                            {{-- Container Responsivo para o Iframe do PDF --}}
                            <div class="ratio ratio-16x9 pdf-container">
                                <iframe src="{{ asset('storage/' . $palace->gastronomia_menu_pdf) }}#toolbar=1" 
                                        frameborder="0" 
                                        allow="autoplay">
                                </iframe>
                            </div>
                        </div>
                        <div class="modal-footer border-top-gold d-flex justify-content-between">
                            <small class="text-secondary italic-desc">Use os controles do leitor para zoom e navegação.</small>
                            <button type="button" class="btn btn-secondary rounded-0 text-uppercase px-4" data-bs-dismiss="modal">Fechar</button>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
</section>

<style>
/* Efeito de zoom na imagem ao passar o mouse */
.style-palace-card {
    transition: transform 0.3s ease;
}
.style-palace-card .transition-scale {
    transition: transform 0.5s ease;
}
.style-palace-card:hover .transition-scale {
    transform: scale(1.06);
}

/* Gradiente sutil escuro sobre a imagem para destacar o título branco */
.card-overlay-gradient {
    background: linear-gradient(to top, rgba(0, 0, 0, 0.8) 0%, rgba(0, 0, 0, 0) 100%);
}

/* Customização fina da descrição abaixo dos cards */
.italic-desc {
    font-style: italic;
    line-height: 1.5;
}

/* Estilo do Botão Outline (Visualizar) */
.btn-gold-outline {
    color: #dfba6b;
    border: 1px solid #dfba6b;
    background-color: transparent;
    transition: all 0.3s ease;
}
.btn-gold-outline:hover {
    color: #000000;
    background-color: #dfba6b;
    border-color: #dfba6b;
    box-shadow: 0 4px 15px rgba(223, 186, 107, 0.3);
}

/* Estilo do Botão Sólido (Baixar) */
.btn-gold-solid {
    color: #000000;
    background-color: #dfba6b;
    border: 1px solid #dfba6b;
    transition: all 0.3s ease;
}
.btn-gold-solid:hover {
    color: #dfba6b;
    background-color: transparent;
    border-color: #dfba6b;
    box-shadow: 0 4px 15px rgba(223, 186, 107, 0.2);
}

.tracking-wider {
    letter-spacing: 2px;
}

/* Customizações Customizadas do Modal de Luxo */
.border-gold {
    border: 1px solid #dfba6b !important;
}
.border-bottom-gold {
    border-bottom: 1px solid rgba(223, 186, 107, 0.3) !important;
}
.border-top-gold {
    border-top: 1px solid rgba(223, 186, 107, 0.2) !important;
}
.text-gold {
    color: #dfba6b;
}

/* Garante uma boa altura padrão para a visualização do PDF */
.pdf-container {
    min-height: 70vh;
}
@media (max-width: 576px) {
    .pdf-container {
        min-height: 50vh;
    }
}
</style>