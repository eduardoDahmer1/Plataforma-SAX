<div class="card shadow-sm mb-4">
    <div class="card-header">
        <h4 class="mb-0"><i class="fas fa-blog me-2"></i>Editar Blog</h4>
    </div>
    <div class="card-body">
        <div class="row g-3">
            
            {{-- Título --}}
            <div class="col-md-6">
                <label for="title" class="form-label"><i class="fas fa-heading me-1"></i>Título</label>
                <input type="text" id="title" name="title" class="form-control form-control-lg"
                       value="{{ old('title', $blog->title ?? '') }}" placeholder="Digite o título do blog">
            </div>

            {{-- Subtítulo --}}
            <div class="col-md-6">
                <label for="subtitle" class="form-label"><i class="fas fa-italic me-1"></i>Subtítulo</label>
                <input type="text" id="subtitle" name="subtitle" class="form-control"
                       value="{{ old('subtitle', $blog->subtitle ?? '') }}" placeholder="Digite o subtítulo">
            </div>

            {{-- Categoria --}}
            <div class="col-md-6">
                <label for="category_id" class="form-label"><i class="fas fa-list me-1"></i>Categoria</label>
                <select id="category_id" name="category_id" class="form-select">
                    <option value="">-- Selecione --</option>
                    @foreach($categories as $cat)
                        <option value="{{ $cat->id }}"
                            {{ old('category_id', $blog->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                            {{ $cat->name }}
                        </option>
                    @endforeach
                </select>
            </div>

            {{-- Slug --}}
            <div class="col-md-6">
                <label for="slug" class="form-label"><i class="fas fa-link me-1"></i>Slug</label>
                <input type="text" id="slug" name="slug" class="form-control"
                       value="{{ old('slug', $blog->slug ?? '') }}" placeholder="Slug automático">
            </div>

            {{-- Imagem --}}
            <div class="col-12">
                <label for="image" class="form-label"><i class="fas fa-image me-1"></i>Imagem</label>
                <input type="file" id="image" name="image" class="form-control">
                @if (!empty($blog->image))
                    <div class="mt-3 text-center">
                        <img src="{{ asset('storage/' . $blog->image) }}" 
                             class="img-fluid rounded shadow-sm" 
                             style="max-height: 300px; object-fit: contain;">
                    </div>
                @endif
            </div>

            {{-- Conteúdo --}}
            <div class="col-12">
                <label for="editor-blog" class="form-label"><i class="fas fa-file-alt me-1"></i>Conteúdo</label>
                <textarea id="editor-blog" name="content" class="form-control" rows="8"
                          placeholder="Escreva o conteúdo do blog">{{ old('content', $blog->content ?? '') }}</textarea>
            </div>

            {{-- Publicado em --}}
            <div class="col-md-6">
                <label for="published_at" class="form-label"><i class="fas fa-calendar-alt me-1"></i>Publicado em</label>
                <input type="datetime-local" id="published_at" name="published_at" class="form-control"
                    value="{{ old('published_at', isset($blog->published_at) ? $blog->published_at->format('Y-m-d\TH:i') : '') }}">
            </div>

            {{-- Ativo --}}
            <div class="col-md-6 d-flex align-items-center">
                <div class="form-check form-switch">
                    <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                        {{ old('is_active', $blog->is_active ?? true) ? 'checked' : '' }}>
                    <label class="form-check-label ms-2" for="is_active">Ativo</label>
                </div>
            </div>
        </div>

        {{-- Botão Salvar --}}
        <div class="mt-4">
            <button type="submit" class="btn btn-success btn-lg w-100 w-md-auto">
                <i class="fas fa-save me-1"></i>Salvar
            </button>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const titleInput = document.getElementById('title');
    const slugInput = document.getElementById('slug');

    titleInput.addEventListener("input", function () {
        let slug = this.value
            .toLowerCase()
            .normalize("NFD")
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9]+/g, "-")
            .replace(/(^-|-$)+/g, "");
        slugInput.value = slug;
    });
});
</script>
