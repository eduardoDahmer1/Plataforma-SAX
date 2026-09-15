@extends('layout.admin')

@section('content')
@php
    $translations = $palace->translations->keyBy('locale');
    $pt = $translations->get('pt-br');
    $es = $translations->get('es');
    $en = $translations->get('en');
    $gallery = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : [];
@endphp

<x-admin.card>
    <form action="{{ route('admin.palace.update', $palace->id) }}" method="POST" enctype="multipart/form-data" id="formPalace" class="special-page-form">
        @csrf
        @method('PUT')

        <x-admin.sticky-header
            :title="__('messages.editar_palace_titulo')"
            :cancelRoute="route('admin.palace.index')"
            divider="sax-divider-gold"
            btnClass="btn-dark-gold"
            :submitLabel="__('messages.guardar_cambios_btn')"
            :updatedAt="$palace->updated_at ? __('messages.ultima_atualizacao_label').' '.$palace->updated_at->format('d/m/Y H:i') : null" />

        <x-admin.alert />

        <x-admin.translation-guide shared="Imagens, WhatsApp, mapa e cardápio são compartilhados entre PT, ES e EN." />

        <div class="px-3 d-flex flex-column gap-4">
            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-image" number="01" title="Hero" subtitle="Título, descrição e imagem principal da página." />
                <div class="row g-0">
                    <div class="col-lg-7 p-4">
                        <div class="mb-3">
                            <x-admin.lang-field name="palace_hero_titulo" :label="__('messages.titulo_impacto_label')"
                                :pt="$pt?->palace_hero_titulo ?? $palace->hero_titulo"
                                :es="$es?->palace_hero_titulo" :en="$en?->palace_hero_titulo" />
                        </div>
                        <x-admin.lang-field name="palace_hero_descricao" :label="__('messages.desc_boas_vindas_label')" type="textarea" :rows="5"
                            :pt="$pt?->palace_hero_descricao ?? $palace->hero_descricao"
                            :es="$es?->palace_hero_descricao" :en="$en?->palace_hero_descricao" />
                    </div>
                    <div class="col-lg-5 p-4 bg-light border-start">
                        <x-admin.image-upload name="hero_imagem" previewId="preview-hero" :label="__('messages.imagem_capa_label')"
                            :currentImage="$palace->hero_imagem ? asset('storage/'.$palace->hero_imagem) : null"
                            placeholder="https://placehold.co/600x400?text=Hero" height="13rem" dimensions="1920 × 1080 px"
                            usage="Hero responsivo; mantenha o foco no centro." />
                    </div>
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-glass-martini-alt" number="02" :title="__('messages.bar_bodega_label')" subtitle="Conteúdo da apresentação do ambiente Palace." />
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-5">
                            <x-admin.lang-field name="palace_bar_titulo" :label="__('messages.titulo_bar_label')"
                                :pt="$pt?->palace_bar_titulo ?? $palace->bar_titulo"
                                :es="$es?->palace_bar_titulo" :en="$en?->palace_bar_titulo" />
                        </div>
                        <div class="col-md-7">
                            <x-admin.lang-field name="palace_bar_descricao" :label="__('messages.desc_bar_label')" type="textarea" :rows="4"
                                :pt="$pt?->palace_bar_descricao ?? $palace->bar_descricao"
                                :es="$es?->palace_bar_descricao" :en="$en?->palace_bar_descricao" />
                        </div>
                    </div>
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-utensils" number="03" :title="__('messages.gastronomia_sec')" subtitle="Título, três experiências, fotos e cardápio." />
                <div class="p-4">
                    <div class="mb-4">
                        <x-admin.lang-field name="palace_gastronomia_titulo" :label="__('messages.titulo_gastronomia_label')"
                            :pt="$pt?->palace_gastronomia_titulo ?? $palace->gastronomia_titulo"
                            :es="$es?->palace_gastronomia_titulo" :en="$en?->palace_gastronomia_titulo" />
                    </div>

                    <div class="row g-4">
                        @foreach([
                            ['field' => 'palace_gastronomia_cafe_desc', 'base' => 'gastronomia_cafe_desc', 'image' => 'bar_imagem_1', 'label' => __('messages.meal_timeline_cafe')],
                            ['field' => 'palace_gastronomia_almoco_desc', 'base' => 'gastronomia_almoco_desc', 'image' => 'bar_imagem_2', 'label' => __('messages.meal_timeline_almoco')],
                            ['field' => 'palace_gastronomia_jantar_desc', 'base' => 'gastronomia_jantar_desc', 'image' => 'bar_imagem_3', 'label' => __('messages.meal_timeline_jantar')],
                        ] as $index => $meal)
                            <div class="col-lg-4">
                                <x-admin.image-upload :name="$meal['image']" :previewId="'preview-meal-'.$index" :label="$meal['label']"
                                    :currentImage="$palace->{$meal['image']} ? asset('storage/'.$palace->{$meal['image']}) : null"
                                    :placeholder="'https://placehold.co/600x400?text='.urlencode($meal['label'])" height="10rem" compact />
                                <x-admin.lang-field :name="$meal['field']" label="Descrição" type="textarea" :rows="4"
                                    :pt="$pt?->{$meal['field']} ?? $palace->{$meal['base']}"
                                    :es="$es?->{$meal['field']}" :en="$en?->{$meal['field']}" />
                            </div>
                        @endforeach
                    </div>

                    <div class="border-top pt-4 mt-4">
                        <label class="sax-form-label d-block mb-2"><i class="fas fa-file-pdf text-danger me-1"></i> Cardápio completo (PDF)</label>
                        <input type="file" name="gastronomia_menu_pdf" class="form-control sax-input" accept="application/pdf">
                        @if($palace->gastronomia_menu_pdf)
                            <a href="{{ asset('storage/'.$palace->gastronomia_menu_pdf) }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-dark mt-2">
                                <i class="fas fa-eye me-1"></i> Ver PDF atual
                            </a>
                        @endif
                    </div>
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-calendar-alt" number="04" :title="__('messages.galeria_eventos_sec')" subtitle="Textos e galeria da seção de eventos." />
                <div class="p-4">
                    <div class="row g-3 mb-4">
                        <div class="col-md-5">
                            <x-admin.lang-field name="palace_eventos_titulo" :label="__('messages.titulo_secao_label')"
                                :pt="$pt?->palace_eventos_titulo ?? $palace->eventos_titulo"
                                :es="$es?->palace_eventos_titulo" :en="$en?->palace_eventos_titulo" />
                        </div>
                        <div class="col-md-7">
                            <x-admin.lang-field name="palace_eventos_descricao" label="Descrição dos eventos" type="textarea" :rows="4"
                                :pt="$pt?->palace_eventos_descricao ?? $palace->eventos_descricao"
                                :es="$es?->palace_eventos_descricao" :en="$en?->palace_eventos_descricao" />
                        </div>
                    </div>
                    <input type="hidden" name="eventos_galeria_managed" value="1">
                    <x-admin.gallery-field field="eventos_galeria" :images="$gallery" :max="12"
                        label="Galeria de eventos" dimensions="1200 × 1500 px"
                        hint="Até 12 imagens. Você pode remover imagens existentes ou adicionar novas." />
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-moon" number="05" :title="__('messages.noche_arabe_sec')" subtitle="Todo o conteúdo textual e visual da experiência temática." />
                <div class="row g-0">
                    <div class="col-lg-7 p-4">
                        <div class="row g-3 mb-3">
                            <div class="col-md-7">
                                <x-admin.lang-field name="palace_tematica_titulo" :label="__('messages.titulo_secao_label')"
                                    :pt="$pt?->palace_tematica_titulo ?? $palace->tematica_titulo"
                                    :es="$es?->palace_tematica_titulo" :en="$en?->palace_tematica_titulo" />
                            </div>
                            <div class="col-md-5">
                                <x-admin.lang-field name="palace_tematica_tag" :label="__('messages.etiqueta_label')"
                                    :pt="$pt?->palace_tematica_tag ?? $palace->tematica_tag"
                                    :es="$es?->palace_tematica_tag" :en="$en?->palace_tematica_tag" />
                            </div>
                        </div>
                        <div class="mb-3">
                            <x-admin.lang-field name="palace_tematica_descricao" label="Descrição" type="textarea" :rows="5"
                                :pt="$pt?->palace_tematica_descricao ?? $palace->tematica_descricao"
                                :es="$es?->palace_tematica_descricao" :en="$en?->palace_tematica_descricao" />
                        </div>
                        <x-admin.lang-field name="palace_tematica_preco" :label="__('messages.preco_label')"
                            :pt="$pt?->palace_tematica_preco ?? $palace->tematica_preco"
                            :es="$es?->palace_tematica_preco" :en="$en?->palace_tematica_preco" placeholder="Ex.: 24 U$" />
                    </div>
                    <div class="col-lg-5 p-4 bg-dark border-start">
                        <x-admin.image-upload name="tematica_imagem" previewId="preview-tematica" label="Imagem da Noite Árabe"
                            :currentImage="$palace->tematica_imagem ? asset('storage/'.$palace->tematica_imagem) : null"
                            placeholder="https://placehold.co/600x700?text=Noite+Arabe" height="20rem" dimensions="1200 × 1500 px" />
                    </div>
                </div>
            </div>

            <div class="sax-premium-card shadow-sm overflow-hidden">
                <x-admin.block-header icon="fas fa-map-marker-alt" number="06" :title="__('messages.ubicacion_mapa_sec')" subtitle="Endereço, horários, reservas e mapa." />
                <div class="p-4">
                    <div class="mb-4">
                        <x-admin.lang-field name="palace_contato_endereco" label="Endereço"
                            :pt="$pt?->palace_contato_endereco ?? $palace->contato_endereco"
                            :es="$es?->palace_contato_endereco" :en="$en?->palace_contato_endereco" />
                    </div>
                    <div class="row g-3 mb-4">
                        <div class="col-lg-4">
                            <x-admin.lang-field name="palace_contato_horario_segunda" :label="__('messages.segunda_label')"
                                :pt="$pt?->palace_contato_horario_segunda ?? $palace->contato_horario_segunda"
                                :es="$es?->palace_contato_horario_segunda" :en="$en?->palace_contato_horario_segunda" />
                        </div>
                        <div class="col-lg-4">
                            <x-admin.lang-field name="palace_contato_horario_sabado" :label="__('messages.terca_sabado_label')"
                                :pt="$pt?->palace_contato_horario_sabado ?? $palace->contato_horario_sabado"
                                :es="$es?->palace_contato_horario_sabado" :en="$en?->palace_contato_horario_sabado" />
                        </div>
                        <div class="col-lg-4">
                            <x-admin.lang-field name="palace_contato_horario_domingo" :label="__('messages.domingo_label')"
                                :pt="$pt?->palace_contato_horario_domingo ?? $palace->contato_horario_domingo"
                                :es="$es?->palace_contato_horario_domingo" :en="$en?->palace_contato_horario_domingo" />
                        </div>
                    </div>
                    <div class="row g-3">
                        <div class="col-md-5">
                            <label class="sax-form-label">WhatsApp de reservas</label>
                            <input type="text" name="contato_whatsapp" class="form-control sax-input" value="{{ old('contato_whatsapp', $palace->contato_whatsapp) }}" placeholder="+595 ...">
                        </div>
                        <div class="col-md-7">
                            <label class="sax-form-label">Google Maps (iframe)</label>
                            <textarea name="contato_mapa_iframe" class="form-control sax-input" rows="4" placeholder="Cole aqui o iframe de incorporação do Google Maps">{{ old('contato_mapa_iframe', $palace->contato_mapa_iframe) }}</textarea>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <x-admin.mobile-submit :label="__('messages.guardar_cambios_btn')" />
    </form>
</x-admin.card>
@endsection
