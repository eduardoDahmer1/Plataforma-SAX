@extends('layout.admin')

@section('content')
<div class="sax-stats-wrapper">
    {{-- Cabeçalho com Contexto --}}
    <div class="dashboard-header mb-5">
        <div class="d-flex align-items-center gap-3 mb-2">
            <div class="header-icon-bg">
                <i class="fas fa-chart-line text-dark"></i>
            </div>
            <h2 class="sax-title text-uppercase letter-spacing-2 m-0">Performance de Edição</h2>
        </div>
        <p class="text-muted small ms-5">Acompanhamento diário de atualizações no catálogo de produtos</p>
        <div class="sax-divider-dark ms-5"></div>
    </div>

    <div class="row g-4">
        @foreach ($edicoesPorDia as $linha)
            <div class="col-12 col-sm-6 col-md-4 col-xl-3">
                <div class="sax-stat-card border-0 shadow-sm h-100">
                    <div class="card-content p-4">
                        <div class="d-flex justify-content-between align-items-start mb-3">
                            <span class="date-badge text-uppercase">
                                {{ \Carbon\Carbon::parse($linha->dia)->translatedFormat('d M Y') }}
                            </span>
                            <div class="trend-icon">
                                <i class="fas fa-history opacity-25"></i>
                            </div>
                        </div>
                        
                        <div class="stat-value-container">
                            <h2 class="display-5 fw-bold text-dark m-0">{{ $linha->total }}</h2>
                            <p class="stat-label text-muted text-uppercase letter-spacing-1 m-0">Produtos Editados</p>
                        </div>
                    </div>
                    {{-- Uma barra sutil de progresso ou decorativa no rodapé do card --}}
                    <div class="card-progress-bar"></div>
                </div>
            </div>
        @endforeach
    </div>
</div>
@endsection
<style>
    /* Wrapper Principal */
.sax-stats-wrapper { font-family: 'Inter', sans-serif; }

/* Cabeçalho Customizado */
.header-icon-bg {
    width: 45px;
    height: 45px;
    background: #f0f0f0;
    border-radius: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.2rem;
}

.sax-divider-dark {
    width: 60px;
    height: 3px;
    background: #000;
}

/* Stat Cards */
.sax-stat-card {
    background: #ffffff;
    border-radius: 20px;
    position: relative;
    overflow: hidden;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}

.sax-stat-card:hover {
    transform: translateY(-8px);
    box-shadow: 0 15px 30px rgba(0,0,0,0.08) !important;
}

.date-badge {
    background: #f8f9fa;
    padding: 6px 12px;
    border-radius: 8px;
    font-size: 0.65rem;
    font-weight: 800;
    color: #666;
    letter-spacing: 0.5px;
}

.stat-value-container {
    padding-top: 10px;
}

.stat-label {
    font-size: 0.6rem;
    font-weight: 700;
}

.display-5 {
    letter-spacing: -1px;
}

/* Detalhe de Design: Barra Inferior */
.card-progress-bar {
    position: absolute;
    bottom: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background: #e9ecef;
}

.sax-stat-card:hover .card-progress-bar {
    background: #000; /* Transição para preto no hover */
    transition: background 0.4s;
}

/* Ajustes de Responsividade */
@media (max-width: 576px) {
    .sax-stat-card {
        text-align: center;
    }
    .sax-stat-card .d-flex {
        justify-content: center !important;
    }
}
</style>