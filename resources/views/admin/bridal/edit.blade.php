@extends('layout.admin')

@section('content')
@php
    $services     = is_array($bridal->services)     ? $bridal->services     : (json_decode($bridal->services, true)     ?? []);
    $promos       = is_array($bridal->promos)       ? $bridal->promos       : (json_decode($bridal->promos, true)       ?? []);
    $brands       = is_array($bridal->brands)       ? $bridal->brands       : (json_decode($bridal->brands, true)       ?? []);
    $testimonials = is_array($bridal->testimonials) ? $bridal->testimonials : (json_decode($bridal->testimonials, true) ?? []);

    // Rellenar slots fijos
    for ($i = count($services);     $i < 4; $i++) $services[]     = ['image' => '', 'title' => '', 'description' => ''];
    for ($i = count($promos);       $i < 3; $i++) $promos[]       = ['image' => '', 'title' => '', 'subtitle' => '', 'button' => '', 'link' => ''];
    for ($i = count($testimonials); $i < 4; $i++) $testimonials[] = ['foto' => '', 'quote' => '', 'author' => '', 'ubicacion' => ''];
@endphp

<div class="sax-admin-container py-2 bg-white-soft">
<form id="formBridal" action="{{ route('admin.bridal.update', $bridal->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- ── HEADER STICKY ────────────────────────────────────────── --}}
    <div class="sticky-header px-4 py-3 mb-5 bg-white border-bottom shadow-sm d-flex justify-content-between align-items-center">
        <div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar SAX Bridal</h2>
            <div class="sax-divider-gold"></div>
            <span class="text-muted x-small">
                Última actualización: {{ $bridal->updated_at ? $bridal->updated_at->format('d/m/Y H:i') : 'Nunca' }}
            </span>
        </div>
        <div class="d-flex gap-2 align-items-center">
            <a href="{{ route('admin.bridal.index') }}" class="btn-back-minimal d-none d-md-flex align-items-center">
                <i class="fas fa-times me-1"></i> CANCELAR
            </a>
            <button type="submit" class="btn btn-dark-gold rounded-pill px-4 fw-bold shadow-sm">
                <i class="fas fa-check-circle me-2"></i> GUARDAR CAMBIOS
            </button>
        </div>
    </div>

    {{-- ── ERRORES / FLASH ──────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-modern alert-success mb-4 mx-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger mx-4 mb-4 rounded-3 border-0">
            <p class="fw-bold small mb-2"><i class="fas fa-exclamation-triangle me-1"></i> Hay errores en el formulario:</p>
            <ul class="mb-0 small">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    <div class="px-3 d-flex flex-column gap-4">

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 01. HERO                                                   --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-image"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">01 — Hero</p>
                    <p class="x-small text-muted mb-0">Imagen principal y textos de bienvenida</p>
                </div>
            </div>
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <label class="sax-form-label">Título Principal</label>
                        <input type="text" name="hero_title" class="form-control sax-input"
                               value="{{ old('hero_title', $bridal->hero_title) }}">
                    </div>
                    <div class="mb-3">
                        <label class="sax-form-label">Subtítulo</label>
                        <input type="text" name="hero_subtitle" class="form-control sax-input"
                               value="{{ old('hero_subtitle', $bridal->hero_subtitle) }}">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Descripción</label>
                        <textarea name="hero_description" class="form-control sax-input" rows="4">{{ old('hero_description', $bridal->hero_description) }}</textarea>
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <label class="sax-form-label d-block mb-2">Imagen Hero</label>
                    <div class="img-preview-box mb-3 rounded-3 overflow-hidden border" style="height:180px;">
                        <img id="prev-hero"
                             src="{{ $bridal->hero_image ? asset('storage/'.$bridal->hero_image) : 'https://placehold.co/600x400/121212/D4AF37?text=Hero' }}"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="upload-zone">
                        <input type="file" name="hero_image" class="upload-input img-trigger" data-prev="prev-hero" accept="image/*">
                        <i class="fas fa-cloud-upload-alt mb-2 opacity-25 fa-lg"></i>
                        <p class="x-small fw-bold m-0">Haga clic o arrastre una imagen</p>
                        <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. 8MB</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 02. MARCAS (Brands — dinámico)                            --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-tags"></i></div>
                <div class="flex-grow-1">
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">02 — Marcas</p>
                    <p class="x-small text-muted mb-0">Ticker de logos (cantidad libre)</p>
                </div>
                <button type="button" id="btn-add-brand" class="btn btn-sm btn-outline-dark rounded-pill x-small fw-bold px-3">
                    <i class="fas fa-plus me-1"></i> AÑADIR MARCA
                </button>
            </div>
            <div class="p-4">
                <div id="brands-container" class="row g-3">
                    @forelse($brands as $i => $brand)
                        <div class="col-md-3 brand-item">
                            <div class="border rounded-3 p-3 bg-light position-relative">
                                <button type="button" class="btn-remove-brand position-absolute top-0 end-0 m-2 btn btn-sm btn-light border rounded-circle">
                                    <i class="fas fa-times x-small"></i>
                                </button>
                                <div class="brand-logo-preview mb-2 rounded-2 overflow-hidden border" style="height:60px; background:#fff;">
                                    <img class="brand-prev w-100 h-100 object-fit-contain p-1"
                                         src="{{ !empty($brand['logo_imagen']) ? asset('storage/'.$brand['logo_imagen']) : 'https://placehold.co/160x60/f8fafc/ccc?text=Logo' }}">
                                </div>
                                <div class="upload-zone py-2 mb-2">
                                    <input type="file" name="brands_items[{{ $i }}][logo_imagen]"
                                           class="upload-input brand-logo-trigger" accept="image/*">
                                    <p class="x-small text-muted m-0">Subir logo</p>
                                </div>
                                <input type="hidden" name="brands_items[{{ $i }}][logo_path]"
                                       value="{{ $brand['logo_imagen'] ?? '' }}">
                                <label class="sax-form-label">Nombre</label>
                                <input type="text" name="brands_items[{{ $i }}][nombre]"
                                       class="form-control sax-input"
                                       value="{{ old("brands_items.$i.nombre", $brand['nombre'] ?? '') }}">
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-3 text-muted x-small" id="brands-empty">
                            No hay marcas. Haga clic en "Añadir Marca".
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 03. PROMOS (3 slots fijos)                                --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-percent"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">03 — Promos</p>
                    <p class="x-small text-muted mb-0">Carrusel promocional (máx. 3 ítems)</p>
                </div>
            </div>
            <div class="p-4">
                <div class="row g-3">
                    @for($i = 0; $i < 3; $i++)
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100" style="background:#fafafa;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge-num">{{ $i + 1 }}</span>
                                <span class="x-small fw-bold text-uppercase text-muted">Promo {{ $i + 1 }}</span>
                            </div>

                            {{-- Imagen --}}
                            <div class="img-preview-box mb-2 rounded-2 overflow-hidden border" style="height:120px;">
                                <img id="prev-promo-{{ $i }}"
                                     src="{{ !empty($promos[$i]['image']) ? asset('storage/'.$promos[$i]['image']) : 'https://placehold.co/400x200/121212/D4AF37?text=Promo+'.($i+1) }}"
                                     class="w-100 h-100 object-fit-cover">
                            </div>
                            <div class="upload-zone py-2 mb-3">
                                <input type="file" name="promos_items[{{ $i }}][image]"
                                       class="upload-input img-trigger" data-prev="prev-promo-{{ $i }}" accept="image/*">
                                <p class="x-small text-muted m-0">Cambiar imagen</p>
                            </div>
                            <input type="hidden" name="promos_items[{{ $i }}][image_path]"
                                   value="{{ $promos[$i]['image'] ?? '' }}">

                            <div class="mb-2">
                                <label class="sax-form-label">Título</label>
                                <input type="text" name="promos_items[{{ $i }}][title]"
                                       class="form-control sax-input"
                                       value="{{ old("promos_items.$i.title", $promos[$i]['title'] ?? '') }}">
                            </div>
                            <div class="mb-2">
                                <label class="sax-form-label">Subtítulo</label>
                                <input type="text" name="promos_items[{{ $i }}][subtitle]"
                                       class="form-control sax-input"
                                       value="{{ old("promos_items.$i.subtitle", $promos[$i]['subtitle'] ?? '') }}">
                            </div>
                            <div class="mb-2">
                                <label class="sax-form-label">Texto Botón</label>
                                <input type="text" name="promos_items[{{ $i }}][button]"
                                       class="form-control sax-input"
                                       value="{{ old("promos_items.$i.button", $promos[$i]['button'] ?? '') }}">
                            </div>
                            <div class="mb-0">
                                <label class="sax-form-label">Link</label>
                                <input type="text" name="promos_items[{{ $i }}][link]"
                                       class="form-control sax-input"
                                       value="{{ old("promos_items.$i.link", $promos[$i]['link'] ?? '') }}">
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 04. SERVICIOS (4 slots fijos)                             --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-concierge-bell"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">04 — Servicios</p>
                    <p class="x-small text-muted mb-0">Label, título, CTA y 4 ítems de servicio</p>
                </div>
            </div>
            <div class="p-4">
                {{-- Metadatos de sección --}}
                <div class="row g-3 mb-4 pb-4 border-bottom">
                    <div class="col-md-3">
                        <label class="sax-form-label">Label</label>
                        <input type="text" name="services_label" class="form-control sax-input"
                               value="{{ old('services_label', $bridal->services_label) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="sax-form-label">Título de Sección</label>
                        <input type="text" name="services_title" class="form-control sax-input"
                               value="{{ old('services_title', $bridal->services_title) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="sax-form-label">Texto CTA</label>
                        <input type="text" name="services_cta_text" class="form-control sax-input"
                               value="{{ old('services_cta_text', $bridal->services_cta_text) }}">
                    </div>
                    <div class="col-md-3">
                        <label class="sax-form-label">Link CTA</label>
                        <input type="text" name="services_cta_link" class="form-control sax-input"
                               value="{{ old('services_cta_link', $bridal->services_cta_link) }}">
                    </div>
                </div>

                {{-- 4 slots de servicio --}}
                <div class="row g-3">
                    @for($i = 0; $i < 4; $i++)
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100" style="background:#fafafa;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge-num">{{ $i + 1 }}</span>
                                <span class="x-small fw-bold text-uppercase text-muted">Servicio {{ $i + 1 }}</span>
                            </div>
                            <div class="img-preview-box mb-2 rounded-2 overflow-hidden border" style="height:100px;">
                                <img id="prev-svc-{{ $i }}"
                                     src="{{ !empty($services[$i]['image']) ? asset('storage/'.$services[$i]['image']) : 'https://placehold.co/300x200/fdf8e6/D4AF37?text=Svc+'.($i+1) }}"
                                     class="w-100 h-100 object-fit-cover">
                            </div>
                            <div class="upload-zone py-2 mb-3">
                                <input type="file" name="services_items[{{ $i }}][image]"
                                       class="upload-input img-trigger" data-prev="prev-svc-{{ $i }}" accept="image/*">
                                <p class="x-small text-muted m-0">Cambiar imagen</p>
                            </div>
                            <input type="hidden" name="services_items[{{ $i }}][image_path]"
                                   value="{{ $services[$i]['image'] ?? '' }}">
                            <div class="mb-2">
                                <label class="sax-form-label">Título</label>
                                <input type="text" name="services_items[{{ $i }}][title]"
                                       class="form-control sax-input"
                                       value="{{ old("services_items.$i.title", $services[$i]['title'] ?? '') }}">
                            </div>
                            <div class="mb-0">
                                <label class="sax-form-label">Descripción</label>
                                <textarea name="services_items[{{ $i }}][description]"
                                          class="form-control sax-input" rows="2">{{ old("services_items.$i.description", $services[$i]['description'] ?? '') }}</textarea>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 05. PALACE BANNER                                         --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-landmark"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">05 — Palace Banner</p>
                    <p class="x-small text-muted mb-0">Banner destacado del salón</p>
                </div>
            </div>
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="sax-form-label">Subtítulo (encima del título)</label>
                            <input type="text" name="palace_subtitle" class="form-control sax-input"
                                   value="{{ old('palace_subtitle', $bridal->palace_subtitle) }}">
                        </div>
                        <div class="col-md-6">
                            <label class="sax-form-label">Título</label>
                            <input type="text" name="palace_title" class="form-control sax-input"
                                   value="{{ old('palace_title', $bridal->palace_title) }}">
                        </div>
                        <div class="col-12">
                            <label class="sax-form-label">Descripción</label>
                            <textarea name="palace_description" class="form-control sax-input" rows="3">{{ old('palace_description', $bridal->palace_description) }}</textarea>
                        </div>
                        <div class="col-12">
                            <label class="sax-form-label">Link</label>
                            <input type="text" name="palace_link" class="form-control sax-input"
                                   value="{{ old('palace_link', $bridal->palace_link) }}"
                                   placeholder="https://...">
                        </div>
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <label class="sax-form-label d-block mb-2">Imagen Palace</label>
                    <div class="img-preview-box mb-3 rounded-3 overflow-hidden border" style="height:180px;">
                        <img id="prev-palace"
                             src="{{ $bridal->palace_image ? asset('storage/'.$bridal->palace_image) : 'https://placehold.co/600x400/121212/D4AF37?text=Palace' }}"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="upload-zone">
                        <input type="file" name="palace_image" class="upload-input img-trigger" data-prev="prev-palace" accept="image/*">
                        <i class="fas fa-cloud-upload-alt mb-2 opacity-25 fa-lg"></i>
                        <p class="x-small fw-bold m-0">Haga clic o arrastre una imagen</p>
                        <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. 4MB</p>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 06. TESTIMONIOS (4 slots fijos)                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-quote-left"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">06 — Testimonios</p>
                    <p class="x-small text-muted mb-0">Label, título y 4 reseñas de clientes</p>
                </div>
            </div>
            <div class="p-4">
                {{-- Metadatos --}}
                <div class="row g-3 mb-4 pb-4 border-bottom">
                    <div class="col-md-6">
                        <label class="sax-form-label">Label</label>
                        <input type="text" name="testimonials_label" class="form-control sax-input"
                               value="{{ old('testimonials_label', $bridal->testimonials_label) }}">
                    </div>
                    <div class="col-md-6">
                        <label class="sax-form-label">Título de Sección</label>
                        <input type="text" name="testimonials_title" class="form-control sax-input"
                               value="{{ old('testimonials_title', $bridal->testimonials_title) }}">
                    </div>
                </div>

                {{-- 4 testimonios --}}
                <div class="row g-3">
                    @for($i = 0; $i < 4; $i++)
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100" style="background:#fafafa;">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <div class="testimonial-avatar-upload position-relative">
                                    <img id="prev-test-{{ $i }}"
                                         src="{{ !empty($testimonials[$i]['foto']) ? asset('storage/'.$testimonials[$i]['foto']) : 'https://placehold.co/100x100/eef2f7/ccc?text=Foto' }}"
                                         class="avatar-preview rounded-circle border">
                                    <input type="file" name="testimonials_items[{{ $i }}][foto]"
                                           class="upload-input img-trigger avatar-trigger"
                                           data-prev="prev-test-{{ $i }}" accept="image/*">
                                    <div class="avatar-overlay rounded-circle">
                                        <i class="fas fa-camera x-small text-white"></i>
                                    </div>
                                </div>
                                <div>
                                    <span class="badge-num">{{ $i + 1 }}</span>
                                    <p class="x-small text-muted mb-0 mt-1">Foto del cliente</p>
                                </div>
                            </div>
                            <input type="hidden" name="testimonials_items[{{ $i }}][foto_path]"
                                   value="{{ $testimonials[$i]['foto'] ?? '' }}">
                            <div class="mb-2">
                                <label class="sax-form-label">Nombre del Autor</label>
                                <input type="text" name="testimonials_items[{{ $i }}][author]"
                                       class="form-control sax-input"
                                       value="{{ old("testimonials_items.$i.author", $testimonials[$i]['author'] ?? '') }}">
                            </div>
                            <div class="mb-2">
                                <label class="sax-form-label">Ubicación</label>
                                <input type="text" name="testimonials_items[{{ $i }}][ubicacion]"
                                       class="form-control sax-input"
                                       value="{{ old("testimonials_items.$i.ubicacion", $testimonials[$i]['ubicacion'] ?? '') }}"
                                       placeholder="Ej: Asunción, PY">
                            </div>
                            <div class="mb-0">
                                <label class="sax-form-label">Testimonio</label>
                                <textarea name="testimonials_items[{{ $i }}][quote]"
                                          class="form-control sax-input" rows="3"
                                          maxlength="200"
                                          oninput="this.nextElementSibling.textContent = this.value.length + '/200'"
                                          placeholder="Texto del testimonio...">{{ old("testimonials_items.$i.quote", $testimonials[$i]['quote'] ?? '') }}</textarea>
                                <small class="text-muted float-end mt-1">{{ strlen(old("testimonials_items.$i.quote", $testimonials[$i]['quote'] ?? '')) }}/200</small>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 07. INSTAGRAM + ESTADO DE PÁGINA                         --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-gold"><i class="fab fa-instagram"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">07 — Instagram CTA</p>
                            <p class="x-small text-muted mb-0">Handle de Instagram para el botón de redes</p>
                        </div>
                    </div>
                    <label class="sax-form-label">@ Handle de Instagram</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border x-small fw-bold text-muted">@</span>
                        <input type="text" name="social_instagram" class="form-control sax-input"
                               value="{{ old('social_instagram', ltrim($bridal->social_instagram ?? '', '@')) }}"
                               placeholder="sax.bridal.py">
                    </div>
                </div>
            </div>
            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-gold"><i class="fas fa-toggle-on"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">Configuración General</p>
                            <p class="x-small text-muted mb-0">Estado y nombre interno de la página</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="sax-form-label">Nombre Interno</label>
                        <input type="text" name="title" class="form-control sax-input"
                               value="{{ old('title', $bridal->title) }}" placeholder="SAX Bridal">
                    </div>
                    <div class="d-flex align-items-center gap-3 mt-3">
                        <div class="form-check form-switch m-0">
                            <input class="form-check-input" type="checkbox" role="switch"
                                   name="is_active" id="isActive" value="1"
                                   {{ old('is_active', $bridal->is_active) ? 'checked' : '' }}
                                   style="width:2.5em;height:1.3em;">
                        </div>
                        <label for="isActive" class="sax-form-label m-0">Página Activa</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 08. SUCURSALES                                            --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <div class="section-header px-4 pt-4 pb-3 border-bottom d-flex align-items-center gap-3">
                <div class="icon-circle-gold"><i class="fas fa-map-marker-alt"></i></div>
                <div>
                    <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">08 — Sucursales</p>
                    <p class="x-small text-muted mb-0">Información de las dos tiendas</p>
                </div>
            </div>
            <div class="row g-0">
                {{-- Asunción --}}
                <div class="col-lg-6 p-4" style="border-right: 1px solid #eef2f7;">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="badge-gold-soft text-uppercase">Asunción</span>
                        <i class="fas fa-map-pin text-gold x-small"></i>
                    </div>

                    <div class="img-preview-box mb-2 rounded-3 overflow-hidden border" style="height:140px;">
                        <img id="prev-asuncion"
                             src="{{ $bridal->branch_asuncion_image ? asset('storage/'.$bridal->branch_asuncion_image) : 'https://placehold.co/600x300/121212/D4AF37?text=Asunci%C3%B3n' }}"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="upload-zone py-2 mb-4">
                        <input type="file" name="branch_asuncion_image" class="upload-input img-trigger"
                               data-prev="prev-asuncion" accept="image/*">
                        <p class="x-small text-muted m-0">Cambiar imagen de sucursal</p>
                    </div>

                    <div class="mb-3">
                        <label class="sax-form-label">Nombre de la Sucursal</label>
                        <input type="text" name="branch_asuncion_name" class="form-control sax-input"
                               value="{{ old('branch_asuncion_name', $bridal->branch_asuncion_name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="sax-form-label">Dirección</label>
                        <input type="text" name="branch_asuncion_address" class="form-control sax-input"
                               value="{{ old('branch_asuncion_address', $bridal->branch_asuncion_address) }}">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Teléfono</label>
                        <input type="text" name="branch_asuncion_phone" class="form-control sax-input"
                               value="{{ old('branch_asuncion_phone', $bridal->branch_asuncion_phone) }}">
                    </div>
                </div>

                {{-- Ciudad del Este --}}
                <div class="col-lg-6 p-4">
                    <div class="d-flex align-items-center gap-2 mb-4">
                        <span class="badge-gold-soft text-uppercase">Ciudad del Este</span>
                        <i class="fas fa-map-pin text-gold x-small"></i>
                    </div>

                    <div class="img-preview-box mb-2 rounded-3 overflow-hidden border" style="height:140px;">
                        <img id="prev-cde"
                             src="{{ $bridal->branch_cde_image ? asset('storage/'.$bridal->branch_cde_image) : 'https://placehold.co/600x300/121212/D4AF37?text=CDE' }}"
                             class="w-100 h-100 object-fit-cover">
                    </div>
                    <div class="upload-zone py-2 mb-4">
                        <input type="file" name="branch_cde_image" class="upload-input img-trigger"
                               data-prev="prev-cde" accept="image/*">
                        <p class="x-small text-muted m-0">Cambiar imagen de sucursal</p>
                    </div>

                    <div class="mb-3">
                        <label class="sax-form-label">Nombre de la Sucursal</label>
                        <input type="text" name="branch_cde_name" class="form-control sax-input"
                               value="{{ old('branch_cde_name', $bridal->branch_cde_name) }}">
                    </div>
                    <div class="mb-3">
                        <label class="sax-form-label">Dirección</label>
                        <input type="text" name="branch_cde_address" class="form-control sax-input"
                               value="{{ old('branch_cde_address', $bridal->branch_cde_address) }}">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Teléfono</label>
                        <input type="text" name="branch_cde_phone" class="form-control sax-input"
                               value="{{ old('branch_cde_phone', $bridal->branch_cde_phone) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- SEO                                                        --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm p-4 bg-dark text-white border-0">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="icon-circle-gold"><i class="fas fa-search-plus"></i></div>
                <h6 class="fw-bold text-gold text-uppercase letter-spacing-2 mb-0">SEO & Metadatos</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="sax-form-label text-white opacity-75">Meta Title</label>
                    <input type="text" name="meta_title" class="form-control sax-input sax-input-dark"
                           value="{{ old('meta_title', $bridal->meta_title) }}"
                           placeholder="SAX Bridal — Tu día ideal">
                </div>
                <div class="col-md-6">
                    <label class="sax-form-label text-white opacity-75">Meta Description</label>
                    <textarea name="meta_description" class="form-control sax-input sax-input-dark" rows="3"
                              placeholder="Descripción para Google (máx. 160 caracteres)...">{{ old('meta_description', $bridal->meta_description) }}</textarea>
                </div>
            </div>
        </div>

        {{-- ── FOOTER ACCIONES ─────────────────────────────────────── --}}
        <div class="d-flex justify-content-between align-items-center pt-3 pb-4 border-top">
            <a href="{{ route('admin.bridal.index') }}" class="btn-back-minimal">
                <i class="fas fa-arrow-left me-1"></i> VOLVER AL DASHBOARD
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bridal.index') }}" class="btn btn-light rounded-pill px-4 x-small fw-bold text-muted">
                    DESCARTAR
                </a>
                <button type="submit" class="btn btn-dark-gold rounded-pill px-5 fw-bold">
                    <i class="fas fa-check-circle me-2"></i> GUARDAR CAMBIOS
                </button>
            </div>
        </div>

    </div>
</form>

{{-- MOBILE: botón fijo inferior --}}
<div class="d-md-none fixed-bottom p-3 bg-white border-top shadow-lg" style="z-index:1030;">
    <button form="formBridal" type="submit" class="btn btn-dark-gold w-100 py-3 rounded-pill fw-bold">
        <i class="fas fa-check-circle me-2"></i> GUARDAR CAMBIOS
    </button>
</div>
</div>

<style>
    :root { --gold: #D4AF37; --gold-light: #fdf8e6; --sax-dark: #121212; }

    /* ── Base ───────────────────────────────────────────── */
    .bg-white-soft  { background-color: #f8fafc; }
    .text-gold      { color: var(--gold) !important; }
    .x-small        { font-size: 0.68rem; }
    .letter-spacing-2 { letter-spacing: 2px; }
    .letter-spacing-1 { letter-spacing: 1px; }

    /* ── Título / Divider ───────────────────────────────── */
    .sax-title        { font-size: 1.4rem; font-weight: 900; color: var(--sax-dark); }
    .sax-divider-gold { width: 45px; height: 4px; background: var(--gold); margin: 8px 0; border-radius: 2px; }

    /* ── Sticky Header ──────────────────────────────────── */
    .sticky-header { position: sticky; top: 0; z-index: 1020; }

    /* ── Premium Card ───────────────────────────────────── */
    .sax-premium-card { background: #fff; border-radius: 20px; border: 1px solid #eef2f7; }

    /* ── Card Section Header ────────────────────────────── */
    .section-header { background: #fafafa; border-radius: 20px 20px 0 0; }

    /* ── Icon Circle ────────────────────────────────────── */
    .icon-circle-gold {
        width: 38px; height: 38px; border-radius: 50%;
        background: var(--gold-light); color: var(--gold);
        display: flex; align-items: center; justify-content: center;
        font-size: .9rem; flex-shrink: 0;
    }

    /* ── Labels / Inputs ────────────────────────────────── */
    .sax-form-label {
        font-size: 0.7rem; font-weight: 700;
        color: #64748b; margin-bottom: 5px;
        text-transform: uppercase; letter-spacing: .5px;
        display: block;
    }
    .sax-input {
        border: 1px solid #e2e8f0; border-radius: 12px;
        font-size: 0.85rem; background: #fff;
    }
    .sax-input:focus {
        border-color: var(--gold);
        box-shadow: 0 0 0 3px rgba(212,175,55,.12);
    }
    .sax-input-dark {
        background: rgba(255,255,255,.07) !important;
        border-color: rgba(255,255,255,.15) !important;
        color: #fff !important;
    }
    .sax-input-dark::placeholder { color: rgba(255,255,255,.35); }
    .sax-input-dark:focus {
        border-color: var(--gold) !important;
        box-shadow: 0 0 0 3px rgba(212,175,55,.2) !important;
    }

    /* ── Upload Zone ────────────────────────────────────── */
    .upload-zone {
        border: 2px dashed #e2e8f0; border-radius: 14px;
        padding: 14px; text-align: center;
        position: relative; cursor: pointer;
        transition: border-color .2s, background .2s;
        display: flex; flex-direction: column;
        align-items: center; justify-content: center;
        min-height: 70px;
    }
    .upload-zone:hover { border-color: var(--gold); background: var(--gold-light); }
    .upload-input {
        position: absolute; inset: 0;
        opacity: 0; cursor: pointer; z-index: 5;
    }

    /* ── Image Preview ──────────────────────────────────── */
    .img-preview-box { background: #f1f5f9; }

    /* ── Badges ─────────────────────────────────────────── */
    .badge-gold-soft {
        background: var(--gold-light); color: var(--gold);
        padding: 4px 10px; border-radius: 6px;
        font-weight: 800; font-size: 0.65rem;
    }
    .badge-num {
        display: inline-flex; align-items: center; justify-content: center;
        width: 22px; height: 22px; border-radius: 50%;
        background: var(--sax-dark); color: var(--gold);
        font-size: .65rem; font-weight: 800;
        flex-shrink: 0;
    }

    /* ── Testimonials Avatar ────────────────────────────── */
    .testimonial-avatar-upload { display: inline-block; position: relative; cursor: pointer; }
    .avatar-preview { width: 56px; height: 56px; object-fit: cover; display: block; }
    .avatar-trigger { position: absolute; inset: 0; opacity: 0; z-index: 5; cursor: pointer; }
    .avatar-overlay {
        position: absolute; inset: 0;
        background: rgba(0,0,0,.45);
        display: flex; align-items: center; justify-content: center;
        opacity: 0; transition: opacity .2s;
    }
    .testimonial-avatar-upload:hover .avatar-overlay { opacity: 1; }

    /* ── Edit Button ────────────────────────────────────── */
    .btn-dark-gold {
        background: var(--sax-dark); color: var(--gold);
        border: none; font-size: 0.8rem; letter-spacing: 1px;
        transition: background .2s;
    }
    .btn-dark-gold:hover { background: #000; color: #fff; }

    /* ── Cancel Link ────────────────────────────────────── */
    .btn-back-minimal {
        text-decoration: none; color: #64748b;
        font-size: 0.72rem; font-weight: 700;
        text-transform: uppercase; letter-spacing: 1px;
    }
    .btn-back-minimal:hover { color: var(--sax-dark); }

    /* ── Alert ──────────────────────────────────────────── */
    .alert-modern { border-radius: 12px; border: none; }
    .alert-success.alert-modern { background: var(--sax-dark); color: var(--gold); }

    @media (max-width: 768px) {
        .sticky-header { padding: 12px 16px !important; }
    }
</style>

@push('scripts')
<script>
// ── Image preview on file select ──────────────────────────────
document.querySelectorAll('.img-trigger').forEach(function(input) {
    input.addEventListener('change', function() {
        if (!this.files || !this.files[0]) return;
        const prevId = this.dataset.prev;
        const reader = new FileReader();
        reader.onload = e => document.getElementById(prevId).src = e.target.result;
        reader.readAsDataURL(this.files[0]);
    });
});

// ── Brand logo preview (inline within brand card) ─────────────
document.addEventListener('change', function(e) {
    if (!e.target.classList.contains('brand-logo-trigger')) return;
    const input = e.target;
    if (!input.files || !input.files[0]) return;
    const img   = input.closest('.brand-item').querySelector('.brand-prev');
    const reader = new FileReader();
    reader.onload = ev => img.src = ev.target.result;
    reader.readAsDataURL(input.files[0]);
});

// ── Brands: anhadir fila  ───────────────────────────────────────────
let brandIndex = {{ count($brands) }};

document.getElementById('btn-add-brand').addEventListener('click', function() {
    const empty = document.getElementById('brands-empty');
    if (empty) empty.remove();

    const i   = brandIndex++;
    const col = document.createElement('div');
    col.className = 'col-md-3 brand-item';
    col.innerHTML = `
        <div class="border rounded-3 p-3 bg-light position-relative">
            <button type="button" class="btn-remove-brand position-absolute top-0 end-0 m-2 btn btn-sm btn-light border rounded-circle">
                <i class="fas fa-times x-small"></i>
            </button>
            <div class="brand-logo-preview mb-2 rounded-2 overflow-hidden border" style="height:60px;background:#fff;">
                <img class="brand-prev w-100 h-100 object-fit-contain p-1"
                     src="https://placehold.co/160x60/f8fafc/ccc?text=Logo">
            </div>
            <div class="upload-zone py-2 mb-2">
                <input type="file" name="brands_items[${i}][logo_imagen]"
                       class="upload-input brand-logo-trigger" accept="image/*">
                <p class="x-small text-muted m-0">Subir logo</p>
            </div>
            <input type="hidden" name="brands_items[${i}][logo_path]" value="">
            <label class="sax-form-label">Nombre</label>
            <input type="text" name="brands_items[${i}][nombre]"
                   class="form-control sax-input" placeholder="Nombre de la marca">
        </div>`;
    document.getElementById('brands-container').appendChild(col);
});

// ── Brands: remove row ────────────────────────────────────────
document.addEventListener('click', function(e) {
    if (e.target.closest('.btn-remove-brand')) {
        e.target.closest('.brand-item').remove();
    }
});
</script>
@endpush
@endsection
