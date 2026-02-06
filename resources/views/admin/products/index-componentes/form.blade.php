<form action="{{ route('admin.products.index') }}" method="GET" class="mb-4" id="filterForm">
    
    <div class="row g-2 mb-3">
        <div class="col-12 col-md-8 col-lg-9">
            <div class="input-group">
                <span class="input-group-text bg-white border-end-0">
                    <i class="fa fa-search text-muted"></i>
                </span>
                <input type="text" name="search" class="form-control border-start-0" 
                    placeholder="Buscar por nome, SKU ou slug..." value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-6 col-md-2 col-lg-1.5">
            <button class="btn btn-primary w-100" type="submit">
                Filtrar
            </button>
        </div>
        <div class="col-6 col-md-2 col-lg-1.5">
            <a href="{{ route('admin.products.index') }}" class="btn btn-outline-secondary w-100">
                Limpar
            </a>
        </div>
    </div>

    <div class="row g-2">
        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Marca</label>
            <select name="brand_id" class="form-select shadow-sm">
                <option value="">Todas as Marcas</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Categoria</label>
            <select name="category_id" class="form-select shadow-sm">
                <option value="">Todas as Categorias</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Status & Estoque</label>
            <select name="status_filter" class="form-select shadow-sm">
                <option value="">Todos os status</option>
                <optgroup label="Visibilidade">
                    <option value="active" @selected(request('status_filter') == 'active')>✓ Ativos</option>
                    <option value="inactive" @selected(request('status_filter') == 'inactive')>✗ Inativos</option>
                </optgroup>
                <optgroup label="Imagens">
                    <option value="with_image" @selected(request('status_filter') == 'with_image')>🖼️ Com Imagem</option>
                    <option value="without_image" @selected(request('status_filter') == 'without_image')>📷 Sem Imagem</option>
                </optgroup>
                <optgroup label="Estoque">
                    <option value="in_stock" @selected(request('status_filter') == 'in_stock')>📦 Com Estoque</option>
                    <option value="out_of_stock" @selected(request('status_filter') == 'out_of_stock')>📭 Sem Estoque</option>
                </optgroup>
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Ordenar por</label>
            <select name="sort_by" class="form-select shadow-sm">
                <option value="">Padrão (Mais recentes)</option>
                <option value="latest" @selected(request('sort_by') == 'latest')>📅 Últimos adicionados</option>
                <option value="oldest" @selected(request('sort_by') == 'oldest')>📅 Primeiros adicionados</option>
                <option value="recently_updated" @selected(request('sort_by') == 'recently_updated')>✏️ Últimos editados</option>
                <option value="price_low" @selected(request('sort_by') == 'price_low')>💰 Menor preço</option>
                <option value="price_high" @selected(request('sort_by') == 'price_high')>💰 Maior preço</option>
                <option value="name_az" @selected(request('sort_by') == 'name_az')>🔤 Nome (A–Z)</option>
            </select>
        </div>

        <div class="col-12 col-md-6 col-lg-3">
            <label class="form-label small fw-bold text-muted mb-1">Destaques</label>
            <select name="highlight_filter" class="form-select shadow-sm">
                <option value="">Todos os destaques</option>
                @foreach ($highlights as $key => $label)
                    <option value="{{ $key }}" @selected(request('highlight_filter') == $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>
</form>