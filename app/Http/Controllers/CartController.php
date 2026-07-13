<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Currency;
use App\Services\CuponService;

class CartController extends Controller
{
    public function __construct(private CuponService $cupons)
    {
    }

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
        $cart = collect();

        if ($user) {
            $cart = Cart::query()
                ->select(['id', 'user_id', 'product_id', 'quantity', 'created_at', 'updated_at'])
                ->where('user_id', $user->id)
                ->with([
                    // category_id/brand_id são necessários para avaliar o escopo dos cupons.
                    'product:id,brand_id,category_id,external_name,photo,price,previous_price,sku,stock,parent_id,color_parent_id,status,product_role',
                    'product.brand:id,name',
                ])
                ->get();
        }

        $currency = $this->getCurrency();
        $symbol = $currency->sign ?? 'R$';
        $decimal = $currency->decimal_separator ?? ',';
        $thousand = $currency->thousands_separator ?? '.';
        $decimals = $currency->decimal_digits ?? 2;
        $rate = $currency->value ?? 1;

        $cart->transform(function ($item) use ($symbol, $decimal, $thousand, $decimals, $rate) {
            if ($item->product) {
                $price = ($item->product->price ?? 0) * $rate;
                $item->product->formatted_price = $symbol . ' ' . number_format($price, $decimals, $decimal, $thousand);

                if ($item->product->previous_price) {
                    $prev = $item->product->previous_price * $rate;
                    $item->product->formatted_previous_price = $symbol . ' ' . number_format($prev, $decimals, $decimal, $thousand);
                }
            }
            return $item;
        });

        // Subtotal, desconto e total vêm do serviço: a view nunca recalcula cupom.
        $resumo = $this->cupons->resumoDoCarrinho($user, $cart->filter(fn ($i) => $i->product)->values());

        return view('cart.view', compact('cart', 'resumo'));
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

        $cart = Cart::where('user_id', $user->id)->get();

        if ($cart->isEmpty()) {
            Cart::create([
                'user_id'    => $user->id,
                'product_id' => $productId,
                'quantity'   => min($quantity, $product->stock),
            ]);
        }

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
            if (!$product) {
                return back()->with('error', 'Produto não encontrado.');
            }

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
