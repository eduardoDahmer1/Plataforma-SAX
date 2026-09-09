@php($aiUnavailableReason = app(\App\Services\ProductAiSettingsService::class)->unavailableReason())
@if($aiUnavailableReason)
    <div class="alert alert-warning">{{ $aiUnavailableReason }}</div>
@endif
@if(auth()->user()?->isMasterAdmin())
    <a class="btn btn-sm btn-outline-secondary mb-3" href="{{ route('admin.ai-settings.edit') }}">Configurar IA y clave API</a>
@endif
