<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CartController extends Controller
{
    // Adicionar produto no carrinho
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        $product = Product::findOrFail($productId);

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'quantity' => $quantity,
                'price' => $product->price,
            ];
        }

        if (auth()->check()) {
            $order = Order::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'status' => 'cart',
                ],
                [
                    'total' => 0,
                    'payment_method' => 'none',  // <<<< ajuste aqui
                ]
            );
        
            $order->items()->updateOrCreate(
                ['product_id' => $product->id],
                ['quantity' => $cart[$productId]['quantity'], 'price' => $product->price]
            );
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Produto adicionado ao carrinho!');
    }

    // Mostrar o carrinho
    public function view()
    {
        $cart = session()->get('cart', []);
        return view('cart.view', compact('cart'));
    }

    // Atualizar quantidade do item no carrinho
    public function update(Request $request, $productId)
    {
        $quantity = (int) $request->input('quantity', 1);

        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
            } else {
                unset($cart[$productId]);
            }
            session()->put('cart', $cart);
            return back()->with('success', 'Carrinho atualizado!');
        }

        return back()->with('error', 'Produto não encontrado no carrinho.');
    }

    // Remover item do carrinho
    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
            return back()->with('success', 'Produto removido do carrinho!');
        }

        return back()->with('error', 'Produto não encontrado no carrinho.');
    }
}
