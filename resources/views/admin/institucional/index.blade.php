@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-4 bg-white-soft">
    {{-- Header Estilo Dashboard SAX --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5 px-4">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">SAX Institucional</h2>
            <div class="sax-divider-gold"></div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
                    <li class="breadcrumb-item x-small text-uppercase active text-gold" aria-current="page">Visão Geral Institucional</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.institucional.edit', $institucional->id) }}" class="btn btn-dark-gold px-4 shadow-sm rounded-pill transition fw-bold">
            <i class="fas fa-edit mr-2 fa-xs"></i> EDITAR INSTITUCIONAL
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-modern alert-success slide-in-top mb-4 mx-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row px-3 g-4">
        {{-- 01. SEÇÃO PRINCIPAL (SOBRE NOSOTROS) --}}
        <div class="col-12 mb-2">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <div class="row g-0 h-100">
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                        <span class="badge-gold-soft mb-3 text-uppercase letter-spacing-1">História & Identidade</span>
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

        {{-- 02. MÉTRICAS SAX (Marcas, m2, etc) --}}
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

        {{-- 03. BLOCOS DE TEXTO (PILAR DE QUALIDADE) --}}
        <div class="col-lg-7">
            <div class="sax-premium-card p-4 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="icon-circle-gold mr-3"><i class="fas fa-award"></i></div>
                    <h6 class="m-0 font-weight-bold text-uppercase letter-spacing-1">Pilares & Valores</h6>
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

        {{-- 04. LOGOS DAS MARCAS (STACK) --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 shadow-sm bg-white">
                <div class="row align-items-center">
                    <div class="col-md-4 border-right">
                        <h6 class="font-weight-bold text-uppercase letter-spacing-1 mb-1">Portfólio de Marcas</h6>
                        <p class="x-small text-muted text-uppercase mb-0">Logos exibidos no rodapé/marcas</p>
                    </div>
                    <div class="col-md-8 pt-3 pt-md-0">
                        <div class="avatar-stack-premium d-flex align-items-center flex-wrap">
                            @php $logos = is_array($institucional->brand_logos) ? $institucional->brand_logos : json_decode($institucional->brand_logos, true); @endphp
                            @forelse(array_slice($logos ?? [], 0, 12) as $logo)
                                <div class="avatar-item border-gold-subtle" style="background: #fff; padding: 5px;">
                                    <img src="{{ asset('storage/'.$logo) }}" style="object-fit: contain;">
                                </div>
                            @empty
                                <span class="text-muted x-small italic">Sin logos registrados.</span>
                            @endforelse
                            @if(count($logos ?? []) > 12)
                                <div class="avatar-item plus-count">+{{ count($logos) - 12 }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 05. TOP SLIDERS & GALERIA --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 shadow-sm bg-white">
                <h6 class="font-weight-bold text-uppercase letter-spacing-1 mb-4">
                    <i class="fas fa-images text-gold mr-2"></i> Galeria de Banners & Fotos
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
</div>

<style>
    /* Reaproveitando seu sistema de identidade SAX Palace */
    :root {
        --gold: #D4AF37;
        --gold-light: #fdf8e6;
        --sax-dark: #121212;
    }

    .bg-white-soft { background-color: #f8fafc; }
    .text-gold { color: var(--gold) !important; }
    .border-gold { border-color: var(--gold) !important; }
    .border-gold-subtle { border: 1px solid rgba(212, 175, 55, 0.3) !important; }

    .sax-title { font-size: 1.6rem; font-weight: 900; color: var(--sax-dark); }
    .sax-divider-gold { width: 50px; height: 4px; background: var(--gold); margin: 10px 0; border-radius: 2px; }
    .letter-spacing-2 { letter-spacing: 2px; }
    .letter-spacing-1 { letter-spacing: 1px; }

    .sax-premium-card { border-radius: 24px; border: 1px solid #eef2f7; transition: all 0.3s; }
    .sax-premium-card:hover { transform: translateY(-5px); box-shadow: 0 15px 30px rgba(0,0,0,0.05) !important; }

    .icon-circle-gold {
        width: 40px; height: 40px; border-radius: 50%;
        background: var(--gold-light); color: var(--gold);
        display: flex; align-items: center; justify-content: center; font-size: 1rem;
    }

    .hero-image-wrapper { height: 100%; min-height: 350px; }
    .badge-gold-soft { background: var(--gold-light); color: var(--gold); padding: 5px 12px; border-radius: 8px; font-weight: 800; font-size: 0.65rem; }

    /* Avatar Stack para Logos */
    .avatar-stack-premium .avatar-item {
        width: 55px; height: 55px; border-radius: 50%;
        border: 2px solid #f8fafc; margin-left: -15px;
        overflow: hidden; background: #fff;
        display: flex; align-items: center; justify-content: center;
        box-shadow: 0 4px 10px rgba(0,0,0,0.05);
    }
    .avatar-stack-premium .avatar-item:first-child { margin-left: 0; }
    .avatar-stack-premium img { max-width: 80%; max-height: 80%; }
    .avatar-item.plus-count { background: var(--gold); color: #fff; font-size: 0.75rem; font-weight: 800; }

    .btn-dark-gold { background: var(--sax-dark); color: var(--gold); border: none; letter-spacing: 1px; font-size: 0.75rem; }
    .btn-dark-gold:hover { background: #000; color: #fff; transform: scale(1.02); }

    .status-indicator { width: 10px; height: 10px; border-radius: 50%; margin-right: 10px; }
    .status-indicator.active { background: #10b981; box-shadow: 0 0 0 4px rgba(16, 185, 129, 0.2); }

    .transition-hover:hover { transform: scale(1.05); transition: 0.4s; }
    .x-small { font-size: 0.65rem; }
    .lead-sm { font-size: 0.95rem; line-height: 1.7; }

    @media (max-width: 768px) {
        .hero-image-wrapper { height: 250px; }
        .avatar-stack-premium { justify-content: center; margin-top: 15px; }
    }
</style>
@endsection