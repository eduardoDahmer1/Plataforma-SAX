<div class="modal fade sax-image-editor" id="saxImageEditorModal" tabindex="-1" aria-labelledby="saxImageEditorTitle" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content">
            <div class="modal-header sax-image-editor__header">
                <div>
                    <span class="sax-image-editor__eyebrow"><i class="fa-solid fa-wand-magic-sparkles"></i> Editor de imagem</span>
                    <h5 class="modal-title" id="saxImageEditorTitle">Prepare a imagem antes de salvar</h5>
                    <p>Arraste para reposicionar. Use a roda do mouse ou o controle de zoom para aproximar.</p>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
            </div>

            <div class="modal-body sax-image-editor__body">
                <aside class="sax-image-editor__sidebar">
                    <button type="button" class="sax-image-editor__auto" data-image-action="auto">
                        <i class="fa-solid fa-wand-magic-sparkles"></i>
                        <span><strong>Ajuste automático</strong><small>Centraliza, enquadra e aproveita melhor o espaço.</small></span>
                    </button>

                    <section class="sax-image-editor__group">
                        <div class="sax-image-editor__group-title"><span>Enquadramento</span><small data-image-output-label>1080 × 1080 px</small></div>
                        <div class="sax-image-editor__segmented" role="group" aria-label="Modo de enquadramento">
                            <button type="button" data-image-mode="contain"><i class="fa-solid fa-compress"></i> Encaixar</button>
                            <button type="button" data-image-mode="cover"><i class="fa-solid fa-crop-simple"></i> Preencher</button>
                        </div>
                        <div class="sax-image-editor__size-grid">
                            <label><span>Largura</span><div><input type="number" min="240" max="2000" step="10" data-image-width><small>px</small></div></label>
                            <button type="button" class="sax-image-editor__ratio-lock is-active" data-image-ratio-lock title="Manter proporção"><i class="fa-solid fa-link"></i></button>
                            <label><span>Altura</span><div><input type="number" min="240" max="2000" step="10" data-image-height><small>px</small></div></label>
                        </div>
                    </section>

                    <section class="sax-image-editor__group">
                        <div class="sax-image-editor__group-title"><span>Zoom e posição</span><strong data-image-zoom-value>100%</strong></div>
                        <div class="sax-image-editor__range-row">
                            <button type="button" data-image-action="zoom-out" aria-label="Diminuir zoom"><i class="fa-solid fa-minus"></i></button>
                            <input type="range" min="40" max="300" step="1" value="100" data-image-zoom>
                            <button type="button" data-image-action="zoom-in" aria-label="Aumentar zoom"><i class="fa-solid fa-plus"></i></button>
                        </div>
                        <div class="sax-image-editor__tool-grid">
                            <button type="button" data-image-action="rotate-left" title="Girar para a esquerda"><i class="fa-solid fa-rotate-left"></i><span>Girar</span></button>
                            <button type="button" data-image-action="rotate-right" title="Girar para a direita"><i class="fa-solid fa-rotate-right"></i><span>Girar</span></button>
                            <button type="button" data-image-action="flip-horizontal" title="Espelhar horizontalmente"><i class="fa-solid fa-left-right"></i><span>Espelhar</span></button>
                            <button type="button" data-image-action="flip-vertical" title="Espelhar verticalmente"><i class="fa-solid fa-up-down"></i><span>Inverter</span></button>
                        </div>
                    </section>

                    <section class="sax-image-editor__group">
                        <div class="sax-image-editor__group-title"><span>Remover fundo e recorte livre</span><small>Máscara profissional</small></div>
                        <button type="button" class="sax-image-editor__remove-bg" data-image-action="remove-background">
                            <i class="fa-solid fa-person-rays"></i>
                            <span><strong>Remover fundo / remover mais</strong><small>Pode repetir para retirar resíduos progressivamente.</small></span>
                        </button>
                        <label class="sax-image-editor__slider-label">
                            <span>Sensibilidade</span><strong data-image-background-tolerance-value>14%</strong>
                            <input type="range" min="4" max="40" step="1" value="14" data-image-background-tolerance>
                        </label>
                        <div class="sax-image-editor__mask-tools">
                            <button type="button" data-image-tool="move" class="is-active" title="Mover imagem"><i class="fa-solid fa-arrows-up-down-left-right"></i><span>Mover</span></button>
                            <button type="button" data-image-tool="erase" title="Apagar fundo manualmente"><i class="fa-solid fa-eraser"></i><span>Borracha</span></button>
                            <button type="button" data-image-tool="restore" title="Restaurar partes apagadas"><i class="fa-solid fa-paintbrush"></i><span>Restaurar</span></button>
                            <button type="button" data-image-tool="lasso" title="Desenhar livremente o contorno que deve permanecer"><i class="fa-solid fa-signature"></i><span>Livre</span></button>
                            <button type="button" data-image-tool="polygon" title="Marcar pontos ao redor do que deve permanecer"><i class="fa-solid fa-draw-polygon"></i><span>Por pontos</span></button>
                            <button type="button" data-image-tool="magic" title="Clique em uma região do fundo para removê-la"><i class="fa-solid fa-wand-magic-sparkles"></i><span>Varinha</span></button>
                        </div>
                        <label class="sax-image-editor__slider-label">
                            <span>Tamanho do pincel</span><strong data-image-brush-size-value>48 px</strong>
                            <input type="range" min="8" max="220" step="2" value="48" data-image-brush-size>
                        </label>
                        <label class="sax-image-editor__slider-label">
                            <span>Suavização da borda</span><strong data-image-edge-refine-value>2 px</strong>
                            <input type="range" min="0" max="6" step="1" value="2" data-image-edge-refine>
                        </label>
                        <div class="sax-image-editor__mask-actions">
                            <button type="button" class="sax-image-editor__recover-details" data-image-action="recover-details"><i class="fa-solid fa-sparkles"></i> Refinar objeto</button>
                            <button type="button" data-image-action="polygon-remove-outside"><i class="fa-solid fa-object-ungroup"></i> Tirar lado de fora</button>
                            <button type="button" data-image-action="polygon-remove-inside"><i class="fa-solid fa-object-group"></i> Tirar lado de dentro</button>
                            <button type="button" data-image-action="refine-mask"><i class="fa-solid fa-wand-magic-sparkles"></i> Suavizar borda</button>
                            <button type="button" data-image-action="invert-mask"><i class="fa-solid fa-circle-half-stroke"></i> Inverter</button>
                            <button type="button" data-image-action="restore-mask"><i class="fa-solid fa-image"></i> Restaurar fundo</button>
                        </div>
                    </section>

                    <section class="sax-image-editor__group">
                        <div class="sax-image-editor__group-title"><span>Desenho e texto</span><small>Para banners e peças</small></div>
                        <div class="sax-image-editor__creative-row">
                            <button type="button" data-image-tool="draw"><i class="fa-solid fa-pencil"></i> Desenhar</button>
                            <label title="Cor do desenho"><input type="color" value="#111111" data-image-draw-color><i class="fa-solid fa-palette"></i></label>
                            <button type="button" data-image-action="clear-drawing"><i class="fa-solid fa-trash-can"></i> Limpar</button>
                        </div>
                        <div class="sax-image-editor__text-tool">
                            <input type="text" maxlength="120" placeholder="Digite um texto para adicionar" data-image-text-input>
                            <div>
                                <input type="number" min="14" max="240" value="64" aria-label="Tamanho do texto" data-image-text-size>
                                <input type="color" value="#111111" aria-label="Cor do texto" data-image-text-color>
                                <button type="button" data-image-action="add-text"><i class="fa-solid fa-font"></i> Adicionar texto</button>
                            </div>
                        </div>
                    </section>

                    <section class="sax-image-editor__group">
                        <div class="sax-image-editor__group-title"><span>Fundo</span><small>Área fora da foto</small></div>
                        <div class="sax-image-editor__backgrounds">
                            <button type="button" data-image-background="#ffffff" style="--image-background:#ffffff" title="Branco"></button>
                            <button type="button" data-image-background="#f4f4f2" style="--image-background:#f4f4f2" title="Cinza claro"></button>
                            <button type="button" data-image-background="#000000" style="--image-background:#000000" title="Preto"></button>
                            <button type="button" data-image-background="transparent" class="is-transparent" title="Transparente"></button>
                            <label class="sax-image-editor__custom-color" title="Cor personalizada"><input type="color" value="#ffffff" data-image-background-custom><i class="fa-solid fa-eye-dropper"></i></label>
                        </div>
                    </section>

                    <section class="sax-image-editor__group">
                        <div class="sax-image-editor__group-title"><span>Qualidade do arquivo</span><strong data-image-quality-value>85%</strong></div>
                        <input type="range" min="45" max="95" step="1" value="85" data-image-quality>
                        <div class="sax-image-editor__quality-labels"><span>Arquivo menor</span><span>Mais definição</span></div>
                    </section>
                </aside>

                <div class="sax-image-editor__workspace">
                    <div class="sax-image-editor__history">
                        <div>
                            <button type="button" data-image-action="undo" title="Desfazer"><i class="fa-solid fa-arrow-rotate-left"></i> Desfazer</button>
                            <button type="button" data-image-action="redo" title="Refazer"><i class="fa-solid fa-arrow-rotate-right"></i> Refazer</button>
                            <button type="button" data-image-action="reset" title="Voltar ao original"><i class="fa-solid fa-clock-rotate-left"></i> Original</button>
                        </div>
                        <span data-image-original-info></span>
                    </div>

                    <div class="sax-image-editor__canvas-shell" data-image-canvas-shell>
                        <canvas data-image-canvas aria-label="Prévia da imagem editada"></canvas>
                        <div class="sax-image-editor__grid" aria-hidden="true"><i></i><i></i><i></i><i></i></div>
                        <span class="sax-image-editor__drag-hint"><i class="fa-solid fa-arrows-up-down-left-right"></i> Arraste a imagem</span>
                    </div>

                    <div class="sax-image-editor__summary">
                        <span><i class="fa-regular fa-file-image"></i><strong data-image-file-name>imagem.webp</strong></span>
                        <span><i class="fa-solid fa-expand"></i><strong data-image-final-size>1080 × 1080 px</strong></span>
                        <span><i class="fa-solid fa-gauge-high"></i><strong data-image-estimated-size>Calculando…</strong></span>
                    </div>
                </div>
            </div>

            <div class="modal-footer sax-image-editor__footer">
                <span><i class="fa-solid fa-circle-info"></i> A imagem original só será substituída quando o formulário for salvo.</span>
                <div>
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="button" class="btn btn-dark" data-image-apply><i class="fa-solid fa-check me-2"></i>Aplicar imagem</button>
                </div>
            </div>
        </div>
    </div>
</div>
