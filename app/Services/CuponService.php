<?php

namespace App\Services;

use App\Models\Cart;
use App\Models\Cupon;
use App\Models\Order;
use App\Models\Product;
use App\Models\User;
use App\Models\UserCupon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Ponto único de verdade dos cupons: valida, calcula o desconto e registra o uso.
 * Carrinho, checkout e painel passam todos por aqui, para que os valores nunca divirjam.
 *
 * Os preços dos produtos são guardados na moeda base (USD) e convertidos só na exibição,
 * portanto todo cálculo abaixo acontece na moeda base.
 */
class CuponService
{
    public const SESSION_KEY = 'cupon_codigo';

    /** Busca um cupom pelo código, sem julgar validade. */
    public function localizar(?string $codigo): ?Cupon
    {
        $codigo = trim((string) $codigo);

        if ($codigo === '') {
            return null;
        }

        return Cupon::whereRaw('UPPER(codigo) = ?', [mb_strtoupper($codigo)])->first();
    }

    /** Itens do carrinho de um usuário, com o produto carregado. */
    public function itensDoCarrinho(?User $user): Collection
    {
        if (!$user) {
            return collect();
        }

        return Cart::with('product')
            ->where('user_id', $user->id)
            ->get()
            ->filter(fn ($item) => $item->product !== null)
            ->values();
    }

    public function subtotal(Collection $itens): float
    {
        return round($itens->sum(fn ($item) => (float) ($item->product->price ?? 0) * (int) $item->quantity), 2);
    }

    /**
     * Avalia um cupom contra um carrinho.
     *
     * @return array{ok: bool, mensagem: ?string, cupon: ?Cupon, subtotal: float,
     *               subtotal_elegivel: float, desconto: float, total: float, itens_elegiveis: array<int>}
     */
    public function avaliar(?Cupon $cupon, Collection $itens, ?User $user = null): array
    {
        $subtotal = $this->subtotal($itens);

        $falha = fn (string $mensagem) => [
            'ok'                => false,
            'mensagem'          => $mensagem,
            'cupon'             => $cupon,
            'subtotal'          => $subtotal,
            'subtotal_elegivel' => 0.0,
            'desconto'          => 0.0,
            'total'             => $subtotal,
            'itens_elegiveis'   => [],
        ];

        if (!$cupon) {
            return $falha(__('messages.cupon_invalido'));
        }

        if (!$cupon->ativo) {
            return $falha(__('messages.cupon_inativo'));
        }

        if (!$cupon->estaVigente()) {
            return $falha($cupon->temUsoDisponivel()
                ? __('messages.cupon_expirado')
                : __('messages.cupon_esgotado'));
        }

        if ($user && !$this->usuarioPodeUsar($cupon, $user)) {
            return $falha(__('messages.cupon_limite_usuario'));
        }

        if ($itens->isEmpty()) {
            return $falha(__('messages.cupon_carrinho_vazio'));
        }

        if ($cupon->valor_minimo && $subtotal < $cupon->valor_minimo) {
            return $falha(__('messages.cupon_valor_minimo', [
                'valor' => currency_format($cupon->valor_minimo),
            ]));
        }

        $elegiveis = $itens->filter(fn ($item) => $cupon->aplicaAoProduto($item->product))->values();

        if ($elegiveis->isEmpty()) {
            return $falha($cupon->preco_maximo_produto
                ? __('messages.cupon_sem_itens_preco', ['valor' => currency_format($cupon->preco_maximo_produto)])
                : __('messages.cupon_sem_itens'));
        }

        $subtotalElegivel = $this->subtotal($elegiveis);
        $desconto = $this->calcularDesconto($cupon, $subtotalElegivel);

        if ($desconto <= 0) {
            return $falha(__('messages.cupon_sem_desconto'));
        }

        return [
            'ok'                => true,
            'mensagem'          => null,
            'cupon'             => $cupon,
            'subtotal'          => $subtotal,
            'subtotal_elegivel' => $subtotalElegivel,
            'desconto'          => $desconto,
            'total'             => round(max(0, $subtotal - $desconto), 2),
            'itens_elegiveis'   => $elegiveis->pluck('product_id')->all(),
        ];
    }

    /**
     * Percentual incide sobre o subtotal elegível; valor fixo é abatido uma única vez
     * (nunca por item, senão o desconto cresceria com a quantidade).
     * O desconto nunca ultrapassa o teto do cupom nem o valor dos itens elegíveis.
     */
    private function calcularDesconto(Cupon $cupon, float $subtotalElegivel): float
    {
        $desconto = $cupon->ehPercentual()
            ? $subtotalElegivel * ($cupon->montante / 100)
            : (float) $cupon->montante;

        if ($cupon->desconto_maximo) {
            $desconto = min($desconto, $cupon->desconto_maximo);
        }

        $desconto = min($desconto, $subtotalElegivel);

        return round(max(0, $desconto), 2);
    }

    /** Quantas vezes o cliente já consumiu o cupom em pedidos fechados. */
    public function usosDoUsuario(Cupon $cupon, User $user): int
    {
        return UserCupon::consumidos()
            ->where('cupon_id', $cupon->id)
            ->where('user_id', $user->id)
            ->count();
    }

