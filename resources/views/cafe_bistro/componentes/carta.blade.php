{{-- Tarefa 6: Carta / Cardápio --}}
<section id="cardapio" class="section-padding" style="background: var(--azul-profundo);">
    <div class="container">

        {{-- Header --}}
        <div class="text-center mb-5" data-reveal="up">
            <span class="eyebrow">Cardápio</span>
            <div class="divider mx-auto"></div>
            <h2 class="section-title">{{ $cafeBistro->cardapio_titulo ?? 'Sabor Autêntico' }}</h2>
        </div>

        {{-- Bento Grid dinámico --}}
        @php $galeria = $cafeBistro->cardapio_galeria ?? []; @endphp

        @if(count($galeria))
            <div class="carta-grid" data-reveal="up">
                @foreach($galeria as $i => $foto)
                    <div class="carta-item {{ $i === 0 ? 'carta-item--tall' : '' }}">
                        <img src="{{ asset('storage/' . $foto) }}"
                             alt="Cardápio imagem {{ $i + 1 }}"
                             class="carta-img"
                             loading="lazy">
                    </div>
                @endforeach
            </div>
        @endif

        {{-- CTA --}}
        <div class="text-center mt-5" data-reveal="up">
            <button class="btn-cafe-white" data-bs-toggle="modal" data-bs-target="#modalCardapio">
                Ver Cardápio
            </button>
        </div>

    </div>
</section>

{{-- Modal de Cardápio (PDF) --}}
<div class="modal fade" id="modalCardapio" tabindex="-1" aria-labelledby="modalCardapioLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered">
        <div class="modal-content carta-modal-content">

            {{-- Botão fechar — canto superior direito --}}
            <button type="button" class="carta-modal-close" data-bs-dismiss="modal" aria-label="Fechar">
                <i class="bi bi-x-lg"></i>
            </button>

            {{-- Header del modal --}}
            <div class="modal-header carta-modal-header border-0">
                <h5 class="modal-title" id="modalCardapioLabel">Nosso Cardápio</h5>
            </div>

            {{-- Cuerpo: visor PDF --}}
            <div class="modal-body carta-modal-body p-0">
                @if($cafeBistro->cardapio_pdf)
                    {{-- Desktop: iframe con scroll nativo del navegador --}}
                    <iframe src="{{ asset('storage/' . $cafeBistro->cardapio_pdf) }}"
                            class="carta-pdf-viewer d-none d-md-block"
                            title="Cardápio PDF"></iframe>

                    {{-- Mobile: mensaje + botón para abrir en nueva pestaña --}}
                    <div class="d-md-none text-center py-5 px-4">
                        <i class="bi bi-file-earmark-pdf" style="font-size: 3rem; opacity: 0.5;"></i>
                        <p class="mt-3 mb-4 small" style="opacity: 0.7;">
                            Para melhor visualização, abra o cardápio no navegador.
                        </p>
                        <a href="{{ asset('storage/' . $cafeBistro->cardapio_pdf) }}"
                           target="_blank" rel="noopener"
                           class="btn-carta-download">
                            <i class="bi bi-box-arrow-up-right me-2"></i>Abrir Cardápio
                        </a>
                    </div>
                @else
                    <div class="carta-pdf-placeholder">
                        <i class="bi bi-file-earmark-pdf" style="font-size: 3rem; opacity: 0.4;"></i>
                        <p class="mt-3 mb-0" style="opacity: 0.5;">PDF do cardápio será integrado aqui</p>
                    </div>
                @endif
            </div>

            {{-- Footer: botón descargar (solo desktop, mobile ya tiene el suyo) --}}
            <div class="modal-footer carta-modal-footer border-0 justify-content-center d-none d-md-flex">
                @if($cafeBistro->cardapio_pdf)
                    <a href="{{ asset('storage/' . $cafeBistro->cardapio_pdf) }}" download class="btn-carta-download">
                        <i class="bi bi-download me-2"></i>Baixar Cardápio
                    </a>
                @endif
            </div>

        </div>
    </div>
</div>
