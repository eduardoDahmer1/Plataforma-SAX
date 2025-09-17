<?php

namespace App\Http\Controllers;

use App\Models\Cupon;
use App\Models\Product;
use App\Models\UserCupon;
use Illuminate\Http\Request;

class CuponUserController extends Controller
{
    /**
     * Verifica se um cupom é válido, retorna o desconto e registra para o usuário
     */
    public function apply(Request $request)
    {
        $request->validate([
            'codigo' => 'required|string',
            'cart' => 'required|array', // array de itens do carrinho [produto_id => quantidade]
        ]);

        $user = auth()->user();
        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'Você precisa estar logado para aplicar cupons.'
            ]);
        }

        $codigo = $request->input('codigo');
        $cart = $request->input('cart');

        $cupon = Cupon::ativos()->where('codigo', $codigo)->first();
        if (!$cupon) {
            return response()->json([
                'success' => false,
                'message' => 'Cupom inválido ou expirado.'
            ]);
        }

        if ($cupon->quantidade && $cupon->quantidade <= 0) {
            return response()->json([
                'success' => false,
                'message' => 'Este cupom não está mais disponível.'
            ]);
        }

        $desconto = 0;
        $subtotal = 0;

        foreach ($cart as $productId => $qty) {
            $product = Product::find($productId);
            if (!$product) continue;

            $aplica = match($cupon->modelo) {
                'categoria' => $product->category_id == $cupon->categoria_id,
                'marca' => $product->brand_id == $cupon->marca_id,
                'produto' => $product->id == $cupon->produto_id,
                default => true,
            };

            if ($aplica) {
                $precoProduto = $product->price * $qty;
                $subtotal += $precoProduto;

                $desconto += $cupon->tipo == 'percentual' 
                    ? ($precoProduto * $cupon->montante) / 100 
                    : $cupon->montante;
            }
        }

        if ($cupon->valor_maximo && $desconto > $cupon->valor_maximo) {
            $desconto = $cupon->valor_maximo;
        }

        if ($cupon->valor_minimo && $subtotal < $cupon->valor_minimo) {
            return response()->json([
                'success' => false,
                'message' => "O cupom só pode ser aplicado em compras acima de {$cupon->valor_minimo}."
            ]);
        }

        // Salva o cupom aplicado para o usuário
        $userCupon = UserCupon::updateOrCreate(
            ['user_id' => $user->id, 'cupon_id' => $cupon->id],
            ['desconto' => round($desconto, 2)]
        );

        return response()->json([
            'success' => true,
            'message' => 'Cupom aplicado com sucesso!',
            'desconto' => round($desconto, 2),
            'subtotal' => round($subtotal, 2),
            'total' => round($subtotal - $desconto, 2),
            'cupon' => $cupon->codigo
        ]);
    }
}