    public function usuarioPodeUsar(Cupon $cupon, User $user): bool
    {
        if (is_null($cupon->limite_por_usuario)) {
            return true;
        }

        return $this->usosDoUsuario($cupon, $user) < $cupon->limite_por_usuario;
    }

    /**
     * Resumo do carrinho já com o cupom da sessão aplicado.
     * Se o cupom deixou de ser válido (carrinho mudou, expirou, esgotou), ele é
     * descartado silenciosamente da sessão — o cliente nunca vê um desconto fantasma.
     *
     * @return array{subtotal: float, desconto: float, total: float, cupon: ?Cupon,
     *               itens_elegiveis: array<int>, aviso: ?string}
     */
    public function resumoDoCarrinho(?User $user, ?Collection $itens = null): array
    {
        $itens = $itens ?? $this->itensDoCarrinho($user);
        $subtotal = $this->subtotal($itens);

        $vazio = [
            'subtotal'        => $subtotal,
            'desconto'        => 0.0,
            'total'           => $subtotal,
            'cupon'           => null,
            'itens_elegiveis' => [],
            'aviso'           => null,
        ];

        $codigo = session(self::SESSION_KEY);

        if (!$codigo || !$user) {
            return $vazio;
        }

        $resultado = $this->avaliar($this->localizar($codigo), $itens, $user);

        if (!$resultado['ok']) {
            $this->remover();

            return array_merge($vazio, ['aviso' => $resultado['mensagem']]);
        }

        return [
            'subtotal'        => $resultado['subtotal'],
            'desconto'        => $resultado['desconto'],
            'total'           => $resultado['total'],
            'cupon'           => $resultado['cupon'],
            'itens_elegiveis' => $resultado['itens_elegiveis'],
            'aviso'           => null,
        ];
    }

    /** Guarda o cupom na sessão (só o código: o desconto é sempre recalculado). */
    public function aplicarNaSessao(Cupon $cupon): void
    {
        session([self::SESSION_KEY => $cupon->codigo]);
    }

    public function remover(): void
    {
        session()->forget(self::SESSION_KEY);
        session()->forget('cupom_aplicado');  // chaves do fluxo antigo
        session()->forget('applied_cupon');
    }

    /**
     * Consome o cupom no fechamento do pedido: incrementa o contador de usos de forma
     * atômica (respeitando a quantidade disponível) e registra o resgate do cliente.
     * Deve rodar dentro da transação do pedido.
     */
    public function registrarUso(Cupon $cupon, User $user, Order $order, float $desconto): bool
    {
        $consumiu = DB::table('cupons')
            ->where('id', $cupon->id)
            ->where(function ($q) {
                $q->whereNull('quantidade')->orWhereColumn('usado', '<', 'quantidade');
            })
            ->update([
                'usado'      => DB::raw('usado + 1'),
                'updated_at' => now(),
            ]);

        if (!$consumiu) {
            return false;
        }

        UserCupon::create([
            'user_id'  => $user->id,
            'cupon_id' => $cupon->id,
            'order_id' => $order->id,
            'desconto' => round($desconto, 2),
            'usado_em' => now(),
        ]);

        return true;
    }

    /** Devolve o uso ao cancelar/estornar um pedido. */
    public function devolverUso(Order $order): void
    {
        if (!$order->cupon_id) {
            return;
        }

        DB::table('cupons')
            ->where('id', $order->cupon_id)
            ->where('usado', '>', 0)
            ->update(['usado' => DB::raw('usado - 1'), 'updated_at' => now()]);

        UserCupon::where('order_id', $order->id)->delete();
    }

    /** Cupons vigentes que um cliente ainda pode usar. */
    public function disponiveisPara(?User $user): Collection
    {
        $cupons = Cupon::vigentes()->with(['category', 'brand', 'product'])->latest()->get();

        if (!$user) {
            return $cupons;
        }

        return $cupons->filter(fn (Cupon $cupon) => $this->usuarioPodeUsar($cupon, $user))->values();
    }

    /**
     * Melhor cupom vigente para um produto — usado no selo do card e da página do produto.
     * A lista de cupons vigentes é carregada uma vez por request.
     */
    public function melhorCuponParaProduto(Product $product): ?Cupon
    {
        static $vigentes = null;

        if ($vigentes === null) {
            $vigentes = Cupon::vigentes()->get();
        }

        $preco = (float) ($product->price ?? 0);

        return $vigentes
            ->filter(fn (Cupon $cupon) => $cupon->aplicaAoProduto($product))
            ->sortByDesc(fn (Cupon $cupon) => $this->calcularDesconto($cupon, $preco))
            ->first();
    }

    /** Quanto um cupom abate no preço unitário de um produto (para exibição). */
    public function descontoNoProduto(Cupon $cupon, Product $product): float
    {
        if (!$cupon->aplicaAoProduto($product)) {
            return 0.0;
        }

        return $this->calcularDesconto($cupon, (float) ($product->price ?? 0));
    }
}
