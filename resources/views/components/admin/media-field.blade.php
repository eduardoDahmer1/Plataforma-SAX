@props([
    'field',             // 'photo' | 'banner' | 'image' | 'internal_banner'
    'label'      => null,
    'current'    => null,
    'uploadUrl'  => null,   // presente → modo EDIT (AJAX); ausente → modo CREATE (submit normal)
    'showDelete' => false,  // true → renderiza botón borrar (la vista pone el form delete fuera del form principal)
    'ratio'      => 'square', // 'square' | 'banner'
    'dimensions' => null,
    'usage'      => null,
])

@php
    $previewId = 'preview-' . $field;
    $emptyIcon = $ratio === 'banner' ? 'fa-images' : 'fa-cloud-upload-alt';
    $src       = $current ? asset('storage/' . $current) : '';
    $aspectRatio = null;
    if ($dimensions && preg_match('/(\d+)\s*[×x]\s*(\d+)/u', $dimensions, $matches)) {
        $aspectRatio = $matches[1] . ' / ' . $matches[2];
    }
@endphp

<section class="sax-media-field sax-media-field--{{ $ratio }}">
    <header class="sax-media-field__head">
        @if($label)
            <label class="sax-label-tiny sax-media-field__label">{{ $label }}</label>
        @endif
        @if($dimensions)
            <div class="admin-media-spec">
                <i class="fa-solid fa-expand"></i>
                <span><strong>{{ $dimensions }}</strong>@if($usage)<small>{{ $usage }}</small>@endif</span>
            </div>
        @endif
    </header>

    <div class="sax-media-field__body">
    @if($uploadUrl)
        {{-- MODO EDIT: AJAX inmediato. El input NO lleva name; el JS deriva el campo de data-preview-id --}}
        <div class="media-upload-preview {{ $ratio === 'banner' ? 'banner-ratio' : '' }} shadow-sm mx-auto"
             @if($aspectRatio) style="--media-aspect: {{ $aspectRatio }}" @endif>

            <img @if($src) src="{{ $src }}" @endif
                 id="{{ $previewId }}"
                 alt="{{ $label ?? $field }}"
                 loading="lazy"
                 decoding="async"
                 style="{{ $current ? '' : 'display:none' }}">

            @if($showDelete)
                <button type="button"
                        onclick="confirmDelete('{{ $field }}')"
                        class="btn-delete-media"
                        style="{{ $current ? '' : 'display:none' }}">
                    <i class="fas fa-times"></i>
                </button>
            @endif

            <button type="button" class="btn-view-media" data-media-view
                    style="{{ $current ? '' : 'display:none' }}"
                    aria-label="Ampliar {{ $label ?? 'imagem' }}">
                <i class="fa-solid fa-magnifying-glass-plus"></i><span>Ampliar</span>
            </button>

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
        <div class="sax-file-dropzone {{ $ratio === 'banner' ? 'banner-ratio' : '' }}"
             @if($aspectRatio) style="--media-aspect: {{ $aspectRatio }}" @endif>
            <img id="{{ $previewId }}" alt="{{ $label ?? $field }}" decoding="async" style="display:none">
            <div class="sax-file-dropzone__empty">
                <i class="fas {{ $emptyIcon }} mb-2 opacity-25"></i>
                <span>Selecionar arquivo</span>
            </div>
            <input type="file"
                   class="form-control sax-file-input"
                   name="{{ $field }}"
                   accept="image/*"
                   data-preview-id="{{ $previewId }}">
            <button type="button" class="btn-view-media" data-media-view style="display:none"
                    aria-label="Ampliar {{ $label ?? 'imagem' }}">
                <i class="fa-solid fa-magnifying-glass-plus"></i><span>Ampliar</span>
            </button>
        </div>
    @endif
    </div>
</section>

@once
    <div class="modal fade admin-media-viewer" id="adminMediaViewer" tabindex="-1"
         aria-labelledby="adminMediaViewerTitle" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <span class="admin-media-viewer__eyebrow">Visualização completa</span>
                        <h2 class="modal-title" id="adminMediaViewerTitle">Imagem</h2>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body"><img id="adminMediaViewerImage" alt=""></div>
                <div class="modal-footer">
                    <span id="adminMediaViewerMeta"></span>
                    <a id="adminMediaViewerOriginal" href="#" target="_blank" rel="noopener noreferrer"
                       class="btn btn-outline-dark btn-sm">
                        <i class="fa-solid fa-arrow-up-right-from-square me-1"></i>Abrir arquivo original
                    </a>
                </div>
            </div>
        </div>
    </div>
@endonce
