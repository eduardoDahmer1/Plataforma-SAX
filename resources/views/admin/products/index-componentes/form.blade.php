<form action="{{ route('admin.products.index') }}" method="GET" id="filterForm">

    {{-- Barra de búsqueda --}}
    <div class="sax-search-wrapper mb-3">
        <div class="row g-2 align-items-center">
            <div class="col">
                <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 px-3">
                        <i class="fa fa-search text-muted"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-0 sax-search-input"
                        placeholder="Buscar por nome, SKU ou slug..." value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-auto">
                <button class="btn btn-outline-dark rounded-3 px-3" type="submit">
                    <i class="fa fa-sliders-h me-2"></i> Filtrar
                </button>
            </div>
        </div>
    </div>

    {{-- Filtros inline --}}
    <div class="sax-filter-bar">
        <div class="sax-filter-item">
            <label class="sax-filter-label">{{ __('messages.marca') }}</label>
            <select name="brand_id" class="form-select sax-filter-select">
                <option value="">Todas as Marcas</option>
                @foreach($brands as $brand)
                    <option value="{{ $brand->id }}" @selected(request('brand_id') == $brand->id)>
                        {{ $brand->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sax-filter-item">
            <label class="sax-filter-label">{{ __('messages.categoria') }}</label>
            <select name="category_id" class="form-select sax-filter-select">
                <option value="">Todas as Categorias</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" @selected(request('category_id') == $category->id)>
                        {{ $category->name }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="sax-filter-item">
            <label class="sax-filter-label">{{ __('messages.status') }}</label>
            <select name="status_filter" class="form-select sax-filter-select">
                <option value="">Todos os status</option>
                <optgroup label="Visibilidade">
                    <option value="active" @selected(request('status_filter') == 'active')>Ativos</option>
                    <option value="inactive" @selected(request('status_filter') == 'inactive')>Inativos</option>
                </optgroup>
                <optgroup label="Imagens">
                    <option value="with_image" @selected(request('status_filter') == 'with_image')>Com Imagem</option>
                    <option value="without_image" @selected(request('status_filter') == 'without_image')>{{ __('messages.sem_imagem') }}</option>
                </optgroup>
                <optgroup label="Estoque">
                    <option value="in_stock" @selected(request('status_filter') == 'in_stock')>Com Estoque</option>
                    <option value="out_of_stock" @selected(request('status_filter') == 'out_of_stock')>Sem Estoque</option>
                </optgroup>
            </select>
        </div>

        <div class="sax-filter-item">
            <label class="sax-filter-label">{{ __('messages.ordenar_por') }}</label>
            <select name="sort_by" class="form-select sax-filter-select">
                <option value="">Mais recentes</option>
                <option value="oldest" @selected(request('sort_by') == 'oldest')>Mais antigos</option>
                <option value="last_edit" @selected(request('sort_by') == 'last_edit')>Últimos editados</option>
                <option value="price_low" @selected(request('sort_by') == 'price_low')>{{ __('messages.menor_preco') }}</option>
                <option value="price_high" @selected(request('sort_by') == 'price_high')>{{ __('messages.maior_preco') }}</option>
                <option value="name_az" @selected(request('sort_by') == 'name_az')>Nome (A–Z)</option>
            </select>
        </div>

        <div class="sax-filter-item">
            <label class="sax-filter-label">Destaques</label>
            <select name="highlight_filter" class="form-select sax-filter-select">
                <option value="">Todos os destaques</option>
                @foreach ($highlights as $key => $label)
                    <option value="{{ $key }}" @selected(request('highlight_filter') == $key)>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    {{-- Link limpar (solo si hay filtros activos) --}}
    @if(request()->hasAny(['search', 'brand_id', 'category_id', 'status_filter', 'sort_by', 'highlight_filter']))
        <div class="mt-2 text-end">
            <a href="{{ route('admin.products.index') }}" class="sax-clear-filters">
                <i class="fa fa-times me-1"></i> Limpar filtros
            </a>
        </div>
    @endif
</form>
