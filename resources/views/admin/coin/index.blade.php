@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Moedas</h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
            <i class="fas fa-plus me-1"></i> Adicionar Moeda
        </button>
    </div>

    {{-- Lista de moedas --}}
    <div class="row g-3">
        @foreach($currencies as $currency)
        <div class="col-md-4">
            <div class="card shadow-sm rounded-4 border-0">
                <div class="card-body">
                    <h5 class="card-title">{{ $currency->name }} <small class="text-muted">({{ $currency->sign }})</small></h5>
                    <p class="card-text">{{ $currency->description }}</p>
                    <p class="mb-1"><strong>Valor:</strong> {{ $currency->value }}</p>
                    @if($currency->is_default)
                        <span class="badge bg-success mb-2">Base</span>
                    @endif
                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-warning btn-sm flex-fill" data-bs-toggle="modal" data-bs-target="#editCurrencyModal{{ $currency->id }}">
                            <i class="fas fa-edit me-1"></i> Editar
                        </button>
                        @if(!$currency->is_default)
                        <a href="{{ route('admin.currencies.default', $currency->id) }}" class="btn btn-primary btn-sm flex-fill">
                            <i class="fas fa-dollar-sign me-1"></i> Definir padrão
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Editar --}}
        <div class="modal fade" id="editCurrencyModal{{ $currency->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ route('admin.currencies.update', $currency->id) }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-warning text-dark rounded-top-4">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Moeda</h5>
                        <button type="button" class="btn-close btn-close-dark" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Nome (ISO) *</label>
                                <input type="text" name="name" class="form-control shadow-sm" value="{{ $currency->name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Cifrão *</label>
                                <input type="text" name="sign" class="form-control shadow-sm" value="{{ $currency->sign }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Valor / Cotação *</label>
                                <input type="number" step="0.0001" name="value" class="form-control shadow-sm" value="{{ $currency->value }}" required>
                            </div>
                        </div>
                        <div class="mt-3">
                            <label class="form-label fw-bold">Descrição *</label>
                            <input type="text" name="description" class="form-control shadow-sm" value="{{ $currency->description }}" required>
                        </div>
                        <div class="row g-3 mt-3">
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Separador decimal</label>
                                <input type="text" name="decimal_separator" class="form-control shadow-sm" value="{{ $currency->decimal_separator ?? '.' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Separador milésimo</label>
                                <input type="text" name="thousands_separator" class="form-control shadow-sm" value="{{ $currency->thousands_separator ?? ',' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="form-label fw-bold">Dígitos decimais</label>
                                <input type="number" name="decimal_digits" class="form-control shadow-sm" value="{{ $currency->decimal_digits ?? 2 }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer justify-content-between px-4 py-3 border-0">
                        <button type="submit" class="btn btn-warning btn-lg shadow-sm"><i class="fas fa-save me-1"></i>Salvar</button>
                        <button type="button" class="btn btn-outline-secondary btn-lg shadow-sm" data-bs-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>

        @endforeach
    </div>
</div>

{{-- Modal Adicionar Moeda --}}
<div class="modal fade" id="addCurrencyModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <form action="{{ route('admin.currencies.store') }}" method="POST" class="modal-content border-0 shadow-lg rounded-4">
            @csrf
            <div class="modal-header bg-primary text-white rounded-top-4">
                <h5 class="modal-title"><i class="fas fa-coins me-2"></i>Adicionar Moeda</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Nome (ISO) *</label>
                        <input type="text" name="name" class="form-control shadow-sm" placeholder="Ex: USD" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Cifrão *</label>
                        <input type="text" name="sign" class="form-control shadow-sm" placeholder="Ex: $" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Valor / Cotação *</label>
                        <input type="number" step="0.0001" name="value" class="form-control shadow-sm" placeholder="Ex: 1.00" required>
                    </div>
                </div>
                <div class="mt-3">
                    <label class="form-label fw-bold">Descrição *</label>
                    <input type="text" name="description" class="form-control shadow-sm" placeholder="Ex: Dólar Americano" required>
                </div>
                <div class="row g-3 mt-3">
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Separador decimal</label>
                        <input type="text" name="decimal_separator" class="form-control shadow-sm" value=".">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Separador milésimo</label>
                        <input type="text" name="thousands_separator" class="form-control shadow-sm" value=",">
                    </div>
                    <div class="col-md-4">
                        <label class="form-label fw-bold">Dígitos decimais</label>
                        <input type="number" name="decimal_digits" class="form-control shadow-sm" value="2">
                    </div>
                </div>
            </div>
            <div class="modal-footer justify-content-between px-4 py-3 border-0">
                <button type="submit" class="btn btn-success btn-lg shadow-sm"><i class="fas fa-plus me-1"></i>Adicionar</button>
                <button type="button" class="btn btn-outline-secondary btn-lg shadow-sm" data-bs-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>
@endsection
