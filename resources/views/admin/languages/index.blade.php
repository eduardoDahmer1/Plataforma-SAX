@extends('layout.admin')

@section('content')
<x-admin.card>
    <div class="sax-tr" id="tr-app"
         data-update-url="{{ route('admin.languages.update', ['language' => '__ID__']) }}"
         data-delete-url="{{ route('admin.languages.destroy', ['language' => '__ID__']) }}">

        {{-- Cabeçalho --}}
        <div class="sax-tr__top">
            <div>
                <h1 class="sax-tr__title">{{ __('messages.traducoes_titulo') }}</h1>
                <span class="sax-tr__sub">
                    {{ __('messages.mostrando') }} {{ $languages->total() }} {{ __('messages.chaves_de_traducao') }}
                    @if ($totalFaltando > 0)
                        · <a href="{{ route('admin.languages.index', ['filtro' => 'faltando']) }}" class="sax-tr__warn">
                            {{ __('messages.traducao_faltando', ['n' => $totalFaltando]) }}
                        </a>
                    @endif
                </span>
            </div>

            <a href="{{ route('admin.languages.create') }}" class="sax-tr__new">
                <i class="fas fa-plus"></i> {{ __('messages.nova_chave') }}
            </a>
        </div>

        {{-- Busca e filtro (server-side: a lista é paginada) --}}
        <div class="sax-tr__bar">
            <form method="GET" action="{{ route('admin.languages.index') }}" class="sax-tr__search">
                <i class="fa fa-search"></i>
                <input type="text" name="search" value="{{ $search }}" autocomplete="off"
                       placeholder="{{ __('messages.buscar_placeholder') }}">
                @if ($filtro)
                    <input type="hidden" name="filtro" value="{{ $filtro }}">
                @endif
            </form>

            <div class="sax-tr__chips">
                <a href="{{ route('admin.languages.index', ['search' => $search]) }}"
                   class="sax-chip {{ !$filtro ? 'is-on' : '' }}">{{ __('messages.cupon_situacao_todas') }}</a>
                <a href="{{ route('admin.languages.index', ['search' => $search, 'filtro' => 'faltando']) }}"
                   class="sax-chip {{ $filtro === 'faltando' ? 'is-on' : '' }}">{{ __('messages.traducao_incompletas') }}</a>
                @if ($search || $filtro)
                    <a href="{{ route('admin.languages.index') }}" class="sax-chip"><i class="fa fa-times"></i></a>
                @endif
            </div>

            <span class="sax-tr__hint" id="tr-feedback" role="status"></span>
        </div>

        {{-- Tabela --}}
        <div class="sax-tr__wrap">
            <table class="sax-tr__table">
                <thead>
                    <tr>
                        <th class="c-key">{{ __('messages.chave') }}</th>
                        <th>PT</th>
                        <th>EN</th>
                        <th>ES</th>
                        <th class="c-act"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($languages as $l)
                        @php $incompleta = blank($l->en) || blank($l->es); @endphp

                        <tr data-row data-id="{{ $l->id }}" class="{{ $incompleta ? 'is-missing' : '' }}">
                            <td class="c-key">
                                <input type="text" name="key" value="{{ $l->key }}" spellcheck="false">
                                <span class="c-id">#{{ $l->id }}</span>
                            </td>
                            <td><input type="text" name="pt" value="{{ $l->pt }}"></td>
                            <td><input type="text" name="en" value="{{ $l->en }}" placeholder="—"></td>
                            <td><input type="text" name="es" value="{{ $l->es }}" placeholder="—"></td>
                            <td class="c-act">
                                {{-- O salvar só acende quando algo muda na linha --}}
                                <button type="button" class="sax-tr__save" data-save hidden>
                                    <i class="fa fa-check"></i>
                                </button>
                                <button type="button" class="sax-tr__del" data-del title="{{ __('messages.eliminar') }}">
                                    <i class="fa fa-trash"></i>
                                </button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="sax-tr__empty">{{ __('messages.nenhuma_traducao') }}</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="sax-tr__pag">{{ $languages->links() }}</div>
    </div>
