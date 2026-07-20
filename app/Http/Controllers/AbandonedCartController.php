<?php

namespace App\Http\Controllers;

use App\Models\AbandonedCart;
use App\Models\Cart;
use Illuminate\Support\Facades\DB;

class AbandonedCartController extends Controller
{
    public function index()
    {
        $carts = AbandonedCart::with('items')->where('user_id', auth()->id())->latest('abandoned_at')->paginate(10);
        return view('users.abandoned-carts.index', compact('carts'));
    }

    public function show(AbandonedCart $abandonedCart)
    {
        abort_unless($abandonedCart->user_id === auth()->id(), 403);
        $abandonedCart->load('items.product');
        return view('users.abandoned-carts.show', compact('abandonedCart'));
    }

    public function restore(AbandonedCart $abandonedCart)
    {
        abort_unless($abandonedCart->user_id === auth()->id(), 403);
        if ($abandonedCart->status === 'restored') return back()->with('error', 'Este carrinho já foi restaurado.');

        $restored = 0;
        DB::transaction(function () use ($abandonedCart, &$restored) {
            $abandonedCart->load('items.product');
            foreach ($abandonedCart->items as $item) {
                if (!$item->product || !$item->product->isSellable()) continue;
                $cartItem = Cart::firstOrNew(['user_id' => auth()->id(), 'product_id' => $item->product_id]);
                $cartItem->quantity = min(($cartItem->exists ? $cartItem->quantity : 0) + $item->quantity, $item->product->stock);
                $cartItem->save();
                $restored++;
            }
            $abandonedCart->update(['status' => 'restored', 'restored_at' => now()]);
        });

        return redirect()->route('cart.view')->with($restored ? 'success' : 'error', $restored ? 'Carrinho restaurado com sucesso.' : 'Nenhum produto deste carrinho está disponível no momento.');
    }
}
