@props(['type' => 'info', 'message'])

@if($message)
    <div class="alert alert-{{ $type }} d-flex align-items-center">
        <i class="fas fa-check-circle me-2"></i> {{ $message }}
    </div>
@endif
