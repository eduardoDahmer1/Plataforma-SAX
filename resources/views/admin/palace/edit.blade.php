@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2 bg-white-soft">
    <form action="{{ route('admin.palace.update', $palace->id) }}" method="POST" enctype="multipart/form-data" id="formPalace">
        @csrf
        @method('PUT')

        {{-- Header Estilo Dashboard Marcas --}}
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5 sticky-header px-4 py-3 bg-white border-bottom">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar SAX Palace</h2>
                <div class="sax-divider-gold"></div>
                <span class="text-muted x-small">Última atualização: {{ $palace->updated_at->format('d/m H:i') }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.palace.index') }}" class="btn-back-minimal me-3 d-none d-md-flex">
                    <i class="fas fa-times me-1"></i> CANCELAR
                </a>
                <button type="submit" class="btn btn-dark-gold rounded-pill px-4 shadow-sm transition fw-bold">
                    GUARDAR CAMBIOS <i class="fas fa-check-circle ms-2"></i>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert alert-modern alert-success slide-in-top mb-4 mx-4">
                <i class="fas fa-check-circle mr-2"></i> {{ session('success') }}
            </div>
        @endif

        <div class="row px-3 g-4">
            {{-- Coluna Principal --}}
            <div class="col-lg-8">
                
                {{-- 1. SEÇÃO HERO --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">01. Identificación & Hero</h6>
                    
                    <div class="mb-4">
                        <label class="sax-form-label">Título de Impacto (Hero)</label>
                        <input type="text" name="hero_titulo" class="form-control sax-input" value="{{ $palace->hero_titulo }}">
                    </div>

                    <div class="mb-0">
                        <label class="sax-form-label">Descripción de Bienvenida</label>
                        <textarea name="hero_descricao" class="form-control sax-input" rows="4">{{ $palace->hero_descricao }}</textarea>
                    </div>
                </div>

                {{-- 2. GASTRONOMIA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">02. Gastronomía & Menús</h6>
                    
                    <div class="mb-4">
                        <label class="sax-form-label text-primary">Título de la Sección</label>
                        <input type="text" name="gastronomia_titulo" class="form-control sax-input border-primary-soft" value="{{ $palace->gastronomia_titulo }}">
                    </div>

                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-warning text-uppercase letter-spacing-1">Café da Manhã</label>
                            <textarea name="gastronomia_cafe_desc" class="form-control sax-input bg-light-soft small" rows="4">{{ $palace->gastronomia_cafe_desc }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-primary text-uppercase letter-spacing-1">Almuerzo</label>
                            <textarea name="gastronomia_almoco_desc" class="form-control sax-input bg-light-soft small" rows="4">{{ $palace->gastronomia_almoco_desc }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-danger text-uppercase letter-spacing-1">Cena</label>
                            <textarea name="gastronomia_jantar_desc" class="form-control sax-input bg-light-soft small" rows="4">{{ $palace->gastronomia_jantar_desc }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 3. GALERIA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">03. Galería de Eventos</h6>
                    
                    <div class="mb-4">
                        <label class="sax-form-label">Título de Galería</label>
                        <input type="text" name="eventos_titulo" class="form-control sax-input" value="{{ $palace->eventos_titulo }}">
                    </div>

                    <div class="asset-upload-zone mb-3" style="min-height: 150px;">
                        <i class="fas fa-images mb-2 opacity-25 fa-2x"></i>
                        <input type="file" name="eventos_galeria[]" class="sax-input-file" multiple>
                        <p class="sax-form-label m-0">Arrastre o haga clic para subir nuevas fotos</p>
                        <span class="x-small text-muted">Nota: Esto reemplazará la galería actual</span>
                    </div>

                    <div class="gallery-preview-grid mt-3">
                        @php $fotos = is_array($palace->eventos_galeria) ? $palace->eventos_galeria : json_decode($palace->eventos_galeria, true); @endphp
                        @foreach($fotos ?? [] as $foto)
                            <div class="gallery-preview-item shadow-sm">
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
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="sax-label m-0 text-dark text-uppercase letter-spacing-1">Imagen Hero</h6>
                        @if($palace->hero_imagem)
                            <span class="badge-success-soft"><i class="fas fa-check"></i> ON</span>
                        @endif
                    </div>
                    <div class="preview-box mb-3 shadow-sm border rounded">
                        <img id="preview-hero" src="{{ $palace->hero_imagem ? asset('storage/'.$palace->hero_imagem) : 'https://placehold.co/600x400' }}" class="img-fluid">
                    </div>
                    <div class="asset-upload-zone py-3">
                        <i class="fas fa-cloud-upload-alt mb-1 opacity-50"></i>
                        <input type="file" name="hero_imagem" class="sax-input-file img-input" data-preview="preview-hero">
                        <p class="x-small fw-bold m-0">CAMBIAR IMAGEN</p>
                    </div>
                </div>

                {{-- EXPERIÊNCIA TEMÁTICA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm bg-dark text-white">
                    <h6 class="sax-label mb-4 text-gold border-bottom border-secondary pb-2 text-uppercase letter-spacing-1">Noche Árabe</h6>
                    
                    <div class="mb-3">
                        <label class="x-small text-uppercase opacity-7 fw-bold">Título</label>
                        <input type="text" name="tematica_titulo" class="form-control sax-input bg-transparent text-white border-secondary small" value="{{ $palace->tematica_titulo }}">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="x-small text-uppercase opacity-7 fw-bold">Etiqueta</label>
                            <input type="text" name="tematica_tag" class="form-control sax-input bg-transparent text-white border-secondary small" value="{{ $palace->tematica_tag }}">
                        </div>
                        <div class="col-6">
                            <label class="x-small text-uppercase opacity-7 fw-bold">Precio</label>
                            <input type="text" name="tematica_preco" class="form-control sax-input bg-transparent text-gold border-secondary small" value="{{ $palace->tematica_preco }}">
                        </div>
                    </div>

                    <div class="asset-upload-zone py-3 bg-white-10 border-secondary">
                        <i class="fas fa-star mb-1 text-gold"></i>
                        <input type="file" name="tematica_imagem" class="sax-input-file img-input" data-preview="preview-tematica">
                        <p class="x-small fw-bold m-0 text-white">CAMBIAR FOTO EVENTO</p>
                    </div>
                    <div class="preview-box-sm mt-3 rounded overflow-hidden shadow-sm" style="height: 100px;">
                        <img id="preview-tematica" src="{{ $palace->tematica_imagem ? asset('storage/'.$palace->tematica_imagem) : 'https://placehold.co/600x400' }}" class="w-100 h-100 object-fit-cover">
                    </div>
                </div>

                {{-- CONTATO --}}
                <div class="sax-premium-card p-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">Contacto</h6>
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
                GUARDAR CAMBIOS <i class="fas fa-check-circle ms-2"></i>
            </button>
        </div>
    </form>
</div>

<style>
    :root {
        --gold: #D4AF37;
        --gold-light: #fdf8e6;
        --sax-dark: #121212;
    }

    .bg-white-soft { background-color: #f8fafc; }
    .bg-light-soft { background-color: #f1f5f9; }
    .bg-white-10 { background: rgba(255,255,255,0.05); }
    .text-gold { color: var(--gold) !important; }

    /* Estilo Marcas Refinado */
    .sax-title { font-size: 1.4rem; font-weight: 900; color: var(--sax-dark); }
    .sax-divider-gold { width: 45px; height: 4px; background: var(--gold); margin: 8px 0; border-radius: 2px; }
    .letter-spacing-2 { letter-spacing: 2px; }
    .letter-spacing-1 { letter-spacing: 1px; }

    .sax-premium-card { background: #fff; border-radius: 20px; border: 1px solid #eef2f7; }
    
    .sax-label { font-size: 0.85rem; font-weight: 800; }
    .sax-form-label { font-size: 0.75rem; font-weight: 700; color: #64748b; margin-bottom: 8px; text-uppercase: uppercase; }

    /* Inputs Estilo Marcas */
    .sax-input {
        border: 1px solid #e2e8f0;
        padding: 12px 16px;
        border-radius: 12px;
        font-size: 0.9rem;
        transition: all 0.2s;
    }
    .sax-input:focus {
        border-color: var(--gold);
        box-shadow: 0 4px 12px rgba(212, 175, 55, 0.1);
        outline: none;
    }

    /* Zonas de Upload (Igual a Marcas) */
    .asset-upload-zone {
        border: 2px dashed #e2e8f0;
        border-radius: 15px;
        padding: 15px;
        text-align: center;
        background: #fbfbfb;
        position: relative;
        transition: all 0.3s ease;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }
    .asset-upload-zone:hover { border-color: var(--gold); background: #fff; }
    .sax-input-file { position: absolute; top: 0; left: 0; width: 100%; height: 100%; opacity: 0; cursor: pointer; z-index: 5; }

    /* Galeria Preview */
    .gallery-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(80px, 1fr)); gap: 10px; }
    .gallery-preview-item { aspect-ratio: 1; border-radius: 10px; overflow: hidden; border: 2px solid #fff; }
    .gallery-preview-item img { width: 100%; height: 100%; object-fit: cover; }

    /* Badges & Botões */
    .badge-success-soft { background: #ecfdf5; color: #10b981; padding: 4px 10px; border-radius: 6px; font-size: 0.65rem; font-weight: 900; }
    .btn-dark-gold { background: var(--sax-dark); color: var(--gold); border: none; font-size: 0.8rem; letter-spacing: 1px; }
    .btn-dark-gold:hover { background: #000; color: #fff; transform: translateY(-2px); }
    .btn-back-minimal { color: #94a3b8; font-weight: 700; font-size: 0.7rem; text-decoration: none; display: flex; align-items: center; }

    .sticky-header { position: sticky; top: 0; z-index: 1020; backdrop-filter: blur(10px); background: rgba(255,255,255,0.9) !important; }
    .x-small { font-size: 0.65rem; }
    .border-primary-soft { border-color: #dbeafe !important; }

    @media (max-width: 768px) {
        .sax-admin-container { padding-bottom: 100px; }
        .sticky-header { border-radius: 0; }
    }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
        // Preview Real-time (SAX Standard)
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