@extends('layout.admin')

@section('content')
<x-admin.card>
    <x-admin.page-header title="Configuración de IA" description="Activación y clave API de este sitio." />
    <x-admin.alert />
    <p>Sitio actual: <strong>{{ ['stage' => 'Stage', 'sax' => 'SAX', 'otica' => 'Óptica'][$profile] ?? $profile }}</strong>. Cada instalación mantiene su propia configuración.</p>
    <form method="POST" action="{{ route('admin.ai-settings.update') }}" autocomplete="off">
        @csrf
        @method('PUT')
        <input type="hidden" name="ai_enabled" value="0">
        <div class="form-check form-switch mb-3">
            <input class="form-check-input" type="checkbox" role="switch" id="aiEnabled" name="ai_enabled" value="1" @checked(old('ai_enabled', $enabled))>
            <label class="form-check-label" for="aiEnabled">Activar IA para productos y lotes</label>
        </div>
        <p class="text-muted">Al desactivar, se bloquean nuevas generaciones y se pausan los productos pendientes en la cola. Una solicitud ya enviada puede terminar. Al reactivar, la cola continúa automáticamente.</p>
        <div class="mb-3">
            <label class="form-label" for="aiKeySource">Clave que utilizará este sitio</label>
            <select class="form-select" name="ai_key_source" id="aiKeySource">
                <option value="environment" @selected(old('ai_key_source', $keySource) === 'environment')>Clave del servidor — {{ $hasEnvironmentKey ? 'configurada' : 'sin configurar' }}</option>
                <option value="stored" @selected(old('ai_key_source', $keySource) === 'stored')>Clave del panel — {{ $hasStoredKey ? 'guardada' : 'sin guardar' }}</option>
            </select>
            @error('ai_key_source')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        <div class="mb-3">
            <label class="form-label" for="aiApiKey">Nueva clave API de OpenAI</label>
            <input type="password" class="form-control" id="aiApiKey" name="ai_api_key" maxlength="512" autocomplete="new-password" spellcheck="false">
            <div class="form-text">Se guarda cifrada y no se muestra de nuevo. Dejá este campo vacío para conservar la clave guardada. Para usarla, elegí “Clave del panel”.</div>
            @error('ai_api_key')<div class="text-danger">{{ $message }}</div>@enderror
        </div>
        @if($hasStoredKey)
            <div class="form-check mb-4">
                <input type="checkbox" class="form-check-input" name="remove_api_key" id="removeApiKey" value="1">
                <label class="form-check-label" for="removeApiKey">Eliminar la clave guardada en el panel</label>
            </div>
        @endif
        <button class="btn btn-dark" type="submit">Guardar configuración</button>
    </form>
</x-admin.card>
@endsection
