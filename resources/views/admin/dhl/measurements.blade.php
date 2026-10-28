@extends('layout.admin')

@push('styles')
<link href="{{ asset('css/admin-dhl-measurements.css') }}?v={{ file_exists(public_path('css/admin-dhl-measurements.css')) ? filemtime(public_path('css/admin-dhl-measurements.css')) : time() }}" rel="stylesheet">
@endpush

@section('content')
<div class="dhl-measurements">
<x-admin.card>
    <x-admin.page-header
        title="Medidas e perfis DHL"
        description="Encontre produtos rapidamente, aplique perfis ou medidas reais em lote e mantenha as médias por categoria." />

    <x-admin.alert />

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Não foi possível salvar:</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="dhl-page-actions">
        <a href="{{ route('admin.dhl.edit') }}" class="btn btn-outline-dark"><i class="fa-solid fa-arrow-left"></i>Voltar para DHL</a>
        @if ($view === 'rules')
            <form method="POST" action="{{ route('admin.dhl.measurements.sync') }}">
                @csrf
                <button class="btn btn-dark"><i class="fa-solid fa-rotate"></i>Sincronizar novas categorias</button>
            </form>
        @endif
    </div>

    <nav class="dhl-view-tabs" aria-label="Áreas de medidas DHL">
        <a href="{{ route('admin.dhl.measurements.index', ['view' => 'products']) }}" class="{{ $view === 'products' ? 'is-active' : '' }}">
            <i class="fa-solid fa-boxes-stacked"></i>
            <span><strong>Produtos</strong><small>Buscar, selecionar e salvar em lote</small></span>
        </a>
        <a href="{{ route('admin.dhl.measurements.index', ['view' => 'rules']) }}" class="{{ $view === 'rules' ? 'is-active' : '' }}">
            <i class="fa-solid fa-sitemap"></i>
            <span><strong>Regras por categoria</strong><small>Médias usadas quando o produto não tem medidas</small></span>
        </a>
    </nav>

    @if ($view === 'products')
        <div class="dhl-guidance">
            <i class="fa-solid fa-lightbulb"></i>
            <div>
                <strong>Fluxo recomendado</strong>
                <p>Pesquise um tipo de produto, selecione os itens semelhantes e use <b>Fixar sugestão inteligente</b>. Para produtos idênticos, aplique as quatro medidas reais em lote.</p>
            </div>
        </div>

        <div class="dhl-stat-grid">
            @foreach ([
                ['Catálogo', $productStats['total'], null, 'fa-boxes-stacked'],
                ['Medidas reais', $productStats['exact'], 'exact', 'fa-ruler-combined'],
                ['Perfil fixado', $productStats['profile'], 'profile', 'fa-thumbtack'],
                ['Automáticos', $productStats['automatic'], 'automatic', 'fa-wand-magic-sparkles'],
                ['Revisão', $productStats['review'], 'review', 'fa-triangle-exclamation'],
            ] as [$label, $value, $measurementFilter, $icon])
                <a class="dhl-stat" href="{{ route('admin.dhl.measurements.index', array_filter(['view' => 'products', 'measurement' => $measurementFilter])) }}">
                    <i class="fa-solid {{ $icon }}"></i>
                    <span>{{ $label }}</span>
                    <strong>{{ number_format($value, 0, ',', '.') }}</strong>
                </a>
            @endforeach
        </div>

        <section class="dhl-search-panel">
            <form method="GET" id="dhl-product-filter-form">
                <input type="hidden" name="view" value="products">
                <div class="dhl-search-primary">
                    <label>
                        <span>Busca inteligente</span>
                        <span class="dhl-search-input">
                            <i class="fa-solid fa-magnifying-glass"></i>
                            <input class="form-control" name="q" value="{{ request('q') }}" placeholder="Nome, SKU, marca, cadeira, móveis, vinho, camiseta...">
                        </span>
                    </label>
                    <button class="btn btn-dark" type="submit"><i class="fa-solid fa-magnifying-glass"></i>Buscar produtos</button>
                    <a class="btn btn-outline-secondary" href="{{ route('admin.dhl.measurements.index', ['view' => 'products']) }}">Limpar</a>
                </div>

                <div class="dhl-quick-searches" aria-label="Buscas rápidas">
                    <span>Atalhos:</span>
                    <a href="{{ route('admin.dhl.measurements.index', ['view' => 'products', 'q' => 'moveis']) }}"><i class="fa-solid fa-couch"></i>Móveis e cadeiras</a>
                    <a href="{{ route('admin.dhl.measurements.index', ['view' => 'products', 'q' => 'vinho']) }}"><i class="fa-solid fa-wine-bottle"></i>Vinhos</a>
                    <a href="{{ route('admin.dhl.measurements.index', ['view' => 'products', 'q' => 'camiseta']) }}"><i class="fa-solid fa-shirt"></i>Camisetas</a>
                    <a href="{{ route('admin.dhl.measurements.index', ['view' => 'products', 'measurement' => 'automatic']) }}"><i class="fa-solid fa-wand-magic-sparkles"></i>Sem perfil fixado</a>
                    <a href="{{ route('admin.dhl.measurements.index', ['view' => 'products', 'measurement' => 'review']) }}"><i class="fa-solid fa-triangle-exclamation"></i>Revisão</a>
                </div>

                <details class="dhl-advanced-filters" @if(collect(['category_id', 'subcategory_id', 'childcategory_id', 'brand_id', 'measurement', 'profile', 'catalog_status', 'sort'])->contains(fn ($key) => request()->filled($key))) open @endif>
                    <summary><i class="fa-solid fa-sliders"></i>Mais filtros</summary>
                    <div class="dhl-filter-grid">
                        <label><span>Categoria</span><select class="form-select" name="category_id" id="dhl-category-filter"><option value="">Todas</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected((int)request('category_id') === $category->id)>{{ $category->name }}</option>@endforeach</select></label>
                        <label><span>Subcategoria</span><select class="form-select" name="subcategory_id" id="dhl-subcategory-filter"><option value="">Todas</option>@foreach($subcategories as $subcategory)<option value="{{ $subcategory->id }}" data-category="{{ $subcategory->category_id }}" @selected((int)request('subcategory_id') === $subcategory->id)>{{ $subcategory->name }}</option>@endforeach</select></label>
                        <label><span>Categoria filha</span><select class="form-select" name="childcategory_id" id="dhl-childcategory-filter"><option value="">Todas</option>@foreach($childCategories as $child)<option value="{{ $child->id }}" data-category="{{ $child->category_id }}" data-subcategory="{{ $child->subcategory_id }}" @selected((int)request('childcategory_id') === $child->id)>{{ $child->name }}</option>@endforeach</select></label>
                        <label><span>Marca</span><select class="form-select" name="brand_id"><option value="">Todas</option>@foreach($brands as $brand)<option value="{{ $brand->id }}" @selected((int)request('brand_id') === $brand->id)>{{ $brand->name }}</option>@endforeach</select></label>
                        <label><span>Situação das medidas</span><select class="form-select" name="measurement"><option value="">Todas</option><option value="exact" @selected(request('measurement') === 'exact')>Medidas reais</option><option value="profile" @selected(request('measurement') === 'profile')>Perfil fixado</option><option value="automatic" @selected(request('measurement') === 'automatic')>Automático / sem perfil</option><option value="review" @selected(request('measurement') === 'review')>Revisão manual</option><option value="incomplete" @selected(request('measurement') === 'incomplete')>Medidas incompletas</option></select></label>
                        <label><span>Perfil DHL</span><select class="form-select" name="profile"><option value="">Todos</option><option value="automatic" @selected(request('profile') === 'automatic')>Automático</option>@foreach($profiles as $code => $profile)<option value="{{ $code }}" @selected(request('profile') === $code)>{{ $profile['label'] }}</option>@endforeach<option value="restricted" @selected(request('profile') === 'restricted')>Restrito / revisão</option></select></label>
                        <label><span>Catálogo</span><select class="form-select" name="catalog_status"><option value="">Ativos e inativos</option><option value="active" @selected(request('catalog_status') === 'active')>Ativos</option><option value="inactive" @selected(request('catalog_status') === 'inactive')>Inativos</option></select></label>
                        <label><span>Ordenar</span><select class="form-select" name="sort"><option value="">Nome A–Z</option><option value="updated" @selected(request('sort') === 'updated')>Alterados recentemente</option><option value="review_first" @selected(request('sort') === 'review_first')>Revisão primeiro</option><option value="exact_first" @selected(request('sort') === 'exact_first')>Medidas reais primeiro</option></select></label>
                        <label><span>Por página</span><select class="form-select" name="per_page">@foreach([25,50,100] as $size)<option value="{{ $size }}" @selected((int)request('per_page', 50) === $size)>{{ $size }} produtos</option>@endforeach</select></label>
                        <button class="btn btn-outline-dark" type="submit"><i class="fa-solid fa-filter"></i>Aplicar filtros</button>
                    </div>
                </details>
            </form>
        </section>

        <form id="dhl-product-bulk-form" method="POST" action="{{ route('admin.dhl.measurements.products.bulk-update') }}" class="dhl-bulk-panel">
            @csrf
            <div class="dhl-bulk-summary">
                <span class="dhl-selection-count" data-selection-count>0 selecionados</span>
                <small>Selecione até 100 produtos desta página.</small>
            </div>
            <label class="dhl-bulk-action"><span>Ação em lote</span><select class="form-select" name="bulk_action" data-bulk-action required><option value="">Escolha uma ação</option><option value="apply_suggestion">Fixar sugestão inteligente</option><option value="apply_profile">Aplicar o mesmo perfil</option><option value="set_exact">Aplicar as mesmas medidas reais</option><option value="clear_exact">Limpar medidas reais</option><option value="clear_profile">Voltar ao perfil automático</option><option value="mark_review">Marcar como restrito / revisão</option><option value="clear_review">Remover marca de restrição</option></select></label>
            <label class="dhl-bulk-profile" data-bulk-profile hidden><span>Perfil</span><select class="form-select" name="profile"><option value="">Escolha</option>@foreach($profiles as $code => $profile)<option value="{{ $code }}">{{ $profile['label'] }}</option>@endforeach</select></label>
            <div class="dhl-bulk-dimensions" data-bulk-dimensions hidden>
                <label><span>Peso kg</span><input class="form-control" name="weight_kg" type="number" min="0.001" max="1000" step="0.001"></label>
                <label><span>Comp. cm</span><input class="form-control" name="length_cm" type="number" min="0.01" max="1000" step="0.01"></label>
                <label><span>Larg. cm</span><input class="form-control" name="width_cm" type="number" min="0.01" max="1000" step="0.01"></label>
                <label><span>Alt. cm</span><input class="form-control" name="height_cm" type="number" min="0.01" max="1000" step="0.01"></label>
            </div>
            <button class="btn btn-dark dhl-bulk-submit" type="submit" data-bulk-submit disabled><i class="fa-solid fa-floppy-disk"></i>Salvar selecionados</button>
        </form>

        <div class="dhl-results-heading">
            <div><span>Resultado</span><strong>{{ number_format($products->total(), 0, ',', '.') }} produto(s) encontrado(s)</strong></div>
            <small>Página {{ $products->currentPage() }} de {{ $products->lastPage() }}</small>
        </div>

        <div class="table-responsive dhl-product-table-wrap">
            <table class="table align-middle dhl-product-table">
                <thead><tr><th class="dhl-check-cell"><input class="form-check-input" type="checkbox" data-select-page aria-label="Selecionar página"></th><th>Produto</th><th>Hierarquia</th><th>Perfil atual / sugestão</th><th>Medida usada pela DHL</th><th>Situação</th><th></th></tr></thead>
                <tbody>
                @forelse($products as $product)
                    @php
                        $measurement = $product->dhl_shipping_measurement;
                        $suggestedCode = $product->dhl_suggested_profile;
                        $currentProfileLabel = $product->shipping_profile === 'restricted'
                            ? 'Restrito / revisão'
                            : ($product->shipping_profile ? ($profiles[$product->shipping_profile]['label'] ?? $product->shipping_profile) : 'Automático');
                        $suggestedLabel = $profiles[$suggestedCode]['label'] ?? $suggestedCode;
                        $hierarchy = collect([$product->category?->name, $product->subcategory?->name, $product->categoriasFilhas?->name])->filter()->implode(' › ');
                        $hasExact = $measurement['profile'] === 'exact';
                    @endphp
                    <tr class="{{ $measurement['restricted'] ? 'is-review' : '' }}">
                        <td class="dhl-check-cell"><input form="dhl-product-bulk-form" class="form-check-input" type="checkbox" name="product_ids[]" value="{{ $product->id }}" data-product-select aria-label="Selecionar {{ $product->name ?: $product->external_name }}"></td>
                        <td>
                            <div class="dhl-product-identity">
                                <img src="{{ $product->dhl_image_url }}" alt="" loading="lazy">
                                <div><strong>{{ $product->name ?: $product->external_name }}</strong><span>SKU {{ $product->sku ?: '—' }}@if($product->brand) · {{ $product->brand->name }}@endif</span></div>
                            </div>
                        </td>
                        <td><span class="dhl-hierarchy">{{ $hierarchy ?: 'Sem categoria vinculada' }}</span></td>
                        <td><strong class="dhl-profile-name">{{ $currentProfileLabel }}</strong><small>Sugestão: {{ $suggestedLabel }}</small></td>
                        <td><strong>{{ number_format($measurement['weight'], 3, ',', '.') }} kg</strong><small>{{ number_format($measurement['length'], 1, ',', '.') }} × {{ number_format($measurement['width'], 1, ',', '.') }} × {{ number_format($measurement['height'], 1, ',', '.') }} cm</small></td>
                        <td>
                            @if($measurement['restricted'])<span class="dhl-status is-review"><i class="fa-solid fa-triangle-exclamation"></i>Revisão</span>
                            @elseif($hasExact)<span class="dhl-status is-exact"><i class="fa-solid fa-ruler-combined"></i>Real</span>
                            @elseif($product->shipping_profile)<span class="dhl-status is-profile"><i class="fa-solid fa-thumbtack"></i>Perfil</span>
                            @else<span class="dhl-status is-auto"><i class="fa-solid fa-wand-magic-sparkles"></i>Automático</span>@endif
                            @if(!$product->status)<span class="dhl-status is-inactive">Inativo</span>@endif
                        </td>
                        <td class="text-end"><a href="{{ route('admin.products.edit', ['product' => $product->id, 'return_to' => request()->fullUrl()]) }}" class="btn btn-sm btn-outline-dark" title="Editar medidas deste produto"><i class="fa-solid fa-pen"></i></a></td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="dhl-empty"><i class="fa-solid fa-magnifying-glass"></i><strong>Nenhum produto encontrado</strong><span>Tente outro termo ou remova alguns filtros.</span></td></tr>
                @endforelse
                </tbody>
            </table>
        </div>
        <div class="dhl-pagination">{{ $products->links() }}</div>
    @else
        <div class="dhl-guidance">
            <i class="fa-solid fa-circle-info"></i>
            <div><strong>Ordem usada no checkout</strong><p>Medidas reais → perfil definido no produto → tipo reconhecido pelo nome → categoria filha → subcategoria → categoria → perfil geral.</p></div>
        </div>

        <div class="dhl-stat-grid is-rules">
            @foreach ([['Regras', $ruleStats['total'], 'fa-list-check'], ['Ativas', $ruleStats['active'], 'fa-toggle-on'], ['Revisão manual', $ruleStats['manual'], 'fa-triangle-exclamation'], ['Produtos com medidas reais', $ruleStats['exact'], 'fa-ruler-combined']] as [$label, $value, $icon])
                <div class="dhl-stat"><i class="fa-solid {{ $icon }}"></i><span>{{ $label }}</span><strong>{{ number_format($value, 0, ',', '.') }}</strong></div>
            @endforeach
        </div>

        <form method="GET" class="dhl-rule-filters">
            <input type="hidden" name="view" value="rules">
            <label class="is-search"><span>Buscar categoria</span><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Ex.: camisetas, calçados, vinhos, óculos"></label>
            <label><span>Nível</span><select class="form-select" name="level"><option value="">Todos</option><option value="category" @selected(request('level') === 'category')>Categoria</option><option value="subcategory" @selected(request('level') === 'subcategory')>Subcategoria</option><option value="childcategory" @selected(request('level') === 'childcategory')>Categoria filha</option></select></label>
            <label><span>Situação</span><select class="form-select" name="status"><option value="">Todas</option><option value="active" @selected(request('status') === 'active')>Ativas</option><option value="inactive" @selected(request('status') === 'inactive')>Inativas</option><option value="manual" @selected(request('status') === 'manual')>Revisão manual</option></select></label>
            <label><span>Perfil</span><select class="form-select" name="rule_profile"><option value="">Todos</option>@foreach($profiles as $code => $profile)<option value="{{ $code }}" @selected(request('rule_profile') === $code)>{{ $profile['label'] }}</option>@endforeach</select></label>
            <button class="btn btn-outline-dark"><i class="fa-solid fa-filter"></i>Filtrar</button>
        </form>

        @foreach ($rules as $rule)
            <form id="rule-form-{{ $rule->id }}" method="POST" action="{{ route('admin.dhl.measurements.update', $rule) }}">@csrf @method('PUT')</form>
        @endforeach

        <div class="table-responsive dhl-rule-table-wrap">
            <table class="table align-middle dhl-rule-table">
                <thead><tr><th>Hierarquia</th><th>Produtos</th><th>Peso/un. (kg)</th><th>Comp. (cm)</th><th>Larg. (cm)</th><th>Alt. (cm)</th><th>Controles</th><th></th></tr></thead>
                <tbody>
                    @forelse ($rules as $rule)
                        @php $formId = 'rule-form-'.$rule->id; $levelLabel = ['category' => 'Categoria', 'subcategory' => 'Subcategoria', 'childcategory' => 'Categoria filha'][$rule->level] ?? $rule->level; @endphp
                        <tr>
                            <td><strong>{{ $rule->scope_name }}</strong><small><span class="badge bg-secondary">{{ $levelLabel }}</span> {{ $profiles[$rule->profile_code]['label'] ?? $rule->profile_code }}</small></td>
                            <td>{{ number_format($productCounts[$rule->hierarchy_key] ?? 0, 0, ',', '.') }}</td>
                            @foreach (['weight_kg' => '0.001', 'length_cm' => '0.01', 'width_cm' => '0.01', 'height_cm' => '0.01'] as $field => $step)<td><input form="{{ $formId }}" type="number" class="form-control" name="{{ $field }}" min="0.001" step="{{ $step }}" value="{{ $rule->{$field} }}" required></td>@endforeach
                            <td><input form="{{ $formId }}" type="hidden" name="active" value="0"><div class="form-check form-switch"><input form="{{ $formId }}" class="form-check-input" type="checkbox" name="active" value="1" id="active-{{ $rule->id }}" @checked($rule->active)><label class="form-check-label" for="active-{{ $rule->id }}">Usar média</label></div><input form="{{ $formId }}" type="hidden" name="requires_manual_review" value="0"><div class="form-check form-switch"><input form="{{ $formId }}" class="form-check-input" type="checkbox" name="requires_manual_review" value="1" id="manual-{{ $rule->id }}" @checked($rule->requires_manual_review)><label class="form-check-label" for="manual-{{ $rule->id }}">Revisão manual</label></div></td>
                            <td><button form="{{ $formId }}" class="btn btn-dark btn-sm"><i class="fa-solid fa-floppy-disk"></i>Salvar</button></td>
                        </tr>
                    @empty<tr><td colspan="8" class="dhl-empty"><strong>Nenhuma regra encontrada.</strong></td></tr>@endforelse
                </tbody>
            </table>
        </div>
        <div class="dhl-pagination">{{ $rules->links() }}</div>
    @endif
</x-admin.card>
</div>
@endsection

@push('scripts')
<script src="{{ asset('js/admin-dhl-measurements.js') }}?v={{ file_exists(public_path('js/admin-dhl-measurements.js')) ? filemtime(public_path('js/admin-dhl-measurements.js')) : time() }}" defer></script>
@endpush
