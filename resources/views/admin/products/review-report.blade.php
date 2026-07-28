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

    <table class="grid">
        <tr>
            <td class="metric">
                <div class="label">Produtos editados</div>
                <div class="value">{{ $products->count() }}</div>
            </td>
            <td class="metric">
                <div class="label">Primeira data do período</div>
                <div class="value">{{ $start->format('d/m/Y') }}</div>
            </td>
            <td class="metric">
                <div class="label">Última data do período</div>
                <div class="value">{{ $end->format('d/m/Y') }}</div>
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
                    <td>{{ $product->editor?->name ?: 'Usuário removido' }}</td>
                </tr>
            @empty
                <tr><td colspan="5">Nenhum produto editado no período selecionado.</td></tr>
            @endforelse
        </tbody>
    </table>

    <div class="foot">
        Gerado em {{ now()->format('d/m/Y H:i') }}. O relatório considera a última edição administrativa registrada em cada produto.
    </div>
</body>
</html>
