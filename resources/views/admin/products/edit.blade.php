@extends('layout.admin')

@section('content')
    @php
        $galleryImages = [];
        if (isset($item->gallery)) {
            $galleryImages = is_array($item->gallery) ? $item->gallery : json_decode($item->gallery, true);
            $galleryImages = is_array($galleryImages) ? array_values($galleryImages) : [];
        }
        $translationsByLocale = $translationsByLocale ?? collect();
        $ptTranslation = $translationsByLocale->get('pt-br');
        $esTranslation = $translationsByLocale->get('es');
        $enTranslation = $translationsByLocale->get('en');
    @endphp
    @php
        $type = $type ?? 'product';
    @endphp

    <x-admin.card>
    <x-admin.page-header title="Editar Produto" description="Edite as informações do produto no catálogo"></x-admin.page-header>

        <div id="productEditFeedback" class="d-none"></div>

        <form id="productEditForm" class="product-edit-form" action="{{ isset($item) ? route('admin.products.update', $item->id) : route('admin.products.store') }}"
            method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            <input type="hidden" name="return_to" value="{{ request('return_to') }}">

            <div class="row g-4 product-edit-grid">
                @if ($type === 'product')
                    <div class="col-md-6">
                        <label for="sku" class="form-label"><i class="fas fa-barcode me-1"></i>SKU</label>
                        <input readonly type="text" id="sku" name="sku" class="form-control"
                            value="{{ old('sku', $item->sku ?? '') }}">
                    </div>

                    <div class="col-md-6">
                        <label for="external_name" class="form-label"><i class="fas fa-tag me-1"></i>Nome original (Sistema GO)</label>
                        <input readonly type="text" id="external_name" name="external_name" class="form-control"
                            value="{{ old('external_name', $item->external_name ?? '') }}">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label"><i class="fas fa-pencil-alt me-1"></i>Nome comercial do produto</label>
                        
                        {{-- products.name é o nome comercial canônico em PT. A tradução antiga
                             não pode sobrescrevê-lo ao abrir e salvar o formulário. --}}
                        <input type="hidden" id="real-name-pt" name="translate[pt-br][name]" value="{{ old('translate.pt-br.name', $item->name ?: ($ptTranslation->name ?? ($item->external_name ?? ''))) }}">
                        <input type="hidden" id="real-name-es" name="translate[es][name]" value="{{ old('translate.es.name', $esTranslation->name ?? '') }}">
                        <input type="hidden" id="real-name-en" name="translate[en][name]" value="{{ old('translate.en.name', $enTranslation->name ?? '') }}">

                        <div>
                            <input type="text" id="visual-name-input" class="form-control" value="">
                        </div>

                        <div class="mt-1">
                            <span class="small text-muted d-block mb-1">Este é o nome exibido no catálogo e na loja.</span>
                            <span class="small text-muted me-2">Editar idioma:</span>
                            <a href="javascript:void(0)" class="badge bg-primary name-lang-btn text-decoration-none" data-lang="pt" onclick="switchLanguage('name', 'pt', this)">PT</a>
                            <a href="javascript:void(0)" class="badge bg-secondary name-lang-btn text-decoration-none" data-lang="es" onclick="switchLanguage('name', 'es', this)">ES</a>
                            <a href="javascript:void(0)" class="badge bg-secondary name-lang-btn text-decoration-none" data-lang="en" onclick="switchLanguage('name', 'en', this)">EN</a>
                            <button type="button" id="translate-name-btn" class="btn btn-outline-primary btn-sm ms-2"
                                    onclick="translateProductField('name')">
                                <i class="fas fa-language me-1"></i>Detectar e traduzir
                            </button>
                        </div>
                    </div>

                    <div class="col-md-6">
                        <label for="brand_id" class="form-label"><i class="fas fa-industry me-1"></i>Marca</label>
                        <select name="brand_id" id="brand_id" class="form-select">
                            <option value="">Selecione uma marca</option>
                            @foreach ($brands as $brand)
                                <option value="{{ $brand->id }}"
                                    {{ (int) old('brand_id', $item->brand_id ?? 0) === (int) $brand->id ? 'selected' : '' }}>
                                    {{ $brand->name ?: $brand->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="category_id" class="form-label"><i class="fas fa-folder me-1"></i>Categoria</label>
                        <select name="category_id" id="category_id" class="form-select">
                            <option value="">Selecione uma categoria</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    {{ $item->category_id === $category->id ? 'selected' : '' }}>
                                    {{ $category->name ?: $category->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="subcategory_id" class="form-label"><i
                                class="fas fa-folder-open me-1"></i>Subcategoria</label>
                        <select name="subcategory_id" id="subcategory_id" class="form-select" data-subcategories='@json($subcategories)' data-selected="{{ old('subcategory_id', $item->subcategory_id ?? '') }}">
                            <option value="">Selecione uma subcategoria</option>
                            @foreach ($subcategories as $subcategory)
                                <option value="{{ $subcategory->id }}"
                                    {{ $item->subcategory_id === $subcategory->id ? 'selected' : '' }}>
                                    {{ $subcategory->name ?: $subcategory->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="categoriasfilhas_id" class="form-label"><i class="fas fa-sitemap me-1"></i>Categorias
                            Filhas</label>
                        <select name="childcategory_id" id="categoriasfilhas_id" class="form-select" data-categoriasfilhas='@json($categoriasfilhas)' data-selected="{{ old('childcategory_id', $item->childcategory_id ?? '') }}">
                            <option value="">Selecione uma categorias filhas</option>
                            @foreach ($categoriasfilhas as $categoriasfilhas)
                                <option value="{{ $categoriasfilhas->id }}"
                                    {{ (int) old('childcategory_id', $item->childcategory_id ?? 0) === (int) $categoriasfilhas->id ? 'selected' : '' }}>
                                    {{ $categoriasfilhas->name ?: $categoriasfilhas->slug }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-6">
                        <label for="price" class="form-label"><i class="fas fa-dollar-sign me-1"></i>Preço</label>
                        <input type="number" step="0.01" id="price" name="price" class="form-control"
                            value="{{ old('price', $item->price ?? 0) }}">
                    </div>

                    <div class="col-md-6">
                        <label for="stock" class="form-label"><i class="fas fa-boxes me-1"></i>Estoque</label>
                        <input type="number" id="stock" name="stock" class="form-control"
                            value="{{ old('stock', $item->stock ?? 0) }}">
                    </div>

                    <div class="col-12">
                        <div class="row g-4">
                            <div class="col-md-6">
                                <label for="photoInput" class="form-label text-bold">
                                    <i class="fas fa-image me-1"></i>Foto Principal
                                </label>
                                <div class="product-media-panel h-100">
                                    <div class="product-main-preview position-relative" id="photoPreviewBox"
                                        style="{{ $item->photo ? '' : 'display:none;' }}">
                                        <img src="{{ $item->photo ? Storage::url($item->photo) : '' }}" id="photoPreviewImg"
                                            class="w-100 h-100" alt="Prévia da foto principal">
                                        @if ($item->photo)
                                            <button type="button"
                                                class="product-media-remove position-absolute top-0 end-0 m-2 shadow-sm"
                                                onclick="if(confirm('Excluir foto principal?')) document.getElementById('deletePhotoForm').submit();">
                                                <i class="fas fa-times"></i>
                                            </button>
                                        @endif
                                    </div>

                                    <div class="product-dropzone {{ $item->photo ? 'mt-3' : '' }}" id="productPhotoDropzone">
                                        <input type="file" name="photo" id="photoInput" class="product-dropzone-input" accept="image/jpeg,image/png,image/webp">
                                        <div class="product-dropzone-icon"><i class="fas fa-cloud-arrow-up"></i></div>
                                        <strong>Arraste a foto principal aqui</strong>
                                        <span>ou clique para selecionar no computador</span>
                                        <small id="photoSelectionStatus">JPG, PNG ou WEBP · máximo 10 MB</small>
                                    </div>
                                </div>
                            </div>

                            <div class="col-md-6">
                                <label class="form-label text-bold">
                                    <i class="fas fa-images me-1"></i>Galeria de Imagens
                                </label>
                                <div class="product-media-panel h-100">
                                    <div class="product-dropzone" id="productGalleryDropzone">
                                        <input type="file" name="gallery[]" id="galleryInput" class="product-dropzone-input" multiple accept="image/jpeg,image/png,image/webp">
                                        <div class="product-dropzone-icon"><i class="fas fa-images"></i></div>
                                        <strong>Arraste várias imagens para a galeria</strong>
                                        <span>ou clique para selecionar no computador</span>
                                        <small id="gallerySelectionCount">Nenhuma nova imagem selecionada</small>
                                    </div>

                                    <div id="galleryPreview" class="product-gallery-preview mt-3" style="display:none;"></div>

                                    @if (!empty($galleryImages))
                                        <button type="button" class="btn btn-outline-dark btn-sm w-100 mt-3" data-bs-toggle="modal"
                                            data-bs-target="#modalGerenciarGaleria">
                                            <i class="fas fa-tasks me-1"></i> Gerenciar Galeria ({{ count($galleryImages) }}
                                            fotos)
                                        </button>
                                    @endif
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal fade" id="modalGerenciarGaleria" tabindex="-1" aria-hidden="true">
                        <div class="modal-dialog modal-lg modal-dialog-centered">
                            <div class="modal-content">
                                <div class="modal-header bg-light">
                                    <h5 class="modal-title"><i class="fas fa-images me-2"></i>Gerenciar Galeria</h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"
                                        aria-label="Close"></button>
                                </div>
                                <div class="modal-body">
                                    <div class="alert alert-info py-2" style="font-size: 13px;">
                                        <i class="fas fa-info-circle me-1"></i> Selecione as imagens que deseja remover.
                                    </div>

                                    <div class="d-flex justify-content-between mb-3">
                                        <button type="button" class="btn btn-outline-secondary btn-sm"
                                            onclick="toggleSelectAll()">
                                            <i class="fas fa-check-double me-1"></i> Selecionar Todas
                                        </button>
                                        <button type="button" class="btn btn-danger btn-sm"
                                            onclick="deleteSelectedImages()">
                                            <i class="fas fa-trash-alt me-1"></i> Excluir Selecionadas
                                        </button>
                                    </div>

                                    <div class="row g-2 overflow-auto" style="max-height: 400px;">
                                        @foreach ($galleryImages as $idx => $img)
                                            <div class="col-4 col-sm-3 col-md-2 position-relative gallery-item">
                                                <label class="d-block border rounded p-1 bg-white cursor-pointer h-100">
                                                    <input type="checkbox" value="{{ basename($img) }}"
                                                        class="gallery-checkbox position-absolute top-0 start-0 m-2">
                                                    <img src="{{ Storage::url($img) }}" class="w-100 rounded"
                                                        style="aspect-ratio: 1/1; object-fit: cover;">
                                                </label>
                                            </div>
                                        @endforeach
                                    </div>
                                </div>
                                <div class="modal-footer">
                                    <button type="button" class="btn btn-secondary"
                                        data-bs-dismiss="modal">Fechar</button>
                                </div>
                            </div>
                        </div>
                    </div>

                    @php
                        $allowImageColorSuggestion = empty($suggestedColor)
                            && (empty($item->color) || strtoupper((string) $item->color) === '#000000');
                    @endphp
                    <div class="mb-3 col-md-6" id="color-section"
                        data-detect-color-from-image="{{ $allowImageColorSuggestion ? '1' : '0' }}">
                        <label class="form-label"><i class="fas fa-palette me-1"></i> Cor do Produto</label>
                        @php
                            $savedColors = old('colors_values', $item->product_colors);
                            if (empty($savedColors) && $suggestedColor) $savedColors = [$suggestedColor];
                            $effectiveColor = $savedColors[0] ?? '#000000';
                            $hasEffectiveColor = !empty($savedColors);
                        @endphp
                        
                        <div class="d-flex align-items-center gap-2 mb-2">
                            <input type="color" id="color-input" value="{{ $effectiveColor }}"
                                class="form-control form-control-color" oninput="document.getElementById('color-search').value = this.value">
                            
                            <input type="text" id="color-search" class="form-control" placeholder="Buscar cor ou código..." 
                                value="{{ $effectiveColor }}" onkeyup="renderColors(this.value)">

                            <button type="button" id="add-color" class="btn btn-outline-dark text-nowrap">Adicionar cor</button>
                            <div class="form-check ms-2">
                                <input class="form-check-input" type="checkbox" name="no_color" id="no_color" {{ !$hasEffectiveColor ? 'checked' : '' }}
                                    onchange="if(this.checked) { document.getElementById('color-input').value = ''; }">
                                <label class="form-check-label" for="no_color">Sem cor</label>
                            </div>
                        </div>

                        <div id="selected-colors" class="d-flex flex-wrap gap-2 mb-2" aria-label="Cores selecionadas">
                            @foreach ($savedColors as $savedColor)
                                <span class="selected-color badge bg-light text-dark border d-inline-flex align-items-center gap-2 p-2" data-color="{{ strtoupper($savedColor) }}">
                                    <i style="width:18px;height:18px;border-radius:50%;background:{{ $savedColor }};border:1px solid #bbb"></i>
                                    {{ strtoupper($savedColor) }}
                                    <button type="button" class="btn-close" style="font-size:8px" aria-label="Remover cor"></button>
                                    <input type="hidden" name="colors_values[]" value="{{ strtoupper($savedColor) }}">
                                </span>
                            @endforeach
                        </div>
                        <div class="form-text mb-2">A primeira é a cor principal. Para packs, adicione até 8 cores; no site elas aparecem dentro de uma única bolinha.</div>

                        @if ($suggestedColor)
                            <div class="alert alert-success py-2 px-3 mb-2 small">
                                <i class="fas fa-wand-magic-sparkles me-1"></i>
                                Cor preenchida automaticamente: <strong>{{ $suggestedColor }}</strong>
                                usando {{ $suggestedColorSource }}. Você pode alterá-la antes de salvar.
                            </div>
                        @endif

                        <div id="image-color-suggestion" class="alert alert-info py-2 px-3 mb-2 small d-none"></div>

                        <div id="color-palette" class="d-flex flex-wrap gap-1 p-2 border rounded bg-light" style="max-height: 200px; overflow-y: auto;"></div>
                        <div id="color-suggestions" class="small text-muted mt-2"></div>
                        <button type="button" class="btn btn-link btn-sm mt-1 p-0" onclick="toggleColors()" id="btn-toggle">Mostrar mais cores...</button>
                    </div>

                @php
                    $categoryMap = [
                        'feminino' => 'vestuario', 'masculino' => 'vestuario', 'unisex' => 'vestuario', 'bridal' => 'vestuario',
                        'tenis' => 'calcados', 'sandalias' => 'calcados', 'sapatos' => 'calcados',
                        'infantil' => 'infantil',
                        'perfumaria' => 'perfumes', 'perfumes' => 'perfumes',
                        'bebidas' => 'bebidas', 'vinhos' => 'bebidas', 'destilados' => 'bebidas',
                        'optico' => 'oculos', 'oculos' => 'oculos',
                        'relogios' => 'relogios',
                        'habanos' => 'habanos',
                        'acessorios' => 'acessorios'
                    ];
                    $categorySlug = strtolower($item->category->slug ?? '');
                    $activeGroup = $categoryMap[$categorySlug] ?? null;
                    $currentSize = old('size', $item->size ?? '');
                    
                    $detectedSize = $item->inferredSize() ?? '';
                @endphp

                <div class="col-md-6" id="size-container" data-current-size="{{ $currentSize }}" data-detected-size="{{ $detectedSize }}" data-active-group="{{ $activeGroup }}">
                    <label class="form-label"><i class="fas fa-ruler-combined me-1"></i>Tamanho / Especificação</label>
                    
                    <select id="type_selector" class="form-select mb-2">
                        <option value="">Selecione o tipo de produto...</option>
                        <option value="manual">Outro (Manual)</option>
                    </select>

                    <select id="size_select" class="form-select d-none">
                        <option value="">Selecione o tamanho</option>
                    </select>

                    <input type="text" id="size_manual" class="form-control mt-2 d-none" value="{{ $currentSize }}" placeholder="Digite o tamanho">
                    
                    <div id="detection-msg" class="small text-muted mt-1 {{ $detectedSize && !$currentSize ? '' : 'd-none' }}">
                        Detectado desde integração: #{{ $detectedSize }}
                    </div>
                </div>

                    <div class="col-12 mb-3">
                        <label class="sax-label"><i class="fas fa-align-left me-1"></i>Descrição do Produto</label>
                        
                        {{-- products.description é a descrição canônica em português. ES e EN
                             permanecem armazenados separadamente em product_translations. --}}
                        <textarea id="real-desc-pt" name="translate[pt-br][details]" class="d-none">{{ old('translate.pt-br.details', $item->description ?: ($ptTranslation->details ?? '')) }}</textarea>
                        <textarea id="real-desc-es" name="translate[es][details]" class="d-none">{{ old('translate.es.details', $esTranslation->details ?? '') }}</textarea>
                        <textarea id="real-desc-en" name="translate[en][details]" class="d-none">{{ old('translate.en.details', $enTranslation->details ?? '') }}</textarea>

                        <div class="editor-rich-wrapper">
                            <textarea id="editor-product" class="form-control"></textarea>
                        </div>

                        <div class="mt-1">
                            <span class="small text-muted d-block mb-1" id="desc-current-language">Conteúdo em Português</span>
                            <span class="small text-muted me-2">Editar idioma:</span>
                            <a href="javascript:void(0)" class="badge bg-primary desc-lang-btn text-decoration-none" data-lang="pt" onclick="switchLanguage('desc', 'pt', this)">PT</a>
                            <a href="javascript:void(0)" class="badge bg-secondary desc-lang-btn text-decoration-none" data-lang="es" onclick="switchLanguage('desc', 'es', this)">ES</a>
                            <a href="javascript:void(0)" class="badge bg-secondary desc-lang-btn text-decoration-none" data-lang="en" onclick="switchLanguage('desc', 'en', this)">EN</a>
                            <button type="button" id="translate-desc-btn" class="btn btn-outline-primary btn-sm ms-2"
                                    onclick="generateSaxDescriptionTranslations()">
                                <i class="fas fa-wand-magic-sparkles me-1"></i>Gerar descrição SAX
                            </button>
                        </div>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="sax-label mb-3">
                            <i class="fas fa-map-marker-alt me-1"></i> Disponible para retirar en tienda
                        </label>

                        <div class="d-flex flex-column gap-2 bg-light p-3 border">
                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input sax-checkbox me-2" type="checkbox" name="stores[]"
                                    value="asuncion" id="store_asuncion"
                                    {{ isset($item) && is_array($item->stores) && in_array('asuncion', $item->stores) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold x-small tracking-wider text-uppercase"
                                    for="store_asuncion">
                                    Asunción
                                </label>
                            </div>

                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input sax-checkbox me-2" type="checkbox" name="stores[]"
                                    value="cde" id="store_cde"
                                    {{ isset($item) && is_array($item->stores) && in_array('cde', $item->stores) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold x-small tracking-wider text-uppercase"
                                    for="store_cde">
                                    Ciudad Del Este
                                </label>
                            </div>

                            <div class="form-check d-flex align-items-center">
                                <input class="form-check-input sax-checkbox me-2" type="checkbox" name="stores[]"
                                    value="pjc" id="store_pjc"
                                    {{ isset($item) && is_array($item->stores) && in_array('pjc', $item->stores) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold x-small tracking-wider text-uppercase"
                                    for="store_pjc">
                                    Pedro Juan Caballero
                                </label>
                            </div>
                        </div>
                    </div>

                    @if (!empty($item->parent_id) || (($item->product_role ?? null) === 'F'))
                        @php
                            $currentParentProduct = $item->parent;
                            $currentParentLabel = $currentParentProduct
                                ? ($currentParentProduct->name ?: ($currentParentProduct->external_name ?: ('#' . $item->parent_id)))
                                : ('#' . $item->parent_id);
                        @endphp
                        <div class="col-12 mb-4">
                            <div class="alert alert-warning border mb-0">
                                <div class="form-check form-switch mb-2">
                                    <input class="form-check-input" type="checkbox" role="switch"
                                        id="force_as_parent" name="force_as_parent" value="1"
                                        {{ old('force_as_parent') ? 'checked' : '' }}>
                                    <label class="form-check-label fw-semibold" for="force_as_parent">
                                        Convertir este producto nuevamente en P
                                    </label>
                                </div>
                                <small class="d-block text-muted">
                                    Este producto está vinculado como variante de talla de {{ $currentParentLabel }}.
                                    Si activas este switch y guardas, se soltará de esa relación de talla y volverá a
                                    su propio ancla de color.
                                </small>
                            </div>
                        </div>
                    @endif

                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="fas fa-link me-1"></i>Variantes de talla</label>
                        <div class="input-group mb-2">
                            <input type="text" id="parent_search" class="form-control"
                                placeholder="Buscar variante por talla..." autocomplete="off"
                                value="{{ $item->relationshipSearchTerm() }}" data-auto-search="1">
                            <button class="btn btn-primary" id="parent_search_btn" type="button"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <div id="parent_results_label" class="relationship-section-label d-none">Sugestões encontradas</div>
                        <div id="parent_results" class="row g-2 relationship-results" style="display:none; z-index:1000;" data-noimage="{{ asset('storage/uploads/noimage.webp') }}" data-current-product-id="{{ $item->id }}" data-current-color-key="{{ $item->relationshipColorKey() }}" data-current-reference-key="{{ $item->relationshipReferenceKey() }}"></div>
                        <div class="relationship-section-label mt-3">Já relacionados <span class="relationship-count" data-count-for="selected_parents">{{ count($item->selected_size_children ?? []) }}</span></div>
                        <div id="selected_parents" class="row g-2 mt-2">
                            @if (!empty($item->selected_size_children))
                                @php
                                    $parentIds = $item->selected_size_children;
                                @endphp
                                @foreach ($parentIds as $pid)
                                    @php $parentProduct = $sizeChildrenProducts->get((int) $pid); @endphp
                                    @if ($parentProduct && (int) $parentProduct->id !== (int) $item->id)
                                        <div class="col-6 col-md-4 col-lg-2" data-id="{{ $parentProduct->id }}">
                                            <div class="card border-success h-100 position-relative">
                                                <img src="{{ $parentProduct->photo_url }}"
                                                    class="card-img-top" style="height:120px; object-fit:cover;">
                                                <div class="card-body p-2">
                                                    <span class="badge bg-success mb-1">Relacionado</span>
                                                    <p class="card-text m-0 fw-bold">
                                                        {{ $parentProduct->external_name ?: $parentProduct->name }}</p>
                                                    <small class="text-muted d-block mt-1">SKU: {{ $parentProduct->sku }}</small>
                                                    @if ($parentProduct->color)
                                                        <div class="d-flex align-items-center mt-1">
                                                            <span
                                                                style="display:inline-block;width:16px;height:16px;background:{{ $parentProduct->color }};border:1px solid #ccc;margin-right:5px;"></span>
                                                            <small>{{ $parentProduct->color }}</small>
                                                        </div>
                                                    @endif
                                                    @if ($parentProduct->size)
                                                        <div class="mt-1">
                                                            <small class="text-muted">Tamanho:
                                                                {{ $parentProduct->size }}</small>
                                                        </div>
                                                    @endif
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item"><i
                                                            class="fas fa-times"></i></button>
                                                    <input type="hidden" name="parent_id[]"
                                                        value="{{ $parentProduct->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        <small class="form-text text-muted d-block mt-2">
                            Variantes com a mesma referência e cor são pré-selecionadas automaticamente. Elas herdam os dados comuns do produto pai; SKU, estoque e tamanho continuam próprios de cada filho.
                        </small>
                    </div>

                    <div class="col-12 mb-4">
                        <label class="form-label"><i class="fas fa-palette me-1"></i>Familia de color</label>
                        <div class="input-group mb-2">
                            <input type="text" id="color_search" class="form-control"
                                placeholder="Buscar produto para a família de cor..." autocomplete="off"
                                value="{{ $item->relationshipSearchTerm() }}" data-auto-search="1">
                            <button class="btn btn-primary" id="color_search_btn" type="button"><i
                                    class="fas fa-search"></i></button>
                        </div>
                        <div id="color_results_label" class="relationship-section-label d-none">Sugestões de outras cores</div>
                        <div id="color_results" class="row g-2 relationship-results" style="display:none; z-index:1000;" data-noimage="{{ asset('storage/uploads/noimage.webp') }}" data-current-product-id="{{ $item->id }}" data-current-color-key="{{ $item->relationshipColorKey() }}" data-current-reference-key="{{ $item->relationshipReferenceKey() }}"></div>
                        <div class="relationship-section-label mt-3">Já relacionados <span class="relationship-count" data-count-for="selected_colors">{{ count($item->selected_color_family_members ?? []) }}</span></div>
                        <div id="selected_colors" class="row g-2 mt-2">
                            @if (!empty($item->selected_color_family_members))
                                @php
                                    $colorIds = $item->selected_color_family_members;
                                @endphp
                                @foreach ($colorIds as $cid)
                                    @php $colorProduct = $colorFamilyProducts->get((int) $cid); @endphp
                                    @if ($colorProduct && (int) $colorProduct->id !== (int) $item->id)
                                        <div class="col-6 col-md-4 col-lg-2" data-id="{{ $colorProduct->id }}">
                                            <div class="card border-success h-100 position-relative">
                                                <img src="{{ $colorProduct->photo_url }}"
                                                    class="card-img-top" style="height:120px; object-fit:cover;">
                                                <div class="card-body p-2">
                                                    <span class="badge bg-success mb-1">Relacionado</span>
                                                    <p class="card-text m-0 fw-bold">
                                                        {{ $colorProduct->external_name ?: $colorProduct->name }}</p>
                                                    <small class="text-muted d-block mt-1">SKU: {{ $colorProduct->sku }}</small>
                                                    @if ($colorProduct->color)
                                                        <div class="d-flex align-items-center mt-1">
                                                            <span
                                                                style="display:inline-block;width:16px;height:16px;background:{{ $colorProduct->color }};border:1px solid #ccc;margin-right:5px;"></span>
                                                            <small>{{ $colorProduct->color }}</small>
                                                        </div>
                                                    @endif
                                                    @if ($colorProduct->size)
                                                        <div class="mt-1">
                                                            <small class="text-muted">Tamanho:
                                                                {{ $colorProduct->size }}</small>
                                                        </div>
                                                    @endif
                                                    <button type="button"
                                                        class="btn btn-sm btn-danger position-absolute top-0 end-0 remove-item"><i
                                                            class="fas fa-times"></i></button>
                                                    <input type="hidden" name="color_parent_id[]"
                                                        value="{{ $colorProduct->id }}">
                                                </div>
                                            </div>
                                        </div>
                                    @endif
                                @endforeach
                            @endif
                        </div>
                        @error('color_parent_id')
                            <div class="text-danger small mt-2">{{ $message }}</div>
                        @enderror
                        <small class="form-text text-muted d-block mt-2">
                            As sugestões usam a mesma referência e mostram somente um produto-base por cor diferente. Cada cor conserva seus próprios tamanhos e estoque.
                        </small>
                    </div>
                @endif
            </div>

            <div class="product-edit-actions mt-4">
                <button id="saveProductBtn" type="submit" class="btn btn-success">
                    <i class="fas fa-save me-2"></i>Salvar Alterações
                </button>
                <a href="{{ request('return_to') ?: route('admin.products.index') }}" class="btn btn-secondary">
                    <i class="fas fa-times me-2"></i>Cancelar
                </a>
            </div>
        </form>

    @if ($item->photo)
        <form action="{{ route('admin.products.deletePhoto', $item->id) }}" method="POST" style="display:none;"
            id="deletePhotoForm">
            @csrf
            @method('DELETE')
        </form>
    @endif

    @php
        $galleryImages = is_array($item->gallery) ? $item->gallery : json_decode($item->gallery, true);
        $galleryImages = is_array($galleryImages) ? array_values($galleryImages) : [];
    @endphp

    @foreach ($galleryImages as $galleryImage)
        <form action="{{ route('admin.products.gallery.delete', [$item->id, basename($galleryImage)]) }}" method="POST"
            style="display:none;" class="deleteGalleryForm">
            @csrf
            @method('DELETE')
        </form>
    @endforeach

    <form id="formMultiDeleteGallery" action="{{ route('admin.products.gallery.multiDelete', $item->id) }}"
        method="POST" style="display:none !important;">
        @csrf
        @method('DELETE')
        <input type="hidden" name="image_names" id="inputImageNames">
    </form>

    </x-admin.card>

@endsection
