@extends('layout.admin')

@section('content')
@php
    $trPt = $institucional->translations->firstWhere('locale', 'pt-br');
    $trEs = $institucional->translations->firstWhere('locale', 'es');
    $trEn = $institucional->translations->firstWhere('locale', 'en');

    $topSliders = is_array($institucional->top_sliders) ? $institucional->top_sliders : (json_decode($institucional->top_sliders, true) ?: []);
    $galleryImages = is_array($institucional->gallery_images) ? $institucional->gallery_images : (json_decode($institucional->gallery_images, true) ?: []);
@endphp

<form action="{{ route('admin.institucional.update', $institucional->id) }}" method="POST" enctype="multipart/form-data" id="formInstitucional">
    @csrf
    @method('PUT')

    <x-admin.sticky-header
        title="{{ __('messages.editar_institucional_titulo') }}"
        :cancelRoute="route('admin.institucional.index')"
        :updatedAt="__('messages.ultima_atualizacao_label') . ' ' . $institucional->updated_at->format('d/m/Y H:i')"
        :submitLabel="__('messages.guardar_cambios_btn')" />

    <x-admin.alert />

    <div class="row g-4">
        {{-- Coluna Principal --}}
        <div class="col-lg-8 d-flex flex-column gap-4">

            {{-- 1. SEÇÃO SOBRE NOSOTROS --}}
            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-pencil-alt" number="01"
                    :title="__('messages.secao_principal_nosotros')" />

                <div class="p-4">
                    <div class="mb-4">
                        <x-admin.lang-field name="inst_section_one_title"
                            :label="__('messages.titulo_secao_label')"
                            :pt="$trPt->inst_section_one_title ?? $institucional->section_one_title"
                            :es="$trEs->inst_section_one_title ?? ''"
                            :en="$trEn->inst_section_one_title ?? ''" />
                    </div>

                    {{-- Conteúdo narrativo (rich text) — mantém o editor TinyMCE já usado no restante do admin --}}
                    <div class="mb-0">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <label class="sax-form-label mb-0"><i class="fas fa-align-left me-1"></i> {{ __('messages.conteudo_narrativo_label') }}</label>
                            <div class="d-flex gap-1">
                                <a href="javascript:void(0)" class="badge bg-primary content-lang-btn text-decoration-none" onclick="switchLanguage('content', 'pt', this)">PT</a>
                                <a href="javascript:void(0)" class="badge bg-secondary content-lang-btn text-decoration-none" onclick="switchLanguage('content', 'es', this)">ES</a>
                                <a href="javascript:void(0)" class="badge bg-secondary content-lang-btn text-decoration-none" onclick="switchLanguage('content', 'en', this)">EN</a>
                            </div>
                        </div>

                        <textarea id="real-content-pt" name="translate[pt-br][inst_section_one_content]" class="d-none">{{ old('translate.pt-br.inst_section_one_content', $trPt->inst_section_one_content ?? $institucional->section_one_content) }}</textarea>
                        <textarea id="real-content-es" name="translate[es][inst_section_one_content]" class="d-none">{{ old('translate.es.inst_section_one_content', $trEs->inst_section_one_content ?? '') }}</textarea>
                        <textarea id="real-content-en" name="translate[en][inst_section_one_content]" class="d-none">{{ old('translate.en.inst_section_one_content', $trEn->inst_section_one_content ?? '') }}</textarea>

                        <div class="editor-rich-wrapper">
                            <textarea id="editor-content" class="form-control"></textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 2. BLOCOS DE TEXTO (PILARES) --}}
            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-award" number="02"
                    :title="__('messages.pilares_qualidade_sec')" />

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
                            <span class="x-small fw-bold text-gold text-uppercase letter-spacing-1 d-block mb-2">{{ __('messages.pilar_label') }} 0{{ $i }}</span>

                            <div class="mb-2">
                                <x-admin.lang-field :name="$titleField"
                                    :pt="$trPt->$titleField ?? $institucional->$baseTitleField"
                                    :es="$trEs->$titleField ?? ''"
                                    :en="$trEn->$titleField ?? ''" />
                            </div>
                            <x-admin.lang-field :name="$bodyField" type="textarea" :rows="2"
                                :pt="$trPt->$bodyField ?? $institucional->$baseBodyField"
                                :es="$trEs->$bodyField ?? ''"
                                :en="$trEn->$bodyField ?? ''" />
                        </div>
                    @endfor
                </div>
            </div>

            {{-- 3. EXPERIÊNCIA VIRTUAL --}}
            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-vr-cardboard" number="03"
                    :title="__('messages.experiencia_virtual_sec')" />

                <div class="p-4">
                    <div class="mb-4">
                        <label class="sax-form-label">{{ __('messages.tour_virtual_label') }}</label>
                        <textarea name="iframe_tour_360" class="form-control sax-input font-monospace small" rows="3" placeholder='<iframe src="..."></iframe>'>{{ old('iframe_tour_360', $institucional->iframe_tour_360) }}</textarea>
                    </div>

                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="sax-form-label">{{ __('messages.camera_ponte_label') }}</label>
                            <textarea name="iframe_ponte_amizade" class="form-control sax-input font-monospace small" rows="3">{{ old('iframe_ponte_amizade', $institucional->iframe_ponte_amizade) }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <label class="sax-form-label">{{ __('messages.camera_centro_label') }}</label>
                            <textarea name="iframe_centro_cde" class="form-control sax-input font-monospace small" rows="3">{{ old('iframe_centro_cde', $institucional->iframe_centro_cde) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 4. GALERIA DE FOTOS --}}
            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-camera-retro" number="04"
                    :title="__('messages.galeria_fotos_sec')"
                    :subtitle="__('messages.gallery_rotation_hint') ?? 'As imagens são exibidas em ordem rotativa automática a cada 2 dias no site.'" />

                <div class="p-4">
                    <x-admin.gallery-field field="gallery_images" :images="$galleryImages"
                        :max="\App\Http\Controllers\Admin\InstitucionalAdminController::MAX_GALLERY_IMAGES" />
                </div>
            </div>
        </div>

        {{-- Coluna Lateral --}}
        <div class="col-lg-4 d-flex flex-column gap-4">
            {{-- IMAGEM PRINCIPAL --}}
            <div class="sax-premium-card p-4 shadow-sm">
                <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">{{ __('messages.imagem_capa_label') }}</h6>
                <x-admin.image-upload name="section_one_image" previewId="preview-section_one_image"
                    :currentImage="$institucional->section_one_image ? asset('storage/'.$institucional->section_one_image) : null"
                    placeholder="https://placehold.co/600x400" />
            </div>

            {{-- MÉTRICAS SAX --}}
            <div class="sax-premium-card p-4 shadow-sm bg-dark text-white">
                <h6 class="sax-label mb-3 text-gold border-bottom border-secondary pb-2 text-uppercase letter-spacing-1">{{ __('messages.numeros_sax_sec') }}</h6>
                <div class="mb-3">
                    <label class="x-small text-uppercase opacity-7 fw-bold">{{ __('messages.qtd_marcas_label') }}</label>
                    <input type="number" name="stat_brands_count" class="form-control sax-input bg-transparent text-gold border-secondary" value="{{ old('stat_brands_count', $institucional->stat_brands_count) }}">
                </div>
                <div class="mb-3">
                    <label class="x-small text-uppercase opacity-7 fw-bold">{{ __('messages.area_total_label') }}</label>
                    <input type="number" name="stat_sqm_count" class="form-control sax-input bg-transparent text-white border-secondary" value="{{ old('stat_sqm_count', $institucional->stat_sqm_count) }}">
                </div>
                <div class="mb-0">
                    <label class="x-small text-uppercase opacity-7 fw-bold">{{ __('messages.colaboradores_label') }}</label>
                    <input type="number" name="stat_employees_count" class="form-control sax-input bg-transparent text-white border-secondary" value="{{ old('stat_employees_count', $institucional->stat_employees_count) }}">
                </div>
            </div>

            {{-- TOP SLIDERS --}}
            <div class="sax-premium-card p-4 shadow-sm">
                <x-admin.gallery-field field="top_sliders" :images="$topSliders"
                    :label="__('messages.banners_top_sec')"
                    :max="\App\Http\Controllers\Admin\InstitucionalAdminController::MAX_TOP_SLIDERS" />
            </div>
        </div>
    </div>
</form>
@endsection
