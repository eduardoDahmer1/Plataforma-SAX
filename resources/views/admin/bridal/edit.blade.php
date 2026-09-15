@extends('layout.admin')

@section('content')
@php
    $translations = $bridal->translations->keyBy('locale');
    $pt = $translations->get('pt-br');
    $es = $translations->get('es');
    $en = $translations->get('en');
    $decodeItems = static fn ($value) => is_array($value) ? $value : (json_decode($value ?: '[]', true) ?: []);
    $rows = ['pt-br' => $pt, 'es' => $es, 'en' => $en];
    $servicesByLocale = $promosByLocale = $testimonialsByLocale = $locationsByLocale = [];

    foreach ($rows as $locale => $row) {
        $servicesByLocale[$locale] = $decodeItems($row?->bridal_services ?? ($locale === 'pt-br' ? $bridal->services : []));
        $promosByLocale[$locale] = $decodeItems($row?->bridal_promos ?? ($locale === 'pt-br' ? $bridal->promos : []));
        $testimonialsByLocale[$locale] = $decodeItems($row?->bridal_testimonials ?? ($locale === 'pt-br' ? $bridal->testimonials : []));
        $locationsByLocale[$locale] = $decodeItems($row?->bridal_locations ?? ($locale === 'pt-br' ? $bridal->locations : []));
        for ($i = count($servicesByLocale[$locale]); $i < 4; $i++) $servicesByLocale[$locale][] = [];
        for ($i = count($promosByLocale[$locale]); $i < 3; $i++) $promosByLocale[$locale][] = [];
        for ($i = count($testimonialsByLocale[$locale]); $i < 4; $i++) $testimonialsByLocale[$locale][] = [];
    }

    $locationCount = max(array_map('count', $locationsByLocale));
    foreach ($locationsByLocale as &$localizedLocations) {
        for ($i = count($localizedLocations); $i < $locationCount; $i++) $localizedLocations[] = [];
    }
    unset($localizedLocations);

    // Brands NO es traducible (va a la tabla principal, igual en todos los idiomas: son nombres propios)
    $brands = is_array($bridal->brands) ? $bridal->brands : (json_decode($bridal->brands, true) ?? []);

@endphp

