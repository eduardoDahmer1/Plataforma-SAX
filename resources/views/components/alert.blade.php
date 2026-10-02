@props(['type' => 'info', 'message'])

@if($message)
    <div class="alert alert-{{ $type }} d-flex align-items-center">
        <i class="fas {{ $type === 'warning' ? 'fa-triangle-exclamation' : ($type === 'danger' ? 'fa-circle-exclamation' : 'fa-check-circle') }} me-2"></i> {{ $message }}
    </div>
@endif
