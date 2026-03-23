@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-4 bg-white-soft">
    {{-- Header Estilo Dashboard SAX --}}
    <div class="dashboard-header d-flex justify-content-between align-items-center mb-5 px-4">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">SAX Palace</h2>
            <div class="sax-divider-gold"></div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
                    <li class="breadcrumb-item x-small text-uppercase active text-gold" aria-current="page">Visão Geral</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.palace.edit', $palace->id) }}" class="btn btn-dark-gold px-4 shadow-sm rounded-pill transition fw-bold">
            <i class="fas fa-pen-nib mr-2 fa-xs"></i> EDITAR CONTENIDO
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-modern alert-success slide-in-top mb-4 mx-4">
            <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row px-3 g-4">
        {{-- 01. HERO PREVIEW - FULL WIDTH --}}
        <div class="col-12 mb-2">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <div class="row g-0 h-100">
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                        <span class="badge-gold-soft mb-3 text-uppercase letter-spacing-1">Sección Principal</span>
                        <h1 class="display-5 font-weight-bold text-dark mb-3">{{ $palace->hero_titulo }}</h1>
                        <p class="text-muted lead-sm mb-4">{{ $palace->hero_descricao }}</p>
                        <div class="d-flex align-items-center">
                            <div class="status-indicator active"></div>
                            <span class="x-small fw-bold text-uppercase tracking-wider text-success">WhatsApp Business Activo</span>
                        </div>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-image-wrapper">
                            <img src="{{ $palace->hero_imagem ? asset('storage/'.$palace->hero_imagem) : 'https://placehold.co/800x600' }}" class="w-100 h-100 object-fit-cover" alt="Hero">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 02. BAR & BODEGA --}}
        <div class="col-lg-6">
            <div class="sax-premium-card p-4 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="icon-circle-gold mr-3"><i class="fas fa-glass-martini-alt"></i></div>
                    <h6 class="m-0 font-weight-bold text-uppercase letter-spacing-1">Bar & Bodega</h6>
                </div>
                <h5 class="text-dark font-weight-bold">{{ $palace->bar_titulo }}</h5>
                <p class="small text-muted mb-4">{{ Str::limit($palace->bar_descricao, 150) }}</p>
                <div class="gallery-stack-modern">
                    @for($i=1; $i<=3; $i++)
                        @php $img = "bar_imagem_$i"; @endphp
                        <div class="stack-item shadow-sm">
                            <img src="{{ asset('storage/'.$palace->$img) }}" class="rounded-lg">
                        </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- 03. GASTRONOMIA --}}
        <div class="col-lg-6">
            <div class="sax-premium-card p-4 h-100 shadow-sm bg-white">
                <div class="d-flex align-items-center mb-4 border-bottom pb-3">
                    <div class="icon-circle-gold mr-3"><i class="fas fa-utensils"></i></div>
                    <h6 class="m-0 font-weight-bold text-uppercase letter-spacing-1">Gastronomía</h6>
                </div>
                <div class="meal-timeline-premium">
                    <div class="meal-point border-warning">
                        <span class="x-small fw-bold text-warning text-uppercase">Café da Manhã</span>
                        <p class="small text-dark mb-0 mt-1">{{ $palace->gastronomia_cafe_desc }}</p>
                    </div>
                    <div class="meal-point border-primary">
                        <span class="x-small fw-bold text-primary text-uppercase">Almuerzo</span>
                        <p class="small text-dark mb-0 mt-1">{{ $palace->gastronomia_almoco_desc }}</p>
                    </div>
                    <div class="meal-point border-danger">
                        <span class="x-small fw-bold text-danger text-uppercase">Cena</span>
                        <p class="small text-dark mb-0 mt-1">{{ $palace->gastronomia_jantar_desc }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 04. GALERIA SOCIAL --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 shadow-sm bg-white">
                <div class="row align-items-center">
                    <div class="col-md-4 border-right">
                        <h6 class="font-weight-bold text-uppercase letter-spacing-1 mb-1">Eventos & Galería</h6>
                        <p class="x-small text-muted text-uppercase mb-0">{{ $palace->eventos_titulo }}</p>
                    </div>
                    <div class="col-md-8 pt-3 pt-md-0">
                        <div class="avatar-stack-premium d-flex align-items-center flex-wrap">
                            @php $fotos = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true); @endphp
                            @forelse(array_slice($fotos ?? [], 0, 10) as $foto)
                                <div class="avatar-item">
                                    <img src="{{ asset('storage/'.$foto) }}">
                                </div>
                            @empty
                                <span class="text-muted x-small italic">Sin imágenes registradas.</span>
                            @endforelse
                            @if(count($fotos ?? []) > 10)
                                <div class="avatar-item plus-count">+{{ count($fotos) - 10 }}</div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 05. NOITE ÁRABE --}}
        <div class="col-lg-7">
            <div class="sax-premium-card overflow-hidden h-100 shadow-sm border-0">
                <div class="d-flex flex-column flex-sm-row h-100">
                    <div class="arab-visual">
                        <img src="{{ asset('storage/'.$palace->tematica_imagem) }}" class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="p-4 d-flex flex-column justify-content-center bg-white">
                        <div class="d-flex justify-content-between align-items-center mb-3">
                            <span class="badge-gold-soft text-uppercase x-small">{{ $palace->tematica_tag }}</span>
                            <span class="text-gold fw-bold h5 mb-0">{{ $palace->tematica_preco }}</span>
                        </div>
                        <h4 class="font-weight-bold text-dark mb-2">{{ $palace->tematica_titulo }}</h4>
                        <p class="small text-muted lh-base mb-0">{{ $palace->tematica_descricao }}</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 06. INFO BOX (DARK) --}}
        <div class="col-lg-5">
            <div class="sax-premium-card p-4 h-100 bg-dark text-white border-0 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute" style="top: -10px; right: -10px; opacity: 0.1;">
                    <i class="fas fa-map-marked-alt fa-6x text-gold"></i>
                </div>
                <h6 class="font-weight-bold text-gold text-uppercase letter-spacing-2 mb-4">Información & Ubicación</h6>
                <div class="contact-item mb-4">
                    <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">WhatsApp de Reservas</label>
                    <div class="d-flex align-items-center">
                        <i class="fab fa-whatsapp text-success mr-2"></i>
                        <span class="fw-bold">{{ $palace->contato_whatsapp }}</span>
                    </div>
                </div>
                <div class="contact-item mb-4">
                    <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Horarios</label>
                    <div class="small lh-lg">
                        <div class="d-flex justify-content-between border-bottom border-secondary pb-1"><span>Segunda:</span> <span>{{ $palace->contato_horario_segunda }}</span></div>
                        <div class="d-flex justify-content-between border-bottom border-secondary pb-1 mt-1"><span>Ter a Sáb:</span> <span>{{ $palace->contato_horario_sabado }}</span></div>
                        <div class="d-flex justify-content-between mt-1"><span>Domingo:</span> <span>{{ $palace->contato_horario_domingo }}</span></div>
                    </div>
                </div>
                <div class="mt-auto">
                    <div class="p-3 rounded bg-white-10 text-center">
                        <i class="fas {{ $palace->contato_mapa_iframe ? 'fa-map-pin text-gold' : 'fa-exclamation-triangle text-warning' }} mr-2"></i>
                        <span class="x-small fw-bold text-uppercase">{{ $palace->contato_mapa_iframe ? 'Mapa de Google Integrado' : 'Mapa no configurado' }}</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@endsection