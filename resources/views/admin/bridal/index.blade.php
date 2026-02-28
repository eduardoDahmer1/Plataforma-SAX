@extends('layout.admin')

@section('content')
@php
    $promos       = is_array($bridal->promos)       ? $bridal->promos       : (json_decode($bridal->promos, true) ?? []);
    $services     = is_array($bridal->services)     ? $bridal->services     : (json_decode($bridal->services, true) ?? []);
    $testimonials = is_array($bridal->testimonials) ? $bridal->testimonials : (json_decode($bridal->testimonials, true) ?? []);
    $locations    = is_array($bridal->locations)    ? $bridal->locations    : (json_decode($bridal->locations, true)    ?? []);

    $sections = [
        'hero'         => !empty($bridal->hero_title),
        'promos'       => count($promos) > 0,
        'services'     => count($services) > 0,
        'palace'       => !empty($bridal->palace_title),
        'testimonials' => count($testimonials) > 0,
        'instagram'    => !empty($bridal->social_instagram),
        'locations'    => count($locations) > 0,
    ];
    $completedCount = count(array_filter($sections));

    // Macro: badge de estado de sección
    $badge = fn($done) => $done
        ? '<span class="badge-section-done"><i class="fas fa-check-circle me-1"></i>Configurado</span>'
        : '<span class="badge-section-empty"><i class="far fa-circle me-1"></i>Vacío</span>';
@endphp

