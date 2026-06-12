@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="d-flex justify-content-between align-items-center mb-4">
        <x-admin.page-header title="{{ __('messages.performance_edicao_titulo') }}" description="{{ __('messages.performance_edicao_desc') }}"></x-admin.page-header>
        
        <select class="form-select w-auto" onchange="window.location.href='?mes=' + this.value">
            @foreach($mesesDisponiveis as $m)
                <option value="{{ $m['value'] }}" {{ $mesSelecionado == $m['value'] ? 'selected' : '' }}>
                    {{ ucfirst($m['label']) }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="sax-stats-wrapper">
        <div class="row g-4">
            @forelse ($edicoesPorDia as $linha)
                <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                    <div class="sax-stat-card border-0 shadow-sm h-100" 
                         onclick="abrirModalLocal('{{ $linha->dia }}')" 
                         style="cursor: pointer;">
                        <div class="card-content p-4">
                            <div class="d-flex justify-content-between align-items-start mb-3">
                                <span class="date-badge text-uppercase">
                                    {{ \Carbon\Carbon::parse($linha->dia)->translatedFormat('d M Y') }}
                                </span>
                            </div>
                            <div class="stat-value-container">
                                <h2 class="display-5 fw-bold text-dark m-0">{{ $linha->total }}</h2>
                                <p class="stat-label text-muted text-uppercase letter-spacing-1 m-0">{{ __('messages.produtos_editados_label') }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <p class="text-center p-5">Nenhum registro encontrado para este mês.</p>
            @endforelse
        </div>
    </div>

    
    {{-- Modal Único --}}
    <div class="modal fade" id="modalDetalhesLocal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content border-0 shadow-lg" style="border-radius: 20px;">
                <div class="modal-header border-0 p-4">
                    <h5 class="modal-title fw-bold text-uppercase" id="tituloModal">{{ __('messages.detalhes_modal_titulo') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 450px;">
                        <table class="table table-hover mb-0">
                            <thead class="bg-light sticky-top">
                                <tr>
                                    <th class="ps-4 border-0 small text-muted">{{ __('messages.col_produto') }}</th>
                                    <th class="border-0 small text-muted text-center">{{ __('messages.col_sku') }}</th>
                                    <th class="pe-4 border-0 small text-muted text-end">{{ __('messages.col_ref') }}</th>
                                </tr>
                            </thead>
                            <tbody id="corpoTabelaLocal"></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script>var dadosProdutos = @json($detalhesProdutos);</script>
</x-admin.card>
@endsection