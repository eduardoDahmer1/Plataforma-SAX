@php
    $modeloAtual = old('modelo', $cupon->modelo ?? 'geral') ?: 'geral';
    $tipoAtual = old('tipo', $cupon->tipo ?? 'percentual');
    $ativoAtual = (bool) old('ativo', $cupon->ativo ?? true);
@endphp

@if ($errors->any())
    <div class="alert alert-danger border-0 rounded-0 x-small py-3 mb-4">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="row g-4">
    {{-- Seção: Identificação e Valor --}}
    <div class="col-12 mb-2">
        <span class="sax-section-title">{{ __('messages.parametros_principais_sec') }}</span>
    </div>

    <div class="col-md-4">
        <label class="sax-label">{{ __('messages.codigo_cupom_label') }} *</label>
        <input type="text" name="codigo" class="form-control sax-input fw-bold text-uppercase"
               value="{{ old('codigo', $cupon->codigo ?? '') }}" placeholder="EJ: PROMO2026" required>
        <small class="text-muted x-small">{{ __('messages.cupon_codigo_ajuda') }}</small>
    </div>

    <div class="col-md-4">
        <label class="sax-label">{{ __('messages.tipo_desconto_label') }} *</label>
        <select name="tipo" id="tipo-cupon" class="form-select sax-input" required>
            <option value="percentual" {{ $tipoAtual === 'percentual' ? 'selected' : '' }}>{{ __('messages.porcentagem_opt') }}</option>
            <option value="valor_fixo" {{ $tipoAtual === 'valor_fixo' ? 'selected' : '' }}>{{ __('messages.valor_fixo_opt') }}</option>
        </select>
    </div>

    <div class="col-md-4">
        <label class="sax-label">{{ __('messages.monto_montante_label') }} *</label>
        <div class="input-group">
            <span class="input-group-text rounded-0" id="montante-prefixo">{{ $tipoAtual === 'percentual' ? '%' : 'US$' }}</span>
            <input type="number" step="0.01" min="0.01" name="montante" class="form-control sax-input font-monospace"
                   value="{{ old('montante', $cupon->montante ?? '') }}" required>
        </div>
        <small class="text-muted x-small" id="montante-ajuda">{{ __('messages.cupon_montante_ajuda_percentual') }}</small>
    </div>

    <div class="col-md-8">
        <label class="sax-label">{{ __('messages.cupon_descricao_label') }}</label>
        <input type="text" name="descricao" class="form-control sax-input"
               value="{{ old('descricao', $cupon->descricao ?? '') }}"
               placeholder="{{ __('messages.cupon_descricao_placeholder') }}">
    </div>

    <div class="col-md-4 d-flex align-items-end">
        <div class="form-check form-switch">
            <input type="hidden" name="ativo" value="0">
            <input class="form-check-input" type="checkbox" role="switch" id="ativo" name="ativo" value="1"
                   {{ $ativoAtual ? 'checked' : '' }}>
            <label class="form-check-label sax-label mb-0" for="ativo">{{ __('messages.cupon_ativo_label') }}</label>
        </div>
    </div>

    {{-- Seção: Limites e Validade --}}
    <div class="col-12 mt-5 mb-2">
        <span class="sax-section-title">{{ __('messages.vigencia_limites_sec') }}</span>
    </div>

    <div class="col-md-3">
        <label class="sax-label">{{ __('messages.valido_desde_label') }} *</label>
        <input type="date" name="data_inicio" class="form-control sax-input"
               value="{{ old('data_inicio', isset($cupon->data_inicio) ? $cupon->data_inicio->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="sax-label">{{ __('messages.valido_ate_label') }} *</label>
        <input type="date" name="data_final" class="form-control sax-input"
               value="{{ old('data_final', isset($cupon->data_final) ? $cupon->data_final->format('Y-m-d') : '') }}" required>
    </div>

    <div class="col-md-3">
        <label class="sax-label">{{ __('messages.quantidade_inicial_label') }}</label>
        <input type="number" min="1" name="quantidade" class="form-control sax-input"
               placeholder="{{ __('messages.cupon_ilimitado') }}" value="{{ old('quantidade', $cupon->quantidade ?? '') }}">
        <small class="text-muted x-small">
            {{ __('messages.cupon_quantidade_ajuda') }}
            @if (($cupon->usado ?? 0) > 0)
                <strong>{{ __('messages.cupon_ja_usado', ['n' => $cupon->usado]) }}</strong>
            @endif
        </small>
    </div>

    <div class="col-md-3">
        <label class="sax-label">{{ __('messages.cupon_limite_usuario_label') }}</label>
        <input type="number" min="1" name="limite_por_usuario" class="form-control sax-input"
               placeholder="{{ __('messages.cupon_ilimitado') }}"
               value="{{ old('limite_por_usuario', $cupon->limite_por_usuario ?? '') }}">
        <small class="text-muted x-small">{{ __('messages.cupon_limite_usuario_ajuda') }}</small>
    </div>

    {{-- Seção: Regras de valor --}}
    <div class="col-12 mt-5 mb-2">
        <span class="sax-section-title">{{ __('messages.cupon_regras_valor_sec') }}</span>
    </div>

    <div class="col-md-4">
        <label class="sax-label">{{ __('messages.compra_minima_label') }}</label>
        <input type="number" step="0.01" min="0" name="valor_minimo" class="form-control sax-input"
               value="{{ old('valor_minimo', $cupon->valor_minimo ?? '') }}">
        <small class="text-muted x-small">{{ __('messages.cupon_valor_minimo_ajuda') }}</small>
    </div>

    <div class="col-md-4" id="desconto-maximo-field">
        <label class="sax-label">{{ __('messages.cupon_desconto_maximo_label') }}</label>
        <input type="number" step="0.01" min="0" name="desconto_maximo" class="form-control sax-input"
               value="{{ old('desconto_maximo', $cupon->desconto_maximo ?? '') }}">
        <small class="text-muted x-small">{{ __('messages.cupon_desconto_maximo_ajuda') }}</small>
    </div>

    <div class="col-md-4">
        <label class="sax-label">{{ __('messages.cupon_preco_maximo_produto_label') }}</label>
        <input type="number" step="0.01" min="0" name="preco_maximo_produto" class="form-control sax-input"
               value="{{ old('preco_maximo_produto', $cupon->preco_maximo_produto ?? '') }}">
        <small class="text-muted x-small">{{ __('messages.cupon_preco_maximo_produto_ajuda') }}</small>
    </div>

    {{-- Seção: Escopo de Aplicação --}}
    <div class="col-12 mt-5 mb-2">
        <span class="sax-section-title">{{ __('messages.regras_aplicacao_sec') }}</span>
    </div>

    <div class="col-md-6">
        <label class="sax-label">{{ __('messages.restringir_por_label') }}</label>
        <select name="modelo" id="modelo-cupon" class="form-select sax-input border-dark">
            <option value="geral" {{ $modeloAtual === 'geral' ? 'selected' : '' }}>{{ __('messages.aplicar_todo_site_opt') }}</option>
            <option value="categoria" {{ $modeloAtual === 'categoria' ? 'selected' : '' }}>{{ __('messages.categoria_especifica_opt') }}</option>
            <option value="marca" {{ $modeloAtual === 'marca' ? 'selected' : '' }}>{{ __('messages.marca_especifica_opt') }}</option>
            <option value="produto" {{ $modeloAtual === 'produto' ? 'selected' : '' }}>{{ __('messages.produto_especifico_opt') }}</option>
            <option value="nome" {{ $modeloAtual === 'nome' ? 'selected' : '' }}>{{ __('messages.cupon_por_nome_opt') }}</option>
        </select>
    </div>

    <div class="col-md-6 cupon-escopo-field" data-modelo="categoria">
        <label class="sax-label">{{ __('messages.selecionar_categoria_label') }}</label>
        <select name="categoria_id" class="form-select sax-input">
            <option value="">{{ __('messages.todas_opt') }}</option>
            @foreach ($categorias as $category)
                <option value="{{ $category->id }}" {{ (int) old('categoria_id', $cupon->categoria_id ?? 0) === $category->id ? 'selected' : '' }}>
                    {{ $category->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 cupon-escopo-field" data-modelo="marca">
        <label class="sax-label">{{ __('messages.selecionar_marca_label') }}</label>
        <select name="marca_id" class="form-select sax-input">
            <option value="">{{ __('messages.todas_opt') }}</option>
            @foreach ($marcas as $brand)
                <option value="{{ $brand->id }}" {{ (int) old('marca_id', $cupon->marca_id ?? 0) === $brand->id ? 'selected' : '' }}>
                    {{ $brand->name }}
                </option>
            @endforeach
        </select>
    </div>

    <div class="col-md-6 cupon-escopo-field" data-modelo="produto">
        <label class="sax-label">{{ __('messages.selecionar_produto_label') }}</label>

        {{-- Busca sob demanda: o catálogo é grande demais para um <select> completo --}}
        <div class="position-relative" id="produto-busca-wrap"
             data-url="{{ route('admin.cupons.produtos') }}">
            <input type="hidden" name="produto_id" id="produto_id"
                   value="{{ old('produto_id', $cupon->produto_id ?? '') }}">

            <input type="text" id="produto-busca" class="form-control sax-input" autocomplete="off"
                   placeholder="{{ __('messages.cupon_produto_busca_placeholder') }}"
                   value="{{ $produtoSelecionado ? $produtoSelecionado->external_name . ' — ' . $produtoSelecionado->sku : '' }}">

            <div id="produto-resultados" class="list-group position-absolute w-100 shadow-sm d-none"
                 style="z-index: 20; max-height: 260px; overflow-y: auto;"></div>
        </div>

        <small class="text-muted x-small">{{ __('messages.cupon_produto_busca_ajuda') }}</small>
    </div>

    <div class="col-md-6 cupon-escopo-field" data-modelo="nome">
        <label class="sax-label">{{ __('messages.cupon_nome_termo_label') }}</label>
        <input type="text" name="nome_termo" class="form-control sax-input"
               value="{{ old('nome_termo', $cupon->nome_termo ?? '') }}" placeholder="{{ __('messages.cupon_nome_termo_placeholder') }}">
        <small class="text-muted x-small">{{ __('messages.cupon_nome_termo_ajuda') }}</small>
    </div>

    {{-- Botões --}}
    <div class="col-12 mt-5 pt-4 border-top d-flex gap-3">
        <button type="submit" class="btn btn-dark rounded-0 px-5 text-uppercase fw-bold x-small tracking-wider">
            {{ $button }}
        </button>
        <a href="{{ route('admin.cupons.index') }}" class="btn btn-outline-secondary rounded-0 px-4 text-uppercase fw-bold x-small tracking-wider">
            {{ __('messages.cancelar_btn') }}
        </a>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const modelo = document.getElementById('modelo-cupon');
    const tipo = document.getElementById('tipo-cupon');
    const camposEscopo = document.querySelectorAll('.cupon-escopo-field');
    const descontoMaximo = document.getElementById('desconto-maximo-field');
    const prefixo = document.getElementById('montante-prefixo');
    const ajudaMontante = document.getElementById('montante-ajuda');

    const textos = {
        percentual: @json(__('messages.cupon_montante_ajuda_percentual')),
        valorFixo: @json(__('messages.cupon_montante_ajuda_valor'))
    };

    // Mostra só o campo do escopo escolhido.
    function alternarEscopo() {
        camposEscopo.forEach(function (campo) {
            campo.style.display = campo.dataset.modelo === modelo.value ? 'block' : 'none';
        });
    }

    // Teto de desconto só faz sentido para percentual: um valor fixo já é o próprio teto.
    function alternarTipo() {
        const ehPercentual = tipo.value === 'percentual';
        prefixo.textContent = ehPercentual ? '%' : 'US$';
        ajudaMontante.textContent = ehPercentual ? textos.percentual : textos.valorFixo;
        descontoMaximo.style.display = ehPercentual ? 'block' : 'none';
    }

    modelo.addEventListener('change', alternarEscopo);
    tipo.addEventListener('change', alternarTipo);

    alternarEscopo();
    alternarTipo();

    // --- Autocomplete de produto ---
    const buscaWrap = document.getElementById('produto-busca-wrap');
    const buscaInput = document.getElementById('produto-busca');
    const buscaResultados = document.getElementById('produto-resultados');
    const produtoIdInput = document.getElementById('produto_id');
    let debounce = null;

    function fecharResultados() {
        buscaResultados.classList.add('d-none');
        buscaResultados.innerHTML = '';
    }

    buscaInput.addEventListener('input', function () {
        const termo = this.value.trim();

        // Digitar de novo invalida o produto escolhido antes.
        produtoIdInput.value = '';
        clearTimeout(debounce);

        if (termo.length < 2) {
            fecharResultados();
            return;
        }

        debounce = setTimeout(function () {
            fetch(buscaWrap.dataset.url + '?q=' + encodeURIComponent(termo), {
                headers: { 'Accept': 'application/json' }
            })
            .then(res => res.json())
            .then(function (produtos) {
                buscaResultados.innerHTML = '';

                if (!produtos.length) {
                    fecharResultados();
                    return;
                }

                produtos.forEach(function (produto) {
                    const item = document.createElement('button');
                    item.type = 'button';
                    item.className = 'list-group-item list-group-item-action x-small text-start';
                    item.textContent = produto.texto + ' · ' + produto.preco;

                    item.addEventListener('click', function () {
                        produtoIdInput.value = produto.id;
                        buscaInput.value = produto.texto;
                        fecharResultados();
                    });

                    buscaResultados.appendChild(item);
                });

                buscaResultados.classList.remove('d-none');
            })
            .catch(fecharResultados);
        }, 300);
    });

    document.addEventListener('click', function (e) {
        if (!buscaWrap.contains(e.target)) fecharResultados();
    });
});
</script>
