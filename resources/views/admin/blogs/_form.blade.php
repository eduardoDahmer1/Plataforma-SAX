<div class="mb-3">
    <label>Título</label>
    <input type="text" name="title" class="form-control" value="{{ old('title', $blog->title ?? '') }}">
</div>

<div class="mb-3">
    <label>Subtítulo</label>
    <input type="text" name="subtitle" class="form-control" value="{{ old('subtitle', $blog->subtitle ?? '') }}">
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
    <textarea name="content" class="form-control" rows="5">{{ old('content', $blog->content ?? '') }}</textarea>
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
