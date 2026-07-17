@extends('layout.admin')

@section('content')
@php
    $tipos = [
        1 => ['rotulo' => __('messages.contato_tipo_consulta'), 'cor' => '#3b6fd4', 'icone' => 'fa-comment'],
        2 => ['rotulo' => __('messages.contato_tipo_curriculo'), 'cor' => '#1f7a37', 'icone' => 'fa-file-lines'],
        3 => ['rotulo' => __('messages.contato_tipo_newsletter'), 'cor' => '#9a7b1f', 'icone' => 'fa-envelope'],
    ];
@endphp

<x-admin.card>
    <div class="sax-cat sax-msg" id="msg-app">

        {{-- Cabeçalho --}}
        <div class="sax-cat__top">
            <div>
                <h1 class="sax-cat__title">{{ __('messages.centro_mensagens_titulo') }}</h1>
                <span class="sax-cat__sub">{{ __('messages.gestao_comunicacoes_desc') }}</span>
            </div>

            <a href="{{ route('admin.contacts.export', ['type' => $type]) }}" class="sax-cat__new">
                <i class="fas fa-download"></i> {{ __('messages.exportar_btn') }}
            </a>
        </div>

        {{-- Busca + abas por tipo + itens por página --}}
        <div class="sax-cat__bar sax-msg__bar">
            <form method="GET" action="{{ route('admin.contatos.index') }}" class="sax-cat__search">
                <i class="fa fa-search"></i>
                <input type="text" name="search" value="{{ $search }}" autocomplete="off"
                       placeholder="{{ __('messages.contato_buscar_placeholder') }}">
                @if ($type)
                    <input type="hidden" name="type" value="{{ $type }}">
                @endif
                <input type="hidden" name="per_page" value="{{ $perPage }}">
            </form>

            <div class="sax-msg__tabs">
                <a href="{{ route('admin.contatos.index', ['search' => $search, 'per_page' => $perPage]) }}"
                   class="sax-chip {{ !$type ? 'is-on' : '' }}">
                    {{ __('messages.cupon_situacao_todas') }} <span>{{ $totais['all'] }}</span>
                </a>

                @foreach ($tipos as $id => $info)
                    <a href="{{ route('admin.contatos.index', ['type' => $id, 'search' => $search, 'per_page' => $perPage]) }}"
                       class="sax-chip {{ (int) $type === $id ? 'is-on' : '' }}">
                        {{ $info['rotulo'] }} <span>{{ $totais[$id] }}</span>
                    </a>
                @endforeach
            </div>

            <form method="GET" action="{{ route('admin.contatos.index') }}" class="sax-msg__per">
                @if ($type) <input type="hidden" name="type" value="{{ $type }}"> @endif
                @if ($search) <input type="hidden" name="search" value="{{ $search }}"> @endif
                <select name="per_page" onchange="this.form.submit()">
                    @foreach ([20, 30, 50, 100] as $opt)
                        <option value="{{ $opt }}" @selected($perPage == $opt)>{{ $opt }}</option>
                    @endforeach
                </select>
            </form>

            <span class="sax-msg__hint" id="msg-feedback" role="status"></span>
        </div>

        {{-- Lista --}}
        <div class="sax-msg__list">
            @forelse ($contacts as $contact)
                @php
                    $info = $tipos[$contact->contact_type] ?? ['rotulo' => '—', 'cor' => '#999', 'icone' => 'fa-circle'];
                    $anexoUrl = $contact->attachment ? asset('storage/' . $contact->attachment) : null;
                    $extensao = $contact->attachment ? strtoupper(pathinfo($contact->attachment, PATHINFO_EXTENSION)) : null;
                @endphp

                <article class="sax-msg__item" data-row data-id="{{ $contact->id }}">
                    <div class="sax-msg__main" data-toggle role="button" tabindex="0"
                         aria-expanded="false" title="{{ __('messages.contato_ver_completo') }}">

                        <span class="sax-msg__type" style="--cor: {{ $info['cor'] }}" title="{{ $info['rotulo'] }}">
                            <i class="fa-solid {{ $info['icone'] }}"></i>
                        </span>

                        <div class="sax-msg__who">
                            <span class="sax-msg__name">{{ $contact->name ?: '—' }}</span>
                            <span class="sax-msg__contactinfo">
                                {{ $contact->email }}
                                @if ($contact->phone) · {{ $contact->phone }} @endif
                            </span>
                        </div>

                        {{-- A mensagem é sempre escapada: muitos envios são spam com HTML.
                             Inscrições de newsletter chegam sem texto, daí o cast. --}}
                        <p class="sax-msg__preview">{{ Str::limit((string) $contact->message, 110) }}</p>

                        <div class="sax-msg__side">
                            @if ($anexoUrl)
                                <span class="sax-msg__clip" title="{{ __('messages.contato_tem_anexo') }}">
                                    <i class="fa fa-paperclip"></i> {{ $extensao }}
                                </span>
                            @endif
                            <span class="sax-msg__date">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                        </div>

                        <i class="fa fa-chevron-down sax-msg__caret"></i>
                    </div>

                    {{-- Detalhe: só aparece ao clicar --}}
                    <div class="sax-msg__detail" hidden>
                        <div class="sax-msg__cols">
                            <div>
                                <span class="sax-msg__label">{{ __('messages.contato_tipo') }}</span>
                                <span class="sax-msg__value">{{ $info['rotulo'] }}</span>
                            </div>
                            <div>
                                <span class="sax-msg__label">E-mail</span>
                                <a class="sax-msg__value" href="mailto:{{ $contact->email }}">{{ $contact->email ?: '—' }}</a>
                            </div>
                            <div>
                                <span class="sax-msg__label">{{ __('messages.telefone') }}</span>
                                @if ($contact->phone)
                                    <a class="sax-msg__value" target="_blank" rel="noopener"
                                       href="https://wa.me/{{ preg_replace('/\D/', '', $contact->phone) }}">
                                        <i class="fab fa-whatsapp"></i> {{ $contact->phone }}
                                    </a>
                                @else
                                    <span class="sax-msg__value">—</span>
                                @endif
                            </div>
                            <div>
                                <span class="sax-msg__label">{{ __('messages.cupon_col_data') }}</span>
                                <span class="sax-msg__value">{{ $contact->created_at->format('d/m/Y H:i') }}</span>
                            </div>
                        </div>

                        <span class="sax-msg__label">{{ __('messages.contato_mensagem') }}</span>
                        <div class="sax-msg__text">{{ $contact->message ?: '—' }}</div>

                        @if ($anexoUrl)
                            <span class="sax-msg__label">{{ __('messages.contato_anexo') }}</span>
                            <div class="sax-msg__file">
                                <i class="fa fa-file"></i>
                                <span>{{ basename($contact->attachment) }}</span>
                                <a href="{{ $anexoUrl }}" target="_blank" rel="noopener" class="sax-cat-act">
                                    <i class="fa fa-eye"></i> {{ __('messages.contato_abrir') }}
                                </a>
                                <a href="{{ $anexoUrl }}" download class="sax-cat-act">
                                    <i class="fa fa-download"></i> {{ __('messages.contato_baixar') }}
                                </a>
                            </div>
                        @endif

                        <div class="sax-msg__actions">
                            @if ($contact->email)
                                <a href="mailto:{{ $contact->email }}" class="sax-cat-act">
                                    <i class="fa fa-reply"></i> {{ __('messages.contato_responder') }}
                                </a>
                            @endif
                            <button type="button" class="sax-cat-act sax-cat-act--danger" data-del>
                                <i class="fa fa-trash"></i> {{ __('messages.eliminar') }}
                            </button>
                        </div>
                    </div>
                </article>
            @empty
                <p class="sax-cat__empty">{{ __('messages.contato_nenhum') }}</p>
            @endforelse
        </div>

        <div class="sax-cat__pag">{{ $contacts->links() }}</div>
    </div>
