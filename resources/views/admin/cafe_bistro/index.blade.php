@extends('layout.admin')

@section('content')
@php
    $horarios       = $cafeBistro->horarios ?? [];
    $eventosTipos   = $cafeBistro->eventos_tipos ?? [];
    $eventosGaleria = $cafeBistro->eventos_galeria ?? [];

    $breadcrumb = '<nav aria-label="breadcrumb"><ol class="breadcrumb bg-transparent p-0 mb-0">
        <li class="breadcrumb-item x-small text-uppercase"><a href="#" class="text-muted">Admin</a></li>
        <li class="breadcrumb-item x-small text-uppercase active text-bistro" aria-current="page">' . __('messages.visao_geral_label') . '</li>
    </ol></nav>';
@endphp

<x-admin.card>
    <x-admin.page-header
        title="SAX Café & Bistrô"
        :description="$breadcrumb"
        divider="sax-divider-bistro">
        <x-slot:actions>
            <a href="{{ route('admin.cafe_bistro.edit', $cafeBistro->id) }}" class="btn btn-dark-bistro px-4 shadow-sm rounded-pill fw-bold">
                <i class="fas fa-pen-nib me-2 fa-xs"></i> {{ __('messages.editar_conteudo_btn') }}
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    <div class="row g-4">

        {{-- 01. HERO --}}
        <div class="col-12">
            <div class="sax-premium-card overflow-hidden border-0 shadow-sm">
                <x-admin.block-header icon="fas fa-image" theme="bistro" number="01" title="Hero" :subtitle="__('messages.welcome_main_image_texts')" />
                <div class="row g-0">
                    <div class="col-lg-7 p-4 p-md-5 d-flex flex-column justify-content-center bg-white">
                        <span class="badge-bistro-soft mb-3 text-uppercase letter-spacing-1 d-inline-block">Hero</span>
                        <h2 class="display-6 fw-bold text-dark mb-2">{{ $cafeBistro->hero_titulo ?? '—' }}</h2>
                        <h5 class="text-muted fw-normal mb-3">{{ $cafeBistro->hero_subtitulo ?? '—' }}</h5>
                    </div>
                    <div class="col-lg-5">
                        <div class="hero-image-wrapper">
                            <img src="{{ $cafeBistro->hero_imagen ? asset('storage/'.$cafeBistro->hero_imagen) : 'https://placehold.co/800x500/0f1d35/4a6fa5?text=Hero+Bistro' }}"
                                 class="w-100 h-100 object-fit-cover" alt="Hero">
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 02. SOBRE NÓS --}}
        <div class="col-lg-6">
            <div class="sax-premium-card p-0 shadow-sm overflow-hidden h-100">
                <x-admin.block-header icon="fas fa-book-open" theme="bistro" number="02" :title="__('messages.about_us_title')" :subtitle="__('messages.about_space_history_desc')" />
                <div class="d-flex flex-column flex-sm-row" style="min-height:200px;">
                    <div class="sobre-visual flex-shrink-0">
                        <img src="{{ $cafeBistro->sobre_imagen ? asset('storage/'.$cafeBistro->sobre_imagen) : 'https://placehold.co/400x300/0f1d35/4a6fa5?text=Sobre' }}"
                             class="w-100 h-100 object-fit-cover" alt="Sobre">
                    </div>
                    <div class="p-4 d-flex flex-column justify-content-center bg-white flex-grow-1">
                        <h5 class="fw-bold text-dark mb-2">{{ $cafeBistro->sobre_titulo ?? '—' }}</h5>
                        <p class="small text-muted lh-base mb-0">
                            {{ $cafeBistro->sobre_texto ? Str::limit($cafeBistro->sobre_texto, 150) : '—' }}
                        </p>
                    </div>
                </div>
            </div>
        </div>

        {{-- 03. CARDÁPIO --}}
        <div class="col-lg-6">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100">
                <x-admin.block-header icon="fas fa-utensils" theme="bistro" number="03" :title="__('messages.menu_title')" :subtitle="__('messages.menu_title_pdf_desc')" />
                <div class="p-4">
                    <h5 class="fw-bold text-dark mb-3">{{ $cafeBistro->cardapio_titulo ?? '—' }}</h5>
                    @if($cafeBistro->cardapio_subtitulo)
                        <p class="small text-muted mb-3">{{ $cafeBistro->cardapio_subtitulo }}</p>
                    @endif
                    <div class="d-flex align-items-center gap-3">
                        <i class="fas fa-file-pdf fa-2x {{ $cafeBistro->cardapio_pdf ? 'text-bistro' : 'text-muted opacity-25' }}"></i>
                        <div>
                            @if($cafeBistro->cardapio_pdf)
                                <p class="small fw-bold mb-0">{{ __('messages.pdf_uploaded_status') }}</p>
                                <p class="x-small text-muted mb-0">{{ basename($cafeBistro->cardapio_pdf) }}</p>
                            @else
                                <p class="small text-muted mb-0">{{ __('messages.no_pdf_uploaded_status') }}</p>
                                <p class="x-small text-muted mb-0">{{ __('messages.upload_from_editor_hint') }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 04. EVENTOS --}}
        <div class="col-12">
            <div class="sax-premium-card p-0 shadow-sm bg-white">
                <x-admin.block-header icon="fas fa-glass-cheers" theme="bistro" number="04" :title="__('messages.events_title')" :subtitle="__('messages.events_gallery_desc')" />
                <div class="p-4">
                    <div class="row">
                        <div class="col-md-6">
                            <h5 class="fw-bold text-dark mb-2">{{ $cafeBistro->eventos_titulo ?? '—' }}</h5>
                            <p class="x-small text-muted mb-2">{{ $cafeBistro->eventos_subtitulo ?? '' }}</p>
                            <p class="small text-muted lh-base mb-3">
                                {{ $cafeBistro->eventos_texto ? Str::limit($cafeBistro->eventos_texto, 150) : '—' }}
                            </p>
                            @if(count($eventosTipos) > 0)
                                <div class="d-flex flex-wrap gap-1">
                                    @foreach($eventosTipos as $tipo)
                                        <span class="badge bg-light text-dark border x-small">{{ $tipo }}</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                        <div class="col-md-6">
                            @if(count($eventosGaleria) > 0)
                                <div class="row g-2">
                                    @foreach($eventosGaleria as $img)
                                        <div class="col-4">
                                            <img src="{{ asset('storage/'.$img) }}" class="w-100 rounded" style="height:5rem;object-fit:cover;" alt="">
                                        </div>
                                    @endforeach
                                </div>
                                <p class="x-small text-muted mt-2 mb-0">
                                    <i class="fas fa-images me-1 opacity-50"></i>{{ count($eventosGaleria) }} {{ __('messages.images_count_label') }}
                                </p>
                            @else
                                <div class="empty-state-mini">
                                    <i class="fas fa-images empty-icon d-block"></i>
                                    <p class="x-small text-muted mb-0">{{ __('messages.no_events_gallery_status') }}</p>
                                </div>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- 05. HORÁRIOS --}}
        <div class="col-lg-5">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100">
                <x-admin.block-header icon="fas fa-clock" theme="bistro" number="05" :title="__('messages.opening_hours_title')" :subtitle="__('messages.opening_hours_desc')" />
                <div class="p-4">
                    @if(count($horarios) > 0)
                        <table class="w-100">
                            <tbody>
                                @foreach($horarios as $h)
                                    @php $abertura = $h['apertura'] ?? ''; @endphp
                                    <tr class="border-bottom">
                                        <td class="py-2 small fw-bold">{{ $h['dia'] ?? '' }}</td>
                                        <td class="py-2 small text-end {{ $abertura ? 'text-muted' : 'text-danger' }}">
                                            {{ $abertura ? $abertura . ' — ' . ($h['cierre'] ?? '') : __('messages.closed_status') }}
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    @else
                        <div class="empty-state-mini">
                            <i class="fas fa-clock empty-icon d-block"></i>
                            <p class="x-small text-muted mb-0">{{ __('messages.no_hours_configured_status') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- 06. CONTACTO --}}
        <div class="col-lg-7">
            <div class="sax-premium-card p-0 shadow-sm bg-white h-100">
                <x-admin.block-header icon="fas fa-map-marker-alt" theme="bistro" number="06" :title="__('messages.contact_title')" :subtitle="__('messages.contact_details_desc')" />
                <div class="p-4">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="fas fa-map-pin text-bistro mt-1"></i>
                                <div>
                                    <p class="x-small text-uppercase fw-bold text-muted mb-0">{{ __('messages.address_label') }}</p>
                                    <p class="small mb-0">{{ $cafeBistro->direccion ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="fas fa-phone text-bistro mt-1"></i>
                                <div>
                                    <p class="x-small text-uppercase fw-bold text-muted mb-0">{{ __('messages.phone_label') }}</p>
                                    <p class="small mb-0">{{ $cafeBistro->telefono ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="fab fa-whatsapp text-bistro mt-1"></i>
                                <div>
                                    <p class="x-small text-uppercase fw-bold text-muted mb-0">{{ __('messages.whatsapp_label') }}</p>
                                    <p class="small mb-0">{{ $cafeBistro->whatsapp ?? '—' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="fab fa-instagram text-bistro mt-1"></i>
                                <div>
                                    <p class="x-small text-uppercase fw-bold text-muted mb-0">{{ __('messages.instagram_label') }}</p>
                                    <p class="small mb-0">{{ $cafeBistro->instagram_url ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2 mb-3">
                                <i class="fab fa-facebook text-bistro mt-1"></i>
                                <div>
                                    <p class="x-small text-uppercase fw-bold text-muted mb-0">{{ __('messages.facebook_label') }}</p>
                                    <p class="small mb-0">{{ $cafeBistro->facebook_url ?? '—' }}</p>
                                </div>
                            </div>
                            <div class="d-flex align-items-start gap-2">
                                <i class="fas fa-map text-bistro mt-1"></i>
                                <div>
                                    <p class="x-small text-uppercase fw-bold text-muted mb-0">{{ __('messages.map_embed_label') }}</p>
                                    <p class="small mb-0">{{ $cafeBistro->mapa_embed ? __('messages.configured_status') : '—' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEO & METADADOS --}}
        <div class="col-12">
            <div class="sax-premium-card p-4 bg-dark text-white border-0 shadow-lg position-relative overflow-hidden">
                <div class="position-absolute" style="top:-0.625rem;right:-0.625rem;opacity:.05;">
                    <i class="fas fa-search fa-8x text-bistro"></i>
                </div>
                <div class="d-flex align-items-center mb-4">
                    <div class="d-flex align-items-center gap-3">
                        <div class="icon-circle-bistro"><i class="fas fa-search-plus"></i></div>
                        <h6 class="fw-bold text-bistro text-uppercase letter-spacing-2 mb-0">{{ __('messages.seo_metadata') }}</h6>
                    </div>
                    <span class="ms-auto badge bg-light text-dark x-small">{{ __('messages.google_preview_label') }}</span>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3 mb-md-0">
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">{{ __('messages.meta_title_label') }}</label>
                        <span class="fw-bold">{{ $cafeBistro->meta_title ?? '— ' . __('messages.nao_configurado_status') }}</span>
                    </div>
                    <div class="col-md-6">
                        <label class="x-small text-uppercase opacity-50 fw-bold d-block mb-1">{{ __('messages.meta_description_label') }}</label>
                        <span class="small opacity-75">
                            {{ $cafeBistro->meta_description ? Str::limit($cafeBistro->meta_description, 160) : '— ' . __('messages.nao_configurado_status') }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>{{-- /row --}}
</x-admin.card>

@endsection
