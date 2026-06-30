@props([
    'field',             // 'photo' | 'banner' | 'image' | 'internal_banner'
    'label'      => null,
    'current'    => null,
    'uploadUrl'  => null,   // presente → modo EDIT (AJAX); ausente → modo CREATE (submit normal)
    'showDelete' => false,  // true → renderiza botón borrar (la vista pone el form delete fuera del form principal)
    'ratio'      => 'square', // 'square' | 'banner'
])

@php
    $previewId = 'preview-' . $field;
    $emptyIcon = $ratio === 'banner' ? 'fa-images' : 'fa-cloud-upload-alt';
    $src       = $current ? asset('storage/' . $current) : '';
@endphp

@if($label)
    <label class="sax-label-tiny mb-2 d-block text-center">{{ $label }}</label>
@endif

@if($uploadUrl)
    {{-- MODO EDIT: AJAX inmediato. El input NO lleva name; el JS deriva el campo de data-preview-id --}}
    <div class="media-upload-preview {{ $ratio === 'banner' ? 'banner-ratio' : '' }} shadow-sm mx-auto">

        <img src="{{ $src }}"
             id="{{ $previewId }}"
             style="{{ $current ? '' : 'display:none' }}">

        @if($showDelete)
            <button type="button"
                    onclick="confirmDelete('{{ $field }}')"
                    class="btn-delete-media"
                    style="{{ $current ? '' : 'display:none' }}">
                <i class="fas fa-times"></i>
            </button>
        @endif

        @unless($current)
            <div class="empty-upload"><i class="fas {{ $emptyIcon }}"></i></div>
        @endunless

        <input type="file"
               class="input-overlay"
               accept="image/*"
               data-upload-url="{{ $uploadUrl }}"
               data-preview-id="{{ $previewId }}">
    </div>

@else
    {{-- MODO CREATE: el archivo viaja en el submit normal del form --}}
    <div class="sax-file-dropzone">
        <i class="fas {{ $emptyIcon }} mb-2 opacity-25"></i>
        <input type="file"
               class="form-control sax-file-input"
               name="{{ $field }}"
               accept="image/*">
    </div>
@endif
