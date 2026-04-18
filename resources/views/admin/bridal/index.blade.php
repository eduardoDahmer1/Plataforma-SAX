@extends('layout.admin')

@section('content')
@php
    $promos       = is_array($bridal->promos)       ? $bridal->promos       : (json_decode($bridal->promos, true) ?? []);
    $services     = is_array($bridal->services)     ? $bridal->services     : (json_decode($bridal->services, true) ?? []);
    $testimonials = is_array($bridal->testimonials) ? $bridal->testimonials : (json_decode($bridal->testimonials, true) ?? []);
    $locations    = is_array($bridal->locations)    ? $bridal->locations    : (json_decode($bridal->locations, true)    ?? []);

    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb bg-transparent p-0 mb-0">
        <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
        <li class="breadcrumb-item x-small text-uppercase active text-gold" aria-current="page">' . __('messages.visao_geral_label') . '</li>
    </ol></nav>';
@endphp

<x-admin.card>
    <x-admin.page-header
        title="SAX Bridal"
        :description="$breadcrumb"
        divider="sax-divider-gold">
        <x-slot:actions>
            <a href="{{ route('admin.bridal.edit', $bridal->id) }}" class="btn btn-dark-gold px-4 shadow-sm rounded-pill fw-bold">
                <i class="fas fa-pen-nib me-2 fa-xs"></i> {{__('messages.editar_conteudo_btn')}}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    {{-- ── FLASH ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="alert alert-sax-success alert-dismissible fade show mb-4">
            <i class="fas fa-check-circle me-2"></i> {{ session('success') }}
        </div>
    @endif

    <div class="row g-4">

        {{-- 01. HERO --}}
        <div class="col-12">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <x-admin.block-header icon="fas fa-image" number="01" title="Hero" :subtitle="__('messages.welcome_main_image_texts')" />
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

        {{-- 02. PROMOS --}}
        <div class="col-12">
            <div class="sax-premium-card p-0 shadow-sm bg-white">
                <x-admin.block-header icon="fas fa-percent" number="02" title="Promos" :subtitle="__('messages.featured_carousel')" />
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
                            <p class="x-small text-muted mb-0">{{ __('messages.nao_configurado_status') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 04. SERVICES --}}
        <div class="col-12">
            <div class="sax-premium-card p-0 shadow-sm bg-white">
                <x-admin.block-header icon="fas fa-concierge-bell" number="04" :title="__('messages.services')" :subtitle="$bridal->services_label ?: __('messages.nao_configurado_status')" />
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

        {{-- 06. TESTIMONIALS --}}
        <div class="col-12">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100">
                <x-admin.block-header icon="fas fa-quote-left" number="06" :title="__('messages.testimonials')" :subtitle="$bridal->testimonials_label ?: __('messages.nao_configurado_status')" />
                <div class="p-4">
                    @if(count($testimonials) > 0)
                        <div class="row g-2">
                            @foreach($testimonials as $t)
                                <div class="col-6">
                                    <div class="testimonial-mini p-4">
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
                                            "{{ $t['quote'] ?? '' }}"
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

        {{-- 07. INSTAGRAM | 07. SUCURSALES --}}
        <div class="col-lg-4">
            <div class="sax-premium-card p-0 shadow-sm h-100 instagram-card">
                <x-admin.block-header icon="fab fa-instagram" number="07" title="Instagram" :subtitle="__('messages.social_media_cta')" />
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

        <div class="col-lg-8">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100 overflow-hidden">
                <x-admin.block-header icon="fas fa-map-marker-alt" number="07" title="Sucursales" :subtitle="__('messages.locations_contacts')" />
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
                            {{ count($locations) }} {{ __('messages.locations_configured_count') }}
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

        {{-- SEO & METADATOS --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 bg-dark text-white border-0 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute" style="top:-10px;right:-10px;opacity:.05;">
                    <i class="fas fa-search fa-8x text-gold"></i>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-gold"><i class="fas fa-search-plus"></i></div>
                        <h6 class="fw-bold text-gold text-uppercase letter-spacing-2 mb-0">{{ __('messages.seo_metadata') }}</h6>
                    </div>
                    <span class="ms-auto badge bg-light text-dark x-small">Google Preview</span>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Meta Title</label>
                        <span class="fw-bold">{{ $bridal->meta_title ?? '— Não configurado' }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">Meta Description</label>
                        <span class="small opacity-75">
                            {{ $bridal->meta_description ? Str::limit($bridal->meta_description, 160) : '— Não configurado' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</x-admin.card>

@endsection
