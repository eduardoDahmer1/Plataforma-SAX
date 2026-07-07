@extends('layout.admin')

@section('content')
@php
    $eventosTipos = is_array($cafeBistro->eventos_tipos)
        ? $cafeBistro->eventos_tipos
        : (json_decode($cafeBistro->eventos_tipos, true) ?? []);

    $horarios = $cafeBistro->horarios ?? [];

    // Traducciones por idioma para precargar los campos traducibles
    $tr  = $cafeBistro->translations->keyBy('locale');
    $tpt = $tr->get('pt-br');
    $tes = $tr->get('es');
    $ten = $tr->get('en');
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

    <x-admin.sticky-header :title="__('messages.edit_sax_cafe_bistro')" cancelRoute="{{ route('admin.cafe_bistro.index') }}"
        divider="sax-divider-bistro" btnClass="btn-dark-bistro" :submitLabel="__('messages.save_changes_btn')"
        :updatedAt="$cafeBistro->updated_at ? __('messages.last_update_label').': '.$cafeBistro->updated_at->format('d/m/Y H:i') : null" />

    <x-admin.alert />

    <div class="px-3 mb-3 x-small text-muted">
        <i class="fas fa-language me-1"></i> Cada campo de texto pode ser editado em PT / ES / EN. As imagens e horários são comuns a todos os idiomas.
    </div>

    <div class="px-3 d-flex flex-column gap-4">

        {{-- 01. HERO                                                  --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-image" theme="bistro" number="01" title="Hero" :subtitle="__('messages.hero_background_welcome_texts')" />
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <x-admin.lang-field name="cafe_hero_titulo" :label="__('messages.main_title_label')"
                            :pt="$tpt?->cafe_hero_titulo ?? $cafeBistro->hero_titulo"
                            :es="$tes?->cafe_hero_titulo" :en="$ten?->cafe_hero_titulo"
                            placeholder="Um lugar para saborear o momento." />
                    </div>
                    <div class="mb-0">
                        <x-admin.lang-field name="cafe_hero_subtitulo" :label="__('messages.subtitle_label')"
                            :pt="$tpt?->cafe_hero_subtitulo ?? $cafeBistro->hero_subtitulo"
                            :es="$tes?->cafe_hero_subtitulo" :en="$ten?->cafe_hero_subtitulo"
                            placeholder="Frescor ao amanhecer, cafés de origem..." />
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <x-admin.image-upload name="hero_imagen" previewId="prev-hero" :label="__('messages.hero_image_label')"
                        :currentImage="$cafeBistro->hero_imagen ? asset('storage/'.$cafeBistro->hero_imagen) : null"
                        placeholder="https://placehold.co/600x400/0f1d35/ffffff?text=Hero" height="11.25rem" />
                </div>
            </div>
        </div>

        {{-- 02. SOBRE NÓS                                            --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-store-alt" theme="bistro" number="02" :title="__('messages.about_us_title')" :subtitle="__('messages.about_image_title_text_desc')" />
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <x-admin.lang-field name="cafe_sobre_titulo" :label="__('messages.main_title_label')"
                            :pt="$tpt?->cafe_sobre_titulo ?? $cafeBistro->sobre_titulo"
                            :es="$tes?->cafe_sobre_titulo" :en="$ten?->cafe_sobre_titulo"
                            placeholder="Onde cada detalhe importa" />
                    </div>
                    <div class="mb-0">
                        <x-admin.lang-field name="cafe_sobre_texto" :label="__('messages.description_label')"
                            type="textarea" :rows="6"
                            :pt="$tpt?->cafe_sobre_texto ?? $cafeBistro->sobre_texto"
                            :es="$tes?->cafe_sobre_texto" :en="$ten?->cafe_sobre_texto"
                            placeholder="Descrição do espaço..." />
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <x-admin.image-upload name="sobre_imagen" previewId="prev-sobre" :label="__('messages.about_image_label')"
                        :currentImage="$cafeBistro->sobre_imagen ? asset('storage/'.$cafeBistro->sobre_imagen) : null"
                        placeholder="https://placehold.co/600x400/0f1d35/ffffff?text=Sobre" height="11.25rem" />
                </div>
            </div>
        </div>

        {{-- 03. CARDÁPIO                                             --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-book-open" theme="bistro" number="03" :title="__('messages.menu_title')" :subtitle="__('messages.menu_titles_pdf_max_desc')" />
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <x-admin.lang-field name="cafe_cardapio_titulo" :label="__('messages.section_title_label')"
                            :pt="$tpt?->cafe_cardapio_titulo ?? $cafeBistro->cardapio_titulo"
                            :es="$tes?->cafe_cardapio_titulo" :en="$ten?->cafe_cardapio_titulo"
                            placeholder="A Nossa Carta" />
                    </div>
                    <div class="col-md-6">
                        <x-admin.lang-field name="cafe_cardapio_subtitulo" :label="__('messages.subtitle_label')"
                            :pt="$tpt?->cafe_cardapio_subtitulo ?? $cafeBistro->cardapio_subtitulo"
                            :es="$tes?->cafe_cardapio_subtitulo" :en="$ten?->cafe_cardapio_subtitulo"
                            placeholder="Sabores que contam histórias" />
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

        {{-- 04. EVENTOS                                               --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-champagne-glasses" theme="bistro" number="04" :title="__('messages.events_title')" :subtitle="__('messages.events_text_types_gallery_desc')" />
            <div class="p-4">
                <div class="row g-3">
                    {{-- Título y subtítulo --}}
                    <div class="col-md-6">
                        <x-admin.lang-field name="cafe_eventos_titulo" :label="__('messages.section_title_label')"
                            :pt="$tpt?->cafe_eventos_titulo ?? $cafeBistro->eventos_titulo"
                            :es="$tes?->cafe_eventos_titulo" :en="$ten?->cafe_eventos_titulo"
                            placeholder="Eventos Especiais" />
                    </div>
                    <div class="col-md-6">
                        <x-admin.lang-field name="cafe_eventos_subtitulo" :label="__('messages.subtitle_label')"
                            :pt="$tpt?->cafe_eventos_subtitulo ?? $cafeBistro->eventos_subtitulo"
                            :es="$tes?->cafe_eventos_subtitulo" :en="$ten?->cafe_eventos_subtitulo"
                            placeholder="Celebre seus momentos conosco" />
                    </div>

                    {{-- Texto descriptivo --}}
                    <div class="col-12">
                        <x-admin.lang-field name="cafe_eventos_texto" :label="__('messages.description_label')"
                            type="textarea" :rows="4"
                            :pt="$tpt?->cafe_eventos_texto ?? $cafeBistro->eventos_texto"
                            :es="$tes?->cafe_eventos_texto" :en="$ten?->cafe_eventos_texto"
                            placeholder="Descrição do espaço para eventos..." />
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

        {{-- 05. HORÁRIOS                                             --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-clock" theme="bistro" number="05" :title="__('messages.opening_hours_title')" :subtitle="__('messages.week_days_opening_hours_desc')" />
            <div class="p-4">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="sax-form-label">Segunda-feira</label>
                        <input type="text" name="horario_segunda" class="form-control sax-input"
                               value="{{ old('horario_segunda', $horarios['segunda'] ?? '') }}"
                               placeholder="Fechado">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">Terça-feira — Quinta-feira</label>
                        <input type="text" name="horario_terca_quinta" class="form-control sax-input"
                               value="{{ old('horario_terca_quinta', $horarios['terca_quinta'] ?? '') }}"
                               placeholder="09:00 — 23:00">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">Sexta-feira — Sábado</label>
                        <input type="text" name="horario_sexta_sabado" class="form-control sax-input"
                               value="{{ old('horario_sexta_sabado', $horarios['sexta_sabado'] ?? '') }}"
                               placeholder="09:00 — 23:30">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">Domingo</label>
                        <input type="text" name="horario_domingo" class="form-control sax-input"
                               value="{{ old('horario_domingo', $horarios['domingo'] ?? '') }}"
                               placeholder="09:00 — 23:00">
                    </div>
                </div>
            </div>
        </div>

        {{-- 06. CONTATO E LOCALIZAÇÃO                                --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-location-dot" theme="bistro" number="06"
                                  title="Contato e Localização"
                                  subtitle="Endereço, telefone, redes sociais e mapa" />
            <div class="p-4">
                <div class="row g-3">
                    {{-- Endereço --}}
                    <div class="col-md-6">
                        <x-admin.lang-field name="cafe_direccion" label="Endereço"
                            :pt="$tpt?->cafe_direccion ?? $cafeBistro->direccion"
                            :es="$tes?->cafe_direccion" :en="$ten?->cafe_direccion"
                            placeholder="Shopping Dubai, Pedro Juan Caballero — Paraguai" />
                    </div>

                    {{-- Telefone --}}
                    <div class="col-md-6">
                        <label class="sax-form-label">Telefone</label>
                        <input type="text" name="telefono" class="form-control sax-input"
                               value="{{ old('telefono', $cafeBistro->telefono) }}"
                               placeholder="+595 993 011502">
                    </div>

                    {{-- Instagram --}}
                    <div class="col-md-6">
                        <label class="sax-form-label">Instagram</label>
                        <input type="url" name="instagram_url" class="form-control sax-input"
                               value="{{ old('instagram_url', $cafeBistro->instagram_url) }}"
                               placeholder="https://instagram.com/seu_perfil">
                    </div>

                    {{-- Facebook --}}
                    <div class="col-md-6">
                        <label class="sax-form-label">Facebook</label>
                        <input type="url" name="facebook_url" class="form-control sax-input"
                               value="{{ old('facebook_url', $cafeBistro->facebook_url) }}"
                               placeholder="https://facebook.com/sua_pagina">
                    </div>

                    {{-- Mapa: iframe google maps --}}
                    <div class="col-12">
                        <label class="sax-form-label">Google Maps (Iframe)</label>
                        <textarea name="mapa_embed" class="form-control sax-input x-small" rows="3"
                                  placeholder="Cole aqui o código <iframe> do Google Maps">{{ old('mapa_embed', $cafeBistro->mapa_embed) }}</textarea>
                        <p class="x-small text-muted mt-2 mb-0">
                            <i class="fas fa-info-circle me-1"></i>
                            Cole o código &lt;iframe&gt; completo gerado em "Partilhar → Incorporar um mapa".
                        </p>
                    </div>
                </div>
            </div>
        </div>

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