<div class="sax-admin-container py-4 bg-white-soft">

    {{-- ── HEADER ──────────────────────────────────────────────────── --}}
    <div class="d-flex justify-content-between align-items-center mb-4 px-4">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">SAX Bridal</h2>
            <div class="sax-divider-gold"></div>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb bg-transparent p-0 mb-0">
                    <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
                    <li class="breadcrumb-item x-small text-uppercase active text-gold" aria-current="page">Visión General</li>
                </ol>
            </nav>
        </div>
        <a href="{{ route('admin.bridal.edit', $bridal->id) }}" class="btn btn-dark-gold px-4 shadow-sm rounded-pill fw-bold">
            <i class="fas fa-pen-nib me-2 fa-xs"></i> EDITAR CONTENIDO
        </a>
    </div>

    {{-- ── STATUS BAR ──────────────────────────────────────────────── --}}
    <div class="status-bar mx-4 mb-4 px-4 py-3 d-flex align-items-center gap-3 flex-wrap">
        <div class="d-flex align-items-center gap-2">
            <span class="status-dot {{ $bridal->is_active ? 'dot-success' : 'dot-danger' }}"></span>
            <span class="x-small fw-bold text-uppercase letter-spacing-1">
                {{ $bridal->is_active ? 'Página Activa' : 'Página Inactiva' }}
            </span>
        </div>
        <div class="status-divider"></div>
        <div class="x-small text-muted">
            <i class="fas fa-clock me-1 opacity-50"></i>
            Actualizado: {{ $bridal->updated_at ? $bridal->updated_at->diffForHumans() : 'Sin datos' }}
        </div>
        <div class="status-divider"></div>
        <div class="x-small text-muted">
            <i class="fas fa-layer-group me-1 opacity-50"></i>
            <span class="fw-bold text-dark">{{ $completedCount }}</span> / 7 secciones configuradas
        </div>
        <div class="ms-auto d-flex gap-1 align-items-center">
            @foreach($sections as $key => $done)
                <div class="section-pip {{ $done ? 'pip-done' : 'pip-empty' }}" title="{{ ucfirst($key) }}"></div>
            @endforeach
        </div>
    </div>

    {{-- ── FLASH ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-modern alert-success mb-4 mx-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row px-3 g-4">

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 01. HERO                                                   --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="col-12">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fas fa-image"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 01 — Hero</p>
                        <p class="x-small text-muted mb-0">Imagen principal y textos de bienvenida</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['hero']) !!}</div>
                </div>
                <div class="row g-0">
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                        <span class="badge-gold-soft mb-3 text-uppercase letter-spacing-1 d-inline-block">Hero</span>
                        <h2 class="display-6 fw-bold text-dark mb-2">{{ $bridal->hero_title ?? '—' }}</h2>
                        <h5 class="text-muted fw-normal mb-3">{{ $bridal->hero_subtitle ?? '—' }}</h5>
                        <p class="text-muted mb-0" style="font-size:.95rem;line-height:1.65;">
                            {{ $bridal->hero_description ? Str::limit($bridal->hero_description, 220) : '—' }}
                        </p>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-image-wrapper">
                            <img src="{{ $bridal->hero_image ? asset('storage/'.$bridal->hero_image) : 'https://placehold.co/800x500/121212/D4AF37?text=Hero+Bridal' }}"
                                 class="w-100 h-100 object-fit-cover" alt="Hero">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 02. BRANDS | 03. PROMOS | 04. SERVICES                    --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

        {{-- 02. PROMOS --}}
        <div class="col-12">
            <div class="sax-premium-card p-0 shadow-sm bg-white">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fas fa-percent"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 03 — Promos</p>
                        <p class="x-small text-muted mb-0">Carrusel promocional (máx. 3)</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['promos']) !!}</div>
                </div>
                <div class="p-4">
                    @if(count($promos) > 0)
                        <div class="d-flex flex-column gap-2">
                            @foreach($promos as $i => $promo)
                                <div class="promo-row d-flex align-items-center gap-3">
                                    <div class="promo-thumb flex-shrink-0">
                                        @if(!empty($promo['image']))
                                            <img src="{{ asset('storage/'.$promo['image']) }}" alt="">
                                        @else
                                            <div class="promo-thumb-empty"><i class="fas fa-image text-muted x-small"></i></div>
                                        @endif
                                    </div>
                                    <div class="overflow-hidden flex-grow-1">
                                        <p class="small fw-bold mb-0 text-truncate">{{ $promo['title'] ?? '—' }}</p>
                                        <p class="x-small text-muted mb-0 text-truncate">{{ $promo['subtitle'] ?? '' }}</p>
                                    </div>
                                    <span class="badge bg-light text-dark border x-small flex-shrink-0">#{{ $i + 1 }}</span>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state-mini">
                            <i class="fas fa-percent empty-icon d-block"></i>
                            <p class="x-small text-muted mb-0">Sin promos configuradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 04. SERVICES --}}
        <div class="col-12">
            <div class="sax-premium-card p-0 shadow-sm bg-white">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fas fa-concierge-bell"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 04 — Servicios</p>
                        <p class="x-small text-muted mb-0">{{ $bridal->services_label ?: 'Etiqueta no configurada' }}</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['services']) !!}</div>
                </div>
                <div class="p-4">
                    @if(count($services) > 0)
                        <div class="row g-2 mb-3">
                            @foreach($services as $service)
                                <div class="col-6 col-md-3">
                                    <div class="service-mini text-center p-3">
                                        @if(!empty($service['image']))
                                            <img src="{{ asset('storage/'.$service['image']) }}"
                                                 class="service-mini-img mb-2" alt="">
                                        @else
                                            <i class="fas fa-star text-gold fa-lg mb-2 d-block"></i>
                                        @endif
                                        <p class="x-small fw-bold mb-0 text-truncate">{{ $service['title'] ?? '—' }}</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        @if($bridal->services_cta_text)
                            <div class="border-top pt-3">
                                <p class="x-small text-muted mb-0">
                                    <i class="fas fa-link me-1 opacity-50"></i>
                                    CTA: <strong>{{ $bridal->services_cta_text }}</strong>
                                    @if($bridal->services_cta_link)
                                        <span class="opacity-60"> → {{ Str::limit($bridal->services_cta_link, 30) }}</span>
                                    @endif
                                </p>
                            </div>
                        @endif
                    @else
                        <div class="empty-state-mini">
                            <i class="fas fa-concierge-bell empty-icon d-block"></i>
                            <p class="x-small text-muted mb-0">Sin servicios configurados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 05. PALACE BANNER | 06. TESTIMONIALS                      --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

        {{-- 05. PALACE BANNER --}}
        <div class="col-lg-6">
            <div class="sax-premium-card p-0 shadow-sm overflow-hidden h-100">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fas fa-landmark"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 05 — Palace Banner</p>
                        <p class="x-small text-muted mb-0">Banner destacado del salón</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['palace']) !!}</div>
                </div>
                <div class="d-flex flex-column flex-sm-row" style="min-height:200px;">
                    <div class="palace-visual flex-shrink-0">
                        <img src="{{ $bridal->palace_image ? asset('storage/'.$bridal->palace_image) : 'https://placehold.co/400x300/121212/D4AF37?text=Palace' }}"
                             class="w-100 h-100 object-fit-cover" alt="Palace">
                    </div>
                    <div class="p-4 d-flex flex-column justify-content-center bg-white flex-grow-1">
                        @if($bridal->palace_subtitle)
                            <span class="badge-gold-soft text-uppercase x-small mb-2 d-inline-block">
                                {{ $bridal->palace_subtitle }}
                            </span>
                        @endif
                        <h5 class="fw-bold text-dark mb-2">{{ $bridal->palace_title ?? '—' }}</h5>
                        <p class="small text-muted lh-base mb-2">
                            {{ $bridal->palace_description ? Str::limit($bridal->palace_description, 100) : '—' }}
                        </p>
                        @if($bridal->palace_link)
                            <p class="x-small text-muted mb-0">
                                <i class="fas fa-link me-1 opacity-50"></i>{{ Str::limit($bridal->palace_link, 40) }}
                            </p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 06. TESTIMONIALS --}}
        <div class="col-lg-6">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fas fa-quote-left"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 06 — Testimonios</p>
                        <p class="x-small text-muted mb-0">{{ $bridal->testimonials_label ?: 'Etiqueta no configurada' }}</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['testimonials']) !!}</div>
                </div>
                <div class="p-4">
                    @if(count($testimonials) > 0)
                        <div class="row g-2">
                            @foreach($testimonials as $t)
                                <div class="col-6">
                                    <div class="testimonial-mini p-3">
                                        <div class="d-flex align-items-center gap-2 mb-2">
                                            @if(!empty($t['foto']))
                                                <img src="{{ asset('storage/'.$t['foto']) }}"
                                                     class="avatar-round" alt="">
                                            @else
                                                <div class="avatar-placeholder">
                                                    <i class="fas fa-user" style="font-size:.6rem;"></i>
                                                </div>
                                            @endif
                                            <div class="overflow-hidden">
                                                <p class="x-small fw-bold mb-0 text-truncate">{{ $t['author'] ?? '—' }}</p>
                                                <p class="x-small text-muted mb-0 text-truncate">{{ $t['ubicacion'] ?? '' }}</p>
                                            </div>
                                        </div>
                                        <p class="x-small text-muted fst-italic mb-0 lh-sm">
                                            "{{ Str::limit($t['quote'] ?? '', 60) }}"
                                        </p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state-mini">
                            <i class="fas fa-quote-left empty-icon d-block"></i>
                            <p class="x-small text-muted mb-0">Sin testimonios configurados</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 07. INSTAGRAM CTA | 08. SUCURSALES                        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}

        {{-- 07. INSTAGRAM --}}
        <div class="col-lg-4">
            <div class="sax-premium-card p-0 shadow-sm h-100 instagram-card">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fab fa-instagram"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 07 — Instagram</p>
                        <p class="x-small text-muted mb-0">CTA a redes sociales</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['instagram']) !!}</div>
                </div>
                <div class="p-4 d-flex align-items-center gap-3">
                    <i class="fab fa-instagram fa-2x text-gold"></i>
                    <div>
                        @if($bridal->social_instagram)
                            <p class="fw-bold mb-0">{{ $bridal->social_instagram }}</p>
                            <p class="x-small text-muted mb-0">Handle configurado</p>
                        @else
                            <p class="text-muted small mb-1">Sin configurar</p>
                            <p class="x-small text-muted mb-0">Agrega tu @ de Instagram</p>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- 07. SUCURSALES --}}
        <div class="col-lg-8">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100 overflow-hidden">
                <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                    <div class="icon-circle-gold"><i class="fas fa-map-marker-alt"></i></div>
                    <div>
                        <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Sección 07 — Sucursales</p>
                        <p class="x-small text-muted mb-0">Locales y contacto</p>
                    </div>
                    <div class="ms-auto">{!! $badge($sections['locations']) !!}</div>
                </div>
                <div class="p-4">
                    @if(count($locations) > 0)
                        <div class="row g-2">
                            @foreach($locations as $loc)
                                <div class="col-md-4">
                                    <div class="border rounded-3 overflow-hidden" style="background:#fafafa;">
                                        <div class="branch-thumb">
                                            <img src="{{ !empty($loc['image']) ? asset('storage/'.$loc['image']) : 'https://placehold.co/400x130/121212/D4AF37?text=Sucursal' }}"
                                                 class="w-100 h-100 object-fit-cover" alt="">
                                        </div>
                                        <div class="p-3">
                                            <p class="fw-bold mb-1 small">{{ $loc['name'] ?? '—' }}</p>
                                            <p class="x-small text-muted mb-1">
                                                <i class="fas fa-map-pin me-1 opacity-50"></i>{{ $loc['address'] ?? '—' }}
                                            </p>
                                            @if(!empty($loc['whatsapp_url']))
                                                <p class="x-small text-muted mb-0">
                                                    <i class="fab fa-whatsapp me-1 opacity-50"></i>WhatsApp
                                                </p>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                        <p class="x-small text-muted mb-0 mt-3">
                            <i class="fas fa-info-circle me-1 opacity-50"></i>
                            {{ count($locations) }} sucursal(es) configurada(s)
                        </p>
                    @else
                        <div class="empty-state-mini">
                            <i class="fas fa-map-marker-alt empty-icon d-block"></i>
                            <p class="x-small text-muted mb-0">Sin sucursales configuradas</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- SEO & METADATOS                                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 bg-dark text-white border-0 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute" style="top:-10px;right:-10px;opacity:.05;">
                    <i class="fas fa-search fa-8x text-gold"></i>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-gold"><i class="fas fa-search-plus"></i></div>
                        <h6 class="fw-bold text-gold text-uppercase letter-spacing-2 mb-0">SEO & Metadatos</h6>
                    </div>
                    <span class="ms-auto badge bg-light text-dark x-small">Google Preview</span>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Meta Title</label>
                        <span class="fw-bold">{{ $bridal->meta_title ?? '— No configurado' }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Meta Description</label>
                        <span class="small opacity-75">
                            {{ $bridal->meta_description ? Str::limit($bridal->meta_description, 160) : '— No configurado' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</div>

<style>
    :root {
        --gold: #D4AF37;
        --gold-light: #fdf8e6;
        --sax-dark: #121212;
    }

    /* ── Base ───────────────────────────────────────────── */
    .bg-white-soft { background-color: #f8fafc; }
    .text-gold     { color: var(--gold) !important; }
    .x-small       { font-size: 0.68rem; }
    .letter-spacing-2 { letter-spacing: 2px; }
    .letter-spacing-1 { letter-spacing: 1px; }

    /* ── Title / Divider ────────────────────────────────── */
    .sax-title       { font-size: 1.6rem; font-weight: 900; color: var(--sax-dark); }
    .sax-divider-gold{ width: 50px; height: 4px; background: var(--gold); margin: 10px 0; border-radius: 2px; }

    /* ── Premium Card ───────────────────────────────────── */
    .sax-premium-card {
        border-radius: 20px;
        border: 1px solid #eef2f7;
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .sax-premium-card:hover {
        transform: translateY(-3px);
        box-shadow: 0 10px 30px rgba(0,0,0,.08) !important;
    }

    /* ── Section Header (inside card) ───────────────────── */
    .section-header {
        background: #fafafa;
        border-radius: 20px 20px 0 0;
    }

    /* ── Icon Circle ────────────────────────────────────── */
    .icon-circle-gold {
        width: 38px; height: 38px; border-radius: 50%;
        background: var(--gold-light); color: var(--gold);
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; flex-shrink: 0;
    }

    /* ── Badges ─────────────────────────────────────────── */
    .badge-gold-soft {
        background: var(--gold-light); color: var(--gold);
        padding: 4px 10px; border-radius: 6px;
        font-weight: 800; font-size: 0.65rem;
    }
    .badge-section-done {
        background: #ecfdf5; color: #059669;
        padding: 4px 12px; border-radius: 20px;
        font-size: 0.65rem; font-weight: 700;
        white-space: nowrap;
    }
    .badge-section-empty {
        background: #f9fafb; color: #9ca3af;
        padding: 4px 12px; border-radius: 20px;
        font-size: 0.65rem; font-weight: 700;
        border: 1px solid #e5e7eb;
        white-space: nowrap;
    }

    /* ── Status Bar ─────────────────────────────────────── */
    .status-bar {
        background: #fff;
        border-radius: 14px;
        border: 1px solid #eef2f7;
        box-shadow: 0 1px 4px rgba(0,0,0,.04);
    }
    .status-divider { width: 1px; height: 18px; background: #e5e7eb; flex-shrink: 0; }
    .status-dot { width: 9px; height: 9px; border-radius: 50%; display: inline-block; flex-shrink: 0; }
    .dot-success { background: #10b981; box-shadow: 0 0 0 3px rgba(16,185,129,.18); }
    .dot-danger  { background: #ef4444; box-shadow: 0 0 0 3px rgba(239,68,68,.18); }
    .section-pip { width: 9px; height: 9px; border-radius: 50%; }
    .pip-done    { background: #10b981; }
    .pip-empty   { background: #e5e7eb; }

    /* ── Edit Button ────────────────────────────────────── */
    .btn-dark-gold {
        background: var(--sax-dark); color: var(--gold);
        border: none; letter-spacing: 1px; font-size: 0.75rem;
        transition: background .2s, transform .2s;
    }
    .btn-dark-gold:hover { background: #000; color: #fff; transform: scale(1.02); }

    /* ── Hero ───────────────────────────────────────────── */
    .hero-image-wrapper { height: 100%; min-height: 280px; }

    /* ── Brands ─────────────────────────────────────────── */
    .brand-chip { display: flex; align-items: center; }
    .brand-logo { max-height: 34px; max-width: 80px; object-fit: contain; filter: grayscale(20%); }

    /* ── Promos ─────────────────────────────────────────── */
    .promo-row {
        background: #fafafa; border-radius: 10px;
        padding: 10px 12px; border: 1px solid #f1f1f1;
    }
    .promo-thumb, .promo-thumb-empty {
        width: 48px; height: 48px;
        border-radius: 10px; overflow: hidden;
        background: #eef2f7; flex-shrink: 0;
        display: flex; align-items: center; justify-content: center;
    }
    .promo-thumb img { width: 100%; height: 100%; object-fit: cover; }

    /* ── Services ───────────────────────────────────────── */
    .service-mini {
        background: #fafafa; border-radius: 12px;
        border: 1px solid #eef2f7;
    }
    .service-mini-img {
        width: 100%; height: 80px; object-fit: cover;
        border-radius: 8px; margin: 0 auto 6px; display: block;
    }

    /* ── Palace ─────────────────────────────────────────── */
    .palace-visual { width: 42%; min-height: 200px; overflow: hidden; }

    /* ── Testimonials ───────────────────────────────────── */
    .testimonial-mini {
        background: #fafafa; border-radius: 12px;
        border: 1px solid #eef2f7; height: 100%;
    }
    .avatar-round { width: 32px; height: 32px; border-radius: 50%; object-fit: cover; flex-shrink: 0; }
    .avatar-placeholder {
        width: 32px; height: 32px; border-radius: 50%; background: #eef2f7;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0; color: #9ca3af;
    }

    /* ── Instagram ──────────────────────────────────────── */
    .instagram-card { background: linear-gradient(135deg, #fdf8e6 0%, #fff 100%); }

    /* ── Branches ───────────────────────────────────────── */
    .branch-thumb { height: 130px; overflow: hidden; }

    /* ── Empty State ────────────────────────────────────── */
    .empty-state-mini { padding: 24px 10px; text-align: center; }
    .empty-icon { font-size: 1.8rem; color: #d1d5db; margin-bottom: 10px; }

    /* ── Flash Alert ────────────────────────────────────── */
    .alert-modern     { border-radius: 12px; border: none; }
    .alert-success.alert-modern { background: var(--sax-dark); color: var(--gold); }

    /* ── Responsive ─────────────────────────────────────── */
    @media (max-width: 768px) {
        .palace-visual  { width: 100%; min-height: 150px; }
        .hero-image-wrapper { min-height: 200px; }
        .branch-thumb   { height: 100px; }
        .status-bar     { flex-direction: column; align-items: flex-start !important; gap: 10px !important; }
        .status-divider { display: none; }
    }
</style>
@endsection
