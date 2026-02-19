@extends('layout.admin')

@section('content')
<div class="sax-admin-container py-2 bg-white-soft">
    <form action="{{ route('admin.palace.update', $palace->id) }}" method="POST" enctype="multipart/form-data" id="formPalace">
        @csrf
        @method('PUT')

        {{-- Header Estilo Dashboard Marcas --}}
        <div class="dashboard-header d-flex justify-content-between align-items-center mb-5 sticky-header px-4 py-3 bg-white border-bottom shadow-sm">
            <div>
                <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Editar SAX Palace</h2>
                <div class="sax-divider-gold"></div>
                <span class="text-muted x-small">Última atualização: {{ $palace->updated_at->format('d/m H:i') }}</span>
            </div>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.palace.index') }}" class="btn-back-minimal me-3 d-none d-md-flex align-items-center">
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

                {{-- 2. BAR & BODEGA (IMAGENS QUE FALTAVAM) --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">02. Bar & Bodega (Fotos)</h6>
                    <div class="row mb-4">
                        <div class="col-md-6">
                            <label class="sax-form-label">Título do Bar</label>
                            <input type="text" name="bar_titulo" class="form-control sax-input mb-3" value="{{ $palace->bar_titulo }}">
                            <label class="sax-form-label">Descrição do Bar</label>
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
                                        <input type="file" name="bar_imagem_{{$i}}" class="sax-input-file img-input" data-preview="preview-bar-{{$i}}">
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
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">03. Gastronomía & Menús</h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-warning text-uppercase">Café da Manhã</label>
                            <textarea name="gastronomia_cafe_desc" class="form-control sax-input small" rows="4">{{ $palace->gastronomia_cafe_desc }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-primary text-uppercase">Almuerzo</label>
                            <textarea name="gastronomia_almoco_desc" class="form-control sax-input small" rows="4">{{ $palace->gastronomia_almoco_desc }}</textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="x-small fw-bold text-danger text-uppercase">Cena</label>
                            <textarea name="gastronomia_jantar_desc" class="form-control sax-input small" rows="4">{{ $palace->gastronomia_jantar_desc }}</textarea>
                        </div>
                    </div>
                </div>

                {{-- 4. GALERIA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-4 text-dark border-bottom pb-2 text-uppercase letter-spacing-1">04. Galería de Eventos</h6>
                    <div class="asset-upload-zone mb-3" style="min-height: 120px;">
                        <i class="fas fa-images mb-2 opacity-25 fa-2x"></i>
                        <input type="file" name="eventos_galeria[]" class="sax-input-file" multiple>
                        <p class="sax-form-label m-0">Arrastre o haga clic para subir nuevas fotos</p>
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
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">Imagen Hero</h6>
                    <div class="preview-box mb-3 shadow-sm border rounded overflow-hidden">
                        <img id="preview-hero" src="{{ $palace->hero_imagem ? asset('storage/'.$palace->hero_imagem) : 'https://placehold.co/600x400' }}" class="img-fluid w-100">
                    </div>
                    <div class="asset-upload-zone py-3">
                        <input type="file" name="hero_imagem" class="sax-input-file img-input" data-preview="preview-hero">
                        <p class="x-small fw-bold m-0"><i class="fas fa-sync-alt me-1"></i> CAMBIAR IMAGEN</p>
                    </div>
                </div>

                {{-- EXPERIÊNCIA TEMÁTICA --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm bg-dark text-white">
                    <h6 class="sax-label mb-3 text-gold border-bottom border-secondary pb-2 text-uppercase letter-spacing-1">Noche Árabe</h6>
                    <div class="mb-3">
                        <label class="x-small text-uppercase opacity-7 fw-bold">Título</label>
                        <input type="text" name="tematica_titulo" class="form-control sax-input bg-transparent text-white border-secondary small" value="{{ $palace->tematica_titulo }}">
                    </div>
                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="x-small text-uppercase opacity-7 fw-bold">Precio</label>
                            <input type="text" name="tematica_preco" class="form-control sax-input bg-transparent text-gold border-secondary small" value="{{ $palace->tematica_preco }}">
                        </div>
                        <div class="col-6">
                             <label class="x-small text-uppercase opacity-7 fw-bold">Etiqueta</label>
                            <input type="text" name="tematica_tag" class="form-control sax-input bg-transparent text-white border-secondary small" value="{{ $palace->tematica_tag }}">
                        </div>
                    </div>
                    <div class="asset-upload-zone py-3 bg-white-10 border-secondary mb-3">
                        <input type="file" name="tematica_imagem" class="sax-input-file img-input" data-preview="preview-tematica">
                        <p class="x-small fw-bold m-0 text-white">CAMBIAR FOTO</p>
                    </div>
                    <div class="preview-box-sm rounded overflow-hidden" style="height: 120px;">
                        <img id="preview-tematica" src="{{ $palace->tematica_imagem ? asset('storage/'.$palace->tematica_imagem) : 'https://placehold.co/600x400' }}" class="w-100 h-100 object-fit-cover">
                    </div>
                </div>

                {{-- HORÁRIOS (FALTAVA NO FORM) --}}
                <div class="sax-premium-card p-4 mb-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">Horarios de Atención</h6>
                    <div class="mb-2">
                        <label class="x-small fw-bold">Lunes</label>
                        <input type="text" name="contato_horario_segunda" class="form-control sax-input py-1" value="{{ $palace->contato_horario_segunda }}">
                    </div>
                    <div class="mb-2">
                        <label class="x-small fw-bold">Martes a Sábado</label>
                        <input type="text" name="contato_horario_sabado" class="form-control sax-input py-1" value="{{ $palace->contato_horario_sabado }}">
                    </div>
                    <div class="mb-0">
                        <label class="x-small fw-bold">Domingo</label>
                        <input type="text" name="contato_horario_domingo" class="form-control sax-input py-1" value="{{ $palace->contato_horario_domingo }}">
                    </div>
                </div>

                {{-- CONTATO --}}
                <div class="sax-premium-card p-4 shadow-sm">
                    <h6 class="sax-label mb-3 text-dark text-uppercase letter-spacing-1">Ubicación & Mapa</h6>
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
    :root { --gold: #D4AF37; --sax-dark: #121212; }
    .bg-white-soft { background-color: #f8fafc; }
    .bg-white-10 { background: rgba(255,255,255,0.05); }
    .text-gold { color: var(--gold) !important; }
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
    .gallery-preview-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(60px, 1fr)); gap: 8px; }
    .gallery-preview-item { aspect-ratio: 1; border-radius: 8px; overflow: hidden; }
    .gallery-preview-item img { width: 100%; height: 100%; object-fit: cover; }
    .btn-dark-gold { background: var(--sax-dark); color: var(--gold); border: none; font-size: 0.8rem; letter-spacing: 1px; }
    .sticky-header { position: sticky; top: 0; z-index: 1020; }
    .x-small { font-size: 0.65rem; }
</style>
@endsection

@push('scripts')
<script>
    $(document).ready(function() {
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