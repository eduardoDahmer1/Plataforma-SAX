@extends('layout.admin')

@section('content')
<div class="container-fluid py-4 px-md-5">
    
    {{-- Header Minimalista --}}
    <div class="d-flex justify-content-between align-items-end mb-5">
        <div>
            <h1 class="h4 fw-light text-uppercase tracking-wider mb-1">Estructura de la Home</h1>
            <p class="small text-secondary mb-0">Active o desactive la visibilidad de las secciones principales</p>
        </div>
        <button type="submit" form="sectionsForm" class="btn btn-dark btn-sm rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
            Guardar Configuración
        </button>
    </div>

    <div class="row">
        <div class="col-lg-6">
            <form action="{{ route('admin.sections_home.update') }}" method="POST" id="sectionsForm">
                @csrf
                @method('PATCH')

                @php
                    $sections = [
                        'navbar' => ['label' => 'Barra de Navegación', 'icon' => 'fa-bars'],
                        'destaque' => ['label' => 'Productos Destacados', 'icon' => 'fa-star'],
                        'mais_vendidos' => ['label' => 'Más Vendidos', 'icon' => 'fa-fire'],
                        'melhores_avaliacoes' => ['label' => 'Mejor Calificados', 'icon' => 'fa-award'],
                        'super_desconto' => ['label' => 'Súper Descuentos', 'icon' => 'fa-percentage'],
                        'famosos' => ['label' => 'Populares / Famosos', 'icon' => 'fa-users'],
                        'lancamentos' => ['label' => 'Nuevos Lanzamientos', 'icon' => 'fa-calendar-plus'],
                        'tendencias' => ['label' => 'Tendencias', 'icon' => 'fa-chart-line'],
                        'promocoes' => ['label' => 'Promociones Especiales', 'icon' => 'fa-tags'],
                        'ofertas_relampago' => ['label' => 'Ofertas Relámpago', 'icon' => 'fa-bolt'],
                    ];
                @endphp

                <div class="sax-settings-box border">
                    @foreach($sections as $key => $data)
                    <div class="sax-setting-item d-flex align-items-center justify-content-between p-3 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="d-flex align-items-center">
                            <div class="sax-icon-wrapper me-3">
                                <i class="fas {{ $data['icon'] }} text-muted small"></i>
                            </div>
                            <div>
                                <label class="d-block fw-bold text-dark text-uppercase x-small tracking-tighter mb-0 cursor-pointer" for="{{ $key }}">
                                    {{ $data['label'] }}
                                </label>
                                <span class="x-small text-muted italic">Sección: {{ $key }}</span>
                            </div>
                        </div>
                        
                        <div class="form-check form-switch">
                            <input class="form-check-input sax-switch" type="checkbox" name="{{ $key }}" id="{{ $key }}"
                                {{ $settings->{'show_highlight_'.$key} ? 'checked' : '' }}>
                        </div>
                    </div>
                    @endforeach
                </div>
            </form>
        </div>

        {{-- Coluna de Ajuda --}}
        <div class="col-lg-4 offset-lg-1 mt-5 mt-lg-0">
            <div class="border-start ps-4 h-100">
                <h6 class="x-small fw-bold text-uppercase tracking-wider mb-3">Guía de Visualización</h6>
                <p class="x-small text-secondary lh-base italic mb-4">
                    Al activar una sección, esta aparecerá automáticamente en la página principal siguiendo el orden de prioridad del sistema. Asegúrese de tener productos vinculados a cada categoría para evitar espacios vacíos.
                </p>
                <div class="p-3 bg-light border-dashed">
                    <span class="x-small fw-bold text-dark d-block mb-1 text-uppercase">Nota Técnica:</span>
                    <span class="x-small text-muted italic">Los cambios se reflejan instantáneamente tras guardar. La sección "Navbar" controla elementos específicos de la cabecera.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
    /* UI Estilo Preferencias */
    .tracking-wider { letter-spacing: 0.12em; }
    .tracking-tighter { letter-spacing: 0.05em; }
    .x-small { font-size: 0.65rem; }
    .italic { font-style: italic; }
    .cursor-pointer { cursor: pointer; }
    
    .sax-settings-box {
        background: #fff;
    }

    .sax-setting-item {
        transition: background 0.2s ease;
    }
    .sax-setting-item:hover {
        background-color: #fafafa;
    }

    .sax-icon-wrapper {
        width: 30px;
        height: 30px;
        display: flex;
        align-items: center;
        justify-content: center;
        background: #f8f9fa;
        border-radius: 4px;
    }

    /* Custom Switch */
    .sax-switch {
        width: 2.8em !important;
        height: 1.4em !important;
        cursor: pointer;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='rgba%280, 0, 0, 0.25%29'/%3e%3c/svg%3e") !important;
    }
    .sax-switch:checked {
        background-color: #000 !important;
        border-color: #000 !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='-4 -4 8 8'%3e%3ccircle r='3' fill='%23fff'/%3e%3c/svg%3e") !important;
    }
    .sax-switch:focus {
        box-shadow: none !important;
        border-color: #dee2e6 !important;
    }

    .border-dashed { border: 1px dashed #dee2e6 !important; }
</style>
@endsection