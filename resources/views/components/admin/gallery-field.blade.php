@props([
    'field',          // nome do campo (ex.: gallery_images) — usado nos names/ids
    'images' => [],   // caminhos já salvos no storage
    'max'    => 20,   // limite total (existentes + novas)
    'label'  => null,
    'hint'   => null,
])

<div class="gallery-field-manager" data-gallery-field="{{ $field }}" data-max="{{ $max }}">
    @if($label)
        <label class="sax-form-label d-block mb-2">{{ $label }}</label>
    @endif

    <div class="gallery-preview-grid mb-3" id="galleryPreview_{{ $field }}">
        @foreach($images as $img)
            <div class="gallery-preview-item is-existing shadow-sm border">
                <img src="{{ asset('storage/' . $img) }}" class="w-100 h-100 object-fit-cover">
                <input type="hidden" name="{{ $field }}_actual[]" value="{{ $img }}">
                <button type="button" class="gallery-remove-btn"><i class="fas fa-times"></i></button>
            </div>
        @endforeach
    </div>

    <div class="upload-zone">
        <input type="file" id="galleryInput_{{ $field }}" name="{{ $field }}[]" class="upload-input" multiple accept="image/*">
        <i class="fas fa-cloud-upload-alt mb-2 opacity-25 fa-lg"></i>
        <p class="x-small fw-bold m-0">{{ __('messages.click_or_drag_images') ?? 'Clique ou arraste imagens' }}</p>
        @if($hint)
            <p class="x-small text-muted m-0">{{ $hint }}</p>
        @endif
    </div>

    <p class="x-small text-muted mt-2 mb-0">
        <i class="fas fa-info-circle me-1"></i>
        <span id="galleryCount_{{ $field }}">{{ count($images) }}</span>/{{ $max }} {{ __('messages.loaded_images_count') ?? 'imagens carregadas' }}
    </p>
</div>
