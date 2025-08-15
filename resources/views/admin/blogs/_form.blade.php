<div class="mb-3">
    <label>Título</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $blog->title ?? '') }}">
</div>

<div class="mb-3">
    <label>Subtítulo</label>
    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $blog->subtitle ?? '') }}">
</div>

<div class="mb-3">
    <label>Categoria</label>
    <select name="category_id" class="form-control">
        <option value="">-- Selecione --</option>
        @foreach($categories as $cat)
            <option value="{{ $cat->id }}" 
                {{ old('category_id', $blog->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                {{ $cat->name }}
            </option>
        @endforeach
    </select>
</div>

<div class="mb-3">
    <label>Slug</label>
    <input type="text" name="slug" class="form-control" value="{{ old('slug', $blog->slug ?? '') }}">
</div>

<div class="mb-3">
    <label>Imagem</label>
    <input type="file" name="image" class="form-control">
    @if (!empty($blog->image))
        <img src="{{ asset('storage/' . $blog->image) }}" width="100" class="mt-2">
    @endif
</div>

<div class="mb-3">
    <label>Conteúdo</label>
    <textarea id="editor-blog" name="content" class="form-control" rows="5">{{ old('content', $blog->content ?? '') }}</textarea>
</div>

<div class="mb-3">
    <label>Publicado em</label>
    <input type="datetime-local" name="published_at" class="form-control"
        value="{{ old('published_at', isset($blog->published_at) ? $blog->published_at->format('Y-m-d\TH:i') : '') }}">
</div>

<div class="form-check mb-3">
    <input type="checkbox" name="is_active" value="1" class="form-check-input"
        {{ old('is_active', $blog->is_active ?? true) ? 'checked' : '' }}>
    <label class="form-check-label">Ativo</label>
</div>

<button type="submit" class="btn btn-primary">Salvar</button>

<script>
document.addEventListener("DOMContentLoaded", function () {
    const titleInput = document.querySelector('input[name="title"]');
    const slugInput = document.querySelector('input[name="slug"]');

    titleInput.addEventListener("input", function () {
        let slug = this.value
            .toLowerCase()
            .normalize("NFD") // remove acentos
            .replace(/[\u0300-\u036f]/g, "")
            .replace(/[^a-z0-9]+/g, "-") // troca espaço e símbolos por -
            .replace(/(^-|-$)+/g, ""); // remove traços no início/fim
        slugInput.value = slug;
    });
});
</script>
