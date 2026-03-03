<div class="card border shadow-none mb-4 rounded-0">
    <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
        <h5 class="mb-0 text-uppercase tracking-wider fw-bold x-small text-dark">
            Configuración del Artículo
        </h5>
        <span class="badge bg-dark rounded-0 x-small tracking-wider">MODO EDITORIAL</span>
    </div>
    
    <div class="card-body p-4">
        <div class="row g-4">
            {{-- Título Principal --}}
            <div class="col-md-8">
                <label for="title" class="sax-label">Título del Blog</label>
                <input type="text" id="title" name="title" class="form-control sax-input fw-bold fs-5"
                       value="{{ old('title', $blog->title ?? '') }}" placeholder="Ex: Las Tendencias de Verano en SAX">
            </div>

            {{-- Tempo de Leitura --}}
            <div class="col-md-4">
                <label for="read_time" class="sax-label">Tempo de Leitura (Minutos)</label>
                <div class="input-group">
                    <input type="number" id="read_time" name="read_time" class="form-control sax-input"
                           value="{{ old('read_time', $blog->read_time ?? '') }}" placeholder="Ex: 5">
                    <span class="input-group-text bg-white border-start-0 sax-input-icon"><i class="far fa-clock"></i></span>
                </div>
            </div>

            {{-- Subtítulo --}}
            <div class="col-12">
                <label for="subtitle" class="sax-label">Subtítulo / Resumen Corto</label>
                <textarea id="subtitle" name="subtitle" class="form-control sax-input italic" rows="2"
                          placeholder="Una breve introducción...">{{ old('subtitle', $blog->subtitle ?? '') }}</textarea>
            </div>

            {{-- Categoria, Autor e Slug --}}
            <div class="col-md-4">
                <label for="category_id" class="sax-label">Categoría</label>
                <select id="category_id" name="category_id" class="form-select sax-input">
                    <option value="">-- Seleccionar --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('category_id', $blog->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                            {{ strtoupper($cat->name) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-4">
                <label for="author" class="sax-label">Autor</label>
                <input type="text" id="author" name="author" class="form-control sax-input"
                       value="{{ old('author', $blog->author ?? 'SAX Editor') }}">
            </div>
            <div class="col-md-4">
                <label for="slug" class="sax-label">Slug (URL)</label>
                <input type="text" id="slug" name="slug" class="form-control sax-input text-muted bg-light"
                       value="{{ old('slug', $blog->slug ?? '') }}" readonly>
            </div>

            {{-- Imagem de Portada --}}
            <div class="col-12">
                <label class="sax-label">Imagen de Portada</label>
                <div class="sax-upload-container p-3 border">
                    <input type="file" id="image" name="image" class="form-control mb-2 rounded-0 shadow-none">
                    <input type="text" name="image_caption" class="form-control sax-input-sm" 
                           placeholder="Créditos de la imagen" 
                           value="{{ old('image_caption', $blog->image_caption ?? '') }}">
                    
                    @if (!empty($blog->image))
                        <div class="mt-3">
                            <img src="{{ asset('storage/' . $blog->image) }}" class="img-fluid border" style="max-height: 150px;">
                        </div>
                    @endif
                </div>
            </div>

            {{-- O EDITOR RICO (ESTILO IGUAL À IMAGEM) --}}
            <div class="col-12">
                <label for="editor-blog" class="sax-label">Cuerpo del Contenido</label>
                <div class="editor-rich-wrapper">
                    <textarea id="editor-blog" name="content">{{ old('content', $blog->content ?? '') }}</textarea>
                </div>
            </div>

            {{-- SEO & Status --}}
            <div class="col-md-8">
                <label for="meta_description" class="sax-label text-primary">SEO: Meta Description</label>
                <textarea id="meta_description" name="meta_description" class="form-control sax-input" rows="3"
                          maxlength="160">{{ old('meta_description', $blog->meta_description ?? '') }}</textarea>
            </div>

            <div class="col-md-4">
                <label class="sax-label">Visibilidad</label>
                <div class="form-check form-switch mb-2">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active', $blog->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold x-small" for="is_active">PUBLICADO</label>
                </div>
                <div class="form-check form-switch mb-3">
                    <input type="checkbox" id="featured" name="featured" value="1" class="form-check-input"
                        {{ old('featured', $blog->featured ?? false) ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold x-small text-warning" for="featured">★ DESTACADO</label>
                </div>
                <input type="datetime-local" name="published_at" class="form-control sax-input"
                       value="{{ old('published_at', isset($blog->published_at) ? (is_string($blog->published_at) ? date('Y-m-d\TH:i', strtotime($blog->published_at)) : $blog->published_at->format('Y-m-d\TH:i')) : '') }}">
            </div>
        </div>

        <div class="mt-5 border-top pt-4 text-end">
            <button type="submit" class="btn btn-dark rounded-0 px-5 text-uppercase fw-bold x-small py-3 shadow-sm">
                Guardar Artículo
            </button>
        </div>
    </div>
</div>

{{-- Script do TinyMCE sem o aviso de erro --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/tinymce/5.10.7/tinymce.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    tinymce.init({
        selector: '#editor-blog',
        height: 450,
        menubar: false,
        branding: false, // Remove o "Powered by TinyMCE"
        statusbar: true,
        plugins: [
            'advlist autolink lists link image charmap print preview anchor',
            'searchreplace visualblocks code fullscreen',
            'insertdatetime media table paste code help wordcount'
        ],
        // CONFIGURAÇÃO DOS BOTÕES IGUAL À IMAGEM ENVIADA
        toolbar: 'formatselect | bold italic | forecolor backcolor | alignleft aligncenter alignright alignjustify | table | link image | code fullscreen',
        
        // Estilo interno para o texto digitado
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, Helvetica, Arial, sans-serif; font-size: 14px; }',
        
        setup: function (editor) {
            editor.on('change', function () {
                editor.save(); 
            });
        }
    });

    // Lógica do Slug
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');
    if(titleInput && slugInput) {
        titleInput.addEventListener("input", function () {
            let slug = this.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").replace(/[^a-z0-9]+/g, "-").replace(/(^-|-$)+/g, "");
            slugInput.value = slug;
        });
    }
});
</script>

<style>
    .tracking-wider { letter-spacing: 0.12em; }
    .x-small { font-size: 0.7rem; }
    .italic { font-style: italic; }
    .sax-label { font-size: 0.7rem; font-weight: 800; color: #444; text-transform: uppercase; margin-bottom: 8px; display: block; }
    .sax-input { border-radius: 0 !important; border: 1px solid #ddd !important; padding: 12px; }
    .sax-input-sm { font-size: 0.75rem; border-radius: 0; border: 1px solid #eee; padding: 5px 10px; }
    
    /* Remove sombras chatas do bootstrap nos inputs */
    .form-control:focus, .form-select:focus { box-shadow: none !important; border-color: #000 !important; }
    
    /* Estilo do Switch */
    .form-check-input:checked { background-color: #000 !important; border-color: #000 !important; }

    /* Ajuste fino para o Editor ficar idêntico ao da imagem */
    .tox-tinymce {
        border-radius: 0 !important;
        border: 1px solid #ddd !important;
    }
    .tox .tox-toolbar__group { padding: 0 5px !important; }
</style>