@extends('layout.admin')

@section('content')
<x-admin.card>
    {{-- A URL vem do router com marcadores trocados no JS --}}
    <div class="sax-activate" id="activate-app"
         data-toggle-url="{{ route('admin.activate.toggle', ['type' => '__TYPE__', 'id' => '__ID__']) }}">

        {{-- Cabeçalho: busca e filtro valem para as duas listas --}}
        <div class="sax-activate__bar">
            <div class="sax-activate__search">
                <i class="fa fa-search"></i>
                <input type="text" id="activate-search" autocomplete="off"
                       placeholder="{{ __('messages.activate_buscar_placeholder') }}">
            </div>

            <div class="sax-activate__filters" role="group">
                <button type="button" class="sax-chip is-on" data-filter="all">{{ __('messages.cupon_situacao_todas') }}</button>
                <button type="button" class="sax-chip" data-filter="1">{{ __('messages.ativo') }}</button>
                <button type="button" class="sax-chip" data-filter="2">{{ __('messages.inativo') }}</button>
            </div>

            <span class="sax-activate__hint" id="activate-feedback" role="status"></span>
        </div>

        @foreach ([
            ['tipo' => 'category', 'titulo' => __('messages.categorias'), 'icone' => 'fa-layer-group', 'itens' => $categories],
            ['tipo' => 'brand', 'titulo' => __('messages.marcas'), 'icone' => 'fa-tag', 'itens' => $brands],
        ] as $grupo)
            @php $ativos = $grupo['itens']->where('status', 1)->count(); @endphp

            <section class="sax-activate__section" data-section="{{ $grupo['tipo'] }}">
                <header class="sax-activate__head">
                    <h2><i class="fa-solid {{ $grupo['icone'] }}"></i> {{ $grupo['titulo'] }}</h2>
                    <span class="sax-activate__count">
                        <strong data-count-active>{{ $ativos }}</strong> / {{ $grupo['itens']->count() }} {{ __('messages.ativo') }}
                    </span>
                </header>

                <div class="sax-activate__grid">
                    {{-- São milhares de itens: o markup é mínimo de propósito. O nome não é
                         repetido em atributos (a busca lê o próprio texto) e o tipo fica na
                         seção, não em cada botão. --}}
                    @foreach ($grupo['itens'] as $item)
                        @php
                            // Há registros sem nome no banco; mostramos o slug para dar o que clicar.
                            $rotulo = trim((string) $item->name) !== ''
                                ? $item->name
                                : ($item->slug ?: '#' . $item->id);
                        @endphp

                        <div class="ai" data-s="{{ $item->status }}">
                            <span class="ai-n">{{ $rotulo }}</span>
                            <button type="button" class="sax-toggle{{ $item->status == 1 ? ' is-on' : '' }}" data-id="{{ $item->id }}"><i></i></button>
                        </div>
                    @endforeach
                </div>

                <p class="sax-activate__empty" data-empty hidden>{{ __('messages.activate_sem_resultado') }}</p>
            </section>
        @endforeach
    </div>
</x-admin.card>

<style>
.sax-activate { font-size: .8rem; }

/* Barra fixa de busca/filtro */
.sax-activate__bar {
    position: sticky;
    top: 0;
    z-index: 5;
    display: flex;
    flex-wrap: wrap;
    align-items: center;
    gap: .5rem;
    padding: .6rem 0 .75rem;
    margin-bottom: .5rem;
    background: #fff;
    border-bottom: 1px solid #eee;
}

.sax-activate__search {
    position: relative;
    flex: 1 1 260px;
    max-width: 340px;
}

.sax-activate__search i {
    position: absolute;
    left: .6rem;
    top: 50%;
    transform: translateY(-50%);
    font-size: .7rem;
    color: #aaa;
}

.sax-activate__search input {
    width: 100%;
    border: 1px solid #e0e0e0;
    border-radius: 2px;
    padding: .38rem .5rem .38rem 1.7rem;
    font-size: .78rem;
}

.sax-activate__search input:focus { outline: none; border-color: #111; }

.sax-activate__filters { display: flex; gap: .25rem; }

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
}

