@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header de Navegação --}}
    <div class="mb-5">
        <a href="{{ route('admin.cupons.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
            <i class="fa fa-chevron-left me-1"></i> Inventario de Cupones
        </a>
        <h1 class="h4 fw-light mt-2 mb-0 text-uppercase tracking-wider">Configurar Nuevo Cupón</h1>
        <div class="sax-divider-dark mt-3"></div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.cupons.store') }}" method="POST" id="cuponForm">
                @csrf
                
                <div class="card border rounded-0 shadow-none">
                    <div class="card-body p-4 p-md-5">
                        @include('admin.cupon.partials.form', ['button' => 'Registrar Cupón'])
                    </div>
                </div>

                {{-- Ações de Rodapé (Caso não estejam no partial) --}}
                <div class="mt-4 d-flex align-items-center gap-3">
                    <button type="submit" class="btn btn-dark rounded-0 px-5 py-2 fw-bold text-uppercase tracking-wider small">
                        Guardar Cupón
                    </button>
                    <a href="{{ route('admin.cupons.index') }}" class="text-secondary text-decoration-none x-small fw-bold text-uppercase hover-underline">
                        Descartar
                    </a>
                </div>
            </form>
        </div>
    </div>
</div>

<style>
    .tracking-wider { letter-spacing: 0.12em; }
    .x-small { font-size: 0.65rem; }
    .sax-divider-dark { width: 40px; height: 2px; background: #000; }
    .hover-underline:hover { text-decoration: underline !important; }
</style>
@endsection