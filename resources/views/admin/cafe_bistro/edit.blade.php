@extends('layout.admin')

@section('content')
@php
    $eventosTipos = is_array($cafeBistro->eventos_tipos)
        ? $cafeBistro->eventos_tipos
        : (json_decode($cafeBistro->eventos_tipos, true) ?? []);

    $horarios = is_array($cafeBistro->horarios)
        ? $cafeBistro->horarios
        : (json_decode($cafeBistro->horarios, true) ?? []);

    for ($i = count($horarios); $i < 7; $i++) {
        $horarios[] = ['dia' => '', 'apertura' => '', 'cierre' => ''];
    }
@endphp

<div class="sax-admin-container py-2 bg-white-soft">
<form
    id="formCafeBistro"
    action="{{ route('admin.cafe_bistro.update', $cafeBistro->id) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')

    {{-- ── HEADER STICKY ──────────────────────────────────────────── --}}
    <div class="sticky-header px-4 py-3 mb-5 bg-white border-bottom shadow-sm d-flex justify-content-between align-items-center">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar SAX Café & Bistrô</h2>
            <div class="sax-divider-bistro"></div>
            <span class="text-muted x-small">
                Última actualização: {{ $cafeBistro->updated_at ? $cafeBistro->updated_at->format('d/m/Y H:i') : 'Nunca' }}
            </span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('admin.cafe_bistro.index') }}" class="btn-back-minimal d-none d-md-flex align-items-center">
                <i class="fas fa-times me-1"></i> CANCELAR
            </a>
            <button type="submit" class="btn btn-dark-bistro rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-check-circle me-2"></i> SALVAR ALTERAÇÕES
            </button>
        </div>
    </div>

    {{-- ── FEEDBACK ─────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-modern alert-success mb-4 mx-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger mx-4 mb-4 rounded-3 border-0">
            <p class="fw-bold small mb-2"><i class="fas fa-exclamation-triangle me-1"></i> Há erros no formulário:</p>
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="px-3 d-flex flex-column gap-4">

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 01. GENERAL                                               --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-bistro"><i class="fas fa-toggle-on"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">01 — Configuração Geral</p>
                            <p class="x-small text-muted mb-0">Estado de visibilidade da página</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" id="isActive" value="1"
                                   {{ old('is_active', $cafeBistro->is_active) ? 'checked' : '' }}
                                   style="width:2.5em;height:1.3em;">
                        </div>
                        <label for="isActive" class="sax-form-label m-0">Página Ativa</label>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-bistro"><i class="fab fa-whatsapp"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Contato</p>
                            <p class="x-small text-muted mb-0">Número para o botão "Reservar Mesa"</p>
                        </div>
                    </div>
                    <label class="sax-form-label">WhatsApp (só números)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border x-small fw-bold text-muted">+</span>
                        <input type="text" name="whatsapp" class="form-control sax-input"
                               value="{{ old('whatsapp', $cafeBistro->whatsapp) }}"
                               placeholder="595991234567">
                    </div>
                    <p class="x-small text-muted mt-2 mb-0">Sem espaços, traços ou parênteses. Ex: 595991234567</p>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 02. HERO                                                  --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-bistro"><i class="fas fa-image"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">02 — Hero</p>
                    <p class="x-small text-muted mb-0">Imagem de fundo e textos de boas-vindas</p>
                </div>
            </div>
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <label class="sax-form-label">Título Principal</label>
                        <input type="text" name="hero_titulo" class="form-control sax-input"
                               value="{{ old('hero_titulo', $cafeBistro->hero_titulo) }}"
                               placeholder="Um lugar para saborear o momento.">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Subtítulo</label>
                        <input type="text" name="hero_subtitulo" class="form-control sax-input"
                               value="{{ old('hero_subtitulo', $cafeBistro->hero_subtitulo) }}"
                               placeholder="Frescor ao amanhecer, cafés de origem...">
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <label class="sax-form-label d-block mb-2">Imagem Hero</label>
                    <div class="img-preview-box mb-3 rounded-3 overflow-hidden border" style="height:11.25rem;">
                        <img id="prev-hero"
                             src="{{ $cafeBistro->hero_imagen ? asset('storage/'.$cafeBistro->hero_imagen) : 'https://placehold.co/600x400/0f1d35/ffffff?text=Hero' }}"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="upload-zone">
                        <input type="file" name="hero_imagen" class="upload-input img-trigger" data-prev="prev-hero" accept="image/*">
                        <i class="fas fa-cloud-upload-alt mb-2 opacity-25 fa-lg"></i>
                        <p class="x-small fw-bold m-0">Clique ou arraste uma imagem</p>
                        <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. 8MB</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 03. SOBRE NÓS                                            --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-bistro"><i class="fas fa-store-alt"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">03 — Sobre Nós</p>
                    <p class="x-small text-muted mb-0">Imagem, título e texto de apresentação</p>
                </div>
            </div>
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <label class="sax-form-label">Título</label>
                        <input type="text" name="sobre_titulo" class="form-control sax-input"
                               value="{{ old('sobre_titulo', $cafeBistro->sobre_titulo) }}"
                               placeholder="Onde cada detalhe importa">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Texto</label>
                        <textarea name="sobre_texto" class="form-control sax-input" rows="6"
                                  placeholder="Descrição do espaço...">{{ old('sobre_texto', $cafeBistro->sobre_texto) }}</textarea>
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <label class="sax-form-label d-block mb-2">Imagem Sobre Nós</label>
                    <div class="img-preview-box mb-3 rounded-3 overflow-hidden border" style="height:11.25rem;">
                        <img id="prev-sobre"
                             src="{{ $cafeBistro->sobre_imagen ? asset('storage/'.$cafeBistro->sobre_imagen) : 'https://placehold.co/600x400/0f1d35/ffffff?text=Sobre' }}"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="upload-zone">
                        <input type="file" name="sobre_imagen" class="upload-input img-trigger" data-prev="prev-sobre" accept="image/*">
                        <i class="fas fa-cloud-upload-alt mb-2 opacity-25 fa-lg"></i>
                        <p class="x-small fw-bold m-0">Clique ou arraste uma imagem</p>
                        <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. 8MB</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 04. CARDÁPIO                                             --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-bistro"><i class="fas fa-book-open"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">04 — Cardápio</p>
                    <p class="x-small text-muted mb-0">Títulos e PDF do cardápio (máx. 8MB)</p>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="sax-form-label">Título da Seção</label>
                        <input type="text" name="cardapio_titulo" class="form-control sax-input"
                               value="{{ old('cardapio_titulo', $cafeBistro->cardapio_titulo) }}"
                               placeholder="A Nossa Carta">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">Subtítulo</label>
                        <input type="text" name="cardapio_subtitulo" class="form-control sax-input"
                               value="{{ old('cardapio_subtitulo', $cafeBistro->cardapio_subtitulo) }}"
                               placeholder="Sabores que contam histórias">
                    </div>

                    <div class="col-12">
                        <label class="sax-form-label d-block mb-2">PDF do Cardápio</label>

                        @if($cafeBistro->cardapio_pdf)
                            <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light mb-3">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                <div class="flex-grow-1">
                                    <p class="x-small fw-bold mb-0">PDF atual:</p>
                                    <p class="x-small text-muted mb-0">{{ basename($cafeBistro->cardapio_pdf) }}</p>
                                </div>
                                <a href="{{ asset('storage/'.$cafeBistro->cardapio_pdf) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-dark rounded-pill x-small fw-bold px-3">
                                    <i class="fas fa-eye me-1"></i> VER
                                </a>
                            </div>
                        @endif

                        <div class="upload-zone">
                            <input type="file" name="cardapio_pdf" class="upload-input" accept="application/pdf">
                            <i class="fas fa-file-pdf mb-2 opacity-25 fa-lg"></i>
                            <p class="x-small fw-bold m-0">
                                {{ $cafeBistro->cardapio_pdf ? 'Clique para substituir o PDF' : 'Clique para carregar o PDF' }}
                            </p>
                            <p class="x-small text-muted m-0">Somente PDF — máx. 8MB</p>
                        </div>
                    </div>

                    {{-- Galería de imágenes del cardápio (bento grid) --}}
                    <div class="col-12 mt-2">
                        <label class="sax-form-label d-block mb-2">
                            Galeria de Imagens
                            <span class="text-muted fw-normal">(máx. 8 fotos — para o bento grid)</span>
                        </label>

                        {{-- Preview de imágenes existentes --}}
                        <div id="cardapioGaleriaPreview" class="gallery-preview-grid mb-3">
                            @foreach($cafeBistro->cardapio_galeria ?? [] as $index => $foto)
                                <div class="gallery-preview-item shadow-sm border" data-cardapio-img="{{ $index }}">
                                    <img src="{{ asset('storage/'.$foto) }}" class="w-100 h-100 object-fit-cover">
                                    <input type="hidden" name="cardapio_galeria_actual[]" value="{{ $foto }}">
                                    <button type="button" class="gallery-remove-btn" onclick="removeCardapioImg(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Upload de nuevas imágenes --}}
                        <div class="upload-zone" id="cardapioGaleriaZone">
                            <input type="file" name="cardapio_galeria[]" class="upload-input" multiple accept="image/*">
                            <i class="fas fa-images mb-2 opacity-25 fa-lg"></i>
                            <p class="x-small fw-bold m-0">Clique ou arraste imagens</p>
                            <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. 4MB cada</p>
                        </div>

                        <p class="x-small text-muted mt-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="cardapioGaleriaCount">{{ count($cafeBistro->cardapio_galeria ?? []) }}</span>/8 imagens carregadas
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 05. EVENTOS                                               --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-bistro"><i class="fas fa-champagne-glasses"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">05 — Eventos</p>
                    <p class="x-small text-muted mb-0">Textos, tipos e galeria da seção de eventos</p>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    {{-- Título y subtítulo --}}
                    <div class="col-md-6">
                        <label class="sax-form-label">Título da Seção</label>
                        <input type="text" name="eventos_titulo" class="form-control sax-input"
                               value="{{ old('eventos_titulo', $cafeBistro->eventos_titulo) }}"
                               placeholder="Eventos Especiais">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">Subtítulo</label>
                        <input type="text" name="eventos_subtitulo" class="form-control sax-input"
                               value="{{ old('eventos_subtitulo', $cafeBistro->eventos_subtitulo) }}"
                               placeholder="Celebre seus momentos conosco">
                    </div>

                    {{-- Texto descriptivo --}}
                    <div class="col-12">
                        <label class="sax-form-label">Texto</label>
                        <textarea name="eventos_texto" class="form-control sax-input" rows="4"
                                  placeholder="Descrição do espaço para eventos...">{{ old('eventos_texto', $cafeBistro->eventos_texto) }}</textarea>
                    </div>

                    {{-- Tipos de eventos (tags dinámicos) --}}
                    <div class="col-12">
                        <label class="sax-form-label d-block mb-2">
                            Tipos de Eventos
                            <span class="text-muted fw-normal">(pressione Enter para adicionar)</span>
                        </label>
                        <div class="d-flex flex-wrap gap-2 mb-2" id="eventosTiposContainer">
                            @foreach($eventosTipos as $tipo)
                                <span class="eventos-tag">
                                    {{ $tipo }}
                                    <input type="hidden" name="eventos_tipos[]" value="{{ $tipo }}">
                                    <button type="button" class="eventos-tag-remove" onclick="this.parentElement.remove()">&times;</button>
                                </span>
                            @endforeach
                        </div>
                        <input type="text" id="eventosTipoInput" class="form-control sax-input"
                               placeholder="Ex: Aniversários, Casamentos, Corporativo...">
                    </div>

                    {{-- Galería de eventos --}}
                    <div class="col-12 mt-2">
                        <label class="sax-form-label d-block mb-2">Galeria de Eventos</label>

                        <div id="eventosGaleriaPreview" class="gallery-preview-grid mb-3">
                            @foreach($cafeBistro->eventos_galeria ?? [] as $index => $foto)
                                <div class="gallery-preview-item shadow-sm border" data-evento-img="{{ $index }}">
                                    <img src="{{ asset('storage/'.$foto) }}" class="w-100 h-100 object-fit-cover">
                                    <input type="hidden" name="eventos_galeria_actual[]" value="{{ $foto }}">
                                    <button type="button" class="gallery-remove-btn" onclick="removeEventoImg(this)">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="upload-zone">
                            <input type="file" name="eventos_galeria[]" class="upload-input" multiple accept="image/*">
                            <i class="fas fa-images mb-2 opacity-25 fa-lg"></i>
                            <p class="x-small fw-bold m-0">Clique ou arraste imagens</p>
                            <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. 4MB cada</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 06. HORÁRIOS                                             --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-bistro"><i class="fas fa-clock"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">06 — Horários</p>
                    <p class="x-small text-muted mb-0">Dias da semana e horários de funcionamento</p>
                </div>
            </div>
            <div class="p-4">
                @php
                    $diasSemana = ['Segunda-feira','Terça-feira','Quarta-feira','Quinta-feira','Sexta-feira','Sábado','Domingo'];
                @endphp

                <div class="d-flex flex-column gap-2">
                    @foreach($diasSemana as $i => $diaNome)
                        @php
                            $h = $horarios[$i] ?? ['dia' => '', 'apertura' => '', 'cierre' => ''];
                        @endphp
                        <div class="row g-2 align-items-center horario-row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="horario-dia-label">{{ $diaNome }}</span>
                                    <input type="hidden" name="horarios[{{ $i }}][dia]" value="{{ $diaNome }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="x-small text-muted" style="min-width:2rem;">De</span>
                                    <input type="time" name="horarios[{{ $i }}][apertura]"
                                           class="form-control sax-input sax-input-sm"
                                           value="{{ $h['apertura'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="x-small text-muted" style="min-width:2rem;">Até</span>
                                    <input type="time" name="horarios[{{ $i }}][cierre]"
                                           class="form-control sax-input sax-input-sm"
                                           value="{{ $h['cierre'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-2 text-center">
                                <label class="horario-fechado-label">
                                    <input type="checkbox" class="form-check-input me-1 horario-fechado-check"
                                           data-row="{{ $i }}"
                                           {{ ($h['apertura'] ?? '') === '' && ($h['cierre'] ?? '') === '' && $cafeBistro->exists && $cafeBistro->horarios ? 'checked' : '' }}>
                                    <span class="x-small">Fechado</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="x-small text-muted mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    Marque "Fechado" para desabilitar os horários do dia.
                </p>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 07. SEO                                                   --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

        {{-- ── FOOTER ACCIONES ────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center pt-3 pb-4 border-top">
            <a href="{{ route('admin.cafe_bistro.index') }}" class="btn-back-minimal">
                <i class="fas fa-arrow-left me-1"></i> VOLTAR AO DASHBOARD
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.cafe_bistro.index') }}" class="btn btn-light rounded-pill px-4 x-small fw-bold text-muted">
                    DESCARTAR
                </a>
                <button type="submit" class="btn btn-dark-bistro rounded-pill px-5 fw-bold">
                    <i class="fas fa-check-circle me-2"></i> SALVAR ALTERAÇÕES
                </button>
            </div>
        </div>

    </div>

</form>
</div>

{{-- MOBILE: botón fijo inferior --}}
<div class="d-md-none fixed-bottom p-3 bg-white border-top shadow-lg" style="z-index:1030;">
    <button form="formCafeBistro" type="submit" class="btn btn-dark-bistro w-100 py-3 rounded-pill fw-bold">
        <i class="fas fa-check-circle me-2"></i> SALVAR ALTERAÇÕES
    </button>
</div>

@push('scripts')
<script>
// Preview de imágenes individuales (hero, sobre)
document.querySelectorAll('.img-trigger').forEach(function(input) {
    input.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        const prevId = this.dataset.prev;
        const reader = new FileReader();
        reader.onload = e => document.getElementById(prevId).src = e.target.result;
        reader.readAsDataURL(this.files[0]);
    });
});

// Remover imagen existente de la galería del cardápio
function removeCardapioImg(btn) {
    btn.closest('.gallery-preview-item').remove();
    updateCardapioCount();
}

// Actualizar contador de imágenes
function updateCardapioCount() {
    var existentes = document.querySelectorAll('#cardapioGaleriaPreview .gallery-preview-item').length;
    document.getElementById('cardapioGaleriaCount').textContent = existentes;
}

// Remover imagen existente de la galería de eventos
function removeEventoImg(btn) {
    btn.closest('.gallery-preview-item').remove();
}

// Tags dinámicos para tipos de eventos
document.getElementById('eventosTipoInput').addEventListener('keydown', function(e) {
    if (e.key !== 'Enter') return;
    e.preventDefault();
    var val = this.value.trim();
    if (!val) return;

    var container = document.getElementById('eventosTiposContainer');
    var tag = document.createElement('span');
    tag.className = 'eventos-tag';
    tag.innerHTML = val +
        '<input type="hidden" name="eventos_tipos[]" value="' + val.replace(/"/g, '&quot;') + '">' +
        '<button type="button" class="eventos-tag-remove" onclick="this.parentElement.remove()">&times;</button>';
    container.appendChild(tag);
    this.value = '';
});

// Checkbox "Fechado" deshabilita los inputs de horario
document.querySelectorAll('.horario-fechado-check').forEach(function(check) {
    var row = check.closest('.horario-row');
    var timeInputs = row.querySelectorAll('input[type="time"]');

    function toggle() {
        timeInputs.forEach(function(input) {
            input.disabled = check.checked;
            if (check.checked) input.value = '';
            input.style.opacity = check.checked ? '0.4' : '1';
        });
    }

    toggle(); // estado inicial
    check.addEventListener('change', toggle);
});
</script>
@endpush

@endsection