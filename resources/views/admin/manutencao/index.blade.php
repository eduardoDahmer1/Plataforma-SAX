@extends('layout.admin')

@section('content')
<div class="container mt-4">
    <div class="card shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center">
            <h5 class="mb-0">
                <i class="fa-solid fa-screwdriver-wrench me-2"></i> Modo de Manutenção
            </h5>
        </div>
        <div class="card-body text-center">
            @if($setting->maintenance == 1)
                <p class="text-danger mb-3">🔧 O sistema está em <strong>manutenção</strong>.</p>
                <a href="{{ route('admin.maintenance.toggle') }}" class="btn btn-success">
                    <i class="fa fa-play me-2"></i> Desativar Manutenção
                </a>
            @else
                <p class="text-success mb-3">✅ O sistema está <strong>ativo</strong>.</p>
                <a href="{{ route('admin.maintenance.toggle') }}" class="btn btn-warning">
                    <i class="fa fa-pause me-2"></i> Ativar Manutenção
                </a>
            @endif
        </div>
    </div>
</div>
@endsection