</x-admin.card>

<style>
.sax-msg__bar { flex-wrap: wrap; }
.sax-msg__tabs { display: flex; gap: .25rem; flex-wrap: wrap; }
.sax-msg__tabs .sax-chip span { opacity: .55; margin-left: .15rem; }

.sax-msg__per select {
    border: 1px solid #e0e0e0;
    border-radius: 2px;
    padding: .35rem .4rem;
    font-size: .7rem;
    color: #666;
}

.sax-msg__hint { margin-left: auto; font-size: .7rem; color: #2e7d32; min-height: 1rem; }
.sax-msg__hint.is-error { color: #c0392b; }

.sax-msg__list { display: flex; flex-direction: column; gap: .3rem; margin-top: .6rem; }

.sax-msg__item { border: 1px solid #ececec; border-radius: 2px; background: #fff; }
.sax-msg__item:hover { border-color: #d8d8d8; }
.sax-msg__item.is-open { border-color: #111; }

.sax-msg__main {
    display: grid;
    grid-template-columns: 26px minmax(150px, 1.1fr) minmax(0, 2fr) auto 14px;
    align-items: center;
    gap: .7rem;
    padding: .55rem .7rem;
    cursor: pointer;
}

.sax-msg__type {
    width: 24px; height: 24px;
    display: inline-flex; align-items: center; justify-content: center;
    border-radius: 50%;
    background: color-mix(in srgb, var(--cor) 12%, #fff);
    color: var(--cor);
    font-size: .62rem;
}

.sax-msg__who { min-width: 0; }

.sax-msg__name {
    display: block;
    font-size: .76rem;
    font-weight: 700;
    color: #111;
    text-transform: uppercase;
    letter-spacing: .02em;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.sax-msg__contactinfo {
    display: block;
    font-size: .66rem;
    color: #9a9a9a;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
}

.sax-msg__preview {
    margin: 0;
    font-size: .74rem;
    color: #666;
    white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    min-width: 0;
}

.sax-msg__side { display: flex; align-items: center; gap: .5rem; white-space: nowrap; }

.sax-msg__clip {
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .04em;
    color: #1f7a37;
    background: #eef7f0;
    border-radius: 2px;
    padding: .12rem .3rem;
}

.sax-msg__date { font-size: .66rem; color: #aaa; }

.sax-msg__caret { font-size: .6rem; color: #ccc; transition: transform .15s ease; }
.sax-msg__item.is-open .sax-msg__caret { transform: rotate(180deg); }

/* Detalhe */
.sax-msg__detail { padding: .2rem .7rem .7rem; border-top: 1px solid #f2f2f2; }

.sax-msg__cols {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(140px, 1fr));
    gap: .6rem;
    margin: .6rem 0 .8rem;
}

.sax-msg__label {
    display: block;
    font-size: .58rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    color: #b0b0b0;
    margin-bottom: .15rem;
}

.sax-msg__value { font-size: .76rem; color: #333; text-decoration: none; }
a.sax-msg__value:hover { color: #111; text-decoration: underline; }

.sax-msg__text {
    font-size: .8rem;
    line-height: 1.65;
    color: #333;
    background: #fafafa;
    border-left: 2px solid #e5e5e5;
    padding: .6rem .7rem;
    margin-bottom: .8rem;
    white-space: pre-wrap;
    word-break: break-word;
}

.sax-msg__file {
    display: flex;
    align-items: center;
    gap: .5rem;
    border: 1px solid #ececec;
    padding: .4rem .6rem;
    margin-bottom: .8rem;
    font-size: .74rem;
    color: #555;
}

.sax-msg__file > span { flex: 1 1 auto; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
.sax-msg__file .sax-cat-act { flex: 0 0 auto; border: 1px solid #ececec; border-radius: 2px; }

.sax-msg__actions { display: flex; gap: .4rem; }
.sax-msg__actions .sax-cat-act { flex: 0 0 auto; border: 1px solid #ececec; border-radius: 2px; padding: .35rem .7rem; }

@media (max-width: 900px) {
    .sax-msg__main { grid-template-columns: 26px 1fr 14px; grid-row-gap: .3rem; }
    .sax-msg__preview, .sax-msg__side { grid-column: 2 / 4; }
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('msg-app');
    if (!app) return;

    const feedback = document.getElementById('msg-feedback');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let timer = null;

    function avisar(msg, erro) {
        feedback.textContent = msg;
        feedback.classList.toggle('is-error', !!erro);
        clearTimeout(timer);
        timer = setTimeout(() => (feedback.textContent = ''), 2500);
    }

    // Abre/fecha o detalhe da mensagem
    function alternar(item) {
        const detalhe = item.querySelector('.sax-msg__detail');
        const aberto = !detalhe.hidden;

        detalhe.hidden = aberto;
        item.classList.toggle('is-open', !aberto);
        item.querySelector('[data-toggle]').setAttribute('aria-expanded', String(!aberto));
    }

    app.addEventListener('click', function (e) {
        const excluir = e.target.closest('[data-del]');
        const cabecalho = e.target.closest('[data-toggle]');

        if (excluir) {
            const item = excluir.closest('[data-row]');
            if (!confirm('{{ __('messages.confirmar_exclusao') }}')) return;

            excluir.disabled = true;

            fetch('{{ url('admin/contatos') }}/' + item.dataset.id, {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token
                },
                body: JSON.stringify({ _method: 'DELETE' })
            })
            .then(res => res.ok ? res.json() : Promise.reject())
            .then(data => {
                item.remove();
                avisar(data.message, false);
            })
            .catch(() => {
                excluir.disabled = false;
                avisar('{{ __('messages.activate_erro') }}', true);
            });

            return;
        }

        if (cabecalho) {
            alternar(cabecalho.closest('[data-row]'));
        }
    });

    // Teclado: Enter/Espaço abrem a mensagem
    app.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter' && e.key !== ' ') return;
        const cabecalho = e.target.closest('[data-toggle]');
        if (!cabecalho) return;
        e.preventDefault();
        alternar(cabecalho.closest('[data-row]'));
    });
});
</script>
@endsection
