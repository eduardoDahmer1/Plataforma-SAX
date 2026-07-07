@props([
    'name',
    'label' => '',
    'pt' => '',
    'es' => '',
    'en' => '',
    'type' => 'text',
    'placeholder' => '',
    'rows' => 4,
])

<div class="lang-field" data-lang-field data-current-lang="pt-br">
    <div class="d-flex justify-content-between align-items-center mb-1">
        @if($label)
            <label class="sax-form-label mb-0">{{ $label }}</label>
        @else
            <span></span>
        @endif
        <div class="d-flex gap-1">
            <button type="button" class="badge border-0 bg-primary active" data-lang-field-btn="pt-br">PT</button>
            <button type="button" class="badge border-0 bg-secondary" data-lang-field-btn="es">ES</button>
            <button type="button" class="badge border-0 bg-secondary" data-lang-field-btn="en">EN</button>
        </div>
    </div>

    <input type="hidden" name="translate[pt-br][{{ $name }}]" data-lang-real="pt-br"
           value="{{ old('translate.pt-br.'.$name, $pt) }}">
    <input type="hidden" name="translate[es][{{ $name }}]" data-lang-real="es"
           value="{{ old('translate.es.'.$name, $es) }}">
    <input type="hidden" name="translate[en][{{ $name }}]" data-lang-real="en"
           value="{{ old('translate.en.'.$name, $en) }}">

    @if($type === 'textarea')
        <textarea class="form-control sax-input" data-lang-visual rows="{{ $rows }}"
                  placeholder="{{ $placeholder }}">{{ old('translate.pt-br.'.$name, $pt) }}</textarea>
    @else
        <input type="text" class="form-control sax-input" data-lang-visual
               value="{{ old('translate.pt-br.'.$name, $pt) }}" placeholder="{{ $placeholder }}">
    @endif
</div>
