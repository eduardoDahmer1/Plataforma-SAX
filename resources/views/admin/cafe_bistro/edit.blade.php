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

<x-admin.card>
<form
    id="formCafeBistro"
    action="{{ route('admin.cafe_bistro.update', $cafeBistro->id) }}"
    method="POST"
    enctype="multipart/form-data"
>
    @csrf
    @method('PUT')

    {{-- ── HEADER STICKY ──────────────────────────────────────────── --}}
    <x-admin.sticky-header
        :title="__('messages.edit_sax_cafe_bistro')"
        cancelRoute="{{ route('admin.cafe_bistro.index') }}"
        divider="sax-divider-bistro"
        btnClass="btn-dark-bistro"
        :submitLabel="__('messages.save_changes_btn')"
        :updatedAt="$cafeBistro->updated_at ? __('messages.last_update_label').': '.$cafeBistro->updated_at->format('d/m/Y H:i') : null"
    />

    <x-admin.alert />

    <div class="px-3 d-flex flex-column gap-4">

        {{-- 01. GENERAL                                               --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-bistro"><i class="fas fa-toggle-on"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">01 — {{ __('messages.general_settings_title') }}</p>
                            <p class="x-small text-muted mb-0">{{ __('messages.page_visibility_status_desc') }}</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" id="isActive" value="1"
                                   {{ old('is_active', $cafeBistro->is_active) ? 'checked' : '' }}
                                   style="width:2.5em;height:1.3em;">
                        </div>
                        <label for="isActive" class="sax-form-label m-0">{{ __('messages.active_page_label') }}</label>
                    </div>
                </div>
            </div>

            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-bistro"><i class="fab fa-whatsapp"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">{{ __('messages.contact_title') }}</p>
                            <p class="x-small text-muted mb-0">{{ __('messages.reserve_table_number_desc') }}</p>
                        </div>
                    </div>
                    <label class="sax-form-label">{{ __('messages.whatsapp_digits_label') }}</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border x-small fw-bold text-muted">+</span>
                        <input type="text" name="whatsapp" class="form-control sax-input"
                               value="{{ old('whatsapp', $cafeBistro->whatsapp) }}"
                               placeholder="595991234567">
                    </div>
                    <p class="x-small text-muted mt-2 mb-0">{{ __('messages.phone_digits_hint') }}</p>
                </div>
            </div>
        </div>

        {{-- 02. HERO                                                  --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-image" theme="bistro" number="02" title="Hero" :subtitle="__('messages.hero_background_welcome_texts')" />
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <label class="sax-form-label">{{ __('messages.main_title_label') }}</label>
                        <input type="text" name="hero_titulo" class="form-control sax-input"
                               value="{{ old('hero_titulo', $cafeBistro->hero_titulo) }}"
                               placeholder="Um lugar para saborear o momento.">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">{{ __('messages.subtitle_label') }}</label>
                        <input type="text" name="hero_subtitulo" class="form-control sax-input"
                               value="{{ old('hero_subtitulo', $cafeBistro->hero_subtitulo) }}"
                               placeholder="Frescor ao amanhecer, cafés de origem...">
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <x-admin.image-upload
                        name="hero_imagen"
                        previewId="prev-hero"
                        :label="__('messages.hero_image_label')"
                        :currentImage="$cafeBistro->hero_imagen ? asset('storage/'.$cafeBistro->hero_imagen) : null"
                        placeholder="https://placehold.co/600x400/0f1d35/ffffff?text=Hero"
                        height="11.25rem" />
                </div>
            </div>
        </div>

        {{-- 03. SOBRE NÓS                                            --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-store-alt" theme="bistro" number="03" :title="__('messages.about_us_title')" :subtitle="__('messages.about_image_title_text_desc')" />
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <label class="sax-form-label">{{ __('messages.main_title_label') }}</label>
                        <input type="text" name="sobre_titulo" class="form-control sax-input"
                               value="{{ old('sobre_titulo', $cafeBistro->sobre_titulo) }}"
                               placeholder="Onde cada detalhe importa">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">{{ __('messages.description_label') }}</label>
                        <textarea name="sobre_texto" class="form-control sax-input" rows="6"
                                  placeholder="Descrição do espaço...">{{ old('sobre_texto', $cafeBistro->sobre_texto) }}</textarea>
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <x-admin.image-upload
                        name="sobre_imagen"
                        previewId="prev-sobre"
                        :label="__('messages.about_image_label')"
                        :currentImage="$cafeBistro->sobre_imagen ? asset('storage/'.$cafeBistro->sobre_imagen) : null"
                        placeholder="https://placehold.co/600x400/0f1d35/ffffff?text=Sobre"
                        height="11.25rem" />
                </div>
            </div>
        </div>

        {{-- 04. CARDÁPIO                                             --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-book-open" theme="bistro" number="04" :title="__('messages.menu_title')" :subtitle="__('messages.menu_titles_pdf_max_desc')" />
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="sax-form-label">{{ __('messages.section_title_label') }}</label>
                        <input type="text" name="cardapio_titulo" class="form-control sax-input"
                               value="{{ old('cardapio_titulo', $cafeBistro->cardapio_titulo) }}"
                               placeholder="A Nossa Carta">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">{{ __('messages.subtitle_label') }}</label>
                        <input type="text" name="cardapio_subtitulo" class="form-control sax-input"
                               value="{{ old('cardapio_subtitulo', $cafeBistro->cardapio_subtitulo) }}"
                               placeholder="Sabores que contam histórias">
                    </div>

                    <div class="col-12">
                        <label class="sax-form-label d-block mb-2">{{ __('messages.menu_pdf_label') }}</label>

                        @if($cafeBistro->cardapio_pdf)
                            <div class="d-flex align-items-center gap-3 p-3 border rounded-3 bg-light mb-3">
                                <i class="fas fa-file-pdf fa-2x text-danger"></i>
                                <div class="flex-grow-1">
                                    <p class="x-small fw-bold mb-0">{{ __('messages.current_pdf_label') }}:</p>
                                    <p class="x-small text-muted mb-0">{{ basename($cafeBistro->cardapio_pdf) }}</p>
                                </div>
                                <a href="{{ asset('storage/'.$cafeBistro->cardapio_pdf) }}"
                                   target="_blank"
                                   class="btn btn-sm btn-outline-dark rounded-pill x-small fw-bold px-3">
                                    <i class="fas fa-eye me-1"></i> {{ __('messages.view_btn') }}
                                </a>
                            </div>
                        @endif

                        <div class="upload-zone">
                            <input type="file" name="cardapio_pdf" class="upload-input" accept="application/pdf">
                            <i class="fas fa-file-pdf mb-2 opacity-25 fa-lg"></i>
                            <p class="x-small fw-bold m-0">
                                {{ $cafeBistro->cardapio_pdf ? __('messages.replace_pdf_upload') : __('messages.upload_pdf_prompt') }}
                            </p>
                            <p class="x-small text-muted m-0">{{ __('messages.pdf_only_max_size') }}</p>
                        </div>
                    </div>

                    {{-- Galería de imágenes del cardápio (bento grid) --}}
                    <div class="col-12 mt-2">
                        <label class="sax-form-label d-block mb-2">
                            {{ __('messages.image_gallery_label') }}
                            <span class="text-muted fw-normal">({{ __('messages.bento_grid_max_photos_hint') }})</span>
                        </label>

                        {{-- Preview de imágenes existentes --}}
                        <div id="cardapioGaleriaPreview" class="gallery-preview-grid mb-3">
                            @foreach($cafeBistro->cardapio_galeria ?? [] as $index => $foto)
                                <div class="gallery-preview-item shadow-sm border" data-cardapio-img="{{ $index }}">
                                    <img src="{{ asset('storage/'.$foto) }}" class="w-100 h-100 object-fit-cover">
                                    <input type="hidden" name="cardapio_galeria_actual[]" value="{{ $foto }}">
                                    <button type="button" class="gallery-remove-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Upload de nuevas imágenes --}}
                        <div class="upload-zone" id="cardapioGaleriaZone">
                            <input type="file" name="cardapio_galeria[]" class="upload-input" multiple accept="image/*">
                            <i class="fas fa-images mb-2 opacity-25 fa-lg"></i>
                            <p class="x-small fw-bold m-0">{{ __('messages.click_or_drag_images') }}</p>
                            <p class="x-small text-muted m-0">{{ __('messages.image_formats_max_each') }}</p>
                        </div>

                        <p class="x-small text-muted mt-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            <span id="cardapioGaleriaCount">{{ count($cafeBistro->cardapio_galeria ?? []) }}</span>/8 {{ __('messages.loaded_images_count') }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 05. EVENTOS                                               --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-champagne-glasses" theme="bistro" number="05" :title="__('messages.events_title')" :subtitle="__('messages.events_text_types_gallery_desc')" />
            <div class="p-4">
                <div class="row g-3">
                    {{-- Título y subtítulo --}}
                    <div class="col-md-6">
                        <label class="sax-form-label">{{ __('messages.section_title_label') }}</label>
                        <input type="text" name="eventos_titulo" class="form-control sax-input"
                               value="{{ old('eventos_titulo', $cafeBistro->eventos_titulo) }}"
                               placeholder="Eventos Especiais">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">{{ __('messages.subtitle_label') }}</label>
                        <input type="text" name="eventos_subtitulo" class="form-control sax-input"
                               value="{{ old('eventos_subtitulo', $cafeBistro->eventos_subtitulo) }}"
                               placeholder="Celebre seus momentos conosco">
                    </div>

                    {{-- Texto descriptivo --}}
                    <div class="col-12">
                        <label class="sax-form-label">{{ __('messages.description_label') }}</label>
                        <textarea name="eventos_texto" class="form-control sax-input" rows="4"
                                  placeholder="Descrição do espaço para eventos...">{{ old('eventos_texto', $cafeBistro->eventos_texto) }}</textarea>
                    </div>

                    {{-- Tipos de eventos (tags dinámicos) --}}
                    <div class="col-12">
                        <label class="sax-form-label d-block mb-2">
                            {{ __('messages.event_types_label') }}
                            <span class="text-muted fw-normal">({{ __('messages.press_enter_to_add_hint') }})</span>
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
                               placeholder="{{ __('messages.event_type_placeholder') }}">
                    </div>

                    {{-- Galería de eventos --}}
                    <div class="col-12 mt-2">
                        <label class="sax-form-label d-block mb-2">{{ __('messages.events_gallery_label') }}</label>

                        <div id="eventosGaleriaPreview" class="gallery-preview-grid mb-3">
                            @foreach($cafeBistro->eventos_galeria ?? [] as $index => $foto)
                                <div class="gallery-preview-item shadow-sm border" data-evento-img="{{ $index }}">
                                    <img src="{{ asset('storage/'.$foto) }}" class="w-100 h-100 object-fit-cover">
                                    <input type="hidden" name="eventos_galeria_actual[]" value="{{ $foto }}">
                                    <button type="button" class="gallery-remove-btn">
                                        <i class="fas fa-times"></i>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        <div class="upload-zone">
                            <input type="file" name="eventos_galeria[]" class="upload-input" multiple accept="image/*">
                            <i class="fas fa-images mb-2 opacity-25 fa-lg"></i>
                            <p class="x-small fw-bold m-0">{{ __('messages.click_or_drag_images') }}</p>
                            <p class="x-small text-muted m-0">{{ __('messages.image_formats_max_each') }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 06. HORÁRIOS                                             --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-clock" theme="bistro" number="06" :title="__('messages.opening_hours_title')" :subtitle="__('messages.week_days_opening_hours_desc')" />
            <div class="p-4">
                @php
                    $diasSemana = [
                        ['value' => 'Segunda-feira', 'label' => __('messages.monday_label')],
                        ['value' => 'Terça-feira', 'label' => __('messages.tuesday_label')],
                        ['value' => 'Quarta-feira', 'label' => __('messages.wednesday_label')],
                        ['value' => 'Quinta-feira', 'label' => __('messages.thursday_label')],
                        ['value' => 'Sexta-feira', 'label' => __('messages.friday_label')],
                        ['value' => 'Sábado', 'label' => __('messages.saturday_label')],
                        ['value' => 'Domingo', 'label' => __('messages.sunday_label')],
                    ];
                @endphp

                <div class="d-flex flex-column gap-2">
                    @foreach($diasSemana as $i => $dia)
                        @php
                            $diaNome = $dia['value'];
                            $h = $horarios[$i] ?? ['dia' => '', 'apertura' => '', 'cierre' => ''];
                        @endphp
                        <div class="row g-2 align-items-center horario-row">
                            <div class="col-md-4">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="horario-dia-label">{{ $dia['label'] }}</span>
                                    <input type="hidden" name="horarios[{{ $i }}][dia]" value="{{ $diaNome }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="x-small text-muted" style="min-width:2rem;">{{ __('messages.from_time_label') }}</span>
                                    <input type="time" name="horarios[{{ $i }}][apertura]"
                                           class="form-control sax-input sax-input-sm"
                                           value="{{ $h['apertura'] ?? '' }}">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="d-flex align-items-center gap-2">
                                    <span class="x-small text-muted" style="min-width:2rem;">{{ __('messages.to_time_label') }}</span>
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
                                    <span class="x-small">{{ __('messages.closed_status') }}</span>
                                </label>
                            </div>
                        </div>
                    @endforeach
                </div>

                <p class="x-small text-muted mt-3 mb-0">
                    <i class="fas fa-info-circle me-1"></i>
                    {{ __('messages.mark_closed_hint') }}
                </p>
            </div>
        </div>

        {{-- 07. SEO                                                   --}}

        {{-- ── FOOTER ACCIONES ────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center pt-3 pb-4 border-top">
            <a href="{{ route('admin.cafe_bistro.index') }}" class="btn-back-minimal">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back_to_dashboard_btn') }}
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.cafe_bistro.index') }}" class="btn btn-light rounded-pill px-4 x-small fw-bold text-muted">
                    {{ __('messages.discard_btn') }}
                </a>
                <button type="submit" class="btn btn-dark-bistro rounded-pill px-5 fw-bold">
                    <i class="fas fa-check-circle me-2"></i> {{ __('messages.save_changes_btn') }}
                </button>
            </div>
        </div>

    </div>

</form>
</x-admin.card>


{{-- MOBILE: botón fijo inferior --}}
<x-admin.mobile-submit formId="formCafeBistro" btnClass="btn-dark-bistro" :label="__('messages.save_changes_btn')" />


@endsection
