@extends('layout.admin')

@section('content')
@php
    $trPt = $institucional->translations->firstWhere('locale', 'pt-br');
    $trEs = $institucional->translations->firstWhere('locale', 'es');
    $trEn = $institucional->translations->firstWhere('locale', 'en');
    $topSliders = is_array($institucional->top_sliders) ? $institucional->top_sliders : (json_decode($institucional->top_sliders, true) ?: []);
    $galleryImages = is_array($institucional->gallery_images) ? $institucional->gallery_images : (json_decode($institucional->gallery_images, true) ?: []);

    $storyGroups = [
        'Hero e introdução' => [
            ['inst_hero_eyebrow', 'Chamada superior', 'Ex.: Experiência exclusiva', 'text'],
            ['inst_hero_description', 'Descrição do slider', 'Texto curto abaixo do título', 'textarea'],
            ['inst_hero_cta', 'Texto do botão', 'Ex.: Descobrir a SAX', 'text'],
        ],
        'Ecossistema SAX' => [
            ['inst_experiences_eyebrow', 'Chamada da seção', 'Ex.: Um universo, muitas experiências', 'text'],
            ['inst_experiences_title', 'Título', 'Ex.: Descubra o universo SAX', 'text'],
            ['inst_experiences_description', 'Descrição', 'Introdução para Palace, Bridal, Café & Bistrô e setores', 'textarea'],
            ['inst_banner_title', 'Frase do banner de impacto', 'Ex.: O luxo ganha novos significados', 'text'],
        ],
        'Números, galeria e história' => [
            ['inst_stats_eyebrow', 'Chamada dos números', 'Ex.: SAX em números', 'text'],
            ['inst_stats_title', 'Título dos números', 'Ex.: Uma referência construída em escala', 'text'],
            ['inst_gallery_eyebrow', 'Chamada da galeria', 'Ex.: Por dentro da SAX', 'text'],
            ['inst_gallery_title', 'Título da galeria', 'Ex.: Espaços que contam histórias', 'text'],
            ['inst_history_eyebrow', 'Chamada da história', 'Ex.: Nosso legado', 'text'],
            ['inst_history_title', 'Título da história', 'Ex.: Uma trajetória movida por visão', 'text'],
            ['inst_history_intro', 'Introdução da história', 'Texto de abertura para a linha do tempo', 'textarea'],
        ],
        'Vídeos e chamada final' => [
            ['inst_videos_eyebrow', 'Chamada dos vídeos', 'Ex.: Paraguai ao vivo', 'text'],
            ['inst_videos_title', 'Título dos vídeos', 'Ex.: Conectados com Ciudad del Este', 'text'],
            ['inst_videos_description', 'Descrição dos vídeos', 'Contexto para as câmeras e o tour', 'textarea'],
            ['inst_cta_eyebrow', 'Chamada final', 'Ex.: Viva a experiência', 'text'],
            ['inst_cta_title', 'Título final', 'Ex.: Sua próxima descoberta começa aqui', 'text'],
            ['inst_cta_description', 'Descrição final', 'Convite para visitar ou entrar em contato', 'textarea'],
            ['inst_cta_button', 'Texto do botão final', 'Ex.: Planejar minha visita', 'text'],
        ],
    ];

    $defaultMilestones = [
        ['year' => '2008', 'label' => 'Origem', 'pt-br' => ['title' => 'O nascimento de uma visão', 'text' => 'A SAX nasce com a proposta de reunir moda, design, gastronomia e serviço em uma experiência inédita no Paraguai.']],
        ['year' => '2012', 'label' => 'Ciudad del Este', 'pt-br' => ['title' => 'Um destino ganha forma', 'text' => 'A operação se consolida como uma referência regional em curadoria, arquitetura e atendimento.']],
        ['year' => '2023', 'label' => 'SAX Bridal', 'pt-br' => ['title' => 'Novas experiências', 'text' => 'A SAX amplia seu universo com serviços especializados para momentos únicos.']],
        ['year' => '2024', 'label' => 'Hoje', 'pt-br' => ['title' => 'Um ecossistema de possibilidades', 'text' => 'Palace, Bridal, Café & Bistrô e o department store se conectam em uma só assinatura.']],
    ];
    $milestones = collect($institucional->history_milestones ?: $defaultMilestones)->take(8)->values();
@endphp

