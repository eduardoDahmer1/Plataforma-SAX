@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header
        title="Perfil de la Marca"
        description="Gestión de activos de identidad">
        <x-slot:actions>
            <a href="{{ route('admin.brands.index') }}" class="btn-back-minimal">
                <i class="fas fa-chevron-left me-1"></i> VOLVER AL LISTADO
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-4">
        {{-- Coluna de Informações --}}
        <div class="col-lg-12">
            <div class="sax-premium-card h-100 shadow-sm border-0">
                <div class="card-sax-header border-bottom p-4">
                    <h6 class="m-0 fw-bold letter-spacing-1 text-muted small">INFORMACIÓN GENERAL</h6>
                </div>
                <div class="card-sax-body p-4 text-center">
                    {{-- Logo em destaque no perfil --}}
                    <div class="brand-profile-logo mb-4 mx-auto shadow-sm">
                        @if ($brand->image)
                            <img src="{{ asset('storage/' . $brand->image) }}" alt="Logo" class="img-fluid">
                        @else
                            <div class="empty-logo"><i class="fas fa-industry fa-2x"></i></div>
                        @endif
                    </div>

                    <div class="info-group-sax mb-3 text-start">
                        <label class="sax-label">ID DEL SISTEMA</label>
                        <div class="value-box bg-light x-small fw-bold">#{{ $brand->id }}</div>
                    </div>

                    <div class="info-group-sax mb-4 text-start">
                        <label class="sax-label">NOMBRE DE LA MARCA</label>
                        <div class="value-box fw-bold text-dark fs-4">{{ $brand->name ?? 'Sin Nombre' }}</div>
                    </div>

                    <div class="info-group-sax mb-4 text-start">
                        <label class="sax-label">SLUG / URL</label>
                        <div class="value-box bg-dark text-white x-small fw-bold">/marcas/{{ $brand->slug }}</div>
                    </div>

                    <div class="mt-4 pt-3 border-top">
                        <a href="{{ route('admin.brands.edit', $brand) }}" class="btn btn-dark w-100 rounded-pill fw-bold x-small py-3">
                            <i class="fas fa-pen-nib me-2"></i> EDITAR IDENTIDAD
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-admin.card>
@endsection
