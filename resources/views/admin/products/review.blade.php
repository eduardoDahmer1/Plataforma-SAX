@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
        <x-admin.page-header title="{{ __('messages.performance_edicao_titulo') }}" description="{{ __('messages.performance_edicao_desc') }}"></x-admin.page-header>
        
        <select class="form-select w-auto" onchange="window.location.href='?mes=' + this.value">
            @foreach($mesesDisponiveis as $m)
                <option value="{{ $m['value'] }}" {{ $mesSelecionado == $m['value'] ? 'selected' : '' }}>
                    {{ ucfirst($m['label']) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="border rounded-4 bg-light p-3 mb-4">
        <form method="GET" action="{{ route('admin.products.review.pdf') }}" id="product-review-report-form">
            <input type="hidden" name="filter_search" id="report-filter-search">
            <input type="hidden" name="filter_editor" id="report-filter-editor">
            <input type="hidden" name="filter_source" id="report-filter-source">
            <input type="hidden" name="filter_status" id="report-filter-status">
            <input type="hidden" name="filter_image" id="report-filter-image">
            <input type="hidden" name="filter_front" id="report-filter-front">

            <div class="product-review-export-note d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="fa-solid fa-circle-info text-primary mt-1"></i>
                    <div>
                        <strong class="d-block small">O PDF acompanha a sua seleção</strong>
                        <span class="small text-muted">Escolha o período aqui. Busca, editor, origem, status, imagem e disponibilidade no front serão copiados dos filtros abaixo.</span>
                    </div>
                </div>
                <span class="badge bg-white text-dark border" id="product-review-pdf-filter-summary">Sem filtros adicionais</span>
            </div>

            <div class="row g-3 align-items-end">
                <div class="col-12 col-lg-3">
                    <label class="sax-form-label" for="report-period">
                        <i class="fa-regular fa-file-pdf me-1"></i> Selecionar relatório
                    </label>
                    <select class="form-select sax-input" id="report-period" name="period">
                        <option value="day">Por dia</option>
                        <option value="week">Por semana</option>
                        <option value="month">Por mês</option>
                        <option value="custom">Por período de datas</option>
                    </select>
                </div>

                <div class="col-12 col-lg-5" data-report-field="day">
                    <label class="sax-form-label" for="report-day">Dia</label>
                    <input class="form-control sax-input" id="report-day" type="date" name="day" value="{{ now()->format('Y-m-d') }}" required>
                </div>

                <div class="col-12 col-lg-5 d-none" data-report-field="week">
                    <label class="sax-form-label" for="report-week">Semana</label>
                    <input class="form-control sax-input" id="report-week" type="week" name="week" value="{{ now()->format('o-\WW') }}" disabled>
                </div>

                <div class="col-12 col-lg-5 d-none" data-report-field="month">
                    <label class="sax-form-label" for="report-month">Mês</label>
                    <input class="form-control sax-input" id="report-month" type="month" name="month" value="{{ $mesSelecionado }}" disabled>
                </div>

                <div class="col-12 col-lg-5 d-none" data-report-field="custom">
                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <label class="sax-form-label" for="report-start">Data inicial</label>
                            <input class="form-control sax-input" id="report-start" type="date" name="start_date" value="{{ now()->startOfMonth()->format('Y-m-d') }}" disabled>
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="sax-form-label" for="report-end">Data final</label>
                            <input class="form-control sax-input" id="report-end" type="date" name="end_date" value="{{ now()->format('Y-m-d') }}" disabled>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-4">
                    <button class="btn btn-dark w-100" type="submit">
                        <i class="fa-solid fa-download me-1"></i> Baixar PDF com filtros
                    </button>
                </div>
            </div>
        </form>
    </div>

    @unless($opticalAvailable)
        <div class="alert alert-warning" role="alert">Não foi possível consultar as edições da Ótica. Os resultados abaixo mostram apenas esta loja.</div>
    @endunless

    <div class="product-review-filters border rounded-4 p-3 mb-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
            <div>
                <h2 class="h6 fw-bold mb-1"><i class="fa-solid fa-filter me-1"></i> Filtrar produtos editados</h2>
                <p class="small text-muted mb-0">Os cartões abaixo são atualizados automaticamente.</p>
            </div>
            <button class="btn btn-sm btn-outline-secondary" id="product-review-clear" type="button">
                <i class="fa-solid fa-rotate-left me-1"></i> Limpar filtros
            </button>
        </div>
        <div class="row g-2">
            <div class="col-12 col-lg-5">
                <label class="visually-hidden" for="product-review-search">Buscar produto</label>
                <div class="input-group">
                    <span class="input-group-text bg-white"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input class="form-control" id="product-review-search" type="search" placeholder="Nome, SKU ou referência">
                </div>
            </div>
            <div class="col-12 col-sm-4 col-lg">
                <label class="visually-hidden" for="product-review-editor">Editor</label>
                <select class="form-select" id="product-review-editor">
                    <option value="">Todos os editores</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-lg">
                <label class="visually-hidden" for="product-review-source">Origem</label>
                <select class="form-select" id="product-review-source">
                    <option value="">Todas as origens</option>
                    <option value="plataforma">SAX</option>
                    <option value="otica">Ótica</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-lg">
                <label class="visually-hidden" for="product-review-status">Status</label>
                <select class="form-select" id="product-review-status">
                    <option value="">Todos os status</option>
                    <option value="1">Ativos</option>
                    <option value="0">Inativos</option>
                    <option value="unknown">Sem correspondente</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-lg">
                <label class="visually-hidden" for="product-review-image">Imagem</label>
                <select class="form-select" id="product-review-image">
                    <option value="">Todas as imagens</option>
                    <option value="with">Com imagem</option>
                    <option value="without">Sem imagem</option>
                </select>
            </div>
            <div class="col-6 col-sm-4 col-lg">
                <label class="visually-hidden" for="product-review-front">Página pública</label>
                <select class="form-select" id="product-review-front">
                    <option value="">Todo o front</option>
                    <option value="available">Disponíveis no front</option>
                    <option value="unavailable">Sem página pública</option>
                </select>
            </div>
        </div>
        <p class="small text-muted mb-0 mt-3" id="product-review-result-count" aria-live="polite"></p>
        <div class="product-review-quick-filters d-flex flex-wrap align-items-center gap-2 mt-2">
            <span class="small text-muted me-1">Atalhos para revisar:</span>
            <button class="btn btn-sm btn-light border" type="button" data-review-quick-filter="inactive">
                <strong id="product-review-inactive-count">0</strong> inativos
            </button>
            <button class="btn btn-sm btn-light border" type="button" data-review-quick-filter="without-image">
                <strong id="product-review-without-image-count">0</strong> sem imagem
            </button>
            <button class="btn btn-sm btn-light border" type="button" data-review-quick-filter="without-front">
                <strong id="product-review-without-front-count">0</strong> sem página pública
            </button>
        </div>
        <details class="product-review-help mt-3">
            <summary class="small fw-semibold">Como interpretar estes dados</summary>
            <ul class="small text-muted mb-0 mt-2 ps-3">
                <li><strong>Origem</strong> indica em qual catálogo ocorreu a edição mais recente.</li>
                <li><strong>Sem correspondente</strong> significa que o SKU editado não foi localizado nesta loja.</li>
                <li><strong>Sem página pública</strong> indica que o produto abriria uma página indisponível no front.</li>
                <li>Quando o mesmo SKU aparece nas duas bases, vale somente a edição administrativa mais recente.</li>
            </ul>
        </details>
    </div>

    <script type="application/json" id="product-review-json">@json($detalhesProdutos)</script>
    <div class="sax-stats-wrapper" id="product-review-data">
        <div class="row g-3 g-lg-4">
            @forelse ($edicoesPorDia as $linha)
                <div class="col-12 col-sm-6 col-md-4 col-xl-3 product-review-day" data-review-day="{{ $linha->dia }}">
                    <button class="sax-stat-card product-review-day-button border-0 shadow-sm h-100 w-100 text-start"
                            type="button" onclick="abrirModalLocal('{{ $linha->dia }}')">
                        <span class="card-content d-block p-3 p-lg-4">
                            <span class="d-flex justify-content-between align-items-start mb-3">
                                <span class="date-badge text-uppercase">
                                    {{ \Carbon\Carbon::parse($linha->dia)->translatedFormat('d M Y') }}
                                </span>
                                <i class="fa-solid fa-chevron-right text-muted small"></i>
                            </span>
                            <span class="stat-value-container d-block">
                                <strong class="product-review-day-count display-5 fw-bold text-dark d-block">{{ $linha->total }}</strong>
                                <span class="stat-label text-muted text-uppercase letter-spacing-1">{{ __('messages.produtos_editados_label') }}</span>
                            </span>
                        </span>
                    </button>
                </div>
            @empty
                <p class="text-center p-5">Nenhum registro encontrado para este mês.</p>
            @endforelse
            <div class="col-12 d-none" id="product-review-empty">
                <div class="text-center py-5 text-muted">
                    <i class="fa-regular fa-folder-open fa-2x mb-2"></i>
                    <p class="mb-0">Nenhum produto corresponde aos filtros selecionados.</p>
                </div>
            </div>
        </div>
    </div>


    {{-- Modal Único --}}
    <div class="modal fade product-review-modal" id="modalDetalhesLocal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow-lg">
                <div class="modal-header border-0 px-3 px-lg-4 pt-3 pt-lg-4 pb-2">
                    <div>
                        <h5 class="modal-title fw-bold text-uppercase mb-1" id="tituloModal">{{ __('messages.detalhes_modal_titulo') }}</h5>
                        <p class="small text-muted mb-0" id="product-review-modal-count"></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive product-review-table-wrap">
                        <table class="table table-hover align-middle mb-0 product-review-table">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4 border-0 small text-muted">{{ __('messages.col_produto') }}</th>
                                    <th class="border-0 small text-muted">Identificação</th>
                                    <th class="border-0 small text-muted">Edição</th>
                                    <th class="pe-4 border-0 small text-muted text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="corpoTabelaLocal"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection
