@props([
    'name',
    'label' => '',
    'pt' => '',
    'es' => '',
    'en' => '',
    'type' => 'text',
    'placeholder' => '',
    'rows' => 4,
    'help' => null,
    'maxlength' => null,
    'required' => false,
])

@php
    $fieldPath = trim(preg_replace('/\]\[|\[|\]/', '.', $name), '.');
    $fieldId = 'lang-field-'.md5($name);
    $values = [
        'pt-br' => old('translate.pt-br.'.$fieldPath, $pt),
        'es' => old('translate.es.'.$fieldPath, $es),
        'en' => old('translate.en.'.$fieldPath, $en),
    ];
@endphp

<div class="lang-field" data-lang-field data-current-lang="pt-br" id="{{ $fieldId }}">
    <div class="lang-field__header">
        <div class="lang-field__copy">
            @if($label)
                <label class="sax-form-label mb-0" for="{{ $fieldId }}-visual">
                    {{ $label }} @if($required)<span class="text-danger" aria-hidden="true">*</span>@endif
                </label>
            @endif
            <span class="lang-field__current" data-lang-field-status>Conteúdo em Português</span>
        </div>
        <div class="lang-field__tabs" role="tablist" aria-label="Idioma do campo {{ $label ?: $name }}">
            @foreach(['pt-br' => ['PT', 'Português'], 'es' => ['ES', 'Español'], 'en' => ['EN', 'English']] as $locale => [$short, $full])
                <button type="button" class="lang-field__tab {{ $locale === 'pt-br' ? 'active' : '' }}"
                        role="tab" aria-selected="{{ $locale === 'pt-br' ? 'true' : 'false' }}"
                        data-lang-field-btn="{{ $locale }}" data-lang-label="{{ $full }}">
                    <span>{{ $short }}</span>
                    <i class="lang-field__state {{ filled($values[$locale]) ? 'is-complete' : '' }}" data-lang-state="{{ $locale }}" aria-hidden="true"></i>
                </button>
            @endforeach
        </div>
    </div>

    @foreach($values as $locale => $value)
        <input type="hidden" name="translate[{{ $locale }}][{{ $name }}]" data-lang-real="{{ $locale }}" value="{{ $value }}">
    @endforeach

    @if($type === 'textarea')
        <textarea id="{{ $fieldId }}-visual" class="form-control sax-input lang-field__control" data-lang-visual
                  rows="{{ $rows }}" placeholder="{{ $placeholder }}" @if($maxlength) maxlength="{{ $maxlength }}" @endif
                  @if($required) required @endif>{{ $values['pt-br'] }}</textarea>
    @else
        <input id="{{ $fieldId }}-visual" type="{{ $type }}" class="form-control sax-input lang-field__control" data-lang-visual
               value="{{ $values['pt-br'] }}" placeholder="{{ $placeholder }}" @if($maxlength) maxlength="{{ $maxlength }}" @endif
               @if($required) required @endif>
    @endif

    @if($help)
        <p class="lang-field__help"><i class="fas fa-circle-info" aria-hidden="true"></i>{{ $help }}</p>
    @endif
</div>
