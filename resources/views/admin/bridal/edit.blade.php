@extends('layout.admin')

@section('content')
@php
    $services     = is_array($bridal->services)     ? $bridal->services     : (json_decode($bridal->services, true)     ?? []);
    $promos       = is_array($bridal->promos)       ? $bridal->promos       : (json_decode($bridal->promos, true)       ?? []);
    $brands       = is_array($bridal->brands)       ? $bridal->brands       : (json_decode($bridal->brands, true)       ?? []);
    $testimonials = is_array($bridal->testimonials) ? $bridal->testimonials : (json_decode($bridal->testimonials, true) ?? []);
    $locations    = is_array($bridal->locations)    ? $bridal->locations    : (json_decode($bridal->locations, true)    ?? []);

    // Rellenar slots fijos
    for ($i = count($services);     $i < 4; $i++) $services[]     = ['image' => '', 'title' => '', 'description' => ''];
    for ($i = count($promos);       $i < 3; $i++) $promos[]       = ['image' => '', 'title' => '', 'subtitle' => '', 'button' => '', 'link' => ''];
    for ($i = count($testimonials); $i < 4; $i++) $testimonials[] = ['foto' => '', 'quote' => '', 'author' => '', 'ubicacion' => ''];
@endphp

<x-admin.card>
<form id="formBridal" action="{{ route('admin.bridal.update', $bridal->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- ── HEADER STICKY ────────────────────────────────────────── --}}
    <x-admin.sticky-header
        title="Editar SAX Bridal"
        cancelRoute="{{ route('admin.bridal.index') }}"
        :updatedAt="$bridal->updated_at ? 'Última actualización: '.$bridal->updated_at->format('d/m/Y H:i') : null"
    />

    {{-- ── ERRORES / FLASH ──────────────────────────────────────── --}}
    <div class="mx-4">
        <x-admin.alert />
    </div>

    <div class="px-3 d-flex flex-column gap-4">

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 01. HERO                                                   --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-image" number="01" title="Hero" subtitle="Imagen principal y textos de bienvenida" />
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
                    <x-admin.image-upload
                        name="hero_image"
                        previewId="prev-hero"
                        :currentImage="$bridal->hero_image ? asset('storage/'.$bridal->hero_image) : null"
                        placeholder="https://placehold.co/600x400/121212/D4AF37?text=Hero"
                        label="Imagen Hero"
                    />
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 02. PROMOS (3 slots fijos)                                --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-percent" number="03" title="Promos" subtitle="Carrusel promocional (máx. 3 ítems)" />
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
                            <x-admin.image-upload
                                name="promos_items[{{ $i }}][image]"
                                previewId="prev-promo-{{ $i }}"
                                :currentImage="!empty($promos[$i]['image']) ? asset('storage/'.$promos[$i]['image']) : null"
                                placeholder="https://placehold.co/400x200/121212/D4AF37?text=Promo+{{ $i+1 }}"
                                height="120px"
                                compact
                            />
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
            <x-admin.block-header icon="fas fa-concierge-bell" number="04" title="Servicios" subtitle="Label, título, CTA y 4 ítems de servicio" />
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
                            <x-admin.image-upload
                                name="services_items[{{ $i }}][image]"
                                previewId="prev-svc-{{ $i }}"
                                :currentImage="!empty($services[$i]['image']) ? asset('storage/'.$services[$i]['image']) : null"
                                placeholder="https://placehold.co/300x200/fdf8e6/D4AF37?text=Svc+{{ $i+1 }}"
                                height="100px"
                                compact
                            />
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
            <x-admin.block-header icon="fas fa-landmark" number="05" title="Palace Banner" subtitle="Banner destacado del salón" />
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
                    <x-admin.image-upload
                        name="palace_image"
                        previewId="prev-palace"
                        :currentImage="$bridal->palace_image ? asset('storage/'.$bridal->palace_image) : null"
                        placeholder="https://placehold.co/600x400/121212/D4AF37?text=Palace"
                        label="Imagen Palace"
                        maxSize="4MB"
                    />
                </div>
            </div>
        </div>

        {{-- ══════════════════════════════════════════════════════════ --}}
        {{-- 06. TESTIMONIOS (4 slots fijos)                           --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-quote-left" number="06" title="Testimonios" subtitle="Label, título y 4 reseñas de clientes" />
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
                                <x-admin.image-upload
                                    name="testimonials_items[{{ $i }}][foto]"
                                    previewId="prev-test-{{ $i }}"
                                    :currentImage="!empty($testimonials[$i]['foto']) ? asset('storage/'.$testimonials[$i]['foto']) : null"
                                    placeholder="https://placehold.co/100x100/eef2f7/ccc?text=Foto"
                                    circular
                                />
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
        {{-- 07. SUCURSALES (dinámico)                                 --}}
        {{-- ══════════════════════════════════════════════════════════ --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-map-marker-alt" number="07" title="Sucursales" subtitle="Locales y contacto (cantidad libre)" actionLabel="AÑADIR SUCURSAL" actionId="btn-add-location" />
            <div class="p-4">
                <div id="locations-container" class="row g-3" data-loc-count="{{ count($locations) }}">
                    @forelse($locations as $i => $loc)
                        <div class="col-md-4 location-item">
                            <div class="border rounded-3 p-3 bg-light position-relative">
                                <button type="button" class="btn-remove-location position-absolute top-0 end-0 m-2 btn btn-sm btn-light border rounded-circle">
                                    <i class="fas fa-times x-small"></i>
                                </button>
                                <x-admin.image-upload
                                    name="locations_items[{{ $i }}][image]"
                                    previewId="prev-loc-{{ $i }}"
                                    :currentImage="!empty($loc['image']) ? asset('storage/'.$loc['image']) : null"
                                    placeholder="https://placehold.co/400x200/121212/D4AF37?text=Sucursal"
                                    height="120px"
                                    compact
                                />
                                <input type="hidden" name="locations_items[{{ $i }}][image_path]"
                                       value="{{ $loc['image'] ?? '' }}">
                                <div class="mb-2">
                                    <label class="sax-form-label">Nombre</label>
                                    <input type="text" name="locations_items[{{ $i }}][name]"
                                           class="form-control sax-input"
                                           value="{{ old("locations_items.$i.name", $loc['name'] ?? '') }}">
                                </div>
                                <div class="mb-2">
                                    <label class="sax-form-label">Dirección</label>
                                    <input type="text" name="locations_items[{{ $i }}][address]"
                                           class="form-control sax-input"
                                           value="{{ old("locations_items.$i.address", $loc['address'] ?? '') }}">
                                </div>
                                <div class="mb-0">
                                    <label class="sax-form-label">Teléfono (WhatsApp)</label>
                                    <input type="text" name="locations_items[{{ $i }}][phone]"
                                           class="form-control sax-input"
                                           value="{{ old("locations_items.$i.phone", '') }}"
                                           placeholder="+595 XXX XXX XXX">
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 text-center py-3 text-muted x-small" id="locations-empty">
                            No hay sucursales. Haga clic en "Añadir Sucursal".
                        </div>
                    @endforelse
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
<x-admin.mobile-submit formId="formBridal" />
</x-admin.card>

@endsection
