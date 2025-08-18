<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $user = auth()->user();
        $productId = $request->input('product_id');
        $quantity  = (int) $request->input('quantity', 1);

        $product = Product::findOrFail($productId);

        // Se já existe no carrinho -> soma
        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if ($cartItem) {
            $newQty = min($cartItem->quantity + $quantity, $product->stock);
            $cartItem->update(['quantity' => $newQty]);
        } else {
            Cart::create([
                'user_id'    => $user->id,
                'product_id' => $productId,
                'quantity'   => min($quantity, $product->stock),
            ]);
        }

        return back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function view()
    {
        $user = auth()->user();
        $cart = Cart::with('product')->where('user_id', $user->id)->get();

        return view('cart.view', compact('cart'));
    }

    public function update(Request $request, $productId)
    {
        $user = auth()->user();
        $quantity = (int) $request->input('quantity', 1);

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return back()->with('error', 'Produto não encontrado no carrinho.');
        }

        if ($quantity > 0) {
            $product = Product::findOrFail($productId);
            $cartItem->update(['quantity' => min($quantity, $product->stock)]);
        } else {
            $cartItem->delete();
        }

        return back()->with('success', 'Carrinho atualizado!');
    }

    public function remove($productId)
    {
        $user = auth()->user();

        Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        return back()->with('success', 'Produto removido do carrinho!');
    }
}
