@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="Modo de Manutenção" description="Controle o estado de visibilidade do sistema"></x-admin.page-header>
    <div class="text-center py-3">
        @if($setting->maintenance == 1)
            <p class="text-danger mb-3"><i class="fa-solid fa-screwdriver-wrench me-2"></i> O sistema está em <strong>manutenção</strong>.</p>
            <a href="{{ route('admin.maintenance.toggle') }}" class="btn btn-success">
                <i class="fa fa-play me-2"></i> Desativar Manutenção
            </a>
        @else
            <p class="text-success mb-3"><i class="fa fa-check-circle me-2"></i> O sistema está <strong>ativo</strong>.</p>
            <a href="{{ route('admin.maintenance.toggle') }}" class="btn btn-warning">
                <i class="fa fa-pause me-2"></i> Ativar Manutenção
            </a>
        @endif
    </div>
</x-admin.card>
@endsection