<x-admin.card>
<form id="formBridal" class="special-page-form" action="{{ route('admin.bridal.update', $bridal->id) }}" method="POST" enctype="multipart/form-data">
    @csrf
    @method('PUT')

    {{-- ── HEADER STICKY ────────────────────────────────────────── --}}
    <x-admin.sticky-header
        :title="__('messages.edit_sax_bridal')"
        cancelRoute="{{ route('admin.bridal.index') }}"
        :updatedAt="$bridal->updated_at ? __('messages.last_update_label').': '.$bridal->updated_at->format('d/m/Y H:i') : null"
    />

    {{-- ── ERRORES / FLASH ──────────────────────────────────────── --}}
    <div class="mx-3">
        <x-admin.alert />
    </div>

    <x-admin.translation-guide shared="Imagens, links, Instagram, status e telefones são compartilhados entre PT, ES e EN." />

    <div class="px-3 d-flex flex-column gap-4">

        {{-- 01. HERO                                                   --}}
        <div class="sax-premium-card shadow-sm overflow-hidden">
            <x-admin.block-header icon="fas fa-image" number="01" title="Hero" :subtitle="__('messages.identificacao_hero_sec')" />
            <div class="row g-0">
                <div class="col-lg-7 p-4">
                    <div class="mb-3">
                        <x-admin.lang-field name="bridal_hero_title" :label="__('messages.main_title_label')"
                            :pt="$pt?->bridal_hero_title ?? $bridal->hero_title"
                            :es="$es?->bridal_hero_title" :en="$en?->bridal_hero_title" />
                    </div>
                    <div class="mb-3">
                        <x-admin.lang-field name="bridal_hero_subtitle" :label="__('messages.subtitle_label')"
                            :pt="$pt?->bridal_hero_subtitle ?? $bridal->hero_subtitle"
                            :es="$es?->bridal_hero_subtitle" :en="$en?->bridal_hero_subtitle" />
                    </div>
                    <div class="mb-0">
                        <x-admin.lang-field name="bridal_hero_description" :label="__('messages.description_label')" type="textarea" :rows="4"
                            :pt="$pt?->bridal_hero_description ?? $bridal->hero_description"
                            :es="$es?->bridal_hero_description" :en="$en?->bridal_hero_description" />
                    </div>
                </div>
                <div class="col-lg-5 p-4 bg-light border-start">
                    <x-admin.image-upload
                        name="hero_image"
                        previewId="prev-hero"
                        :currentImage="$bridal->hero_image ? asset('storage/'.$bridal->hero_image) : null"
                        placeholder="https://placehold.co/600x400/121212/D4AF37?text=Hero"
                        :label="__('messages.hero_image_label')"
                        dimensions="1920 × 1080 px"
                        usage="Hero responsivo; mantenha o foco no centro."
                    />
                </div>
            </div>
        </div>

        {{-- 02. PROMOS (3 slots fijos)                                --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-percent" number="02" title="Promos" :subtitle="__('messages.promotional_carousel_max_items')" />
            <div class="p-4">
                <div class="row g-3">
                    @for($i = 0; $i < 3; $i++)
                    @php
                        $promoImage = $promosByLocale['pt-br'][$i]['image']
                            ?? $promosByLocale['es'][$i]['image']
                            ?? $promosByLocale['en'][$i]['image']
                            ?? '';
                    @endphp
                    <div class="col-md-4">
                        <div class="border rounded-3 p-3 h-100" style="background:#fafafa;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge-num">{{ $i + 1 }}</span>
                                <span class="x-small fw-bold text-uppercase text-muted">{{ __('messages.promo_item_label') }} {{ $i + 1 }}</span>
                            </div>

                            {{-- Imagen --}}
                            <x-admin.image-upload
                                name="promos_items[{{ $i }}][image]"
                                previewId="prev-promo-{{ $i }}"
                                :currentImage="$promoImage ? asset('storage/'.$promoImage) : null"
                                placeholder="https://placehold.co/400x200/121212/D4AF37?text=Promo+{{ $i+1 }}"
                                height="120px"
                                compact
                            />
                            <input type="hidden" name="promos_items[{{ $i }}][image_path]"
                                   value="{{ $promoImage }}">

                            <div class="mb-2">
                                <x-admin.lang-field :name="'bridal_promos]['.$i.'][title'" :label="__('messages.main_title_label')"
                                    :pt="$promosByLocale['pt-br'][$i]['title'] ?? ''" :es="$promosByLocale['es'][$i]['title'] ?? ''" :en="$promosByLocale['en'][$i]['title'] ?? ''" />
                            </div>
                            <div class="mb-2">
                                <x-admin.lang-field :name="'bridal_promos]['.$i.'][subtitle'" :label="__('messages.subtitle_label')"
                                    :pt="$promosByLocale['pt-br'][$i]['subtitle'] ?? ''" :es="$promosByLocale['es'][$i]['subtitle'] ?? ''" :en="$promosByLocale['en'][$i]['subtitle'] ?? ''" />
                            </div>
                            <div class="mb-2">
                                <x-admin.lang-field :name="'bridal_promos]['.$i.'][button'" :label="__('messages.button_text_label')"
                                    :pt="$promosByLocale['pt-br'][$i]['button'] ?? ''" :es="$promosByLocale['es'][$i]['button'] ?? ''" :en="$promosByLocale['en'][$i]['button'] ?? ''" />
                            </div>
                            <div class="mb-0">
                                <label class="sax-form-label">{{ __('messages.link_label') }}</label>
                                <input type="text" name="promos_items[{{ $i }}][link]"
                                       class="form-control sax-input"
                                       value="{{ old("promos_items.$i.link", $promosByLocale['pt-br'][$i]['link'] ?? $promosByLocale['es'][$i]['link'] ?? $promosByLocale['en'][$i]['link'] ?? '') }}">
                                <small class="text-muted x-small">Link compartilhado entre os idiomas.</small>
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- 03. SERVICIOS (4 slots fijos)                             --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-concierge-bell" number="03" :title="__('messages.services')" :subtitle="__('messages.services_editor_subtitle')" />
            <div class="p-4">
                {{-- Metadatos de sección --}}
                <div class="row g-3 mb-4 pb-4 border-bottom">
                    <div class="col-md-3">
                        <x-admin.lang-field name="bridal_services_label" :label="__('messages.label_label')"
                            :pt="$pt?->bridal_services_label ?? $bridal->services_label" :es="$es?->bridal_services_label" :en="$en?->bridal_services_label" />
                    </div>
                    <div class="col-md-3">
                        <x-admin.lang-field name="bridal_services_title" :label="__('messages.section_title_label')"
                            :pt="$pt?->bridal_services_title ?? $bridal->services_title" :es="$es?->bridal_services_title" :en="$en?->bridal_services_title" />
                    </div>
                    <div class="col-md-3">
                        <x-admin.lang-field name="bridal_services_cta_text" :label="__('messages.cta_text_label')"
                            :pt="$pt?->bridal_services_cta_text ?? $bridal->services_cta_text" :es="$es?->bridal_services_cta_text" :en="$en?->bridal_services_cta_text" />
                    </div>
                    <div class="col-md-3">
                        <label class="sax-form-label">{{ __('messages.cta_link_label') }}</label>
                        <input type="text" name="services_cta_link" class="form-control sax-input"
                               value="{{ old('services_cta_link', $bridal->services_cta_link) }}">
                    </div>
                </div>

                {{-- 4 slots de servicio --}}
                <div class="row g-3">
                    @for($i = 0; $i < 4; $i++)
                    @php
                        $serviceImage = $servicesByLocale['pt-br'][$i]['image']
                            ?? $servicesByLocale['es'][$i]['image']
                            ?? $servicesByLocale['en'][$i]['image']
                            ?? '';
                    @endphp
                    <div class="col-md-3">
                        <div class="border rounded-3 p-3 h-100" style="background:#fafafa;">
                            <div class="d-flex align-items-center gap-2 mb-3">
                                <span class="badge-num">{{ $i + 1 }}</span>
                                <span class="x-small fw-bold text-uppercase text-muted">{{ __('messages.service_item_label') }} {{ $i + 1 }}</span>
                            </div>
                            <x-admin.image-upload
                                name="services_items[{{ $i }}][image]"
                                previewId="prev-svc-{{ $i }}"
                                :currentImage="$serviceImage ? asset('storage/'.$serviceImage) : null"
                                placeholder="https://placehold.co/300x200/fdf8e6/D4AF37?text=Svc+{{ $i+1 }}"
                                height="100px"
                                compact
                            />
                            <input type="hidden" name="services_items[{{ $i }}][image_path]"
                                   value="{{ $serviceImage }}">
                            <div class="mb-2">
                                <x-admin.lang-field :name="'bridal_services]['.$i.'][title'" :label="__('messages.main_title_label')"
                                    :pt="$servicesByLocale['pt-br'][$i]['title'] ?? ''" :es="$servicesByLocale['es'][$i]['title'] ?? ''" :en="$servicesByLocale['en'][$i]['title'] ?? ''" />
                            </div>
                            <div class="mb-0">
                                <x-admin.lang-field :name="'bridal_services]['.$i.'][description'" :label="__('messages.description_label')" type="textarea" :rows="3"
                                    :pt="$servicesByLocale['pt-br'][$i]['description'] ?? ''" :es="$servicesByLocale['es'][$i]['description'] ?? ''" :en="$servicesByLocale['en'][$i]['description'] ?? ''" />
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- 04. TESTIMONIOS (4 slots fijos)                           --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-quote-left" number="04" :title="__('messages.testimonials')" :subtitle="__('messages.testimonials_editor_subtitle')" />
            <div class="p-4">
                {{-- Metadatos --}}
                <div class="row g-3 mb-4 pb-4 border-bottom">
                    <div class="col-md-6">
                        <x-admin.lang-field name="bridal_testimonials_label" :label="__('messages.label_label')"
                            :pt="$pt?->bridal_testimonials_label ?? $bridal->testimonials_label" :es="$es?->bridal_testimonials_label" :en="$en?->bridal_testimonials_label" />
                    </div>
                    <div class="col-md-6">
                        <x-admin.lang-field name="bridal_testimonials_title" :label="__('messages.section_title_label')"
                            :pt="$pt?->bridal_testimonials_title ?? $bridal->testimonials_title" :es="$es?->bridal_testimonials_title" :en="$en?->bridal_testimonials_title" />
                    </div>
                </div>

                {{-- 4 testimonios --}}
                <div class="row g-3">
                    @for($i = 0; $i < 4; $i++)
                    @php
                        $testimonialPhoto = $testimonialsByLocale['pt-br'][$i]['foto']
                            ?? $testimonialsByLocale['es'][$i]['foto']
                            ?? $testimonialsByLocale['en'][$i]['foto']
                            ?? '';
                    @endphp
                    <div class="col-md-6">
                        <div class="border rounded-3 p-3 h-100" style="background:#fafafa;">
                            <div class="d-flex align-items-center gap-3 mb-3">
                                <x-admin.image-upload
                                    name="testimonials_items[{{ $i }}][foto]"
                                    previewId="prev-test-{{ $i }}"
                                    :currentImage="$testimonialPhoto ? asset('storage/'.$testimonialPhoto) : null"
                                    placeholder="https://placehold.co/100x100/eef2f7/ccc?text=Foto"
                                    circular
                                />
                                <div>
                                    <span class="badge-num">{{ $i + 1 }}</span>
                                    <p class="x-small text-muted mb-0 mt-1">{{ __('messages.client_photo_label') }}</p>
                                </div>
                            </div>
                            <input type="hidden" name="testimonials_items[{{ $i }}][foto_path]"
                                   value="{{ $testimonialPhoto }}">
                            <div class="mb-2">
                                <x-admin.lang-field :name="'bridal_testimonials]['.$i.'][author'" :label="__('messages.author_name_label')"
                                    :pt="$testimonialsByLocale['pt-br'][$i]['author'] ?? ''" :es="$testimonialsByLocale['es'][$i]['author'] ?? ''" :en="$testimonialsByLocale['en'][$i]['author'] ?? ''" />
                            </div>
                            <div class="mb-2">
                                <x-admin.lang-field :name="'bridal_testimonials]['.$i.'][ubicacion'" :label="__('messages.location_label')"
                                    :pt="$testimonialsByLocale['pt-br'][$i]['ubicacion'] ?? ''" :es="$testimonialsByLocale['es'][$i]['ubicacion'] ?? ''" :en="$testimonialsByLocale['en'][$i]['ubicacion'] ?? ''"
                                    :placeholder="__('messages.location_placeholder_example')" />
                            </div>
                            <div class="mb-0">
                                <x-admin.lang-field :name="'bridal_testimonials]['.$i.'][quote'" :label="__('messages.testimonial_label')" type="textarea" :rows="3" :maxlength="200"
                                    :pt="$testimonialsByLocale['pt-br'][$i]['quote'] ?? ''" :es="$testimonialsByLocale['es'][$i]['quote'] ?? ''" :en="$testimonialsByLocale['en'][$i]['quote'] ?? ''"
                                    :placeholder="__('messages.testimonial_text_placeholder')" />
                            </div>
                        </div>
                    </div>
                    @endfor
                </div>
            </div>
        </div>

        {{-- 05. INSTAGRAM + ESTADO DE PÁGINA                         --}}
        <div class="row g-4">
            <div class="col-lg-6">
                <div class="sax-premium-card shadow-sm p-4 h-100">
                    <div class="d-flex align-items-center gap-3 mb-4">
                        <div class="icon-circle-gold"><i class="fab fa-instagram"></i></div>
                        <div>
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">05 — Instagram CTA</p>
                            <p class="x-small text-muted mb-0">{{ __('messages.instagram_handle_desc') }}</p>
                        </div>
                    </div>
                    <label class="sax-form-label">{{ __('messages.instagram_handle_label') }}</label>
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
                            <p class="fw-bold text-uppercase letter-spacing-1 small mb-0">06 — {{ __('messages.general_settings_title') }}</p>
                            <p class="x-small text-muted mb-0">{{ __('messages.page_status_internal_name_desc') }}</p>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="sax-form-label">{{ __('messages.internal_name_label') }}</label>
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
                        <label for="isActive" class="sax-form-label m-0">{{ __('messages.active_page_label') }}</label>
                    </div>
                </div>
            </div>
        </div>

        {{-- 07. SUCURSALES (dinámico)                                 --}}
        <div class="sax-premium-card shadow-sm">
            <x-admin.block-header icon="fas fa-map-marker-alt" number="07" title="Sucursales" :subtitle="__('messages.locations_free_quantity')" :actionLabel="__('messages.add_location_btn')" actionId="btn-add-location" />
            <div class="p-4">
                <div id="locations-container" class="row g-3" data-loc-count="{{ $locationCount }}">
                    @for($i = 0; $i < $locationCount; $i++)
                        @php
                            $locationImage = $locationsByLocale['pt-br'][$i]['image']
                                ?? $locationsByLocale['es'][$i]['image']
                                ?? $locationsByLocale['en'][$i]['image']
                                ?? '';
                            $locationPhone = $locationsByLocale['pt-br'][$i]['whatsapp_url']
                                ?? $locationsByLocale['es'][$i]['whatsapp_url']
                                ?? $locationsByLocale['en'][$i]['whatsapp_url']
                                ?? '';
                            $locationPhone = preg_replace('/^https:\/\/wa\.me\//', '', $locationPhone);
                        @endphp
                        <div class="col-md-4 location-item">
                            <div class="border rounded-3 p-3 bg-light position-relative">
                                <button type="button" class="btn-remove-location position-absolute top-0 end-0 m-2 btn btn-sm btn-light border rounded-circle">
                                    <i class="fas fa-times x-small"></i>
                                </button>
                                <x-admin.image-upload
                                    name="locations_items[{{ $i }}][image]"
                                    previewId="prev-loc-{{ $i }}"
                                    :currentImage="$locationImage ? asset('storage/'.$locationImage) : null"
                                    :placeholder="'https://placehold.co/400x200/121212/D4AF37?text='.__('messages.location_placeholder_text')"
                                    height="120px"
                                    compact
                                />
                                <input type="hidden" name="locations_items[{{ $i }}][image_path]"
                                       value="{{ $locationImage }}">
                                <div class="mb-2">
                                    <x-admin.lang-field :name="'bridal_locations]['.$i.'][name'" :label="__('messages.name_label')"
                                        :pt="$locationsByLocale['pt-br'][$i]['name'] ?? ''" :es="$locationsByLocale['es'][$i]['name'] ?? ''" :en="$locationsByLocale['en'][$i]['name'] ?? ''" />
                                </div>
                                <div class="mb-2">
                                    <x-admin.lang-field :name="'bridal_locations]['.$i.'][address'" :label="__('messages.address_label')"
                                        :pt="$locationsByLocale['pt-br'][$i]['address'] ?? ''" :es="$locationsByLocale['es'][$i]['address'] ?? ''" :en="$locationsByLocale['en'][$i]['address'] ?? ''" />
                                </div>
                                <div class="mb-0">
                                    <label class="sax-form-label">{{ __('messages.whatsapp_phone_label') }}</label>
                                    <input type="text" name="locations_items[{{ $i }}][phone]"
                                           class="form-control sax-input"
                                           value="{{ old("locations_items.$i.phone", $locationPhone) }}"
                                           placeholder="+595 XXX XXX XXX">
                                    <small class="text-muted x-small">Compartilhado entre os idiomas.</small>
                                </div>
                            </div>
                        </div>
                    @endfor
                    @if($locationCount === 0)
                        <div class="col-12 text-center py-3 text-muted x-small" id="locations-empty">
                            {{ __('messages.no_locations_add_prompt') }}
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- SEO                                                        --}}
        <div class="sax-premium-card special-page-seo shadow-sm p-4">
            <div class="d-flex align-items-center gap-3 mb-4">
                <div class="icon-circle-gold"><i class="fas fa-search-plus"></i></div>
                <h6 class="fw-bold text-gold text-uppercase letter-spacing-2 mb-0">{{ __('messages.seo_metadata') }}</h6>
            </div>
            <div class="row g-3">
                <div class="col-md-6">
                    <x-admin.lang-field name="bridal_meta_title" :label="__('messages.meta_title_label')"
                        :pt="$pt?->bridal_meta_title ?? $bridal->meta_title" :es="$es?->bridal_meta_title" :en="$en?->bridal_meta_title"
                        :placeholder="__('messages.meta_title_placeholder_bridal')" :maxlength="255" />
                </div>
                <div class="col-md-6">
                    <x-admin.lang-field name="bridal_meta_description" :label="__('messages.meta_description_label')" type="textarea" :rows="3"
                        :pt="$pt?->bridal_meta_description ?? $bridal->meta_description" :es="$es?->bridal_meta_description" :en="$en?->bridal_meta_description"
                        :placeholder="__('messages.meta_description_google_placeholder')" />
                </div>
            </div>
        </div>

        {{-- ── FOOTER ACCIONES ─────────────────────────────────────── --}}
        <div class="special-page-form__footer d-flex justify-content-between align-items-center pt-3 pb-4 border-top">
            <a href="{{ route('admin.bridal.index') }}" class="btn-back-minimal">
                <i class="fas fa-arrow-left me-1"></i> {{ __('messages.back_to_dashboard_btn') }}
            </a>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.bridal.index') }}" class="btn special-page-form__discard px-4 x-small fw-bold">
                    {{ __('messages.discard_btn') }}
                </a>
                <button type="submit" class="btn btn-dark-gold special-page-form__save px-5 fw-bold">
                    <i class="fas fa-check-circle me-2"></i> {{ __('messages.save_changes_btn') }}
                </button>
            </div>
        </div>

    </div>
</form>

{{-- MOBILE: botón fijo inferior --}}
<x-admin.mobile-submit formId="formBridal" />
</x-admin.card>

@endsection
