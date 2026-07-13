@props([
    'name',
    'label',
    'type' => 'text',      // text | select | file
    'value' => null,
    'options' => [],       // select: [valor => rótulo]
    'placeholder' => null,
    'prefix' => null,      // ex.: /marcas/
    'hint' => null,
    'required' => false,
    'accept' => null,      // file
])

@php $valor = old($name, $value); @endphp

<div class="sax-field">
    <label class="sax-field__label" for="{{ $name }}">
        {{ $label }}@if ($required) * @endif
    </label>

    @if ($type === 'select')
        <select id="{{ $name }}" name="{{ $name }}"
                class="sax-field__control @error($name) is-invalid @enderror"
                {{ $required ? 'required' : '' }}>
            <option value="">{{ $placeholder ?? __('messages.selecione') }}</option>
            @foreach ($options as $valorOpcao => $rotulo)
                <option value="{{ $valorOpcao }}" {{ (string) $valor === (string) $valorOpcao ? 'selected' : '' }}>
                    {{ $rotulo }}
                </option>
            @endforeach
        </select>

    @elseif ($type === 'file')
        <div class="sax-upload">
            <i class="fas fa-cloud-arrow-up"></i>
            <input type="file" id="{{ $name }}" name="{{ $name }}" accept="{{ $accept ?? 'image/*' }}">
            @if ($hint)
                <p>{{ $hint }}</p>
            @endif
        </div>

    @else
        @if ($prefix)
            <div class="sax-field__group">
                <span class="sax-field__prefix">{{ $prefix }}</span>
                <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $valor }}"
                       class="sax-field__control @error($name) is-invalid @enderror"
                       placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>
            </div>
        @else
            <input type="{{ $type }}" id="{{ $name }}" name="{{ $name }}" value="{{ $valor }}"
                   class="sax-field__control @error($name) is-invalid @enderror"
                   placeholder="{{ $placeholder }}" {{ $required ? 'required' : '' }}>
        @endif
    @endif

    @if ($hint && $type !== 'file')
        <span class="sax-field__hint">{{ $hint }}</span>
    @endif

    @error($name)
        <span class="sax-field__error">{{ $message }}</span>
    @enderror
</div>
