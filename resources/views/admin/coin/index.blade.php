@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Divisas y Cambios</h1>
            <p class="small text-secondary mb-0">Gestión de tipos de cambio y formatos de moneda</p>
        </div>
        <button class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider" data-bs-toggle="modal" data-bs-target="#addCurrencyModal">
            <i class="fas fa-plus me-2"></i> Nueva Divisa
        </button>
    </div>

    {{-- Grid de Moedas --}}
    <div class="row g-4">
        @foreach($currencies as $currency)
        <div class="col-md-6 col-xl-4">
            <div class="card border rounded-0 shadow-none sax-currency-card {{ $currency->is_default ? 'border-dark' : '' }}">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-start mb-3">
                        <div>
                            <span class="d-block h5 fw-bold mb-0">{{ $currency->name }} <span class="text-secondary fw-light">({{ $currency->sign }})</span></span>
                            <span class="x-small text-uppercase tracking-wider text-muted italic">{{ $currency->description }}</span>
                        </div>
                        @if($currency->is_default)
                            <span class="badge bg-dark rounded-0 x-small tracking-tighter">BASE / DEFAULT</span>
                        @endif
                    </div>

                    <div class="bg-light p-3 mb-3 border-start border-3 {{ $currency->is_default ? 'border-dark' : 'border-secondary' }}">
                        <span class="sax-label">Valor de Conversión</span>
                        <span class="h4 fw-light font-monospace m-0">{{ number_format($currency->value, 4) }}</span>
                    </div>

                    <div class="d-flex gap-3 pt-2 border-top">
                        <button class="btn-clean text-dark x-small fw-bold tracking-tighter hover-underline" data-bs-toggle="modal" data-bs-target="#editCurrencyModal{{ $currency->id }}">
                            EDITAR PARÁMETROS
                        </button>
                        
                        @if(!$currency->is_default)
                        <a href="{{ route('admin.currencies.default', $currency->id) }}" class="text-primary text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                            ESTABLECER COMO BASE
                        </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Modal Editar (Versão Minimalista) --}}
        <div class="modal fade" id="editCurrencyModal{{ $currency->id }}" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered modal-lg">
                <form action="{{ route('admin.currencies.update', $currency->id) }}" method="POST" class="modal-content rounded-0 border-0 shadow-lg">
                    @csrf @method('PUT')
                    <div class="modal-header border-bottom py-3 px-4">
                        <h6 class="modal-title text-uppercase fw-bold tracking-wider x-small">Configuración de Divisa: {{ $currency->name }}</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="row g-4">
                            <div class="col-md-4">
                                <label class="sax-label">ISO Code (Name)</label>
                                <input type="text" name="name" class="form-control sax-input" value="{{ $currency->name }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="sax-label">Símbolo (Sign)</label>
                                <input type="text" name="sign" class="form-control sax-input" value="{{ $currency->sign }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="sax-label">Tasa de Cambio</label>
                                <input type="number" step="0.0001" name="value" class="form-control sax-input font-monospace" value="{{ $currency->value }}" required>
                            </div>
                            <div class="col-12">
                                <label class="sax-label">Descripción</label>
                                <input type="text" name="description" class="form-control sax-input" value="{{ $currency->description }}" required>
                            </div>
                            <div class="col-md-4">
                                <label class="sax-label">Sep. Decimal</label>
                                <input type="text" name="decimal_separator" class="form-control sax-input text-center" value="{{ $currency->decimal_separator ?? '.' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="sax-label">Sep. Miles</label>
                                <input type="text" name="thousands_separator" class="form-control sax-input text-center" value="{{ $currency->thousands_separator ?? ',' }}">
                            </div>
                            <div class="col-md-4">
                                <label class="sax-label">Dígitos Decimais</label>
                                <input type="number" name="decimal_digits" class="form-control sax-input text-center" value="{{ $currency->decimal_digits ?? 2 }}">
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer border-top-0 p-4 pt-0">
                        <button type="button" class="btn btn-outline-secondary btn-sm rounded-0 px-4 x-small fw-bold text-uppercase" data-bs-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-dark btn-sm rounded-0 px-4 x-small fw-bold text-uppercase">Actualizar Divisa</button>
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
        <form action="{{ route('admin.currencies.store') }}" method="POST" class="modal-content rounded-0 border-0 shadow-lg">
            @csrf
            <div class="modal-header border-bottom py-3 px-4">
                <h6 class="modal-title text-uppercase fw-bold tracking-wider x-small">Registrar Nueva Divisa</h6>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                {{-- Mesma estrutura do Edit para manter consistência --}}
                <div class="row g-4">
                    <div class="col-md-4">
                        <label class="sax-label">ISO Code (USD, BRL, PYG)</label>
                        <input type="text" name="name" class="form-control sax-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="sax-label">Símbolo</label>
                        <input type="text" name="sign" class="form-control sax-input" required>
                    </div>
                    <div class="col-md-4">
                        <label class="sax-label">Tasa Inicial</label>
                        <input type="number" step="0.0001" name="value" class="form-control sax-input font-monospace" required>
                    </div>
                    <div class="col-12">
                        <label class="sax-label">Descripción Extendida</label>
                        <input type="text" name="description" class="form-control sax-input" required>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 p-4 pt-0">
                <button type="submit" class="btn btn-dark btn-sm rounded-0 px-4 x-small fw-bold text-uppercase">Guardar Nueva Divisa</button>
            </div>
        </form>
    </div>
</div>

<style>
    /* Estética Financeira Minimalista */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    
    .sax-label {
        font-size: 0.6rem;
        font-weight: 800;
        color: #bbb;
        text-transform: uppercase;
        display: block;
        margin-bottom: 8px;
    }

    .sax-currency-card {
        transition: 0.2s;
        background: #fff;
    }
    .sax-currency-card:hover {
        border-color: #000 !important;
        transform: translateY(-2px);
    }

    .sax-input {
        border-radius: 0 !important;
        border: 1px solid #e5e5e5 !important;
        padding: 10px 12px;
        font-size: 0.85rem;
    }
    .sax-input:focus {
        border-color: #000 !important;
        box-shadow: none !important;
        background-color: #fafafa;
    }

    .btn-clean { background: none; border: none; padding: 0; cursor: pointer; }
    .hover-underline:hover { text-decoration: underline !important; }
    
    /* Fontes mono para números financeiros */
    .font-monospace { font-family: 'SFMono-Regular', Consolas, 'Liberation Mono', Menlo, monospace !important; }
</style>
@endsection