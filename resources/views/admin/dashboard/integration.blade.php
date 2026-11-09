@extends('layout.admin')

@section('title', 'Integração do catálogo')

@section('content')
@include('admin.dashboard.partials.page-header', [
    'eyebrow' => 'Automação', 'title' => 'Integração de produtos', 'icon' => 'fa-arrows-rotate',
    'description' => 'Monitoramento isolado da sincronização para abrir rápido e facilitar a identificação de falhas.',
])

<section class="table-card dashboard-filter-card mb-4">
    <form method="GET" class="dashboard-filter-grid dashboard-filter-grid--compact">
        <div><label for="run-status">Filtrar histórico</label><select id="run-status" name="run_status" class="form-select"><option value="all">Todas as execuções</option><option value="running" @selected($runStatus === 'running')>Em andamento</option><option value="success" @selected($runStatus === 'success')>Concluídas com sucesso</option><option value="failed" @selected($runStatus === 'failed')>Com falha</option></select></div>
        <div class="dashboard-filter-actions"><button class="btn btn-dark" type="submit"><i class="fa-solid fa-filter me-1"></i> Aplicar</button><a class="btn btn-outline-secondary" href="{{ route('admin.dashboard.integration', ['run_status' => $runStatus, 'refresh' => 1]) }}"><i class="fa-solid fa-rotate me-1"></i> Atualizar agora</a></div>
    </form>
</section>

@php
    $status = $integrationMonitor?->status ?? 'never_reported';
    $presentation = [
        'healthy' => ['Saudável', 'success', 'fa-circle-check'], 'running' => ['Sincronização em andamento', 'warning', 'fa-arrows-rotate'],
        'failed' => ['Falha na integração', 'danger', 'fa-triangle-exclamation'], 'stale' => ['Comunicação atrasada', 'danger', 'fa-plug-circle-xmark'],
        'never_reported' => ['Aguardando primeira comunicação', 'secondary', 'fa-clock'],
    ][$status] ?? ['Status desconhecido', 'secondary', 'fa-circle-question'];
@endphp

<section class="integration-health-card integration-health-card--{{ $presentation[1] }}">
    <div class="integration-health-card__status"><span><i class="fa-solid {{ $presentation[2] }}"></i></span><div><small>Status atual</small><h2>{{ $presentation[0] }}</h2><p>O monitor é atualizado automaticamente pelo integrador.</p></div></div>
    <div class="integration-health-card__facts">
        <div><span>Última comunicação</span><strong>{{ $integrationMonitor?->last_heartbeat_at?->format('d/m/Y H:i:s') ?? 'Ainda não recebida' }}</strong></div>
        <div><span>Último sucesso</span><strong>{{ $integrationMonitor?->last_success_at?->format('d/m/Y H:i:s') ?? 'Ainda não registrado' }}</strong></div>
        <div><span>Falhas consecutivas</span><strong>{{ (int) ($integrationMonitor?->consecutive_failures ?? 0) }}</strong></div>
    </div>
</section>

@if($integrationMonitor?->error_message)
    <div class="alert alert-danger mt-4"><strong>{{ $integrationMonitor->error_code ?: 'Erro da integração' }}:</strong> {{ $integrationMonitor->error_message }}</div>
@elseif(!$integrationMonitoringReady)
    <div class="alert alert-secondary mt-4">As tabelas do monitoramento ainda não foram inicializadas.</div>
@elseif(!$integrationEndpointConfigured)
    <div class="alert alert-warning mt-4">O token do monitor de integração ainda não está configurado.</div>
@elseif(!$integrationMonitor)
    <div class="alert alert-info mt-4">O painel está aguardando o primeiro relatório enviado pelo integrador.</div>
@else
    <div class="alert alert-success mt-4"><i class="fa-solid fa-shield-check me-2"></i>O monitor está ativo. Esta página usa cache de 30 segundos para responder rapidamente.</div>
@endif

<div class="row g-3 mt-1">
    <div class="col-xl-8"><section class="table-card h-100"><div class="card-kicker">Últimas 15 execuções</div><h2 class="card-heading mb-3">Histórico da sincronização</h2><div class="table-responsive"><table class="table overview-table"><thead><tr><th>Início</th><th>Resultado</th><th>Duração</th><th>Identificador</th><th>Detalhe</th></tr></thead><tbody>@forelse($integrationRuns as $run)<tr><td>{{ $run->started_at?->format('d/m/Y H:i:s') ?: 'Não informado' }}</td><td><span class="dashboard-status dashboard-status--{{ $run->status === 'success' ? 'success' : ($run->status === 'failed' ? 'danger' : 'warning') }}">{{ ['success' => 'Sucesso', 'failed' => 'Falha', 'running' => 'Em andamento'][$run->status] ?? ucfirst($run->status) }}</span></td><td>{{ $run->duration_seconds !== null ? $run->duration_seconds.' s' : '—' }}</td><td>{{ $run->run_id ?: '—' }}</td><td class="path-cell" title="{{ $run->error_message }}">{{ $run->error_message ?: 'Sem erros registrados' }}</td></tr>@empty<tr><td colspan="5" class="text-center text-muted py-4">Nenhuma execução encontrada para este filtro.</td></tr>@endforelse</tbody></table></div></section></div>
    <div class="col-xl-4"><section class="table-card h-100"><div class="card-kicker">Guia de leitura</div><h2 class="card-heading mb-3">Quando é preciso agir?</h2><div class="dashboard-explanation-list"><div><span class="dashboard-status dashboard-status--success">Saudável</span><p>A última execução terminou corretamente. Nenhuma ação é necessária.</p></div><div><span class="dashboard-status dashboard-status--warning">Em andamento</span><p>O catálogo está sendo atualizado. Aguarde antes de investigar diferenças.</p></div><div><span class="dashboard-status dashboard-status--danger">Falha / atrasada</span><p>Confira a mensagem de erro e as execuções recentes. Falhas consecutivas exigem revisão do integrador.</p></div></div></section></div>
</div>
@endsection
