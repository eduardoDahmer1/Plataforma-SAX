@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="Políticas e Termos" description="Gerencie os textos legais exibidos no site e no checkout." />
    <x-admin.alert />

    <div class="sax-admin-list mt-3">
        @forelse($policies as $policy)
            <div class="sax-admin-card mb-2 p-3">
                <div class="d-flex align-items-center gap-3">
                    <div class="nav-icon-box bg-soft-info"><i class="fa-solid fa-scale-balanced"></i></div>
                    <div class="flex-grow-1 min-w-0">
                        <h6 class="fw-bold mb-1">{{ $policy->title }}</h6>
                        <span class="status-pill {{ $policy->is_active ? 'active' : 'draft' }}">{{ $policy->is_active ? 'Publicada' : 'Oculta' }}</span>
                        <small class="text-muted ms-2">Atualizada em {{ $policy->updated_at->format('d/m/Y H:i') }}</small>
                    </div>
                    <a href="{{ route('policies.index') }}#{{ $policy->slug }}" target="_blank" class="action-icon" title="Visualizar"><i class="far fa-eye"></i></a>
                    <a href="{{ route('admin.policies.edit', $policy) }}" class="action-icon" title="Editar"><i class="far fa-edit"></i></a>
                </div>
            </div>
        @empty
            <div class="alert alert-light border text-center">Execute as migrações para criar as políticas iniciais.</div>
        @endforelse
    </div>
</x-admin.card>
@endsection
