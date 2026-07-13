@extends('layout.admin')

@section('content')
@php
    // Busca a tradução ativa ou retorna um objeto vazio para evitar erro de null
    $locale = translation_locale();
    $translation = $institucional->translations->firstWhere('locale', $locale);

    // Fallback: se não achar a tradução, usa o que estiver na tabela pai
    $title = $translation->inst_section_one_title ?? $institucional->section_one_title;
    $content = $translation->inst_section_one_content ?? $institucional->section_one_content;
    $pilar1T = $translation->inst_text_section_one_title ?? $institucional->text_section_one_title;
    $pilar1B = $translation->inst_text_section_one_body ?? $institucional->text_section_one_body;
    $pilar2T = $translation->inst_text_section_two_title ?? $institucional->text_section_two_title;
    $pilar2B = $translation->inst_text_section_two_body ?? $institucional->text_section_two_body;
    $pilar3T = $translation->inst_text_section_three_title ?? $institucional->text_section_three_title;
    $pilar3B = $translation->inst_text_section_three_body ?? $institucional->text_section_three_body;

    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb bg-transparent p-0 mb-0">
        <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
        <li class="breadcrumb-item x-small text-uppercase active text-gold" aria-current="page">'.__('messages.visao_geral_inst_breadcrumb').'</li>
    </ol></nav>';

    // Reúne todas as imagens cadastradas, já rotuladas por origem, para uma revisão rápida sem precisar entrar na edição
    $banners = is_array($institucional->top_sliders) ? $institucional->top_sliders : (json_decode($institucional->top_sliders, true) ?: []);
    $galeria = is_array($institucional->gallery_images) ? $institucional->gallery_images : (json_decode($institucional->gallery_images, true) ?: []);

    $todasImagens = collect($banners)->map(fn($img) => ['path' => $img, 'label' => __('messages.banners_top_sec')])
        ->merge(collect($galeria)->map(fn($img) => ['path' => $img, 'label' => __('messages.galeria_fotos_sec')]));
@endphp

