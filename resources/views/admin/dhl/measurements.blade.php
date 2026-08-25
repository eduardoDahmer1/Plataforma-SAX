@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Medidas médias DHL"
        description="Configure peso e dimensões médias por categoria. O checkout usa a regra mais específica disponível para cada produto." />

    <x-admin.alert />

    @if ($errors->any())
        <div class="alert alert-danger">
            <strong>Não foi possível salvar:</strong>
            <ul class="mb-0 mt-2">@foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>
        </div>
    @endif

    <div class="d-flex flex-wrap justify-content-between gap-2 mb-4">
        <a href="{{ route('admin.dhl.edit') }}" class="btn btn-outline-dark"><i class="fa-solid fa-arrow-left me-2"></i>Voltar para DHL</a>
        <form method="POST" action="{{ route('admin.dhl.measurements.sync') }}">
            @csrf
            <button class="btn btn-dark"><i class="fa-solid fa-rotate me-2"></i>Sincronizar novas categorias</button>
        </form>
    </div>

    <div class="alert alert-warning border-0">
        <strong>Ordem usada no checkout:</strong> medidas reais → perfil definido no produto → tipo reconhecido pelo nome → categoria filha → subcategoria → categoria → perfil geral.
        As médias são sempre por unidade, multiplicadas pela quantidade e empacotadas automaticamente. Itens sujeitos à revisão continuam sendo cotados e ficam sinalizados para a expedição.
    </div>

    <div class="row g-3 mb-4">
        @foreach ([['Regras', $stats['total']], ['Ativas', $stats['active']], ['Revisão manual', $stats['manual']], ['Produtos com medidas reais', $stats['exact']]] as [$label, $value])
            <div class="col-6 col-xl-3"><div class="border rounded-3 p-3 h-100"><div class="small text-muted text-uppercase">{{ $label }}</div><strong class="fs-4">{{ number_format($value, 0, ',', '.') }}</strong></div></div>
        @endforeach
    </div>

    <form method="GET" class="border rounded-3 p-3 mb-4">
        <div class="row g-2 align-items-end">
            <div class="col-lg-6"><label class="form-label fw-bold">Buscar categoria</label><input class="form-control" name="q" value="{{ request('q') }}" placeholder="Ex.: camisetas, calçados, vinhos, óculos"></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label fw-bold">Nível</label><select class="form-select" name="level"><option value="">Todos</option><option value="category" @selected(request('level') === 'category')>Categoria</option><option value="subcategory" @selected(request('level') === 'subcategory')>Subcategoria</option><option value="childcategory" @selected(request('level') === 'childcategory')>Categoria filha</option></select></div>
            <div class="col-sm-6 col-lg-2"><label class="form-label fw-bold">Situação</label><select class="form-select" name="status"><option value="">Todas</option><option value="active" @selected(request('status') === 'active')>Ativas</option><option value="inactive" @selected(request('status') === 'inactive')>Inativas</option><option value="manual" @selected(request('status') === 'manual')>Revisão manual</option></select></div>
            <div class="col-lg-2 d-grid"><button class="btn btn-outline-dark"><i class="fa-solid fa-magnifying-glass me-2"></i>Filtrar</button></div>
        </div>
    </form>

    @foreach ($rules as $rule)
        <form id="rule-form-{{ $rule->id }}" method="POST" action="{{ route('admin.dhl.measurements.update', $rule) }}">
            @csrf
            @method('PUT')
        </form>
    @endforeach

    <div class="table-responsive border rounded-3">
        <table class="table align-middle mb-0">
            <thead class="table-light">
                <tr><th style="min-width:300px">Hierarquia</th><th>Produtos</th><th>Peso/un. (kg)</th><th>Comp. (cm)</th><th>Larg. (cm)</th><th>Alt. (cm)</th><th>Controles</th><th></th></tr>
            </thead>
            <tbody>
                @forelse ($rules as $rule)
                    @php
                        $formId = 'rule-form-'.$rule->id;
                        $levelLabel = ['category' => 'Categoria', 'subcategory' => 'Subcategoria', 'childcategory' => 'Categoria filha'][$rule->level] ?? $rule->level;
                    @endphp
                    <tr>
                        <td>
                            <strong>{{ $rule->scope_name }}</strong>
                            <div class="small text-muted"><span class="badge bg-secondary me-1">{{ $levelLabel }}</span> Perfil inicial: {{ $rule->profile_code }}</div>
                        </td>
                        <td>{{ number_format($productCounts[$rule->hierarchy_key] ?? 0, 0, ',', '.') }}</td>
                        @foreach (['weight_kg' => '0.001', 'length_cm' => '0.01', 'width_cm' => '0.01', 'height_cm' => '0.01'] as $field => $step)
                            <td><input form="{{ $formId }}" type="number" class="form-control" style="min-width:95px" name="{{ $field }}" min="0.001" step="{{ $step }}" value="{{ $rule->{$field} }}" required></td>
                        @endforeach
                        <td style="min-width:175px">
                            <input form="{{ $formId }}" type="hidden" name="active" value="0">
                            <div class="form-check form-switch"><input form="{{ $formId }}" class="form-check-input" type="checkbox" name="active" value="1" id="active-{{ $rule->id }}" @checked($rule->active)><label class="form-check-label" for="active-{{ $rule->id }}">Usar média</label></div>
                            <input form="{{ $formId }}" type="hidden" name="requires_manual_review" value="0">
                            <div class="form-check form-switch"><input form="{{ $formId }}" class="form-check-input" type="checkbox" name="requires_manual_review" value="1" id="manual-{{ $rule->id }}" @checked($rule->requires_manual_review)><label class="form-check-label" for="manual-{{ $rule->id }}">Revisão manual</label></div>
                        </td>
                        <td><button form="{{ $formId }}" class="btn btn-dark btn-sm"><i class="fa-solid fa-floppy-disk me-1"></i>Salvar</button></td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="text-center text-muted py-5">Nenhuma regra encontrada.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $rules->links() }}</div>
</x-admin.card>
@endsection
