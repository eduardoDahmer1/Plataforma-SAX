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

<style>
.sax-trf { font-size: .8rem; max-width: 960px; }

.sax-trf__head {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    padding-bottom: .7rem;
    margin-bottom: 1.2rem;
    border-bottom: 1px solid #eee;
}

.sax-trf__title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #111;
}

.sax-trf__sub { font-size: .7rem; color: #999; }

.sax-trf__back {
    flex: 0 0 auto;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #777;
    text-decoration: none;
}

.sax-trf__back:hover { color: #111; }

.sax-trf__errors {
    border-left: 2px solid #c0392b;
    background: #fdf3f2;
    padding: .6rem .8rem;
    margin-bottom: 1rem;
}

.sax-trf__errors ul { margin: 0; padding-left: 1rem; font-size: .75rem; color: #a5352a; }

.sax-trf__label {
    display: block;
    font-size: .62rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #888;
    margin-bottom: .3rem;
}

.sax-trf__tag {
    font-size: .55rem;
    background: #f1f1f1;
    border: 1px solid #e5e5e5;
    padding: .05rem .25rem;
    border-radius: 2px;
    color: #666;
}

.sax-trf__input {
    width: 100%;
    border: 1px solid #e0e0e0;
    border-radius: 2px;
    padding: .45rem .55rem;
    font-size: .8rem;
    color: #222;
    resize: vertical;
}

.sax-trf__input:focus { outline: none; border-color: #111; }
.sax-trf__input--key { font-family: ui-monospace, monospace; font-weight: 600; }

.sax-trf__hint { display: block; font-size: .65rem; color: #aaa; margin: .25rem 0 1.2rem; }

.sax-trf__grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(220px, 1fr));
    gap: 1rem;
}

.sax-trf__actions {
    display: flex;
    align-items: center;
    gap: 1rem;
    margin-top: 1.5rem;
    padding-top: 1rem;
    border-top: 1px solid #eee;
}

.sax-trf__save {
    background: #111;
    border: 1px solid #111;
    color: #fff;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: .55rem 1.6rem;
    border-radius: 2px;
    cursor: pointer;
}

.sax-trf__save:hover { opacity: .85; }

.sax-trf__cancel {
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .08em;
    text-transform: uppercase;
    color: #999;
    text-decoration: none;
}

.sax-trf__cancel:hover { color: #111; }
</style>
