@extends('layout.admin')

@section('content')
<div class="sax-stats-wrapper">
    <div class="dashboard-header mb-5">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-bg"><i class="fas fa-chart-line text-dark"></i></div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Performance de Edição</h2>
        </div>
        <p class="text-muted small ms-5">Acompanhamento diário de atualizações no catálogo</p>
        <div class="sax-divider-dark ms-5"></div>
    </div>

    <div class="row g-4">
        @foreach ($edicoesPorDia as $linha)
            <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                <div class="sax-stat-card border-0 shadow-sm h-100" 
                     onclick="abrirModalLocal('{{ $linha->dia }}')" 
                     style="cursor: pointer;">
                    <div class="card-content p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="date-badge text-uppercase">
                                {{ \Carbon\Carbon::parse($linha->dia)->translatedFormat('d M Y') }}
                            </span>
                            <div class="trend-icon"><i class="fas fa-eye opacity-25"></i></div>
                        </div>
                        <div class="stat-value-container">
                            <h2 class="display-5 fw-bold text-dark m-0">{{ $linha->total }}</h2>
                            <p class="stat-label text-muted text-uppercase letter-spacing-1 m-0">Produtos Editados</p>
                        </div>
                    </div>
                    <div class="card-progress-bar"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- Modal Único --}}
<div class="modal fade" id="modalDetalhesLocal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
            <div class="modal-header border-0 p-4">
                <h5 class="modal-title fw-bold text-uppercase" id="tituloModal">Detalhes</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0">
                <div class="table-responsive" style="max-height: 450px;">
                    <table class="table table-hover mb-0">
                        <thead class="bg-light sticky-top">
                            <tr>
                                <th class="ps-4 border-0 small text-muted">PRODUTO</th>
                                <th class="border-0 small text-muted text-center">SKU</th>
                                <th class="pe-4 border-0 small text-muted text-end">REF</th>
                            </tr>
                        </thead>
                        <tbody id="corpoTabelaLocal"></tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    // Injetamos os dados da controller para uma variável JS
    const dadosProdutos = @json($detalhesProdutos);

    function abrirModalLocal(data) {
        const corpo = document.getElementById('corpoTabelaLocal');
        const titulo = document.getElementById('tituloModal');
        const produtosDoDia = dadosProdutos[data] || [];

        titulo.innerText = 'Editados em ' + data;
        corpo.innerHTML = '';

        if (produtosDoDia.length === 0) {
            corpo.innerHTML = '<tr><td colspan="3" class="text-center py-4">Nenhum detalhe encontrado.</td></tr>';
        } else {
            produtosDoDia.forEach(p => {
                corpo.innerHTML += `
                    <tr>
                        <td class="ps-4"><b>${p.name}</b></td>
                        <td class="text-center"><code class="small">${p.sku || '-'}</code></td>
                        <td class="pe-4 text-end"><span class="badge bg-light text-dark border">${p.ref_code || '-'}</span></td>
                    </tr>`;
            });
        }

        new bootstrap.Modal(document.getElementById('modalDetalhesLocal')).show();
    }
</script>
@endsection

<style>
    /* Seus estilos CSS originais aqui... */
    .sax-stats-wrapper { font-family: 'Inter', sans-serif; }
    .sax-stat-card { background: #fff; border-radius: 20px; transition: 0.3s; position: relative; overflow: hidden; }
    .sax-stat-card:hover { transform: translateY(-5px); box-shadow: 0 10px 20px rgba(0,0,0,0.1) !important; }
    .card-progress-bar { position: absolute; bottom: 0; width: 100%; height: 4px; background: #eee; }
    .sax-stat-card:hover .card-progress-bar { background: #000; }
    .date-badge { background: #f8f9fa; padding: 5px 10px; border-radius: 8px; font-size: 10px; font-weight: bold; }
</style>