<form action="{{ route('admin.institucional.update', $institucional->id) }}" method="POST" enctype="multipart/form-data" id="formInstitucional" class="special-page-form">
    @csrf
    @method('PUT')

    <x-admin.sticky-header
        title="{{ __('messages.editar_institucional_titulo') }}"
        :cancelRoute="route('admin.institucional.index')"
        :updatedAt="__('messages.ultima_atualizacao_label') . ' ' . $institucional->updated_at->format('d/m/Y H:i')"
        :submitLabel="__('messages.guardar_cambios_btn')" />

    <x-admin.alert />
    <x-admin.translation-guide shared="Imagens, métricas, ordem automática, câmeras e tour são compartilhados. Todos os textos editoriais aceitam PT, ES e EN." />

    <div class="row g-4">
        <div class="col-lg-8 d-flex flex-column gap-4">
            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-pen-nib" number="01" title="Sobre a SAX" />
                <div class="p-4">
                    <div class="mb-4">
                        <x-admin.lang-field name="inst_section_one_title" label="Título principal"
                            :pt="$trPt->inst_section_one_title ?? $institucional->section_one_title"
                            :es="$trEs->inst_section_one_title ?? ''" :en="$trEn->inst_section_one_title ?? ''" />
                    </div>

                    <div class="lang-field" data-current-lang="pt">
                        <div class="lang-field__header">
                            <div class="lang-field__copy">
                                <label class="sax-form-label mb-0" for="editor-content"><i class="fas fa-align-left me-1"></i> História / texto sobre nós</label>
                                <span class="lang-field__current" data-rich-lang-status>Conteúdo em Português</span>
                            </div>
                            <div class="lang-field__tabs" role="tablist" aria-label="Idioma do conteúdo">
                                @foreach(['pt' => ['PT', 'Português', $trPt->inst_section_one_content ?? $institucional->section_one_content], 'es' => ['ES', 'Español', $trEs->inst_section_one_content ?? ''], 'en' => ['EN', 'English', $trEn->inst_section_one_content ?? '']] as $locale => [$short, $full, $value])
                                    <button type="button" class="lang-field__tab content-lang-btn {{ $locale === 'pt' ? 'active' : '' }}"
                                        data-lang-label="{{ $full }}" onclick="switchLanguage('content', '{{ $locale }}', this)">
                                        <span>{{ $short }}</span><i class="lang-field__state {{ filled($value) ? 'is-complete' : '' }}"></i>
                                    </button>
                                @endforeach
                            </div>
                        </div>
                        <textarea id="real-content-pt" name="translate[pt-br][inst_section_one_content]" class="d-none">{{ old('translate.pt-br.inst_section_one_content', $trPt->inst_section_one_content ?? $institucional->section_one_content) }}</textarea>
                        <textarea id="real-content-es" name="translate[es][inst_section_one_content]" class="d-none">{{ old('translate.es.inst_section_one_content', $trEs->inst_section_one_content ?? '') }}</textarea>
                        <textarea id="real-content-en" name="translate[en][inst_section_one_content]" class="d-none">{{ old('translate.en.inst_section_one_content', $trEn->inst_section_one_content ?? '') }}</textarea>
                        <div class="editor-rich-wrapper"><textarea id="editor-content" class="form-control"></textarea></div>
                    </div>
                </div>
            </div>

            @foreach($storyGroups as $groupTitle => $fields)
                <div class="sax-premium-card shadow-sm overflow-hidden">
                    <x-admin.block-header icon="fas fa-layer-group" :number="str_pad($loop->iteration + 1, 2, '0', STR_PAD_LEFT)" :title="$groupTitle" />
                    <div class="p-4 row g-3">
                        @foreach($fields as [$field, $label, $placeholder, $type])
                            <div class="{{ $type === 'textarea' ? 'col-12' : 'col-md-6' }}">
                                <x-admin.lang-field :name="$field" :label="$label" :type="$type" :rows="$type === 'textarea' ? 3 : 1"
                                    :placeholder="$placeholder"
                                    :pt="data_get($trPt, $field, '')" :es="data_get($trEs, $field, '')" :en="data_get($trEn, $field, '')" />
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-timeline" number="06" title="Marcos da história"
                    subtitle="Edite até oito momentos. Os quatro iniciais já contam a trajetória base." />
                <div class="p-4 d-flex flex-column gap-3">
                    @foreach($milestones as $i => $milestone)
                        <div class="border rounded p-3 bg-light">
                            <div class="row g-3 mb-3">
                                <div class="col-md-3">
                                    <label class="sax-form-label">Ano</label>
                                    <input class="form-control sax-input" name="history_milestones[{{ $i }}][year]" value="{{ old("history_milestones.$i.year", $milestone['year'] ?? '') }}">
                                </div>
                                <div class="col-md-9">
                                    <label class="sax-form-label">Etiqueta</label>
                                    <input class="form-control sax-input" name="history_milestones[{{ $i }}][label]" value="{{ old("history_milestones.$i.label", $milestone['label'] ?? '') }}">
                                </div>
                            </div>
                            @foreach(['pt-br' => 'Português', 'es' => 'Español', 'en' => 'English'] as $locale => $localeLabel)
                                <div class="row g-2 mb-2">
                                    <div class="col-md-4">
                                        <label class="x-small fw-bold text-uppercase">{{ $localeLabel }} — título</label>
                                        <input class="form-control sax-input" name="history_milestones[{{ $i }}][{{ $locale }}][title]" value="{{ old("history_milestones.$i.$locale.title", data_get($milestone, "$locale.title")) }}">
                                    </div>
                                    <div class="col-md-8">
                                        <label class="x-small fw-bold text-uppercase">{{ $localeLabel }} — texto</label>
                                        <textarea class="form-control sax-input" rows="2" name="history_milestones[{{ $i }}][{{ $locale }}][text]">{{ old("history_milestones.$i.$locale.text", data_get($milestone, "$locale.text")) }}</textarea>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-award" number="07" title="Pilares da marca" />
                <div class="p-4 d-flex flex-column gap-3">
                    @for ($i = 1; $i <= 3; $i++)
                        @php
                            $slug = $i == 1 ? 'one' : ($i == 2 ? 'two' : 'three');
                            $titleField = "inst_text_section_{$slug}_title";
                            $bodyField = "inst_text_section_{$slug}_body";
                            $baseTitleField = "text_section_{$slug}_title";
                            $baseBodyField = "text_section_{$slug}_body";
                        @endphp
                        <div class="p-3 rounded bg-light border-start border-gold">
                            <span class="x-small fw-bold text-gold text-uppercase d-block mb-2">Pilar 0{{ $i }}</span>
                            <div class="mb-2"><x-admin.lang-field :name="$titleField" :pt="$trPt->$titleField ?? $institucional->$baseTitleField" :es="$trEs->$titleField ?? ''" :en="$trEn->$titleField ?? ''" /></div>
                            <x-admin.lang-field :name="$bodyField" type="textarea" :rows="2" :pt="$trPt->$bodyField ?? $institucional->$baseBodyField" :es="$trEs->$bodyField ?? ''" :en="$trEn->$bodyField ?? ''" />
                        </div>
                    @endfor
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-vr-cardboard" number="08" title="Vídeos do Paraguai e tour virtual" />
                <div class="p-4">
                    <div class="mb-3"><label class="sax-form-label">Tour 360°</label><textarea name="iframe_tour_360" class="form-control sax-input font-monospace small" rows="3">{{ old('iframe_tour_360', $institucional->iframe_tour_360) }}</textarea></div>
                    <div class="row g-3">
                        <div class="col-md-6"><label class="sax-form-label">Câmera Ponte da Amizade</label><textarea name="iframe_ponte_amizade" class="form-control sax-input font-monospace small" rows="3">{{ old('iframe_ponte_amizade', $institucional->iframe_ponte_amizade) }}</textarea></div>
                        <div class="col-md-6"><label class="sax-form-label">Câmera Centro CDE</label><textarea name="iframe_centro_cde" class="form-control sax-input font-monospace small" rows="3">{{ old('iframe_centro_cde', $institucional->iframe_centro_cde) }}</textarea></div>
                    </div>
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-camera-retro" number="09" title="Galeria de fotos"
                    subtitle="As fotos também alimentam automaticamente os banners internos, sem repetição imediata." />
                <div class="p-4"><x-admin.gallery-field field="gallery_images" :images="$galleryImages" :max="\App\Http\Controllers\Admin\InstitucionalAdminController::MAX_GALLERY_IMAGES" /></div>
            </div>
        </div>

        <div class="col-lg-4 d-flex flex-column gap-4">
            <div class="sax-premium-card p-4 shadow-sm">
                <h6 class="sax-label mb-3 text-uppercase">Imagem sobre nós</h6>
                <x-admin.image-upload name="section_one_image" previewId="preview-section_one_image"
                    :currentImage="$institucional->section_one_image ? asset('storage/'.$institucional->section_one_image) : null"
                    placeholder="https://placehold.co/600x400" dimensions="1600 × 1000 px" usage="Imagem editorial principal." />
            </div>

            <div class="sax-premium-card p-4 shadow-sm bg-dark text-white">
                <h6 class="sax-label mb-3 text-gold border-bottom border-secondary pb-2 text-uppercase">SAX em números</h6>
                @foreach([
                    ['stat_brands_count', 'Marcas', $institucional->stat_brands_count],
                    ['stat_categories_count', 'Categorias / setores', $institucional->stat_categories_count ?? 30],
                    ['stat_sqm_count', 'Área total (mil m²)', $institucional->stat_sqm_count],
                    ['stat_employees_count', 'Colaboradores', $institucional->stat_employees_count],
                    ['founded_year', 'Ano de fundação', $institucional->founded_year ?? 2008],
                ] as [$name, $label, $value])
                    <div class="mb-3"><label class="x-small text-uppercase opacity-75 fw-bold">{{ $label }}</label><input type="number" name="{{ $name }}" class="form-control sax-input bg-transparent text-white border-secondary" value="{{ old($name, $value) }}"></div>
                @endforeach
            </div>

            <div class="sax-premium-card p-4 shadow-sm">
                <label class="sax-form-label">Tempo do slider</label>
                <div class="input-group mb-4"><input type="number" min="3" max="20" name="hero_autoplay_seconds" class="form-control sax-input" value="{{ old('hero_autoplay_seconds', $institucional->hero_autoplay_seconds ?? 6) }}"><span class="input-group-text">segundos</span></div>
                <x-admin.gallery-field field="top_sliders" :images="$topSliders" label="Banners do slider"
                    dimensions="1920 × 1080 px" hint="A ordem é sorteada automaticamente a cada visita." :max="\App\Http\Controllers\Admin\InstitucionalAdminController::MAX_TOP_SLIDERS" />
            </div>
        </div>
    </div>
</form>
@endsection
