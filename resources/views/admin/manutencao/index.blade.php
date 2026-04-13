@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header 
        title="{{ __('messages.modo_manutencao_titulo') }}" 
        description="{{ __('messages.controle_visibilidade_desc') }}">
    </x-admin.page-header>

    <div class="text-center py-5">
        @if($setting->maintenance == 1)
            <div class="mb-4">
                <i class="fa-solid fa-screwdriver-wrench fa-3x text-danger mb-3"></i>
                <p class="text-danger fs-5 fw-bold text-uppercase tracking-wider">
                    {{ __('messages.sistema_em_manutencao_aviso') }}
                </p>
            </div>
            <a href="{{ route('admin.maintenance.toggle') }}" class="btn btn-success btn-lg rounded-0 px-5 fw-bold text-uppercase x-small">
                <i class="fa fa-play me-2"></i> {{ __('messages.desativar_manutencao_btn') }}
            </a>
        @else
            <div class="mb-4">
                <i class="fa-solid fa-check-circle fa-3x text-success mb-3"></i>
                <p class="text-success fs-5 fw-bold text-uppercase tracking-wider">
                    {{ __('messages.sistema_ativo_aviso') }}
                </p>
            </div>
            <a href="{{ route('admin.maintenance.toggle') }}" class="btn btn-warning btn-lg rounded-0 px-5 fw-bold text-uppercase x-small">
                <i class="fa fa-pause me-2"></i> {{ __('messages.ativar_manutencao_btn') }}
            </a>
        @endif
    </div>
</x-admin.card>
@endsection