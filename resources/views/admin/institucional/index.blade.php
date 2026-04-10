@extends('layout.admin')

@section('content')
@php
    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb bg-transparent p-0 mb-0">
        <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
        <li class="breadcrumb-item x-small text-uppercase active text-gold" aria-current="page">Visão Geral Institucional</li>
    </ol></nav>';
@endphp

<x-admin.card>
    <x-admin.page-header
        title="SAX Institucional"
        :description="$breadcrumb"
        divider="sax-divider-gold">
        <x-slot:actions>
            <a href="{{ route('admin.institucional.edit', $institucional->id) }}" class="btn btn-dark-gold px-4 shadow-sm rounded-pill transition fw-bold">
                <i class="fas fa-edit me-2 fa-xs"></i> EDITAR INSTITUCIONAL
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="row g-4">
        {{-- 01. SEÇÃO PRINCIPAL (SOBRE NOSOTROS) --}}
        <div class="col-12 mb-2">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <div class="row g-0 h-100">
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                        <span class="badge-gold-soft mb-3 text-uppercase letter-spacing-1">História &amp; Identidade</span>
                        <h1 class="display-5 font-weight-bold text-dark mb-3">{{ $institucional->section_one_title }}</h1>
                        <p class="text-muted lead-sm mb-4">{{ Str::limit($institucional->section_one_content, 300) }}</p>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator active"></div>
                            <span class="x-small fw-bold text-uppercase tracking-wider text-success">Conteúdo Ativo no Site</span>
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
            <div class="sax-premium-card p-4 h-100 bg-dark text-white border-0 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute" style="top: -10px; right: -10px; opacity: 0.1;">
                    <i class="fas fa-chart-line fa-6x text-gold"></i>
                </div>
                <h6 class="font-weight-bold text-gold text-uppercase letter-spacing-2 mb-4">Números da Plataforma</h6>

                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <div>
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Marcas Internacionais</label>
                        <span class="h3 font-weight-bold text-gold mb-0">+{{ $institucional->stat_brands_count }}</span>
                    </div>
                    <i class="fas fa-tags text-muted"></i>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-4 border-bottom border-secondary pb-3">
                    <div>
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Área Total (m²)</label>
                        <span class="h3 font-weight-bold text-white mb-0">{{ $institucional->stat_sqm_count }}k</span>
                    </div>
                    <i class="fas fa-expand-arrows-alt text-muted"></i>
                </div>

                <div class="d-flex justify-content-between align-items-center mb-0">
                    <div>
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Colaboradores</label>
                        <span class="h3 font-weight-bold text-white mb-0">{{ $institucional->stat_employees_count }}</span>
                    </div>
                    <i class="fas fa-users text-muted"></i>
                </div>
            </div>
        </div>

        {{-- 03. PILARES & VALORES --}}
        <div class="col-lg-7">
            <div class="sax-premium-card p-4 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="icon-circle-gold mr-3"><i class="fas fa-award"></i></div>
                    <h6 class="m-0 font-weight-bold text-uppercase letter-spacing-1">Pilares &amp; Valores</h6>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-4">
                        <h6 class="font-weight-bold text-dark mb-1">{{ $institucional->text_section_one_title }}</h6>
                        <p class="x-small text-muted mb-0">{{ Str::limit($institucional->text_section_one_body, 120) }}</p>
                    </div>
                    <div class="col-md-6 mb-4">
                        <h6 class="font-weight-bold text-dark mb-1">{{ $institucional->text_section_two_title }}</h6>
                        <p class="x-small text-muted mb-0">{{ Str::limit($institucional->text_section_two_body, 120) }}</p>
                    </div>
                    <div class="col-12">
                        <div class="p-3 rounded-lg bg-light border-left border-gold">
                            <h6 class="font-weight-bold text-dark mb-1">{{ $institucional->text_section_three_title }}</h6>
                            <p class="x-small text-muted mb-0">{{ Str::limit($institucional->text_section_three_body, 150) }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 04. EXPERIÊNCIA VIRTUAL --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 shadow-sm bg-white">
                <h6 class="font-weight-bold text-uppercase letter-spacing-1 mb-4">
                    <i class="fas fa-vr-cardboard text-gold me-2"></i> Experiência Virtual &amp; Câmeras
                </h6>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="x-small text-uppercase fw-bold text-muted d-block mb-2">Tour Virtual 360°</label>
                        <div class="rounded overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                            @if($institucional->iframe_tour_360)
                                <div class="text-center p-3">
                                    <i class="fas fa-check-circle text-success fa-2x mb-2"></i>
                                    <p class="m-0 x-small fw-bold">Link de Tour Ativo</p>
                                </div>
                            @else
                                <span class="text-muted x-small italic">Não configurado</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="x-small text-uppercase fw-bold text-muted d-block mb-2">Ponte da Amizade</label>
                        <div class="rounded overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                            @if($institucional->iframe_ponte_amizade)
                                <i class="fas fa-video text-gold"></i>
                            @else
                                <span class="text-muted x-small italic">Offline</span>
                            @endif
                        </div>
                    </div>
                    <div class="col-md-3 mb-3">
                        <label class="x-small text-uppercase fw-bold text-muted d-block mb-2">Centro CDE</label>
                        <div class="rounded overflow-hidden border bg-light d-flex align-items-center justify-content-center" style="height: 150px;">
                            @if($institucional->iframe_centro_cde)
                                <i class="fas fa-video text-gold"></i>
                            @else
                                <span class="text-muted x-small italic">Offline</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 05. GALERIA DE BANNERS & FOTOS --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 shadow-sm bg-white">
                <h6 class="font-weight-bold text-uppercase letter-spacing-1 mb-4">
                    <i class="fas fa-images text-gold me-2"></i> Galeria de Banners &amp; Fotos
                </h6>
                <div class="row g-2">
                    @php
                        $banners = is_array($institucional->top_sliders) ? $institucional->top_sliders : json_decode($institucional->top_sliders, true);
                        $galeria = is_array($institucional->gallery_images) ? $institucional->gallery_images : json_decode($institucional->gallery_images, true);
                        $todas = array_merge($banners ?? [], $galeria ?? []);
                    @endphp
                    @foreach(array_slice($todas, 0, 6) as $img)
                        <div class="col-md-2 col-4">
                            <div class="rounded-lg overflow-hidden shadow-sm" style="height: 120px;">
                                <img src="{{ asset('storage/'.$img) }}" class="w-100 h-100 object-fit-cover transition-hover">
                            </div>
                        </div>
                    @endforeach
                    @if(count($todas) > 6)
                        <div class="col-md-2 col-4 d-flex align-items-center justify-content-center bg-light rounded-lg border">
                            <span class="fw-bold text-gold">+{{ count($todas) - 6 }} fotos</span>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-admin.card>

@endsection
