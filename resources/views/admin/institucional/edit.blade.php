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
                <span class="text-muted x-small">Última atualização: {{ $institucional->updated_at->format('d/m H:i') }}</span>
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
                        <input type="file" name="section_one_image" class="sax-input-file img-input" data-preview="preview-section-one">
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

                {{-- LOGOS DAS MARCAS --}}
                <div class="sax-premium-card p-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">Logos de Marcas</h6>
                    <div class="asset-upload-zone py-3 border-secondary-soft mb-3">
                        <input type="file" name="brand_logos[]" class="sax-input-file" multiple>
                        <p class="x-small fw-bold m-0"><i class="fas fa-cloud-upload-alt me-1"></i> CARGAR LOGOS</p>
                    </div>
                    <div class="gallery-preview-grid">
                        @php $logos = is_array($institucional->brand_logos) ? $institucional->brand_logos : json_decode($institucional->brand_logos, true); @endphp
                        @foreach($logos ?? [] as $logo)
                            <div class="gallery-preview-item border" style="background: #f8f9fa;">
                                <img src="{{ asset('storage/'.$logo) }}" style="object-fit: contain; padding: 2px;">
                            </div>
                        @endforeach
                    </div>
                </div>

            </div>
        </div>
    </form>
</div>

<style>
    /* Reaproveitando os estilos do Palace para manter unidade visual */
    :root { --gold: #D4AF37; --sax-dark: #121212; }
    .bg-white-soft { background-color: #f8fafc; }
    .text-gold { color: var(--gold) !important; }
    .border-gold { border-color: var(--gold) !important; }
    .border-gold-subtle { border-color: rgba(212, 175, 55, 0.3) !important; }
    .sax-title { font-size: 1.4rem; font-weight: 900; color: var(--sax-dark); }
    .sax-divider-gold { width: 45px; height: 4px; background: var(--gold); margin: 8px 0; border-radius: 2px; }
    .letter-spacing-2 { letter-spacing: 2px; }
    .letter-spacing-1 { letter-spacing: 1px; }
    .sax-premium-card { background: #fff; border-radius: 20px; border: 1px solid #eef2f7; }
    .sax-form-label { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 5px; text-transform: uppercase; }
    .sax-input { border: 1px solid #e2e8f0; border-radius: 12px; font-size: 0.85rem; }
    .sax-input:focus { border-color: var(--gold); box-shadow: 0 0 0 3px rgba(212, 175, 55, 0.1); }
    .asset-upload-zone { border: 2px dashed #e2e8f0; border-radius: 15px; text-align: center; position: relative; transition: all 0.3s; cursor: pointer; }
    .asset-upload-zone:hover { border-color: var(--gold); background: #fff; }
    .sax-input-file { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 5; }
    .gallery-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(50px, 1fr)); gap: 8px; }
    .gallery-preview-item { aspect-ratio: 1; border-radius: 8px; overflow: hidden; background: #eee; }
    .gallery-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .btn-dark-gold { background: var(--sax-dark); color: var(--gold); border: none; font-size: 0.8rem; letter-spacing: 1px; }
    .btn-back-minimal { color: #64748b; font-size: 0.7rem; font-weight: 800; text-decoration: none; letter-spacing: 1px; }
    .sticky-header { position: sticky; top: 0; z-index: 1020; }
    .x-small { font-size: 0.65rem; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview em tempo real para inputs de imagem única
        $('.img-input').change(function() {
            const input = this;
            const previewId = $(this).data('preview');
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => $('#' + previewId).attr('src', e.target.result);
                reader.readAsDataURL(input.files[0]);
            }
        });
    });
</script>
@endpush