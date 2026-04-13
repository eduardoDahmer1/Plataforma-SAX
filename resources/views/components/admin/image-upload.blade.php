@props([
    'name',
    'previewId',
    'currentImage' => null,
    'placeholder'  => '',
    'label'        => null,
    'height'       => '180px',
    'maxSize'      => '8MB',
    'accept'       => 'image/*',
    'compact'      => false,
    'circular'     => false,
])
@php $src = $currentImage ?? $placeholder; @endphp

@if($circular)

    {{-- ── Avatar circular (Testimonios) ── --}}
    <div class="testimonial-avatar-upload position-relative">
        <img id="{{ $previewId }}" src="{{ $src }}"
             class="avatar-preview rounded-circle border">
        <input type="file" name="{{ $name }}"
               class="upload-input img-trigger avatar-trigger"
               data-prev="{{ $previewId }}" accept="{{ $accept }}">
        <div class="avatar-overlay rounded-circle">
            <i class="fas fa-camera x-small text-white"></i>
        </div>
    </div>

@else

    {{-- ── Rectangular: full o compact ── --}}
    @if($label)
        <label class="sax-form-label d-block mb-2">{{ $label }}</label>
    @endif
    <div class="img-preview-box mb-{{ $compact ? '2' : '3' }} rounded-{{ $compact ? '2' : '3' }} overflow-hidden border"
         style="height:{{ $height }};"><img id="{{ $previewId }}" src="{{ $src }}"
             class="w-100 h-100 object-fit-cover">
    </div>
    <div class="upload-zone {{ $compact ? 'py-2 mb-3' : '' }}">
        <input type="file" name="{{ $name }}"
               class="upload-input img-trigger"
               data-prev="{{ $previewId }}" accept="{{ $accept }}">
        @if(!$compact)
            <i class="fas fa-cloud-upload-alt mb-2 opacity-25 fa-lg"></i>
            <p class="x-small fw-bold m-0">Haga clic o arrastre una imagen</p>
            <p class="x-small text-muted m-0">JPG, PNG, WEBP — máx. {{ $maxSize }}</p>
        @else
            <p class="x-small text-muted m-0">Cambiar imagen</p>
        @endif
    </div>

@endif
