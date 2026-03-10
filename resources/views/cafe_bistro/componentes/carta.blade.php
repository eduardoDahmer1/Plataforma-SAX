{{-- Tarefa 6: Carta / Cardápio --}}
<section id="cardapio" class="section-padding" style="background: var(--azul-profundo);">
    <div class="container">

        {{-- Header --}}
        <div class="text-center mb-5" data-reveal="up">
            <span class="eyebrow">Cardápio</span>
            <div class="divider mx-auto"></div>
            <h2 class="section-title">Sabor Autêntico</h2>
        </div>

        {{-- Bento Grid --}}
        <div class="carta-grid" data-reveal="up">

            {{-- Pos 1: Vertical grande (ocupa 2 filas) --}}
            <div class="carta-item carta-item--tall">
                <div class="img-placeholder carta-img">GOURMET</div>
            </div>

            {{-- Pos 2: Cuadrada --}}
            <div class="carta-item">
                <div class="img-placeholder carta-img">PASTA</div>
            </div>

            {{-- Pos 3: Cuadrada --}}
            <div class="carta-item">
                <div class="img-placeholder carta-img">COCKTAIL</div>
            </div>

            {{-- Pos 4: Cuadrada --}}
            <div class="carta-item">
                <div class="img-placeholder carta-img">DESSERT</div>
            </div>

            {{-- Pos 5: Cuadrada --}}
            <div class="carta-item">
                <div class="img-placeholder carta-img">COFFEE</div>
            </div>
        </div>

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
                {{-- Conectar cuando el PDF esté disponible --}}
                <div class="carta-pdf-placeholder">
                    <i class="bi bi-file-earmark-pdf" style="font-size: 3rem; opacity: 0.4;"></i>
                    <p class="mt-3 mb-0" style="opacity: 0.5;">PDF do cardápio será integrado aqui</p>
                </div>
            </div>

            {{-- Footer: botón descargar --}}
            <div class="modal-footer carta-modal-footer border-0 justify-content-center">
                {{-- Conectar href cuando el PDF esté disponible --}}
                <a href="#" class="btn-carta-download" onclick="event.preventDefault();">
                    <i class="bi bi-download me-2"></i>Baixar Cardápio
                </a>
            </div>

        </div>
    </div>
</div>
