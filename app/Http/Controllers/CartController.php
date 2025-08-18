<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CartController extends Controller
{
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);

        $product = Product::findOrFail($productId);

        $cart = session()->get('cart', []);

        $productName = $product->name ?? $product->external_name ?? 'Produto';

        if (isset($cart[$productId])) {
            $newQty = $cart[$productId]['quantity'] + $quantity;
            $cart[$productId]['quantity'] = min($newQty, $product->stock); // Limita pelo estoque
        } else {
            $cart[$productId] = [
                'product_id'    => $productId,
                'name'          => $product->name,
                'external_name' => $product->external_name,
                'slug'          => $product->slug,
                'sku'           => $product->sku,  // se tiver no model
                'price'         => $product->price,
                'quantity'      => min($quantity, $product->stock),
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
                    'payment_method' => 'none',
                ]
            );
        
            $order->items()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id
                ],
                [
                    'quantity'      => $cart[$productId]['quantity'],
                    'price'         => $product->price,
                    'name'          => $product->name,
                    'external_name' => $product->external_name,
                    'slug'          => $product->slug,
                    'sku'           => $product->sku, // se tiver no model
                ]
            );
        }

        session()->put('cart', $cart);

        return back()->with('success', 'Produto adicionado ao carrinho!');
    }

    public function syncCartSession()
    {
        $cart = session()->get('cart', []);

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $cart[$productId]['name'] = $product->name;
                $cart[$productId]['external_name'] = $product->external_name;
                $cart[$productId]['slug'] = $product->slug;
                $cart[$productId]['sku'] = $product->sku;
                $cart[$productId]['stock'] = $product->stock;
            } else {
                // Remove produtos que não existem mais
                unset($cart[$productId]);
            }
        }

        session()->put('cart', $cart);
    }

    public function view()
    {
        $cart = session()->get('cart', []);
        return view('cart.view', compact('cart'));
    }

    public function update(Request $request, $productId)
    {
        $quantity = (int) $request->input('quantity', 1);
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $product = Product::find($productId);
            if (!$product) return back()->with('error', 'Produto não encontrado.');

            if ($quantity > 0) {
                $cart[$productId]['quantity'] = min($quantity, $product->stock); // Limita pelo estoque
            } else {
                unset($cart[$productId]);
            }

            session()->put('cart', $cart);

            if (auth()->check()) {
                $order = Order::where('user_id', auth()->id())->where('status', 'cart')->first();
                if ($order) {
                    $orderItem = $order->items()->where('product_id', $productId)->first();
                    if ($orderItem) {
                        $orderItem->update([
                            'quantity'      => $cart[$productId]['quantity'] ?? 0,
                            'price'         => $product->price,
                            'name'          => $product->name,
                            'external_name' => $product->external_name,
                            'slug'          => $product->slug,
                            'sku'           => $product->sku,
                        ]);
                    }
                    $total = $order->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                    $order->update(['total' => $total]);
                }
            }

            return back()->with('success', 'Carrinho atualizado!');
        }

        return back()->with('error', 'Produto não encontrado no carrinho.');
    }

    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);

            if (auth()->check()) {
                $order = Order::where('user_id', auth()->id())->where('status', 'cart')->first();
                if ($order) {
                    $order->items()->where('product_id', $productId)->delete();
                    if ($order->items()->count() === 0) $order->delete();
                }
            }

            return back()->with('success', 'Produto removido do carrinho!');
        }

        return back()->with('error', 'Produto não encontrado no carrinho.');
    }
}
