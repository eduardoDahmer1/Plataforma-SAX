<div class="card border shadow-none mb-4 rounded-0">
    <div class="card-header bg-white py-3 border-bottom">
        <h5 class="mb-0 text-uppercase tracking-wider fw-bold x-small text-dark">
            Configuración del Artículo
        </h5>
    </div>
    <div class="card-body p-4">
        <div class="row g-4">
            
            {{-- Título --}}
            <div class="col-md-6">
                <label for="title" class="sax-label">Título del Blog</label>
                <input type="text" id="title" name="title" class="form-control sax-input fw-bold"
                       value="{{ old('title', $blog->title ?? '') }}" placeholder="Digite o título...">
            </div>

            {{-- Subtítulo --}}
            <div class="col-md-6">
                <label for="subtitle" class="sax-label">Subtítulo</label>
                <input type="text" id="subtitle" name="subtitle" class="form-control sax-input italic"
                       value="{{ old('subtitle', $blog->subtitle ?? '') }}" placeholder="Digite o subtítulo...">
            </div>

            {{-- Categoria --}}
            <div class="col-md-6">
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

            {{-- Slug --}}
            <div class="col-md-6">
                <label for="slug" class="sax-label">Slug (URL Automática)</label>
                <input type="text" id="slug" name="slug" class="form-control sax-input text-muted"
                       value="{{ old('slug', $blog->slug ?? '') }}" readonly>
            </div>

            {{-- Imagem --}}
            <div class="col-12">
                <label for="image" class="sax-label">Imagen de Portada</label>
                <div class="border p-3 bg-light-subtle">
                    <input type="file" id="image" name="image" class="form-control rounded-0 border-0 bg-transparent">
                    
                    @if (!empty($blog->image))
                        <div class="mt-3">
                            <img src="{{ asset('storage/' . $blog->image) }}" 
                                 class="img-fluid border shadow-sm" 
                                 style="max-height: 200px; object-fit: contain;">
                        </div>
                    @endif
                </div>
            </div>

            {{-- Conteúdo --}}
            <div class="col-12">
                <label for="editor-blog" class="sax-label">Cuerpo del Contenido</label>
                <textarea id="editor-blog" name="content" class="form-control sax-input" rows="10"
                          placeholder="Escreva aqui...">{{ old('content', $blog->content ?? '') }}</textarea>
            </div>

            {{-- Publicado em --}}
            <div class="col-md-6">
                <label for="published_at" class="sax-label">Fecha de Publicación</label>
                <input type="datetime-local" id="published_at" name="published_at" class="form-control sax-input"
                    value="{{ old('published_at', isset($blog->published_at) ? $blog->published_at->format('Y-m-d\TH:i') : '') }}">
            </div>

            {{-- Ativo --}}
            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch pt-3">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active', $blog->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label ms-2 text-uppercase fw-bold x-small tracking-wider" for="is_active">Artículo Activo</label>
                </div>
            </div>
        </div>

        {{-- Botão Salvar --}}
        <div class="mt-5 border-top pt-4">
            <button type="submit" class="btn btn-dark rounded-0 px-5 text-uppercase fw-bold x-small tracking-wider py-2">
                Guardar Artículo
            </button>
        </div>
    </div>
</div>

<style>
    /* Tipografia e Estilo Minimalista Seguro */
    .tracking-wider { letter-spacing: 0.12em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    .bg-light-subtle { background-color: #fafafa !important; }
    
    /* Labels Técnicas */
    .sax-label {
        font-size: 0.65rem;
        font-weight: 800;
        color: #888;
        text-transform: uppercase;
        margin-bottom: 8px;
        display: block;
        letter-spacing: 0.05em;
    }

    /* Inputs que respeitam o container */
    .sax-input {
        border-radius: 0 !important;
        border: 1px solid #e0e0e0 !important;
        box-shadow: none !important;
        font-size: 0.9rem;
        padding: 10px 12px;
    }
    
    .sax-input:focus {
        border-color: #000 !important;
        background-color: #fff;
    }

    /* Estilo do Switch */
    .form-check-input:checked {
        background-color: #000 !important;
        border-color: #000 !important;
    }
</style>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    if(titleInput && slugInput) {
        titleInput.addEventListener("input", function () {
            let slug = this.value
                .toLowerCase()
                .normalize("NFD")
                .replace(/[\u0300-\u036f]/g, "")
                .replace(/[^a-z0-9]+/g, "-")
                .replace(/(^-|-$)+/g, "");
            slugInput.value = slug;
        });
    }
});
</script>