@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Cupones de Descuento</h1>
            <p class="small text-secondary mb-0">Gestión de incentivos y reglas de precio</p>
        </div>
        <a href="{{ route('admin.cupons.create') }}" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
            <i class="fas fa-plus me-2"></i> Nuevo Cupón
        </a>
    </div>

    {{-- Alertas --}}
    @if (session('error') || session('success'))
        <div class="alert {{ session('error') ? 'alert-danger' : 'alert-dark' }} border-0 rounded-0 x-small fw-bold text-uppercase py-3 mb-4 shadow-sm">
            <i class="fas {{ session('error') ? 'fa-exclamation-circle' : 'fa-check-circle' }} me-2"></i>
            {{ session('error') ?? session('success') }}
        </div>
    @endif

    {{-- Grid de Cupons --}}
    <div class="row g-4">
        @forelse($cupons as $cupon)
            <div class="col-md-6 col-lg-4 col-xl-3">
                <div class="card border rounded-0 shadow-none sax-coupon-card">
                    <div class="card-body p-0">
                        {{-- Cabeçalho do Ticket --}}
                        <div class="p-3 bg-light border-bottom d-flex justify-content-between align-items-center">
                            <span class="x-small fw-800 text-uppercase text-secondary tracking-wider">Código</span>
                            <span class="badge {{ $cupon->tipo === 'percentual' ? 'bg-primary' : 'bg-dark' }} rounded-0 x-small">
                                {{ $cupon->tipo === 'percentual' ? '%' : '$' }}
                            </span>
                        </div>
                        
                        {{-- Corpo do Ticket --}}
                        <div class="p-4 text-center border-bottom border-dashed">
                            <h2 class="h3 fw-900 tracking-tighter mb-1 text-uppercase">{{ $cupon->codigo }}</h2>
                            <span class="h5 fw-light text-dark font-monospace">
                                {{ $cupon->tipo === 'percentual' ? $cupon->montante . '%' : number_format($cupon->montante, 2) }}
                            </span>
                        </div>

                        {{-- Detalhes Técnicos --}}
                        <div class="p-3">
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sax-label-mini">Modelo</span>
                                <span class="x-small fw-bold text-dark text-uppercase">{{ $cupon->modelo ?? 'Universal' }}</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span class="sax-label-mini">Categoría</span>
                                <span class="x-small fw-bold text-dark text-uppercase">{{ $cupon->category->name ?? 'Todas' }}</span>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span class="sax-label-mini">Marca</span>
                                <span class="x-small fw-bold text-dark text-uppercase">{{ $cupon->brand->name ?? 'Todas' }}</span>
                            </div>
                        </div>

                        {{-- Ações --}}
                        <div class="p-3 bg-light d-flex gap-3 border-top">
                            <a href="{{ route('admin.cupons.edit', $cupon) }}" class="text-dark text-decoration-none x-small fw-bold tracking-tighter hover-underline">
                                EDITAR
                            </a>
                            <form action="{{ route('admin.cupons.destroy', $cupon) }}" method="POST"
                                onsubmit="return confirm('¿Eliminar cupón?')" class="ms-auto m-0">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-clean text-danger x-small fw-bold tracking-tighter">
                                    ELIMINAR
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12 py-5 text-center border border-dashed">
                <p class="text-muted x-small text-uppercase tracking-wider mb-0 italic">No hay cupones activos en este momento.</p>
            </div>
        @endforelse
    </div>

    {{-- Paginação --}}
    <div class="mt-5 d-flex justify-content-center">
        {{ $cupons->links() }}
    </div>
</div>

<style>
    /* UI Cupones Minimalista */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .fw-800 { font-weight: 800; }
    .fw-900 { font-weight: 900; }
    .italic { font-style: italic; }
    
    .sax-label-mini {
        font-size: 0.55rem;
        font-weight: 800;
        color: #bbb;
        text-transform: uppercase;
    }

    .sax-coupon-card {
        transition: 0.2s;
        background: #fff;
    }
    .sax-coupon-card:hover {
        border-color: #000 !important;
        transform: translateY(-3px);
    }

    .border-dashed { border-style: dashed !important; }
    .font-monospace { font-family: 'SFMono-Regular', Consolas, monospace; }

    /* Utilitários */
    .btn-clean { background: none; border: none; padding: 0; cursor: pointer; }
    .hover-underline:hover { text-decoration: underline !important; }
</style>
@endsection