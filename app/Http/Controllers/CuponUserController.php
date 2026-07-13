<?php

namespace App\Http\Controllers;

use App\Models\UserCupon;
use App\Services\CuponService;
use Illuminate\Http\Request;

class CuponUserController extends Controller
{
    public function __construct(private CuponService $cupons)
    {
    }

    /**
     * Página "Meus cupons": os cupons que o cliente pode usar agora e o histórico de uso.
     */
    public function index()
    {
        $user = auth()->user();

        if (!$user) {
            return redirect()->route('login')->with('error', __('messages.cupon_precisa_login'));
        }

        $disponiveis = $this->cupons->disponiveisPara($user);

        $historico = UserCupon::consumidos()
            ->with(['cupon', 'order'])
            ->where('user_id', $user->id)
            ->latest('usado_em')
            ->get();

        $resumo = $this->cupons->resumoDoCarrinho($user);

        return view('users.cupon', compact('disponiveis', 'historico', 'resumo'));
    }

    /**
     * Aplica um cupom ao carrinho. Responde JSON quando chamado via fetch e
     * volta com flash message quando vem de um formulário normal.
     */
    public function apply(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string|max:60',
        ]);

        $user = auth()->user();

        if (!$user) {
            return $this->responder($request, false, __('messages.cupon_precisa_login'));
        }

        $cupon = $this->cupons->localizar($request->input('codigo'));
        $itens = $this->cupons->itensDoCarrinho($user);
        $resultado = $this->cupons->avaliar($cupon, $itens, $user);

        if (!$resultado['ok']) {
            $this->cupons->remover();

            return $this->responder($request, false, $resultado['mensagem']);
        }

        $this->cupons->aplicarNaSessao($resultado['cupon']);

        return $this->responder($request, true, __('messages.cupon_aplicado'), $resultado);
    }

    // Rota antiga do formulário: mesmo comportamento.
    public function applyCupon(Request $request)
    {
        return $this->apply($request);
    }

    public function remove(Request $request)
    {
        $this->cupons->remover();

        $resumo = $this->cupons->resumoDoCarrinho(auth()->user());

        return $this->responder($request, true, __('messages.cupon_removido'), $resumo);
    }

    private function responder(Request $request, bool $ok, ?string $mensagem, array $dados = [])
    {
        if ($request->expectsJson()) {
            $subtotal = (float) ($dados['subtotal'] ?? 0);
            $desconto = (float) ($dados['desconto'] ?? 0);
            $total    = (float) ($dados['total'] ?? $subtotal);
            $cupon    = $dados['cupon'] ?? null;

            return response()->json([
                'success'            => $ok,
                'message'            => $mensagem,
                'codigo'             => $cupon->codigo ?? null,
                'rotulo'             => $cupon?->rotuloDesconto(),
                'escopo'             => $cupon?->rotuloEscopo(),
                'subtotal'           => round($subtotal, 2),
                'desconto'           => round($desconto, 2),
                'total'              => round($total, 2),
                'subtotal_formatado' => currency_format($subtotal),
                'desconto_formatado' => currency_format($desconto),
                'total_formatado'    => currency_format($total),
            ], $ok ? 200 : 422);
        }

        return back()->with($ok ? 'success' : 'error', $mensagem);
    }
}