.sax-chip:hover { border-color: #111; color: #111; }
.sax-chip.is-on { background: #111; border-color: #111; color: #fff; }

.sax-activate__hint {
    margin-left: auto;
    font-size: .7rem;
    color: #2e7d32;
    min-height: 1rem;
}

.sax-activate__hint.is-error { color: #c0392b; }

/* Seções */
.sax-activate__section { margin-top: 1.5rem; }

.sax-activate__head {
    display: flex;
    align-items: baseline;
    justify-content: space-between;
    gap: 1rem;
    margin-bottom: .6rem;
}

.sax-activate__head h2 {
    margin: 0;
    font-size: .78rem;
    font-weight: 700;
    letter-spacing: .12em;
    text-transform: uppercase;
    color: #111;
}

.sax-activate__head i { margin-right: .35rem; color: #999; }

.sax-activate__count { font-size: .7rem; color: #999; white-space: nowrap; }
.sax-activate__count strong { color: #111; }

/* Grade compacta */
.sax-activate__grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(210px, 1fr));
    gap: .25rem .5rem;
}

.ai {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: .5rem;
    padding: .3rem .1rem .3rem .45rem;
    border-bottom: 1px solid #f2f2f2;
    min-width: 0;
}

.ai[hidden] { display: none; }

.ai-n {
    font-size: .76rem;
    color: #444;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
    min-width: 0;
}

.ai[data-s="1"] .ai-n { color: #111; font-weight: 600; }

/* Interruptor */
.sax-toggle {
    flex: 0 0 auto;
    width: 30px;
    height: 16px;
    padding: 0;
    border: 1px solid #d5d5d5;
    border-radius: 10px;
    background: #ececec;
    cursor: pointer;
    position: relative;
    transition: background .15s ease, border-color .15s ease;
}

.sax-toggle i {
    position: absolute;
    top: 1px;
    left: 1px;
    width: 12px;
    height: 12px;
    border-radius: 50%;
    background: #fff;
    box-shadow: 0 1px 2px rgba(0,0,0,.25);
    transition: transform .15s ease;
}

.sax-toggle.is-on { background: #1f7a37; border-color: #1f7a37; }
.sax-toggle.is-on i { transform: translateX(14px); }
.sax-toggle:disabled { opacity: .5; cursor: wait; }

.sax-activate__empty {
    font-size: .72rem;
    color: #999;
    font-style: italic;
    padding: .75rem 0 0;
    margin: 0;
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const app = document.getElementById('activate-app');
    if (!app) return;

    const feedback = document.getElementById('activate-feedback');
    const busca = document.getElementById('activate-search');
    const chips = app.querySelectorAll('.sax-chip');
    let filtroStatus = 'all';
    let timerFeedback = null;

    function avisar(mensagem, erro) {
        feedback.textContent = mensagem;
        feedback.classList.toggle('is-error', !!erro);
        clearTimeout(timerFeedback);
        timerFeedback = setTimeout(() => (feedback.textContent = ''), 2500);
    }

    // O nome usado na busca é lido do próprio texto (e guardado em cache na 1ª vez),
    // para não repetir o nome num atributo em cada um dos milhares de itens.
    function nomeDe(item) {
        if (!item._nome) {
            item._nome = item.querySelector('.ai-n').textContent.trim().toLowerCase();
        }
        return item._nome;
    }

    // Busca e filtro rodam sobre os itens já carregados: nada de recarregar a página.
    function aplicarFiltros() {
        const termo = busca.value.trim().toLowerCase();

        app.querySelectorAll('[data-section]').forEach(function (secao) {
            let visiveis = 0;

            secao.querySelectorAll('.ai').forEach(function (item) {
                const casaTexto = !termo || nomeDe(item).includes(termo);
                const casaStatus = filtroStatus === 'all' || item.dataset.s === filtroStatus;
                const mostrar = casaTexto && casaStatus;

                item.hidden = !mostrar;
                if (mostrar) visiveis++;
            });

            secao.querySelector('[data-empty]').hidden = visiveis > 0;
        });
    }

    busca.addEventListener('input', aplicarFiltros);

    chips.forEach(function (chip) {
        chip.addEventListener('click', function () {
            chips.forEach(c => c.classList.remove('is-on'));
            chip.classList.add('is-on');
            filtroStatus = chip.dataset.filter;
            aplicarFiltros();
        });
    });

    // Cada interruptor salva sozinho; o contador da seção acompanha.
    app.addEventListener('click', function (e) {
        const botao = e.target.closest('.sax-toggle');
        if (!botao) return;

        const item = botao.closest('.ai');
        const secao = botao.closest('[data-section]');
        const ligadoAntes = botao.classList.contains('is-on');

        botao.disabled = true;

        // O tipo (category/brand) vem da seção, não de cada botão.
        const url = app.dataset.toggleUrl
            .replace('__TYPE__', secao.dataset.section)
            .replace('__ID__', botao.dataset.id);

        fetch(url, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content
            }
        })
        .then(res => res.ok ? res.json() : Promise.reject())
        .then(function (data) {
            botao.classList.toggle('is-on', data.ativo);
            item.dataset.s = data.status;

            const contador = secao.querySelector('[data-count-active]');
            contador.textContent = Number(contador.textContent) + (data.ativo ? 1 : -1);

            avisar(data.message, false);
            aplicarFiltros(); // o item pode sair do filtro atual
        })
        .catch(function () {
            botao.classList.toggle('is-on', ligadoAntes); // desfaz a mudança visual
            avisar('{{ __('messages.activate_erro') }}', true);
        })
        .finally(function () {
            botao.disabled = false;
        });
    });
});
</script>
@endsection
