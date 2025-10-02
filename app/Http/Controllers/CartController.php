<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Currency;

class CartController extends Controller
{
    protected function getCurrency()
    {
        $currencySession = session('currency');
        $currencyId = null;

        if (is_array($currencySession) && isset($currencySession[0])) {
            $currencyId = $currencySession[0];
        } elseif (!is_array($currencySession) && $currencySession) {
            $currencyId = $currencySession;
        }

        $currency = $currencyId ? Currency::find($currencyId) : null;
        if (!$currency) {
            $currency = Currency::where('is_default', 1)->first();
        }

        return $currency;
    }

    public function add(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para adicionar ao carrinho.');
        }

        // 🚨 Bloqueia carrinho para user_type = 1
        if ($user->user_type == 1) {
            return back()->with('error', 'Seu perfil não tem permissão para adicionar produtos ao carrinho.');
        }

        $productId = $request->input('product_id');
        $quantity  = (int) $request->input('quantity', 1);

        $product = Product::find($productId);
        if (!$product) {
            return back()->with('error', 'Produto não encontrado.');
        }

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
        $cart = $user ? Cart::with('product')->where('user_id', $user->id)->get() : collect();

        $currency = $this->getCurrency();
        $symbol = $currency->sign ?? 'R$';
        $decimal = $currency->decimal_separator ?? ',';
        $thousand = $currency->thousands_separator ?? '.';
        $rate = $currency->value ?? 1;

        $cart->transform(function ($item) use ($symbol, $decimal, $thousand, $rate) {
            if ($item->product) {
                $price = ($item->product->price ?? 0) * $rate;
                $item->product->formatted_price = $symbol . ' ' . number_format($price, 2, $decimal, $thousand);

                if ($item->product->previous_price) {
                    $prev = $item->product->previous_price * $rate;
                    $item->product->formatted_previous_price = $symbol . ' ' . number_format($prev, 2, $decimal, $thousand);
                }
            }
            return $item;
        });

        return view('cart.view', compact('cart'));
    }

    public function addAndCheckout(Request $request)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para comprar.');
        }

        $productId = $request->input('product_id');
        $quantity  = (int) $request->input('quantity', 1);

        $product = Product::find($productId);
        if (!$product) {
            return back()->with('error', 'Produto não encontrado.');
        }

        // Busca itens do carrinho do usuário
        $cart = Cart::where('user_id', $user->id)->get();

        // Se carrinho estiver vazio -> adiciona
        if ($cart->isEmpty()) {
            Cart::create([
                'user_id'    => $user->id,
                'product_id' => $productId,
                'quantity'   => min($quantity, $product->stock),
            ]);
        }

        // Vai direto pro checkout
        return redirect()->route('checkout.index');
    }


    public function update(Request $request, $productId)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para atualizar o carrinho.');
        }

        $quantity = (int) $request->input('quantity', 1);

        $cartItem = Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->first();

        if (!$cartItem) {
            return back()->with('error', 'Produto não encontrado no carrinho.');
        }

        if ($quantity > 0) {
            $product = Product::find($productId);
            $cartItem->update(['quantity' => min($quantity, $product->stock)]);
        } else {
            $cartItem->delete();
        }

        return back()->with('success', 'Carrinho atualizado!');
    }

    public function remove($productId)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para remover do carrinho.');
        }

        Cart::where('user_id', $user->id)
            ->where('product_id', $productId)
            ->delete();

        return back()->with('success', 'Produto removido do carrinho!');
    }
}
