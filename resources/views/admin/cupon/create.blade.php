@extends('layout.admin')

@section('content')
<x-admin.card>
    {{-- Header de Navegação --}}
    <div class="mb-5">
        <a href="{{ route('admin.cupons.index') }}" class="text-decoration-none x-small fw-bold text-uppercase text-secondary tracking-wider">
            <i class="fa fa-chevron-left me-1"></i> {{ __('messages.inventario_cupons_link') }}
        </a>
        <h1 class="h4 fw-light mt-2 mb-0 text-uppercase tracking-wider">{{ __('messages.configurar_novo_cupon_titulo') }}</h1>
        <div class="sax-divider-dark mt-3"></div>
    </div>

    <div class="row">
        <div class="col-lg-12">
            <form action="{{ route('admin.cupons.store') }}" method="POST" id="cuponForm">
                @csrf
                
                <div class="card border rounded-0 shadow-none">
                    <div class="card-body p-4 p-md-5">
                        @include('admin.cupon.partials.form', ['button' => __('messages.registrar_cupon_btn')])
                    </div>
                </div>
            </form>
        </div>
    </div>

</x-admin.card>
@endsection