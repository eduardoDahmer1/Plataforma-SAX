<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use App\Models\Category;
use App\Models\Cupon;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class CuponController extends Controller
{
    public function index(Request $request)
    {
        $busca = trim((string) $request->input('busca'));
        $situacao = $request->input('situacao'); // vigentes | agendados | expirados | inativos

        $cupons = Cupon::query()
            ->with(['category', 'brand', 'product'])
            ->when($busca !== '', fn ($q) => $q->where('codigo', 'like', "%{$busca}%"))
            ->when($situacao === 'vigentes', fn ($q) => $q->vigentes())
            ->when($situacao === 'agendados', fn ($q) => $q->where('ativo', true)->whereDate('data_inicio', '>', now()))
            ->when($situacao === 'expirados', fn ($q) => $q->whereDate('data_final', '<', now()))
            ->when($situacao === 'inativos', fn ($q) => $q->where('ativo', false))
            ->latest()
            ->paginate(12)
            ->withQueryString();

        return view('admin.cupon.index', compact('cupons', 'busca', 'situacao'));
    }

    public function create()
    {
        return view('admin.cupon.create', $this->opcoesDoFormulario());
    }

    public function store(Request $request)
    {
        $dados = $this->validarCupon($request);

        try {
            $cupon = Cupon::create($dados);

            return redirect()
                ->route('admin.cupons.show', $cupon)
                ->with('success', __('messages.cupon_criado_sucesso'));
        } catch (\Throwable $e) {
            Log::error('Erro ao criar cupom', ['error' => $e->getMessage()]);

            return back()->withInput()->with('error', __('messages.cupon_erro_criar'));
        }
    }

    public function show(Cupon $cupon)
    {
        $cupon->load(['category', 'brand', 'product']);

        $usos = $cupon->usos()
            ->consumidos()
            ->with(['user', 'order'])
            ->latest('usado_em')
            ->paginate(15);

        $totalDescontado = $cupon->usos()->consumidos()->sum('desconto');

        return view('admin.cupon.show', compact('cupon', 'usos', 'totalDescontado'));
    }

    public function edit(Cupon $cupon)
    {
        return view('admin.cupon.edit', array_merge(
            ['cupon' => $cupon],
            $this->opcoesDoFormulario($cupon)
        ));
    }

    public function update(Request $request, Cupon $cupon)
    {
        $dados = $this->validarCupon($request, $cupon);

        try {
            $cupon->update($dados);

            return redirect()
                ->route('admin.cupons.show', $cupon)
                ->with('success', __('messages.cupon_atualizado_sucesso'));
        } catch (\Throwable $e) {
            Log::error('Erro ao atualizar cupom', ['id' => $cupon->id, 'error' => $e->getMessage()]);

            return back()->withInput()->with('error', __('messages.cupon_erro_atualizar'));
        }
    }

    public function destroy(Cupon $cupon)
    {
        // Cupom já usado vira histórico de pedidos: desativa em vez de apagar,
        // senão os pedidos perdem a referência do desconto que receberam.
        if ($cupon->usos()->consumidos()->exists()) {
            $cupon->update(['ativo' => false]);

            return redirect()
                ->route('admin.cupons.index')
                ->with('success', __('messages.cupon_desativado_em_uso'));
        }

        $cupon->delete();

        return redirect()
            ->route('admin.cupons.index')
            ->with('success', __('messages.cupon_deletado_sucesso'));
    }

    /** Liga/desliga o cupom sem abrir a edição. */
    public function toggle(Cupon $cupon)
    {
        $cupon->update(['ativo' => !$cupon->ativo]);

        return back()->with('success', $cupon->ativo
            ? __('messages.cupon_ativado')
            : __('messages.cupon_desativado'));
    }

    /**
     * Busca de produtos do formulário (autocomplete).
     * O catálogo tem dezenas de milhares de itens: listá-los todos num <select>
     * geraria alguns MB de HTML por página, então o produto é buscado sob demanda.
     */
    public function buscarProdutos(Request $request)
    {
        $termo = trim((string) $request->input('q'));

        if (mb_strlen($termo) < 2) {
            return response()->json([]);
        }

        $produtos = Product::query()
            ->whereNotNull('external_name')
            ->where('status', 1)
            ->where(function ($q) use ($termo) {
                $q->where('external_name', 'like', "%{$termo}%")
                  ->orWhere('sku', 'like', "%{$termo}%");
            })
            ->orderBy('external_name')
            ->limit(20)
            ->get(['id', 'external_name', 'sku', 'price']);

        return response()->json($produtos->map(fn ($p) => [
            'id'    => $p->id,
            'texto' => $p->external_name . ' — ' . ($p->sku ?: 's/ SKU'),
            'preco' => currency_format($p->price),
        ]));
    }

    private function opcoesDoFormulario(?Cupon $cupon = null): array
    {
        // Só entram no formulário categorias/marcas ativas e que tenham ao menos um
        // produto ativo: cupom apontado para escopo vazio nunca daria desconto.
        return [
            'categorias' => Category::whereNotNull('name')
                ->where('status', 1)
                ->whereHas('products', fn ($q) => $q->where('status', 1))
                ->orderBy('name')
                ->get(['id', 'name', 'slug']),
            'marcas'     => Brand::whereNotNull('name')
                ->where('status', 1)
                ->whereHas('products', fn ($q) => $q->where('status', 1))
                ->orderBy('name')
                ->get(['id', 'name']),
            // Só o produto já vinculado; os demais vêm da busca sob demanda.
            'produtoSelecionado' => $cupon?->product,
        ];
    }

    /**
     * Valida o cupom e devolve só os campos que fazem sentido para o modelo escolhido:
     * os vínculos dos outros modelos são zerados para não sobrar regra órfã.
     */
    private function validarCupon(Request $request, ?Cupon $cupon = null): array
    {
        $modelo = $request->input('modelo') ?: 'geral';

        $dados = $request->validate([
            'codigo' => [
                'required', 'string', 'max:60', 'regex:/^[A-Za-z0-9_-]+$/',
                Rule::unique('cupons', 'codigo')->ignore($cupon?->id),
            ],
            'descricao'            => 'nullable|string|max:255',
            'ativo'                => 'nullable|boolean',
            'tipo'                 => 'required|in:percentual,valor_fixo',
            'montante'             => [
                'required', 'numeric', 'min:0.01',
                $request->input('tipo') === 'percentual' ? 'max:100' : 'max:999999',
            ],
            'data_inicio'          => 'required|date',
            'data_final'           => 'required|date|after_or_equal:data_inicio',
            // A quantidade nunca pode cair abaixo do que já foi usado.
            'quantidade'           => 'nullable|integer|min:' . max(1, (int) ($cupon->usado ?? 0)),
            'limite_por_usuario'   => 'nullable|integer|min:1',
            'valor_minimo'         => 'nullable|numeric|min:0',
            'desconto_maximo'      => 'nullable|numeric|min:0',
            'preco_maximo_produto' => 'nullable|numeric|min:0',
            'modelo'               => ['nullable', Rule::in(Cupon::MODELOS)],
            'categoria_id'         => 'nullable|exists:categories,id|required_if:modelo,categoria',
            'marca_id'             => 'nullable|exists:brands,id|required_if:modelo,marca',
            'produto_id'           => 'nullable|exists:products,id|required_if:modelo,produto',
            'nome_termo'           => 'nullable|string|max:120|required_if:modelo,nome',
        ], [
            'codigo.regex'            => __('messages.cupon_codigo_formato'),
            'categoria_id.required_if' => __('messages.cupon_exige_categoria'),
            'marca_id.required_if'     => __('messages.cupon_exige_marca'),
            'produto_id.required_if'   => __('messages.cupon_exige_produto'),
            'nome_termo.required_if'   => __('messages.cupon_exige_nome'),
            'montante.max'             => __('messages.cupon_percentual_max'),
        ]);

        $dados['codigo'] = mb_strtoupper(trim($dados['codigo']));
        $dados['ativo'] = $request->boolean('ativo');
        $dados['modelo'] = $modelo;

        // Percentual não usa "valor fixo máximo por engano": o teto continua sendo
        // desconto_maximo. Já o valor fixo não tem sentido ter teto de desconto.
        if ($dados['tipo'] === 'valor_fixo') {
            $dados['desconto_maximo'] = null;
        }

        $dados['categoria_id'] = $modelo === 'categoria' ? $dados['categoria_id'] : null;
        $dados['marca_id']     = $modelo === 'marca' ? $dados['marca_id'] : null;
        $dados['produto_id']   = $modelo === 'produto' ? $dados['produto_id'] : null;
        $dados['nome_termo']   = $modelo === 'nome' ? trim((string) $dados['nome_termo']) : null;

        return $dados;
    }
}
