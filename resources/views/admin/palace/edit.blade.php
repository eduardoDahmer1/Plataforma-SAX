@extends('layout.admin')

@section('content')
<x-admin.card>
    <form action="{{ route('admin.palace.update', $palace->id) }}" method="POST" enctype="multipart/form-data" id="formPalace">
        @csrf
        @method('PUT')

        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5 sticky-header px-4 py-3 bg-white border-bottom shadow-sm">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">{{ __('messages.editar_palace_titulo') }}</h2>
                <div class="sax-divider-gold"></div>
                <span class="text-muted x-small">{{ __('messages.ultima_atualizacao_label') }} {{ $palace->updated_at ? $palace->updated_at->format('d/m H:i') : '' }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.palace.index') }}" class="btn-back-minimal me-3 d-none d-md-flex align-items-center">
                    <i class="fas fa-times me-1"></i> {{ __('messages.cancelar_btn') }}
                </a>
                <button type="submit" class="btn btn-dark-gold rounded-pill px-4 shadow-sm transition fw-bold">
                    {{ __('messages.guardar_cambios_btn') }} <i class="fas fa-check-circle ms-2"></i>
                </button>
            </div>
        </div>

        <x-admin.alert />

        <div class="row px-3 g-4">
            <div class="col-lg-8">

                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.identificacao_hero_sec') }}</h6>
                    
                    <div class="mb-4 group-container">
                        <label class="sax-form-label">{{ __('messages.titulo_impacto_label') }}</label>
                        <input type="hidden" id="real-heroT-pt" name="translate[pt-br][palace_hero_titulo]" value="{{ old('translate.pt-br.palace_hero_titulo', $palace->translations->where('locale', 'pt-br')->first()->palace_hero_titulo ?? $palace->hero_titulo) }}">
                        <input type="hidden" id="real-heroT-es" name="translate[es][palace_hero_titulo]" value="{{ old('translate.es.palace_hero_titulo', $palace->translations->where('locale', 'es')->first()->palace_hero_titulo ?? '') }}">
                        <input type="hidden" id="real-heroT-en" name="translate[en][palace_hero_titulo]" value="{{ old('translate.en.palace_hero_titulo', $palace->translations->where('locale', 'en')->first()->palace_hero_titulo ?? '') }}">
                        <input type="text" id="visual-heroT-input" class="form-control sax-input" value="{{ old('translate.pt-br.palace_hero_titulo', $palace->translations->where('locale', 'pt-br')->first()->palace_hero_titulo ?? $palace->hero_titulo) }}">
                        <div class="mt-2">
                            <span class="small text-muted me-2">Editar idioma:</span>
                            <a href="javascript:void(0)" class="badge bg-primary heroT-lang-btn text-decoration-none" onclick="switchLanguage('heroT', 'pt', this)">PT</a>
                            <a href="javascript:void(0)" class="badge bg-secondary heroT-lang-btn text-decoration-none" onclick="switchLanguage('heroT', 'es', this)">ES</a>
                            <a href="javascript:void(0)" class="badge bg-secondary heroT-lang-btn text-decoration-none" onclick="switchLanguage('heroT', 'en', this)">EN</a>
                        </div>
                    </div>

                    <div class="mb-0 group-container">
                        <label class="sax-form-label">{{ __('messages.desc_boas_vindas_label') }}</label>
                        <textarea id="real-heroD-pt" name="translate[pt-br][palace_hero_descricao]" class="d-none">{{ old('translate.pt-br.palace_hero_descricao', $palace->translations->where('locale', 'pt-br')->first()->palace_hero_descricao ?? $palace->hero_descricao) }}</textarea>
                        <textarea id="real-heroD-es" name="translate[es][palace_hero_descricao]" class="d-none">{{ old('translate.es.palace_hero_descricao', $palace->translations->where('locale', 'es')->first()->palace_hero_descricao ?? '') }}</textarea>
                        <textarea id="real-heroD-en" name="translate[en][palace_hero_descricao]" class="d-none">{{ old('translate.en.palace_hero_descricao', $palace->translations->where('locale', 'en')->first()->palace_hero_descricao ?? '') }}</textarea>
                        <textarea id="visual-heroD-input" class="form-control sax-input" rows="4">{{ old('translate.pt-br.palace_hero_descricao', $palace->translations->where('locale', 'pt-br')->first()->palace_hero_descricao ?? $palace->hero_descricao) }}</textarea>
                        <div class="mt-2">
                            <span class="small text-muted me-2">Editar idioma:</span>
                            <a href="javascript:void(0)" class="badge bg-primary heroD-lang-btn text-decoration-none" onclick="switchLanguage('heroD', 'pt', this)">PT</a>
                            <a href="javascript:void(0)" class="badge bg-secondary heroD-lang-btn text-decoration-none" onclick="switchLanguage('heroD', 'es', this)">ES</a>
                            <a href="javascript:void(0)" class="badge bg-secondary heroD-lang-btn text-decoration-none" onclick="switchLanguage('heroD', 'en', this)">EN</a>
                        </div>
                    </div>
                </div>

                <div class="sax-premium-card p-4 mb-4 shadow-sm group-container">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.bar_bodega_fotos_sec') }}</h6>
                    
                    <input type="hidden" id="real-barT-pt" name="translate[pt-br][palace_bar_titulo]" value="{{ old('translate.pt-br.palace_bar_titulo', $palace->translations->where('locale', 'pt-br')->first()->palace_bar_titulo ?? $palace->bar_titulo) }}">
                    <input type="hidden" id="real-barT-es" name="translate[es][palace_bar_titulo]" value="{{ old('translate.es.palace_bar_titulo', $palace->translations->where('locale', 'es')->first()->palace_bar_titulo ?? '') }}">
                    <input type="hidden" id="real-barT-en" name="translate[en][palace_bar_titulo]" value="{{ old('translate.en.palace_bar_titulo', $palace->translations->where('locale', 'en')->first()->palace_bar_titulo ?? '') }}">
                    
                    <textarea id="real-barD-pt" name="translate[pt-br][palace_bar_descricao]" class="d-none">{{ old('translate.pt-br.palace_bar_descricao', $palace->translations->where('locale', 'pt-br')->first()->palace_bar_descricao ?? $palace->bar_descricao) }}</textarea>
                    <textarea id="real-barD-es" name="translate[es][palace_bar_descricao]" class="d-none">{{ old('translate.es.palace_bar_descricao', $palace->translations->where('locale', 'es')->first()->palace_bar_descricao ?? '') }}</textarea>
                    <textarea id="real-barD-en" name="translate[en][palace_bar_descricao]" class="d-none">{{ old('translate.en.palace_bar_descricao', $palace->translations->where('locale', 'en')->first()->palace_bar_descricao ?? '') }}</textarea>

                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="sax-form-label">{{ __('messages.titulo_bar_label') }}</label>
                            <input type="text" id="visual-barT-input" class="form-control sax-input mb-3" value="{{ old('translate.pt-br.palace_bar_titulo', $palace->translations->where('locale', 'pt-br')->first()->palace_bar_titulo ?? $palace->bar_titulo) }}">
                            <label class="sax-form-label">{{ __('messages.desc_bar_label') }}</label>
                            <textarea id="visual-barD-input" class="form-control sax-input" rows="3">{{ old('translate.pt-br.palace_bar_descricao', $palace->translations->where('locale', 'pt-br')->first()->palace_bar_descricao ?? $palace->bar_descricao) }}</textarea>
                            <div class="mt-2">
                                <span class="small text-muted me-2">Editar idioma:</span>
                                <a href="javascript:void(0)" class="badge bg-primary barT-lang-btn barD-lang-btn text-decoration-none" onclick="switchLanguage('barT', 'pt', this); switchLanguage('barD', 'pt', this)">PT</a>
                                <a href="javascript:void(0)" class="badge bg-secondary barT-lang-btn barD-lang-btn text-decoration-none" onclick="switchLanguage('barT', 'es', this); switchLanguage('barD', 'es', this)">ES</a>
                                <a href="javascript:void(0)" class="badge bg-secondary barT-lang-btn barD-lang-btn text-decoration-none" onclick="switchLanguage('barT', 'en', this); switchLanguage('barD', 'en', this)">EN</a>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="row g-2">
                                @for($i=1; $i<=3; $i++)
                                @php $field = "bar_imagem_$i"; @endphp
                                <div class="col-4">
                                    <div class="preview-box-sm mb-2 rounded border overflow-hidden" style="height: 80px;">
                                        <img id="preview-bar-{{$i}}" src="{{ $palace->$field ? asset('storage/'.$palace->$field) : 'https://placehold.co/200x200' }}" class="w-100 h-100 object-fit-cover">
                                    </div>
                                    <div class="asset-upload-zone p-1 py-2 text-center">
                                        <input type="file" name="{{$field}}" class="sax-input-file img-trigger" data-prev="preview-bar-{{$i}}">
                                        <i class="fas fa-camera fa-xs opacity-50"></i>
                                    </div>
                                </div>
                                @endfor
                            </div>
                        </div>
                    </div>
                </div>

                <div class="sax-premium-card p-4 mb-4 shadow-sm group-container">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">{{ __('messages.gastronomia_sec') }}</h6>
                    
                    <input type="hidden" id="real-gastT-pt" name="translate[pt-br][palace_gastronomia_titulo]" value="{{ old('translate.pt-br.palace_gastronomia_titulo', $palace->translations->where('locale', 'pt-br')->first()->palace_gastronomia_titulo ?? $palace->gastronomia_titulo) }}">
                    <input type="hidden" id="real-gastT-es" name="translate[es][palace_gastronomia_titulo]" value="{{ old('translate.es.palace_gastronomia_titulo', $palace->translations->where('locale', 'es')->first()->palace_gastronomia_titulo ?? '') }}">
                    <input type="hidden" id="real-gastT-en" name="translate[en][palace_gastronomia_titulo]" value="{{ old('translate.en.palace_gastronomia_titulo', $palace->translations->where('locale', 'en')->first()->palace_gastronomia_titulo ?? '') }}">
                    
                    <textarea id="real-gastD-pt" name="translate[pt-br][palace_tematica_descricao]" class="d-none">{{ old('translate.pt-br.palace_tematica_descricao', $palace->translations->where('locale', 'pt-br')->first()->palace_tematica_descricao ?? $palace->gastronomia_descricao) }}</textarea>
                    <textarea id="real-gastD-es" name="translate[es][palace_tematica_descricao]" class="d-none">{{ old('translate.es.palace_tematica_descricao', $palace->translations->where('locale', 'es')->first()->palace_tematica_descricao ?? '') }}</textarea>
                    <textarea id="real-gastD-en" name="translate[en][palace_tematica_descricao]" class="d-none">{{ old('translate.en.palace_tematica_descricao', $palace->translations->where('locale', 'en')->first()->palace_tematica_descricao ?? '') }}</textarea>

                    <div class="mb-3">
                        <label class="sax-form-label">{{ __('messages.titulo_gastronomia_label') }}</label>
                        <input type="text" id="visual-gastT-input" class="form-control sax-input" value="{{ old('translate.pt-br.palace_gastronomia_titulo', $palace->translations->where('locale', 'pt-br')->first()->palace_gastronomia_titulo ?? $palace->gastronomia_titulo) }}">
                    </div>
                    <div class="mb-3">
                        <label class="sax-form-label">{{ __('messages.desc_gastronomia_label') }}</label>
                        <textarea id="visual-gastD-input" class="form-control sax-input" rows="3">{{ old('translate.pt-br.palace_tematica_descricao', $palace->translations->where('locale', 'pt-br')->first()->palace_tematica_descricao ?? $palace->gastronomia_descricao) }}</textarea>
                    </div>
                    
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <div class="mt-1">
                            <span class="small text-muted me-2">Editar idioma:</span>
                            <a href="javascript:void(0)" class="badge bg-primary gastT-lang-btn gastD-lang-btn text-decoration-none" onclick="switchLanguage('gastT', 'pt', this); switchLanguage('gastD', 'pt', this)">PT</a>
                            <a href="javascript:void(0)" class="badge bg-secondary gastT-lang-btn gastD-lang-btn text-decoration-none" onclick="switchLanguage('gastT', 'es', this); switchLanguage('gastD', 'es', this)">ES</a>
                            <a href="javascript:void(0)" class="badge bg-secondary gastT-lang-btn gastD-lang-btn text-decoration-none" onclick="switchLanguage('gastT', 'en', this); switchLanguage('gastD', 'en', this)">EN</a>
                        </div>
                    </div>
                    
                    <div class="border-top pt-3 mt-2">
                        <label class="sax-form-label d-block mb-2 fw-bold text-dark text-uppercase letter-spacing-1" style="font-size: 11px;">
                            <i class="fas fa-file-pdf text-danger me-1"></i> Arquivo do Cardápio Completo (PDF)
                        </label>
                        
                        <div class="row align-items-center g-3">
                            <div class="col-md-7">
                                <div class="asset-upload-zone py-3 px-3 border border-dashed rounded bg-light text-center position-relative">
                                    <i class="fas fa-cloud-upload-alt mb-1 text-secondary opacity-75 fa-lg"></i>
                                    <input type="file" name="gastronomia_menu_pdf" class="sax-input-file" accept="application/pdf" style="opacity: 0; position: absolute; top:0; left:0; width:100%; height:100%; cursor:pointer;">
                                    <p class="x-small text-secondary m-0">Clique ou arraste o arquivo .pdf para substituir</p>
                                </div>
                            </div>
                            <div class="col-md-5">
                                @if($palace->gastronomia_menu_pdf)
                                    <div class="p-2 border border-info-subtle rounded bg-info-soft d-flex align-items-center justify-content-between">
                                        <div class="d-flex align-items-center overflow-hidden me-2">
                                            <i class="fas fa-check-circle text-info me-2 fa-lg flex-shrink-0"></i>
                                            <span class="x-small text-dark text-truncate fw-bold">Cardápio Cadastrado</span>
                                        </div>
                                        <a href="{{ asset('storage/' . $palace->gastronomia_menu_pdf) }}" target="_blank" class="btn btn-sm btn-outline-info px-2 py-1 rounded-0" style="font-size: 10px;">
                                            <i class="fas fa-eye"></i> Ver PDF
                                        </a>
                                    </div>
                                @else
                                    <div class="p-2 border border-warning-subtle rounded bg-light d-flex align-items-center">
                                        <i class="fas fa-exclamation-triangle text-warning me-2"></i>
                                        <span class="x-small text-muted italic">Nenhum arquivo PDF enviado ainda.</span>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

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

            <div class="col-lg-4">
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

        <div class="d-md-none fixed-bottom p-3 bg-white border-top shadow-lg" style="z-index: 1030;">
            <button type="submit" class="btn btn-dark-gold w-100 py-3 rounded-pill fw-bold">
                {{ __('messages.guardar_cambios_btn') }} <i class="fas fa-check-circle ms-2"></i>
            </button>
        </div>
    </form>
</x-admin.card>
@endsection