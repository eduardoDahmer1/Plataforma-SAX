@php
    $blog = $blog ?? null;
    $isEdit = $blog !== null;
    $metaDescription = old('meta_description', $blog?->meta_description ?? '');
@endphp

<x-admin.sticky-header
    :title="$isEdit ? __('messages.editar_artigo_titulo') : __('messages.novo_artigo_titulo')"
    :cancelRoute="route('admin.blogs.index')"
    :submitLabel="__('messages.guardar_articulo_btn')"
    :updatedAt="$blog?->updated_at ? __('messages.ultima_atualizacao_label') . ': ' . $blog->updated_at->format('d/m/Y H:i') : null" />

<x-admin.alert />

<div class="d-flex flex-column gap-4">

    {{-- 01. IDENTIFICAÇÃO --}}
    <div class="sax-premium-card shadow-sm overflow-hidden">
        <x-admin.block-header icon="fas fa-heading" number="01"
            :title="__('messages.configuracao_artigo')"
            :subtitle="__('messages.identificacao_artigo_desc')" />

        <div class="p-4">
            <div class="row g-4">
                <div class="col-12">
                    <label for="title" class="sax-label">{{ __('messages.titulo_blog_label') }}</label>
                    <input type="text" id="title" name="title" class="form-control sax-input fw-bold fs-5"
                           value="{{ old('title', $blog?->title ?? '') }}" placeholder="Ex: Las Tendencias de Verano en SAX">
                </div>

                <div class="col-12">
                    <label for="subtitle" class="sax-label">{{ __('messages.subtitulo_label') }}</label>
                    <textarea id="subtitle" name="subtitle" class="form-control sax-input italic" rows="2"
                              placeholder="{{ __('messages.subtitulo_placeholder') }}">{{ old('subtitle', $blog?->subtitle ?? '') }}</textarea>
                </div>

                <div class="col-md-4">
                    <label for="category_id" class="sax-label">{{ __('messages.categoria_label') }}</label>
                    <select id="category_id" name="category_id" class="form-select sax-input">
                        <option value="">{{ __('messages.selecionar_placeholder') }}</option>
                        @foreach($categories as $cat)
                            <option value="{{ $cat->id }}"
                                {{ old('category_id', $blog?->category_id ?? '') == $cat->id ? 'selected' : '' }}>
                                {{ strtoupper($cat->name) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-4">
                    <label for="author" class="sax-label">{{ __('messages.autor_label') }}</label>
                    <input type="text" id="author" name="author" class="form-control sax-input"
                           value="{{ old('author', $blog?->author ?? 'SAX Editor') }}">
                </div>
                <div class="col-md-4">
                    <label for="slug" class="sax-label">{{ __('messages.slug_label') }}</label>
                    <input type="text" id="slug" name="slug" class="form-control sax-input text-muted bg-light"
                           value="{{ old('slug', $blog?->slug ?? '') }}" readonly>
                    <small class="text-muted x-small mt-1 d-block">{{ __('messages.slug_gerado_automaticamente') }}</small>
                </div>
            </div>
        </div>
    </div>

    {{-- 02. IMAGEM DE CAPA --}}
    <div class="sax-premium-card shadow-sm overflow-hidden">
        <x-admin.block-header icon="fas fa-image" number="02"
            :title="__('messages.imagem_portada_label')"
            :subtitle="__('messages.imagem_capa_desc')" />

        <div class="p-4">
            <x-admin.image-upload name="image" previewId="blogCoverPreview"
                :currentImage="$blog?->image ? Storage::url($blog->image) : null"
                placeholder="https://placehold.co/1200x400/1a1a1a/ffffff?text=Imagem+de+Capa"
                height="220px" maxSize="8MB" />

            <label for="image_caption" class="sax-label mt-3">{{ __('messages.creditos_imagem_placeholder') }}</label>
            <input type="text" id="image_caption" name="image_caption" class="form-control sax-input-sm"
                   placeholder="{{ __('messages.creditos_imagem_placeholder') }}"
                   value="{{ old('image_caption', $blog?->image_caption ?? '') }}">
        </div>
    </div>

    {{-- 03. CORPO DO ARTIGO --}}
    <div class="sax-premium-card shadow-sm overflow-hidden">
        <x-admin.block-header icon="fas fa-align-left" number="03"
            :title="__('messages.corpo_conteudo_label')"
            :subtitle="__('messages.corpo_conteudo_desc')" />

        <div class="p-4">
            <div class="editor-rich-wrapper">
                <textarea id="editor-blog" name="content"
                          data-upload-url="{{ route('admin.blogs.upload-image') }}">{{ old('content', $blog?->content ?? '') }}</textarea>
            </div>
        </div>
    </div>

    {{-- 04. GALERIA DE IMAGENS --}}
    <div class="sax-premium-card shadow-sm overflow-hidden">
        <x-admin.block-header icon="fas fa-images" number="04"
            :title="__('messages.galeria_imagens_label')"
            :subtitle="__('messages.galeria_imagens_desc')" />

        <div class="p-4">
            <div id="blogGaleriaPreview" class="gallery-preview-grid mb-3">
                @foreach($blog?->gallery ?? [] as $index => $foto)
                    <div class="gallery-preview-item is-existing shadow-sm border">
                        <img src="{{ Storage::url($foto) }}" class="w-100 h-100 object-fit-cover">
                        <input type="hidden" name="gallery_actual[]" value="{{ $foto }}">
                        <button type="button" class="gallery-remove-btn">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                @endforeach
            </div>

            <div class="upload-zone" id="blogGaleriaZone">
                <input type="file" id="blogGalleryInput" name="gallery[]" class="upload-input" multiple accept="image/*">
                <i class="fas fa-images mb-2 opacity-25 fa-lg"></i>
                <p class="x-small fw-bold m-0">{{ __('messages.click_or_drag_images') }}</p>
                <p class="x-small text-muted m-0">{{ __('messages.image_formats_max_each') }}</p>
            </div>

            <p class="x-small text-muted mt-2 mb-0">
                <i class="fas fa-info-circle me-1"></i>
                <span id="blogGaleriaCount">{{ count($blog?->gallery ?? []) }}</span>/10 {{ __('messages.loaded_images_count') }}
            </p>
        </div>
    </div>

    {{-- 05. SEO & PUBLICAÇÃO --}}
    <div class="sax-premium-card shadow-sm overflow-hidden">
        <x-admin.block-header icon="fas fa-globe" number="05"
            :title="__('messages.seo_publicacao_label')"
            :subtitle="__('messages.seo_publicacao_desc')" />

        <div class="p-4">
            <div class="row g-4">
                <div class="col-md-8">
                    <label for="meta_description" class="sax-label">SEO: Meta Description</label>
                    <textarea id="meta_description" name="meta_description" class="form-control sax-input" rows="3"
                              maxlength="160">{{ $metaDescription }}</textarea>
                    <small class="text-muted x-small mt-1 d-block">
                        <span id="metaDescCount">{{ strlen($metaDescription) }}</span>/160
                    </small>
                </div>

                <div class="col-md-4">
                    <label class="sax-label">{{ __('messages.visibilidade_label') }}</label>
                    <div class="form-check form-switch mb-2">
                        <input type="checkbox" id="is_active" name="is_active" value="1" class="form-check-input"
                            {{ old('is_active', $blog?->is_active ?? true) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold x-small" for="is_active">{{ __('messages.status_publicado') }}</label>
                    </div>
                    <div class="form-check form-switch mb-3">
                        <input type="checkbox" id="featured" name="featured" value="1" class="form-check-input"
                            {{ old('featured', $blog?->featured ?? false) ? 'checked' : '' }}>
                        <label class="form-check-label fw-bold x-small text-warning" for="featured">{{ __('messages.destacado_label') }}</label>
                    </div>
                    <input type="datetime-local" name="published_at" class="form-control sax-input"
                           value="{{ old('published_at', isset($blog->published_at) ? (is_string($blog->published_at) ? date('Y-m-d\TH:i', strtotime($blog->published_at)) : $blog->published_at->format('Y-m-d\TH:i')) : '') }}">
                </div>
            </div>
        </div>
    </div>

    <x-admin.form-actions :cancelRoute="route('admin.blogs.index')" :submitLabel="__('messages.guardar_articulo_btn')" />
</div>
