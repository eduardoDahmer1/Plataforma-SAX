<!doctype html>
<html lang="pt-BR">
<head>
    <meta charset="utf-8">
    <link rel="stylesheet" href="{{ public_path('css/admin-report.css') }}">
</head>
<body>
    <div class="head">
        <h1>Relatório de produtos editados</h1>
        <p>{{ $periodLabel }} · {{ $start->format('d/m/Y') }} a {{ $end->format('d/m/Y') }}</p>
    </div>

    @unless($opticalAvailable)
        <p class="note">Não foi possível consultar as edições da Ótica. Este relatório mostra apenas esta loja.</p>
    @endunless

    <div class="filters">
        <strong>Filtros aplicados</strong>
        @forelse($filterLabels as $filterLabel)
            <span class="tag">{{ $filterLabel }}</span>
        @empty
            <span class="note">Nenhum filtro adicional; somente o período selecionado.</span>
        @endforelse
    </div>

    <table class="grid">
        <tr>
            <td class="metric">
                <div class="label">Produtos filtrados</div>
                <div class="value">{{ $products->count() }}</div>
            </td>
            <td class="metric">
                <div class="label">Editores envolvidos</div>
                <div class="value">{{ $reportMetrics['editors'] }}</div>
            </td>
            <td class="metric">
                <div class="label">Sem imagem</div>
                <div class="value">{{ $reportMetrics['without_image'] }}</div>
            </td>
            <td class="metric">
                <div class="label">Sem página pública</div>
                <div class="value">{{ $reportMetrics['without_front'] }}</div>
            </td>
        </tr>
    </table>

    <h2>Resumo diário</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Data</th>
                <th>Quantidade</th>
            </tr>
        </thead>
        <tbody>
            @forelse($dailyTotals as $date => $total)
                <tr>
                    <td>{{ \Carbon\Carbon::parse($date)->format('d/m/Y') }}</td>
                    <td>{{ $total }}</td>
                </tr>
            @empty
                <tr><td colspan="2">Nenhum produto editado no período.</td></tr>
            @endforelse
        </tbody>
    </table>

    <h2>Produtos</h2>
    <table class="data">
        <thead>
            <tr>
                <th>Data e hora</th>
                <th>Produto</th>
                <th>SKU</th>
                <th>Referência</th>
                <th>Origem</th>
                <th>Status</th>
                <th>Imagem</th>
                <th>Front</th>
                <th>Editado por</th>
            </tr>
        </thead>
        <tbody>
            @forelse($products as $product)
                <tr>
                    <td>{{ $product->admin_edited_at->format('d/m/Y H:i') }}</td>
                    <td>{{ $product->external_name ?: $product->name ?: 'Produto sem nome' }}</td>
                    <td>{{ $product->sku ?: '-' }}</td>
                    <td>{{ $product->ref_code ?: '-' }}</td>
                    <td>{{ $product->source_label }}</td>
                    <td>
                        {{ $product->review_status === null
                            ? 'Sem correspondente'
                            : ($product->review_status === 1 ? 'Ativo' : 'Inativo') }}
                    </td>
                    <td>{{ $product->review_has_image ? 'Sim' : 'Não' }}</td>
                    <td>{{ $product->review_front_available ? 'Disponível' : 'Indisponível' }}</td>
                    <td>{{ $product->editor_label }}</td>
                </tr>
            @empty
                <tr><td colspan="9">Nenhum produto corresponde ao período e aos filtros selecionados.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">
        Gerado em {{ now()->format('d/m/Y H:i') }}. O relatório considera a última edição administrativa registrada por SKU.
        “Sem página pública” indica que o produto não atende atualmente às regras de exibição do front.
    </div>
</body>
</html>
