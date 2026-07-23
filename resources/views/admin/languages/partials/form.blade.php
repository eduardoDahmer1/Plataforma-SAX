@php $editando = isset($language); @endphp

<div class="sax-trf">
    <div class="sax-trf__head">
        <div>
            <h1 class="sax-trf__title">
                {{ $editando ? __('messages.editar_traducao') : __('messages.nova_traducao') }}
            </h1>
            <span class="sax-trf__sub">{{ __('messages.traducao_form_ajuda') }}</span>
        </div>

        <a href="{{ route('admin.languages.index') }}" class="sax-trf__back">
            <i class="fa fa-chevron-left"></i> {{ __('messages.voltar') }}
        </a>
    </div>

    @if ($errors->any())
        <div class="sax-trf__errors">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST"
          action="{{ $editando ? route('admin.languages.update', $language->id) : route('admin.languages.store') }}">
        @csrf
        @if ($editando) @method('PUT') @endif

        <label class="sax-trf__label" for="key">{{ __('messages.label_chave') }} *</label>
        <input type="text" id="key" name="key" class="sax-trf__input sax-trf__input--key" required
               spellcheck="false" placeholder="{{ __('messages.placeholder_chave') }}"
               value="{{ old('key', $language->key ?? '') }}">
        <span class="sax-trf__hint">{{ __('messages.traducao_chave_formato') }}</span>

        <div class="sax-trf__grid">
            @foreach ([
                ['campo' => 'pt', 'rotulo' => __('messages.portugues_label'), 'obrig' => true],
                ['campo' => 'en', 'rotulo' => __('messages.ingles_label'), 'obrig' => false],
                ['campo' => 'es', 'rotulo' => __('messages.espanhol_label'), 'obrig' => false],
            ] as $idioma)
                <div>
                    <label class="sax-trf__label" for="{{ $idioma['campo'] }}">
                        {{ $idioma['rotulo'] }} <span class="sax-trf__tag">{{ strtoupper($idioma['campo']) }}</span>
                        @if ($idioma['obrig']) * @endif
                    </label>
                    {{-- Só o português é obrigatório: o controller aceita EN/ES vazios --}}
                    <textarea id="{{ $idioma['campo'] }}" name="{{ $idioma['campo'] }}" rows="3"
                              class="sax-trf__input" {{ $idioma['obrig'] ? 'required' : '' }}>{{ old($idioma['campo'], $language->{$idioma['campo']} ?? '') }}</textarea>
                </div>
            @endforeach
        </div>

        <div class="sax-trf__actions">
            <button type="submit" class="sax-trf__save">{{ __('messages.salvar_traducoes') }}</button>
            <a href="{{ route('admin.languages.index') }}" class="sax-trf__cancel">{{ __('messages.cancelar_btn') }}</a>
        </div>
    </form>
</div>
