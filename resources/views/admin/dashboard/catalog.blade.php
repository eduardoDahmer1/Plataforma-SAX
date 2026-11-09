@extends('layout.admin')

@section('title', 'Catálogo e estoque')

@section('content')
@include('admin.dashboard.partials.page-header', [
    'eyebrow' => 'Operação', 'title' => 'Catálogo e estoque', 'icon' => 'fa-boxes-stacked',
    'description' => 'Indicadores do catálogo carregados somente quando você precisa consultá-los.',
    'actionRoute' => route('admin.products.index'), 'actionLabel' => 'Gerenciar produtos',
])

<section class="table-card dashboard-filter-card mb-4">
    <form method="GET" class="dashboard-filter-grid dashboard-filter-grid--search">
        <div class="dashboard-filter-search"><label for="catalog-q">Buscar produto</label><input id="catalog-q" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Nome ou SKU"></div>
        <div><label for="catalog-status">Situação</label><select id="catalog-status" name="status" class="form-select"><option value="all">Todos</option><option value="active" @selected($filters['status'] === 'active')>Ativos</option><option value="inactive" @selected($filters['status'] === 'inactive')>Inativos</option></select></div>
        <div><label for="catalog-stock">Estoque</label><select id="catalog-stock" name="stock" class="form-select"><option value="all">Todos</option><option value="healthy" @selected($filters['stock'] === 'healthy')>Saudável (mais de 5)</option><option value="low" @selected($filters['stock'] === 'low')>Baixo (1 a 5)</option><option value="out" @selected($filters['stock'] === 'out')>Sem estoque</option></select></div>
        <div><label for="catalog-sort">Ordenar resultado</label><select id="catalog-sort" name="sort" class="form-select"><option value="views_desc" @selected($filters['sort'] === 'views_desc')>Mais visualizados</option><option value="stock_asc" @selected($filters['sort'] === 'stock_asc')>Menor estoque</option><option value="recent" @selected($filters['sort'] === 'recent')>Atualizados recentemente</option></select></div>
        <div class="dashboard-filter-actions"><button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i> Aplicar</button><a class="btn btn-outline-secondary" href="{{ route('admin.dashboard.catalog') }}">Limpar</a></div>
    </form>
</section>

<div class="dashboard-reading-guide mb-4"><i class="fa-solid fa-circle-info"></i><div><strong>Visão rápida do catálogo</strong><span>Os seis quadros mostram a saúde geral da base. A tabela mostra {{ number_format($filteredProducts, 0, ',', '.') }} produtos encontrados pelos filtros, limitada aos 20 primeiros para manter a página rápida.</span></div></div>

<div class="row g-3 mb-4">
    @foreach ([
        ['Produtos', $productMetrics->total ?? 0, 'fa-boxes-stacked', 'bg-blue'], ['Produtos ativos', $productMetrics->active_total ?? 0, 'fa-circle-check', 'bg-green'],
        ['Baixo estoque', $productMetrics->low_stock ?? 0, 'fa-triangle-exclamation', 'bg-gold'], ['Sem estoque', $productMetrics->out_of_stock ?? 0, 'fa-box-open', 'bg-red'],
        ['Marcas', $brandsCount, 'fa-copyright', 'bg-purple'], ['Categorias', $categoriesCount, 'fa-tags', 'bg-cyan'],
    ] as [$label,$value,$icon,$tone])
        <div class="col-6 col-lg-4 col-xl-2"><div class="metric-card"><div class="metric-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format((int) $value, 0, ',', '.') }}</div><div class="metric-note">Visão geral da base</div></div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-7"><section class="table-card"><div class="card-kicker">Resultado dos filtros</div><h2 class="card-heading mb-3">Produtos para análise</h2><div class="table-responsive"><table class="table overview-table"><thead><tr><th>#</th><th>Produto</th><th>Situação</th><th>Visualizações</th><th>Estoque</th></tr></thead><tbody>@forelse($topProducts as $product)<tr><td><span class="rank">{{ $loop->iteration }}</span></td><td><a href="{{ route('admin.products.edit', $product) }}" class="fw-bold text-dark">{{ $product->name ?: $product->external_name ?: '#'.$product->id }}</a><small class="d-block text-muted">SKU {{ $product->sku ?: 'não informado' }}</small></td><td><span class="badge-soft {{ $product->status ? 'text-success' : 'text-muted' }}">{{ $product->status ? 'Ativo' : 'Inativo' }}</span></td><td>{{ number_format($product->views ?? 0, 0, ',', '.') }}</td><td><span class="badge-soft {{ $product->stock <= 0 ? 'text-danger' : ($product->stock <= 5 ? 'text-warning' : 'text-success') }}">{{ $product->stock ?? 0 }}</span></td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Nenhum produto encontrado com estes filtros.</td></tr>@endforelse</tbody></table></div></section></div>
    <div class="col-xl-5"><section class="table-card"><h2 class="card-heading mb-3">Atenção de estoque</h2><div class="dashboard-stock-list">@forelse($lowStockProducts as $product)<a href="{{ route('admin.products.edit', $product) }}"><span>{{ $product->name ?: $product->external_name ?: '#'.$product->id }}</span><strong class="{{ $product->stock <= 0 ? 'text-danger' : 'text-warning' }}">{{ $product->stock }} un.</strong></a>@empty<div class="dashboard-empty-state"><i class="fa-solid fa-circle-check"></i><span>Nenhum produto com estoque crítico.</span></div>@endforelse</div></section></div>
</div>
@endsection
