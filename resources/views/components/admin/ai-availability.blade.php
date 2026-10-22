@php($aiUnavailableReason = app(\App\Services\ProductAiSettingsService::class)->unavailableReason())
@if($aiUnavailableReason)
    <div class="alert alert-warning">{{ $aiUnavailableReason }}</div>
@endif