</x-admin.card>

<style>
.sax-tr { font-size: .8rem; }

.sax-tr__top {
    display: flex;
    align-items: flex-end;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: .9rem;
}

.sax-tr__title {
    margin: 0;
    font-size: 1rem;
    font-weight: 700;
    letter-spacing: .14em;
    text-transform: uppercase;
    color: #111;
}

.sax-tr__sub { font-size: .7rem; color: #999; }
.sax-tr__warn { color: #b8860b; text-decoration: none; font-weight: 600; }
.sax-tr__warn:hover { text-decoration: underline; }

.sax-tr__new {
    flex: 0 0 auto;
    background: #111;
    color: #fff;
    border: 1px solid #111;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .1em;
    text-transform: uppercase;
    padding: .45rem .9rem;
    border-radius: 2px;
    text-decoration: none;
}

.sax-tr__new:hover { opacity: .85; color: #fff; }

/* Barra fixa */
.sax-tr__bar {
    position: sticky;
    top: 0;
    z-index: 5;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
    padding: .6rem 0 .7rem;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.sax-tr__search { position: relative; flex: 1 1 260px; max-width: 360px; margin: 0; }

.sax-tr__search i {
    position: absolute; left: .6rem; top: 50%; transform: translateY(-50%);
    font-size: .7rem; color: #aaa;
}

.sax-tr__search input {
    width: 100%;
    border: 1px solid #e0e0e0;
    border-radius: 2px;
    padding: .38rem .5rem .38rem 1.7rem;
    font-size: .78rem;
}

.sax-tr__search input:focus { outline: none; border-color: #111; }

.sax-tr__chips { display: flex; gap: .25rem; }

.sax-chip {
    border: 1px solid #e0e0e0;
    background: #fff;
    color: #666;
    font-size: .65rem;
    font-weight: 700;
    letter-spacing: .06em;
    text-transform: uppercase;
    padding: .35rem .6rem;
    border-radius: 2px;
    cursor: pointer;
    text-decoration: none;
}

.sax-chip:hover { border-color: #111; color: #111; }
.sax-chip.is-on { background: #111; border-color: #111; color: #fff; }

.sax-tr__hint { margin-left: auto; font-size: .7rem; color: #2e7d32; min-height: 1rem; }
.sax-tr__hint.is-error { color: #c0392b; }

/* Tabela densa */
.sax-tr__wrap { overflow-x: auto; }

.sax-tr__table { width: 100%; border-collapse: collapse; margin-top: .5rem; }

.sax-tr__table th {
    text-align: left;
    font-size: .6rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #999;
    padding: .4rem .35rem;
    border-bottom: 1px solid #eee;
    white-space: nowrap;
}

.sax-tr__table td {
    padding: .15rem .35rem;
    border-bottom: 1px solid #f4f4f4;
    vertical-align: middle;
}

.sax-tr__table tr.is-missing .c-key input { border-left: 2px solid #e0b74a; }
.sax-tr__table tr:hover td { background: #fafafa; }

.sax-tr__table input {
    width: 100%;
    min-width: 120px;
    border: 1px solid transparent;
    background: transparent;
    padding: .3rem .4rem;
    font-size: .76rem;
    color: #333;
    border-radius: 2px;
}

.sax-tr__table input:hover { border-color: #ececec; }
.sax-tr__table input:focus { outline: none; border-color: #111; background: #fff; }
.sax-tr__table input.is-dirty { background: #fffdf3; border-color: #e0b74a; }

.c-key { width: 22%; white-space: nowrap; }
.c-key input { font-family: ui-monospace, monospace; font-size: .72rem; color: #111; font-weight: 600; }
.c-id { font-size: .58rem; color: #ccc; padding-left: .4rem; }

.c-act { width: 62px; white-space: nowrap; text-align: right; }

.sax-tr__save, .sax-tr__del {
    border: 1px solid #e0e0e0;
    background: #fff;
    width: 24px; height: 24px;
    line-height: 1;
    font-size: .65rem;
    border-radius: 2px;
    cursor: pointer;
    color: #999;
}

.sax-tr__save { background: #1f7a37; border-color: #1f7a37; color: #fff; }
.sax-tr__save:hover { opacity: .85; }
.sax-tr__del:hover { border-color: #c0392b; color: #c0392b; }
.sax-tr__save:disabled, .sax-tr__del:disabled { opacity: .5; cursor: wait; }

.sax-tr__empty { text-align: center; color: #999; font-style: italic; padding: 2rem 0; }
.sax-tr__pag { display: flex; justify-content: center; margin-top: 1rem; }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('tr-app');
    if (!app) return;

    const feedback = document.getElementById('tr-feedback');
    const token = document.querySelector('meta[name="csrf-token"]').content;
    let timer = null;

    function avisar(msg, erro) {
        feedback.textContent = msg;
        feedback.classList.toggle('is-error', !!erro);
        clearTimeout(timer);
        timer = setTimeout(() => (feedback.textContent = ''), 2500);
    }

    function url(base, id) {
        return app.dataset[base].replace('__ID__', id);
    }

    // Marca a linha como alterada e revela o botão de salvar.
    app.addEventListener('input', function (e) {
        const input = e.target.closest('.sax-tr__table input');
        if (!input) return;

        const linha = input.closest('[data-row]');
        input.classList.toggle('is-dirty', input.value !== input.defaultValue);

        const alterada = [...linha.querySelectorAll('input')].some(i => i.value !== i.defaultValue);
        linha.querySelector('[data-save]').hidden = !alterada;
    });

    // Ctrl+Enter ou Enter salva a linha
    app.addEventListener('keydown', function (e) {
        if (e.key !== 'Enter') return;
        const linha = e.target.closest('[data-row]');
        if (!linha) return;
        e.preventDefault();
        salvar(linha);
    });

    function salvar(linha) {
        const botao = linha.querySelector('[data-save]');
        const inputs = linha.querySelectorAll('input');
        const corpo = {};
        inputs.forEach(i => (corpo[i.name] = i.value));

        botao.disabled = true;

        fetch(url('updateUrl', linha.dataset.id), {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': token,
                'X-HTTP-Method-Override': 'PUT'
            },
            body: JSON.stringify({ ...corpo, _method: 'PUT' })
        })
        .then(res => res.json().then(data => ({ ok: res.ok, data })))
        .then(({ ok, data }) => {
            if (!ok) {
                const erro = data.errors ? Object.values(data.errors)[0][0] : (data.message || 'Erro');
                avisar(erro, true);
                return;
            }

            // O valor salvo vira o novo "original": a linha deixa de estar alterada.
            inputs.forEach(i => {
                i.defaultValue = i.value;
                i.classList.remove('is-dirty');
            });

            botao.hidden = true;
            linha.classList.toggle('is-missing', data.falta);
            avisar(data.message, false);
        })
        .catch(() => avisar('{{ __('messages.activate_erro') }}', true))
        .finally(() => (botao.disabled = false));
    }

    app.addEventListener('click', function (e) {
        const salvarBtn = e.target.closest('[data-save]');
        const delBtn = e.target.closest('[data-del]');

        if (salvarBtn) {
            salvar(salvarBtn.closest('[data-row]'));
            return;
        }

        if (delBtn) {
            const linha = delBtn.closest('[data-row]');
            if (!confirm('{{ __('messages.confirmar_exclusao') }}')) return;

            delBtn.disabled = true;

            fetch(url('deleteUrl', linha.dataset.id), {
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
                linha.remove();
                avisar(data.message, false);
            })
            .catch(() => {
                delBtn.disabled = false;
                avisar('{{ __('messages.activate_erro') }}', true);
            });
        }
    });
});
</script>
@endsection