<x-admin.card>
    <x-admin.page-header
        title="SAX Institucional"
        :description="$breadcrumb"
        divider="sax-divider-gold">
        <x-slot:actions>
            <a href="{{ route('admin.institucional.edit', $institucional->id) }}" class="btn btn-dark-gold px-4 shadow-sm rounded-pill transition fw-bold">
                <i class="fas fa-edit me-2 fa-xs"></i> {{ __('messages.editar_institucional_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="row g-4">
        {{-- 01. SEÇÃO PRINCIPAL --}}
        <div class="col-12">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <div class="row g-0 h-100">
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                        <span class="badge-gold-soft mb-3 text-uppercase letter-spacing-1 align-self-start">{{ __('messages.historia_identidade_badge') }}</span>
                        <h1 class="display-5 font-weight-bold text-dark mb-3">{{ $title }}</h1>
                        <p class="text-muted lead-sm mb-4">{!! Str::limit(strip_tags($content), 300) !!}</p>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator active"></div>
                            <span class="x-small fw-bold text-uppercase tracking-wider text-success">{{ __('messages.conteudo_ativo_site') }}</span>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-image-wrapper">
                            <img src="{{ $institucional->section_one_image ? asset('storage/'.$institucional->section_one_image) : 'https://placehold.co/800x600' }}" class="w-100 h-100 object-fit-cover" alt="Institucional">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 02. MÉTRICAS SAX --}}
        <div class="col-lg-5">
            <div class="sax-premium-card h-100 bg-dark text-white border-0 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute" style="top: -10px; right: -10px; opacity: 0.1;">
                    <i class="fas fa-chart-line fa-6x text-gold"></i>
                </div>
                <div class="p-4">
                    <h6 class="font-weight-bold text-gold text-uppercase letter-spacing-2 mb-4">{{ __('messages.numeros_plataforma_label') }}</h6>

                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                        <div>
                            <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">{{ __('messages.marcas_internacionais_label') }}</label>
                            <span class="h3 font-weight-bold text-gold mb-0">+{{ $institucional->stat_brands_count }}</span>
                        </div>
                        <i class="fas fa-tags text-muted"></i>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                        <div>
                            <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">{{ __('messages.area_total_label') }}</label>
                            <span class="h3 font-weight-bold text-white mb-0">{{ $institucional->stat_sqm_count }}k</span>
                        </div>
                        <i class="fas fa-expand-arrows-alt text-muted"></i>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-0">
                        <div>
                            <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">{{ __('messages.colaboradores_label') }}</label>
                            <span class="h3 font-weight-bold text-white mb-0">{{ $institucional->stat_employees_count }}</span>
                        </div>
                        <i class="fas fa-users text-muted"></i>
                    </div>
                </div>
            </div>
        </div>

        {{-- 03. PILARES & VALORES --}}
        <div class="col-lg-7">
            <div class="sax-premium-card h-100 shadow-sm bg-white overflow-hidden">
                <x-admin.block-header icon="fas fa-award" :title="__('messages.pilares_valores_label')" />
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6 mb-4">
                            <h6 class="font-weight-bold text-dark mb-1">{{ $pilar1T }}</h6>
                            <p class="x-small text-muted mb-0">{{ Str::limit($pilar1B, 120) }}</p>
                        </div>
                        <div class="col-md-6 mb-4">
                            <h6 class="font-weight-bold text-dark mb-1">{{ $pilar2T }}</h6>
                            <p class="x-small text-muted mb-0">{{ Str::limit($pilar2B, 120) }}</p>
                        </div>
                        <div class="col-12">
                            <div class="p-3 rounded-lg bg-light border-left border-gold">
                                <h6 class="font-weight-bold text-dark mb-1">{{ $pilar3T }}</h6>
                                <p class="x-small text-muted mb-0">{{ Str::limit($pilar3B, 150) }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 04. EXPERIÊNCIA VIRTUAL --}}
        <div class="col-12">
            <div class="sax-premium-card shadow-sm bg-white overflow-hidden">
                <x-admin.block-header icon="fas fa-vr-cardboard" :title="__('messages.exp_virtual_cameras_label')" />
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6 col-lg-4">
                            <label class="x-small text-uppercase fw-bold text-muted d-block mb-2">{{ __('messages.tour_virtual_label') }}</label>
                            <div class="rounded overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                @if($institucional->iframe_tour_360)
                                    <div class="text-center p-3">
                                        <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                        <p class="m-0 x-small fw-bold">{{ __('messages.tour_ativo_status') }}</p>
                                    </div>
                                @else
                                    <span class="text-muted x-small italic">{{ __('messages.nao_configurado_status') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="x-small text-uppercase fw-bold text-muted d-block mb-2">{{ __('messages.camera_ponte_label') }}</label>
                            <div class="rounded overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                @if($institucional->iframe_ponte_amizade)
                                    <i class="fas fa-video text-gold fa-2x"></i>
                                @else
                                    <span class="text-muted x-small italic">{{ __('messages.nao_configurado_status') }}</span>
                                @endif
                            </div>
                        </div>
                        <div class="col-md-6 col-lg-4">
                            <label class="x-small text-uppercase fw-bold text-muted d-block mb-2">{{ __('messages.camera_centro_label') }}</label>
                            <div class="rounded overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="height: 120px;">
                                @if($institucional->iframe_centro_cde)
                                    <i class="fas fa-video text-gold fa-2x"></i>
                                @else
                                    <span class="text-muted x-small italic">{{ __('messages.nao_configurado_status') }}</span>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 05. GALERIA — revisão rápida com lightbox, sem precisar entrar na edição --}}
        <div class="col-12">
            <div class="sax-premium-card shadow-sm bg-white overflow-hidden">
                <x-admin.block-header icon="fas fa-images"
                    :title="__('messages.galeria_banners_fotos_label')"
                    :subtitle="$todasImagens->count() . ' ' . (__('messages.loaded_images_count') ?? 'imagens carregadas')" />
                <div class="p-4">
                    @if($todasImagens->isEmpty())
                        <p class="text-muted x-small italic mb-0">{{ __('messages.nao_configurado_status') }}</p>
                    @else
                        <div class="row g-2">
                            @foreach($todasImagens as $img)
                                <div class="col-6 col-md-2">
                                    <a href="{{ asset('storage/'.$img['path']) }}" data-fancybox="institucional-gallery"
                                       data-caption="{{ $img['label'] }}"
                                       class="d-block rounded-lg overflow-hidden shadow-sm position-relative admin-gallery-thumb">
                                        <img src="{{ asset('storage/'.$img['path']) }}" class="w-100 h-100 object-fit-cover transition-hover" style="height: 110px;">
                                        <span class="admin-gallery-thumb-tag">{{ $img['label'] }}</span>
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection
