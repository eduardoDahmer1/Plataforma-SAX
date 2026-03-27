@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2 bg-white-soft">
    <form action="{{ route('admin.institucional.update', $institucional->id) }}" method="POST" enctype="multipart/form-data" id="formInstitucional">
        @csrf
        @method('PUT')

        {{-- Header Estilo Dashboard Marcas --}}
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5 sticky-header px-4 py-3 bg-white border-bottom shadow-sm">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar Institucional</h2>
                <div class="sax-divider-gold"></div>
                <span class="text-muted x-small">Última actualización: {{ $institucional->updated_at->format('d/m H:i') }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.institucional.index') }}" class="btn-back-minimal me-3 d-none d-md-flex align-items-center">
                    <i class="fas fa-times me-1"></i> CANCELAR
                </a>
                <button type="submit" class="btn btn-dark-gold rounded-pill px-4 shadow-sm transition fw-bold">
                    GUARDAR CAMBIOS <i class="fas fa-check-circle ms-2"></i>
                </button>
            </div>
        </div>

        <div class="row px-3 g-4">
            {{-- Coluna Principal --}}
            <div class="col-lg-8">
                
                {{-- 1. SEÇÃO SOBRE NOSOTROS --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">01. Sección Principal (Sobre Nosotros)</h6>
                    <div class="mb-4">
                        <label class="sax-form-label">Título de la Sección</label>
                        <input type="text" name="section_one_title" class="form-control sax-input" value="{{ $institucional->section_one_title }}">
                    </div>
                    <div class="mb-0">
                        <label class="sax-form-label">Contenido Narrativo</label>
                        <textarea name="section_one_content" class="form-control sax-input" rows="6">{{ $institucional->section_one_content }}</textarea>
                    </div>
                </div>

                {{-- 2. BLOCOS DE TEXTO (PILARES) --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">02. Pilares de Calidad & Experiencia</h6>
                    
                    <div class="p-3 mb-3 rounded bg-light border-start border-gold">
                        <label class="sax-form-label text-gold">Pilar 01: Título & Texto</label>
                        <input type="text" name="text_section_one_title" class="form-control sax-input mb-2 font-weight-bold" value="{{ $institucional->text_section_one_title }}">
                        <textarea name="text_section_one_body" class="form-control sax-input small" rows="2">{{ $institucional->text_section_one_body }}</textarea>
                    </div>

                    <div class="p-3 mb-3 rounded bg-light border-start border-gold">
                        <label class="sax-form-label text-gold">Pilar 02: Título & Texto</label>
                        <input type="text" name="text_section_two_title" class="form-control sax-input mb-2 font-weight-bold" value="{{ $institucional->text_section_two_title }}">
                        <textarea name="text_section_two_body" class="form-control sax-input small" rows="2">{{ $institucional->text_section_two_body }}</textarea>
                    </div>

                    <div class="p-3 rounded bg-light border-start border-gold">
                        <label class="sax-form-label text-gold">Pilar 03: Título & Texto</label>
                        <input type="text" name="text_section_three_title" class="form-control sax-input mb-2 font-weight-bold" value="{{ $institucional->text_section_three_title }}">
                        <textarea name="text_section_three_body" class="form-control sax-input small" rows="2">{{ $institucional->text_section_three_body }}</textarea>
                    </div>
                </div>

                {{-- NOVO: 04. EXPERIÊNCIA VIRTUAL (IFRAMES) --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">04. Experiencia Virtual & Cámaras</h6>
                    
                    <div class="mb-4">
                        <label class="sax-form-label">Iframe: Tour Virtual 360° (Tienda)</label>
                        <textarea name="iframe_tour_360" class="form-control sax-input font-monospace small" rows="3" placeholder='<iframe src="..."></iframe>'>{{ $institucional->iframe_tour_360 }}</textarea>
                        <small class="text-muted x-small">Pegue o código gerado pelo Google Maps ou Matterport.</small>
                    </div>

                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="sax-form-label">Iframe: Cámara Ponte da Amizade</label>
                            <textarea name="iframe_ponte_amizade" class="form-control sax-input font-monospace small" rows="3">{{ $institucional->iframe_ponte_amizade }}</textarea>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="sax-form-label">Iframe: Cámara Centro CDE</label>
                            <textarea name="iframe_centro_cde" class="form-control sax-input font-monospace small" rows="3">{{ $institucional->iframe_centro_cde }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 3. GALERIA DE FOTOS --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">03. Galería de Fotos Institucional</h6>
                    <div class="asset-upload-zone mb-3" style="min-height: 120px;">
                        <i class="fas fa-camera-retro mb-2 opacity-25 fa-2x"></i>
                        <input type="file" name="gallery_images[]" class="sax-input-file" multiple>
                        <p class="sax-form-label m-0">Arrastre o haga clic para subir fotos de la tienda</p>
                    </div>
                    <div class="gallery-preview-grid mt-3">
                        @php $fotos = is_array($institucional->gallery_images) ? $institucional->gallery_images : json_decode($institucional->gallery_images, true); @endphp
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
                
                {{-- IMAGEM PRINCIPAL --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">Imagen de Portada</h6>
                    <div class="preview-box mb-3 shadow-sm border rounded overflow-hidden">
                        <img id="preview-section-one" src="{{ $institucional->section_one_image ? asset('storage/'.$institucional->section_one_image) : 'https://placehold.co/600x400' }}" class="img-fluid w-100">
                    </div>
                    <div class="asset-upload-zone py-3">
                        <input type="file" name="section_one_image" class="sax-input-file img-trigger" data-prev="preview-section-one">
                        <p class="x-small fw-bold m-0"><i class="fas fa-sync-alt me-1"></i> CAMBIAR IMAGEN</p>
                    </div>
                </div>

                {{-- MÉTRICAS SAX (CARD DARK) --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm bg-dark text-white">
                    <h6 class="sax-label mb-3 text-gold border-bottom border-secondary pb-2 text-uppercase letter-spacing-1">Números de SAX</h6>
                    <div class="mb-3">
                        <label class="x-small text-uppercase opacity-7 fw-bold">Cantidad de Marcas</label>
                        <input type="number" name="stat_brands_count" class="form-control sax-input bg-transparent text-gold border-secondary" value="{{ $institucional->stat_brands_count }}">
                    </div>
                    <div class="mb-3">
                        <label class="x-small text-uppercase opacity-7 fw-bold">Área Total (m²)</label>
                        <input type="number" name="stat_sqm_count" class="form-control sax-input bg-transparent text-white border-secondary" value="{{ $institucional->stat_sqm_count }}">
                    </div>
                    <div class="mb-0">
                        <label class="x-small text-uppercase opacity-7 fw-bold">Colaboradores</label>
                        <input type="number" name="stat_employees_count" class="form-control sax-input bg-transparent text-white border-secondary" value="{{ $institucional->stat_employees_count }}">
                    </div>
                </div>

                {{-- TOP SLIDERS --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">Banners (Top Sliders)</h6>
                    <div class="asset-upload-zone py-3 bg-light border-gold-subtle mb-3">
                        <input type="file" name="top_sliders[]" class="sax-input-file" multiple>
                        <p class="x-small fw-bold m-0 text-gold"><i class="fas fa-plus-circle me-1"></i> SUBIR BANNERS</p>
                    </div>
                    <div class="gallery-preview-grid">
                        @php $sliders = is_array($institucional->top_sliders) ? $institucional->top_sliders : json_decode($institucional->top_sliders, true); @endphp
                        @foreach($sliders ?? [] as $slide)
                            <div class="gallery-preview-item shadow-sm">
                                <img src="{{ asset('storage/'.$slide) }}">
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

@endsection

