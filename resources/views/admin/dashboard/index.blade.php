@extends('layout.admin')

@section('title', 'Central administrativa')

@section('content')
<section class="overview-hero dashboard-hub-hero mb-4">
    <div class="d-md-flex justify-content-between align-items-center position-relative" style="z-index:1">
        <div>
            <div class="overview-eyebrow">Central administrativa</div>
            <h1 class="overview-title">O que você quer acompanhar?</h1>
            <p class="overview-subtitle">Cada área abre separadamente para manter o painel organizado, rápido e fácil de consultar.</p>
        </div>
        <div class="overview-date"><i class="fa-regular fa-calendar me-2"></i>{{ now()->translatedFormat('d \d\e F \d\e Y') }}</div>
    </div>
</section>

@php
    $dashboardSections = [
        ['admin.dashboard.orders', 'Pedidos', 'Acompanhe pagamentos, andamento, formas de pagamento e os pedidos mais recentes.', 'fa-bag-shopping', 'blue', 'Vendas'],
        ['admin.dashboard.integration', 'Integração', 'Veja a comunicação do catálogo, último sucesso, falhas e saúde da sincronização.', 'fa-arrows-rotate', 'green', 'Automação'],
        ['admin.dashboard.users', 'Usuários', 'Consulte clientes cadastrados, novos usuários, países e contas verificadas.', 'fa-users', 'purple', 'Clientes'],
        ['admin.dashboard.metrics', 'Métricas e tráfego', 'Analise visitantes, páginas vistas, cliques, dispositivos e telas utilizadas.', 'fa-chart-line', 'gold', 'Audiência'],
        ['admin.dashboard.catalog', 'Catálogo e estoque', 'Monitore produtos ativos, baixo estoque, marcas, categorias e itens mais vistos.', 'fa-boxes-stacked', 'cyan', 'Operação'],
        ['admin.dashboard.reports', 'Relatórios', 'Escolha um período, confira resultados comerciais e gere o relatório em PDF.', 'fa-file-lines', 'red', 'Resultados'],
    ];
@endphp

<section class="dashboard-hub-grid" aria-label="Áreas da visão geral">
    @foreach ($dashboardSections as [$routeName, $title, $description, $icon, $tone, $eyebrow])
        <a href="{{ route($routeName) }}" class="dashboard-hub-card dashboard-hub-card--{{ $tone }}">
            <span class="dashboard-hub-card__icon"><i class="fa-solid {{ $icon }}"></i></span>
            <span class="dashboard-hub-card__content">
                <small>{{ $eyebrow }}</small>
                <strong>{{ $title }}</strong>
                <span>{{ $description }}</span>
            </span>
            <span class="dashboard-hub-card__action">Abrir área <i class="fa-solid fa-arrow-right"></i></span>
        </a>
    @endforeach
</section>

<section class="dashboard-hub-shortcuts table-card mt-4">
    <div>
        <div class="card-kicker">Acessos rápidos</div>
        <h2 class="card-heading">Ferramentas usadas no dia a dia</h2>
    </div>
    <div class="dashboard-hub-shortcuts__links">
        <a href="{{ route('admin.notifications.index') }}"><i class="fa-regular fa-bell"></i> Notificações</a>
        <a href="{{ route('admin.orders.index') }}"><i class="fa-solid fa-list-check"></i> Todos os pedidos</a>
        <a href="{{ route('admin.products.index') }}"><i class="fa-solid fa-box"></i> Produtos</a>
        <a href="{{ route('admin.clients.index') }}"><i class="fa-regular fa-address-book"></i> Clientes</a>
        <a href="{{ route('admin.store-controls.edit') }}"><i class="fa-solid fa-sliders"></i> Controle da loja</a>
    </div>
</section>
@endsection
