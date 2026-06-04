@if ($paginator->hasPages())
    <nav class="d-flex justify-content-between align-items-center sax-pagination-wrapper">
        <div class="d-flex justify-content-between flex-fill d-sm-none">
            <ul class="pagination pagination-sm mb-0">
                {{-- Previous Page Link --}}
                @if ($paginator->onFirstPage())
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.previous')</span>
                    </li>
                @else
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">@lang('pagination.previous')</a>
                    </li>
                @endif

                {{-- Next Page Link --}}
                @if ($paginator->hasMorePages())
                    <li class="page-item">
                        <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">@lang('pagination.next')</a>
                    </li>
                @else
                    <li class="page-item disabled" aria-disabled="true">
                        <span class="page-link">@lang('pagination.next')</span>
                    </li>
                @endif
            </ul>
        </div>

        <div class="d-none flex-sm-fill d-sm-flex align-items-sm-center justify-content-sm-between width-full-pagination">
            <div class="pagination-text-info">
                <p class="small text-muted mb-0">
                    Mostrando de 
                    <span class="font-weight-bold font-dark-info">{{ $paginator->firstItem() }}</span>
                    a 
                    <span class="font-weight-bold font-dark-info">{{ $paginator->lastItem() }}</span>
                    de um total de 
                    <span class="font-weight-bold font-dark-info">{{ $paginator->total() }}</span>
                    registros
                </p>
            </div>

            <div class="pagination-buttons-box">
                <ul class="pagination pagination-sm mb-0">
                    {{-- Previous Page Link --}}
                    @if ($paginator->onFirstPage())
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.previous')">
                            <span class="page-link" aria-hidden="true">&lsaquo;</span>
                        </li>
                    @else
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev" aria-label="@lang('pagination.previous')">&lsaquo;</a>
                        </li>
                    @endif

                    {{-- Pagination Elements --}}
                    @foreach ($elements as $element)
                        {{-- "Three Dots" Separator --}}
                        @if (is_string($element))
                            <li class="page-item disabled" aria-disabled="true"><span class="page-link">{{ $element }}</span></li>
                        @endif

                        {{-- Array Of Links --}}
                        @if (is_array($element))
                            @foreach ($element as $page => $url)
                                @if ($page == $paginator->currentPage())
                                    <li class="page-item active" aria-current="page"><span class="page-link">{{ $page }}</span></li>
                                @else
                                    <li class="page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                                @endif
                            @endforeach
                        @endif
                    @endforeach

                    {{-- Next Page Link --}}
                    @if ($paginator->hasMorePages())
                        <li class="page-item">
                            <a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next" aria-label="@lang('pagination.next')">&rsaquo;</a>
                        </li>
                    @else
                        <li class="page-item disabled" aria-disabled="true" aria-label="@lang('pagination.next')">
                            <span class="page-link" aria-hidden="true">&rsaquo;</span>
                        </li>
                    @endif
                </ul>
            </div>
        </div>
    </nav>
@endif

<style>
    /* Container Geral da Paginação */
.sax-pagination-wrapper {
    padding: 10px 0;
    width: 100%;
}

.width-full-pagination {
    width: 100%;
}

/* Customização do texto "Mostrando de X a Y" */
.pagination-text-info p {
    font-size: 14px;
    color: #333333 !important;
}

.font-dark-info {
    color: #333333;
    font-weight: 700;
}

/* ===================================================
   RESET E ESTILIZAÇÃO COMPLETA DA PAGINAÇÃO (SEM BORDAS)
   =================================================== */

/* Remove as bordas, arredondamentos e fundos de toda a lista */
.sax-pagination-wrapper .pagination .page-link {
    color: #333333 !important; /* Texto preto para os botões */
    background-color: transparent !important; /* Sem fundo */
    border: none !important; /* Remove TODAS as bordas */
    padding: 6px 12px;
    font-size: 14px;
    font-weight: 600;
    margin: 0 2px; /* Pequeno espaçamento entre os números */
    transition: all 0.2s ease;
}

/* Efeito ao passar o mouse (Hover) nos botões numéricos */
.sax-pagination-wrapper .pagination .page-item:not(.active):not(.disabled) .page-link:hover {
    background-color: #e9ecef !important; /* Fundo cinza sutil ao passar o mouse */
    color: #333333 !important;
    border-radius: 4px !important;
}

/* Página Atual Ativa (A caixinha azul do print) */
.sax-pagination-wrapper .pagination .page-item.active .page-link {
    z-index: 3;
    color: black !important;
    border-bottom: 1px solid black !important;
}

/* Botões Desabilitados (Setas apagadas quando não há mais páginas) */
.sax-pagination-wrapper .pagination .page-item.disabled .page-link {
    color: #c0c0c0 !important; /* Cinza bem clarinho/apagado */
    background-color: transparent !important;
    pointer-events: none;
    cursor: not-allowed;
    opacity: 0.6;
}

/* Remove os arredondamentos padrão dos cantos do Bootstrap nas pontas */
.sax-pagination-wrapper .pagination .page-item:first-child .page-link,
.sax-pagination-wrapper .pagination .page-item:last-child .page-link {
    border-radius: 0 !important;
}
</style>