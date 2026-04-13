@extends('layout.admin')

@section('content')
<x-admin.card>
    <form action="{{ route('admin.palace.update', $palace->id) }}" method="POST" enctype="multipart/form-data" id="formPalace">
        @csrf
        @method('PUT')

        {{-- Header Estilo Dashboard Marcas --}}
        <x-admin.sticky-header
            :title="__('messages.editar_palace_titulo')"
            :cancelRoute="route('admin.palace.index')"
            :cancelLabel="__('messages.cancelar_btn')"
            :submitLabel="__('messages.guardar_cambios_btn')"
            :updatedAt="__('messages.ultima_atualizacao_label').' '.$palace->updated_at->format('d/m H:i')"
        />

        <div class="row px-3 g-4">
            {{-- Coluna Principal --}}
            <div class="col-lg-8">
                
                {{-- 1. SEÇÃO HERO --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.identificacao_hero_sec') }}</h6>
                    <div class="mb-4">
                        <label class="sax-form-label">{{ __('messages.titulo_impacto_label') }}</label>
                        <input type="text" name="hero_titulo" class="form-control sax-input" value="{{ $palace->hero_titulo }}">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">{{ __('messages.desc_boas_vindas_label') }}</label>
                        <textarea name="hero_descricao" class="form-control sax-input" rows="4">{{ $palace->hero_descricao }}</textarea>
                    </div>
                </div>

                {{-- 2. BAR & BODEGA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.bar_bodega_fotos_sec') }}</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="sax-form-label">{{ __('messages.titulo_bar_label') }}</label>
                            <input type="text" name="bar_titulo" class="form-control sax-input mb-3" value="{{ $palace->bar_titulo }}">
                            <label class="sax-form-label">{{ __('messages.desc_bar_label') }}</label>
                            <textarea name="bar_descricao" class="form-control sax-input" rows="3">{{ $palace->bar_descricao }}</textarea>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-2">
                                @for($i=1; $i<=3; $i++)
                                @php $field = "bar_imagem_$i"; @endphp
                                <div class="col-4">
                                    <div class="preview-box-sm mb-2 rounded border overflow-hidden" style="height: 80px;">
                                        <img id="preview-bar-{{$i}}" src="{{ $palace->$field ? asset('storage/'.$palace->$field) : 'https://placehold.co/200x200' }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div class="asset-upload-zone p-1 py-2">
                                        <input type="file" name="bar_imagem_{{$i}}" class="sax-input-file img-trigger" data-prev="preview-bar-{{$i}}">
                                        <i class="fas fa-camera fa-xs opacity-50"></i>
                                        <span style="font-size: 8px;" class="fw-bold">FOTO {{$i}}</span>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                {{-- 3. GASTRONOMIA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.gastronomia_menus_sec') }}</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-warning text-uppercase">{{ __('messages.cafe_manha_label') }}</label>
                            <textarea name="gastronomia_cafe_desc" class="form-control sax-input small" rows="4">{{ $palace->gastronomia_cafe_desc }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-primary text-uppercase">{{ __('messages.almoco_label') }}</label>
                            <textarea name="gastronomia_almoco_desc" class="form-control sax-input small" rows="4">{{ $palace->gastronomia_almoco_desc }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-danger text-uppercase">{{ __('messages.jantar_label') }}</label>
                            <textarea name="gastronomia_jantar_desc" class="form-control sax-input small" rows="4">{{ $palace->gastronomia_jantar_desc }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 4. GALERIA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.galeria_eventos_sec') }}</h6>
                    <div class="asset-upload-zone mb-3" style="min-height: 120px;">
                        <i class="fas fa-images mb-2 opacity-25 fa-2x"></i>
                        <input type="file" name="eventos_galeria[]" class="sax-input-file" multiple>
                        <p class="sax-form-label m-0">{{ __('messages.upload_fotos_instrucao') }}</p>
                    </div>
                                        <div class="gallery-preview-grid mt-3">
                        @php $fotos = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true); @endphp
                        @foreach($fotos ?? [] as $foto)
                            <div class="gallery-preview-item shadow-sm border">
                                <img src="{{ asset('storage/'.$foto) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            {{-- Coluna Lateral --}}
            <div class="col-lg-4">
                {{-- BANNER PRINCIPAL --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">{{ __('messages.imagem_capa_label') }} (Hero)</h6>
                    <div class="preview-box mb-3 shadow-sm border rounded overflow-hidden">
                        <img id="preview-hero" src="{{ $palace->hero_imagem ? asset('storage/'.$palace->hero_imagem) : 'https://placehold.co/600x400' }}" class="img-fluid w-100">
                    </div>
                    <div class="asset-upload-zone py-3">
                        <input type="file" name="hero_imagem" class="sax-input-file img-trigger" data-prev="preview-hero">
                        <p class="x-small fw-bold m-0"><i class="fas fa-sync-alt me-1"></i> {{ __('messages.alterar_imagem_btn') }}</p>
                    </div>
                </div>

                {{-- EXPERIÊNCIA TEMÁTICA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm bg-dark text-white">
                    <h6 class="sax-label mb-3 text-gold border-bottom border-secondary pb-2 text-uppercase letter-spacing-1">{{ __('messages.noche_arabe_sec') }}</h6>
                    <div class="mb-3">
                        <label class="x-small text-uppercase opacity-7 fw-bold">{{ __('messages.titulo_secao_label') }}</label>
                        <input type="text" name="tematica_titulo" class="form-control sax-input bg-transparent text-white border-secondary small" value="{{ $palace->tematica_titulo }}">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="x-small text-uppercase opacity-7 fw-bold">{{ __('messages.preco_label') }}</label>
                            <input type="text" name="tematica_preco" class="form-control sax-input bg-transparent text-gold border-secondary small" value="{{ $palace->tematica_preco }}">
                        </div>
                        <div class="col-6">
                             <label class="x-small text-uppercase opacity-7 fw-bold">{{ __('messages.etiqueta_label') }}</label>
                            <input type="text" name="tematica_tag" class="form-control sax-input bg-transparent text-white border-secondary small" value="{{ $palace->tematica_tag }}">
                        </div>
                    </div>
                                        <div class="asset-upload-zone py-3 bg-white-10 border-secondary mb-3">
                        <input type="file" name="tematica_imagem" class="sax-input-file img-trigger" data-prev="preview-tematica">
                        <p class="x-small fw-bold m-0 text-white">CAMBIAR FOTO</p>
                    </div>
                    <div class="preview-box-sm rounded overflow-hidden" style="height: 120px;">
                        <img id="preview-tematica" src="{{ $palace->tematica_imagem ? asset('storage/'.$palace->tematica_imagem) : 'https://placehold.co/600x400' }}" class="w-100 h-100 object-fit-cover">
                    </div>
                </div>

                {{-- HORÁRIOS --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">{{ __('messages.horarios_atencao_sec') }}</h6>
                    <div class="mb-2">
                        <label class="x-small fw-bold">{{ __('messages.segunda_label') }}</label>
                        <input type="text" name="contato_horario_segunda" class="form-control sax-input py-1" value="{{ $palace->contato_horario_segunda }}">
                    </div>
                    <div class="mb-2">
                        <label class="x-small fw-bold">{{ __('messages.terca_sabado_label') }}</label>
                        <input type="text" name="contato_horario_sabado" class="form-control sax-input py-1" value="{{ $palace->contato_horario_sabado }}">
                    </div>
                    <div class="mb-0">
                        <label class="x-small fw-bold">{{ __('messages.domingo_label') }}</label>
                        <input type="text" name="contato_horario_domingo" class="form-control sax-input py-1" value="{{ $palace->contato_horario_domingo }}">
                    </div>
                </div>

                {{-- CONTATO --}}
                <div class="sax-premium-card p-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">{{ __('messages.ubicacion_mapa_sec') }}</h6>
                    <div class="mb-3">
                        <label class="sax-form-label">WhatsApp</label>
                        <input type="text" name="contato_whatsapp" class="form-control sax-input border-success-soft" value="{{ $palace->contato_whatsapp }}">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Google Maps (Iframe)</label>
                        <textarea name="contato_mapa_iframe" class="form-control sax-input x-small" rows="3">{{ $palace->contato_mapa_iframe }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Botão Mobile Fixo --}}
        <div class="d-md-none fixed-bottom p-3 bg-white border-top shadow-lg" style="z-index: 1030;">
            <button type="submit" class="btn btn-dark-gold w-100 py-3 rounded-pill fw-bold">
                {{ __('messages.guardar_cambios_btn') }} <i class="fas fa-check-circle ms-2"></i>
            </button>
        </div>
    </form>
</x-admin.card>
@endsection