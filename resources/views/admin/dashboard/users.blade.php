@extends('layout.admin')

@section('title', 'Painel de usuários')

@section('content')
@include('admin.dashboard.partials.page-header', [
    'eyebrow' => 'Clientes', 'title' => 'Usuários', 'icon' => 'fa-users',
    'description' => 'Cadastros, contas verificadas e origem dos clientes em uma página própria.',
    'actionRoute' => route('admin.clients.index'), 'actionLabel' => 'Abrir lista de clientes',
])

<section class="table-card dashboard-filter-card mb-4">
    <form method="GET" class="dashboard-filter-grid dashboard-filter-grid--search">
        <div class="dashboard-filter-search"><label for="users-q">Buscar cliente</label><input id="users-q" name="q" class="form-control" value="{{ $filters['q'] }}" placeholder="Nome ou e-mail"></div>
        <div><label for="users-period">Cadastro no período</label><select id="users-period" name="period" class="form-select"><option value="7" @selected($filters['period'] === '7')>Últimos 7 dias</option><option value="30" @selected($filters['period'] === '30')>Últimos 30 dias</option><option value="90" @selected($filters['period'] === '90')>Últimos 90 dias</option><option value="all" @selected($filters['period'] === 'all')>Todo o histórico</option></select></div>
        <div><label for="users-verification">Verificação do e-mail</label><select id="users-verification" name="verification" class="form-select"><option value="all">Todos</option><option value="verified" @selected($filters['verification'] === 'verified')>Verificado</option><option value="unverified" @selected($filters['verification'] === 'unverified')>Não verificado</option></select></div>
        <div><label for="users-country">País informado</label><select id="users-country" name="country" class="form-select"><option value="">Todos</option>@foreach($countryOptions as $country)<option value="{{ $country }}" @selected($filters['country'] === $country)>{{ $country }}</option>@endforeach</select></div>
        <div class="dashboard-filter-actions"><button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i> Aplicar</button><a class="btn btn-outline-secondary" href="{{ route('admin.dashboard.users') }}">Limpar</a></div>
    </form>
</section>

<div class="dashboard-reading-guide mb-4"><i class="fa-solid fa-circle-info"></i><div><strong>O que este recorte mostra</strong><span>Foram encontrados {{ number_format($filteredCustomers, 0, ',', '.') }} clientes em {{ mb_strtolower($periodLabel) }}. A verificação confirma o e-mail; não significa que o cliente já realizou uma compra.</span></div></div>

<div class="row g-3 mb-4">
    @foreach ([['Resultado dos filtros', $filteredCustomers, 'fa-filter', 'bg-blue', $periodLabel], ['E-mails verificados', $verifiedCustomers, 'fa-envelope-circle-check', 'bg-green', 'Dentro do resultado'], ['E-mails não verificados', $unverifiedCustomers, 'fa-envelope-open', 'bg-gold', 'Podem exigir acompanhamento'], ['Base total de clientes', $totalCustomers, 'fa-user-group', 'bg-purple', 'Todo o histórico']] as [$label,$value,$icon,$tone,$note])
        <div class="col-12 col-sm-6 col-xl-3"><div class="metric-card"><div class="metric-icon {{ $tone }}"><i class="fa-solid {{ $icon }}"></i></div><div class="metric-label">{{ $label }}</div><div class="metric-value">{{ number_format($value, 0, ',', '.') }}</div><div class="metric-note">{{ $note }}</div></div></div>
    @endforeach
</div>

<div class="row g-3">
    <div class="col-xl-8"><section class="table-card"><div class="card-kicker">Até 20 registros</div><h2 class="card-heading mb-3">Cadastros encontrados</h2><div class="table-responsive"><table class="table overview-table"><thead><tr><th>Cliente</th><th>E-mail</th><th>Verificação</th><th>País</th><th>Cadastro</th></tr></thead><tbody>@forelse($recentCustomers as $customer)<tr><td><a href="{{ route('admin.clients.show', $customer) }}" class="fw-bold text-dark">{{ $customer->name }}</a></td><td>{{ $customer->email }}</td><td><span class="badge-soft {{ $customer->email_verified_at ? 'text-success' : 'text-warning' }}">{{ $customer->email_verified_at ? 'Verificado' : 'Pendente' }}</span></td><td>{{ $customer->country ?: 'Não informado' }}</td><td>{{ $customer->created_at?->format('d/m/Y H:i') }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Nenhum cliente encontrado com estes filtros.</td></tr>@endforelse</tbody></table></div></section></div>
    <div class="col-xl-4"><section class="table-card"><h2 class="card-heading">Clientes por país</h2><div class="dashboard-breakdown-list mt-3">@forelse($customerCountries as $country)<div><span>{{ $country->country_label }}</span><strong>{{ number_format($country->total, 0, ',', '.') }}</strong></div>@empty<p class="text-muted small mb-0">Sem dados de país.</p>@endforelse</div></section></div>
</div>
@endsection
