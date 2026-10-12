@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="Gestão de outlet"
        description="Movimente lotes com segurança e acompanhe quem enviou ou restaurou cada produto.">
        <x-slot:actions>
            <a href="{{ route('admin.products.index', ['outlet_filter' => 'outlet', 'outlet_order' => 'outlet_recent']) }}" class="btn btn-outline-dark btn-sax-lg px-4 text-uppercase fw-bold letter-spacing-1">
                <i class="fa fa-list me-2"></i> Abrir catálogo Outlet
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <x-admin.alert />

    @if (session('missing_skus') && count(session('missing_skus')))
        <div class="alert alert-warning mb-4">
            <strong>{{ count(session('missing_skus')) }} SKU(s) não encontrado(s):</strong>
            <div class="mt-2 small text-break">{{ implode(', ', session('missing_skus')) }}</div>
        </div>
    @endif

    <section class="outlet-metrics" aria-label="Resumo do Outlet">
        @foreach ([
            ['Atualmente no Outlet', $outletCount, 'Total bloqueado no e-commerce'],
            ['Com estoque', $outletWithStock, 'Itens que merecem revisão comercial'],
            ['Entraram em 30 dias', $outletLast30Days, 'Movimentação recente'],
            ['Restaurados em 30 dias', $restoredLast30Days, 'Devolvidos ao catálogo'],
        ] as [$label, $value, $note])
            <div class="outlet-metric-card">
                <span>{{ $label }}</span><strong>{{ number_format($value, 0, ',', '.') }}</strong><small>{{ $note }}</small>
            </div>
        @endforeach
    </section>

    <div class="row g-4 mt-1">
        <div class="col-xl-8">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4 p-xl-5">
                    <div class="mb-4">
                        <span class="outlet-section-kicker">Operação em lote</span>
                        <h2 class="h5 fw-bold mb-1">Enviar ou restaurar por SKU</h2>
                        <p class="small text-muted mb-0">A operação ficará registrada com data, hora e usuário responsável.</p>
                    </div>
                    <form method="POST" action="{{ route('admin.products.outlet.update') }}">
                        @csrf
                        @method('PUT')
                        <div class="mb-4">
                            <label for="skus" class="form-label fw-bold text-uppercase letter-spacing-1 small">Lista de SKUs</label>
                            <textarea id="skus" name="skus" rows="12" class="form-control outlet-skus @error('skus') is-invalid @enderror"
                                placeholder="Cole os SKUs aqui, um por linha. Também aceitamos vírgula, ponto e vírgula ou espaço." required>{{ old('skus') }}</textarea>
                            @error('skus')<div class="invalid-feedback">{{ $message }}</div>@enderror
                            <div class="form-text mt-2">Até 5.000 SKUs por operação. Duplicados e itens que já estão no estado escolhido serão ignorados.</div>
                        </div>
                        <fieldset>
                            <legend class="form-label fw-bold text-uppercase letter-spacing-1 small">O que deseja fazer?</legend>
                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label class="outlet-action-option outlet-action-danger">
                                        <input type="radio" name="action" value="outlet" @checked(old('action', 'outlet') === 'outlet') required>
                                        <span class="outlet-action-icon"><i class="fa fa-box-open"></i></span>
                                        <span><strong>Enviar para outlet</strong><small>Desativa, remove dos carrinhos e bloqueia a venda.</small></span>
                                    </label>
                                </div>
                                <div class="col-md-6">
                                    <label class="outlet-action-option outlet-action-success">
                                        <input type="radio" name="action" value="restore" @checked(old('action') === 'restore') required>
                                        <span class="outlet-action-icon"><i class="fa fa-rotate-left"></i></span>
                                        <span><strong>Voltar ao normal</strong><small>Restaura o status anterior do produto.</small></span>
                                    </label>
                                </div>
                            </div>
                            @error('action')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                        </fieldset>
                        <div class="d-flex justify-content-end mt-4">
                            <button type="submit" class="btn btn-dark px-5 py-3 text-uppercase fw-bold letter-spacing-1"
                                onclick="return confirm('Confirma a alteração em lote para todos os SKUs informados?')">Aplicar aos produtos</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="col-xl-4">
            <div class="outlet-guidance-card h-100">
                <span class="outlet-section-kicker">Checklist editorial</span>
                <h2 class="h5 fw-bold">Antes de movimentar</h2>
                <ul>
                    <li><i class="fa fa-check"></i><span>Confirme o estoque e o preço do lote.</span></li>
                    <li><i class="fa fa-check"></i><span>Revise se produtos pai e filhos devem seguir juntos.</span></li>
                    <li><i class="fa fa-check"></i><span>Use os filtros abaixo para evitar itens antigos esquecidos.</span></li>
                    <li><i class="fa fa-shield-alt"></i><span>Itens no Outlet ficam fora do catálogo, busca, carrinho e checkout.</span></li>
                </ul>
                <a href="{{ route('admin.products.index', ['outlet_filter' => 'outlet', 'outlet_stock' => 'in_stock']) }}" class="btn btn-outline-dark w-100">Revisar Outlet com estoque</a>
            </div>
        </div>
    </div>

    <section class="outlet-audit-section mt-4" aria-labelledby="outletAuditTitle">
        <div class="outlet-audit-heading">
            <div>
                <span class="outlet-section-kicker">Consulta e auditoria</span>
                <h2 id="outletAuditTitle" class="h5 fw-bold mb-1">Produtos atualmente no Outlet</h2>
                <p class="small text-muted mb-0">Filtre por produto, período, responsável ou situação de estoque.</p>
            </div>
            <span class="badge bg-dark">{{ number_format($outletProducts->total(), 0, ',', '.') }} resultado(s)</span>
        </div>

        <form method="GET" action="{{ route('admin.products.outlet.form') }}" class="outlet-audit-filters">
            <div class="outlet-audit-search"><label for="outlet_search">Produto ou SKU</label><input type="search" id="outlet_search" name="outlet_search" class="form-control" value="{{ request('outlet_search') }}" placeholder="Nome ou SKU"></div>
            <div><label for="audit_outlet_from">Desde</label><input type="date" id="audit_outlet_from" name="outlet_from" class="form-control" value="{{ request('outlet_from') }}"></div>
            <div><label for="audit_outlet_to">Até</label><input type="date" id="audit_outlet_to" name="outlet_to" class="form-control" value="{{ request('outlet_to') }}"></div>
            <div><label for="audit_outlet_user">Enviado por</label><select id="audit_outlet_user" name="outlet_user_id" class="form-select"><option value="">Todos</option>@foreach($outletUsers as $outletUser)<option value="{{ $outletUser->id }}" @selected(request('outlet_user_id') == $outletUser->id)>{{ $outletUser->name }}</option>@endforeach</select></div>
            <div><label for="audit_outlet_stock">Estoque</label><select id="audit_outlet_stock" name="outlet_stock" class="form-select"><option value="">Todos</option><option value="in_stock" @selected(request('outlet_stock') === 'in_stock')>Com estoque</option><option value="out_of_stock" @selected(request('outlet_stock') === 'out_of_stock')>Sem estoque</option></select></div>
            <div class="outlet-audit-filter-actions"><button class="btn btn-dark" type="submit"><i class="fa fa-filter me-1"></i> Filtrar</button><a href="{{ route('admin.products.outlet.form') }}" class="btn btn-outline-secondary" title="Limpar filtros"><i class="fa fa-times"></i></a></div>
        </form>

        <div class="row g-4">
            <div class="col-xl-8">
                <div class="table-responsive outlet-audit-table-wrap">
                    <table class="table outlet-audit-table align-middle mb-0">
                        <thead><tr><th>Produto</th><th>Estoque</th><th>Entrada</th><th>Responsável</th><th></th></tr></thead>
                        <tbody>
                            @forelse($outletProducts as $product)
                                <tr>
                                    <td><strong>{{ $product->name ?: $product->external_name }}</strong><span>SKU {{ $product->sku }}{{ $product->brand ? ' · '.$product->brand->name : '' }}</span></td>
                                    <td><span class="outlet-stock-badge {{ $product->stock > 0 ? 'has-stock' : 'no-stock' }}">{{ $product->stock }}</span></td>
                                    <td>{{ $product->outlet_at?->format('d/m/Y H:i') ?? 'Legado' }}</td>
                                    <td>{{ $product->outletOperator?->name ?? 'Sistema/legado' }}</td>
                                    <td class="text-end"><a href="{{ route('admin.products.edit', $product) }}" class="btn btn-sm btn-outline-dark" title="Editar produto"><i class="far fa-edit"></i></a></td>
                                </tr>
                            @empty
                                <tr><td colspan="5" class="text-center text-muted py-5">Nenhum produto encontrado com estes filtros.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($outletProducts->hasPages())<div class="mt-3">{{ $outletProducts->links() }}</div>@endif
            </div>
            <div class="col-xl-4">
                <div class="outlet-history-card">
                    <h3 class="h6 fw-bold mb-3">Histórico recente</h3>
                    <div class="outlet-history-list">
                        @forelse($recentOutletEvents as $event)
                            <div class="outlet-history-item">
                                <span class="outlet-history-icon {{ $event->action === 'outlet' ? 'is-outlet' : 'is-restore' }}"><i class="fa {{ $event->action === 'outlet' ? 'fa-box-open' : 'fa-rotate-left' }}"></i></span>
                                <div><strong>{{ $event->product_name ?: ($event->product?->name ?: $event->product?->external_name) ?: 'Produto removido' }}</strong><span>SKU {{ $event->sku ?: '—' }} · {{ $event->action === 'outlet' ? 'Enviado ao Outlet' : 'Restaurado' }}</span><small>{{ $event->created_at?->format('d/m/Y H:i') }} · {{ $event->operator?->name ?? 'Sistema' }}</small></div>
                            </div>
                        @empty
                            <p class="small text-muted mb-0">O histórico começará a aparecer nas próximas movimentações.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </section>
</x-admin.card>
@endsection
