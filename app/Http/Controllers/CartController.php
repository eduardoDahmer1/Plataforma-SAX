<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Cart;
use App\Models\Currency;
use App\Services\CuponService;
use App\Models\AbandonedCart;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Mail\AbandonedCartHelpMail;
use App\Services\BusinessEventService;

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

        $product = Product::sellable()->find($productId);
        if (!$product) {
            return back()->with('error', 'Este produto não está disponível para venda no e-commerce.');
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
                ->available()
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

        $product = Product::sellable()->find($productId);
        if (!$product) {
            return back()->with('error', 'Este produto não está disponível para venda no e-commerce.');
        }

        $cart = Cart::where('user_id', $user->id)->available()->get();

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
            $product = Product::sellable()->find($productId);
            if (!$product) {
                $cartItem->delete();
                return back()->with('error', 'O produto foi removido do carrinho porque não está mais disponível para venda.');
            }

            $cartItem->update(['quantity' => min($quantity, $product->stock)]);
        } else {
            $removalResult = $this->removeCartProduct($user->id, $productId);

            if ($removalResult === 'last_item') {
                return back()->with('error', __('messages.cart_last_item_requires_abandon'));
            }

            if ($removalResult === 'not_found') {
                return back()->with('error', __('messages.cart_product_not_found'));
            }
        }

        return back()->with('success', 'Carrinho atualizado!');
    }

    public function remove($productId)
    {
        $user = auth()->user();
        if (!$user) {
            return redirect()->route('login')->with('error', 'Você precisa estar logado para remover do carrinho.');
        }

        $removalResult = $this->removeCartProduct($user->id, $productId);

        if ($removalResult === 'last_item') {
            return back()->with('error', __('messages.cart_last_item_requires_abandon'));
        }

        if ($removalResult === 'not_found') {
            return back()->with('error', __('messages.cart_product_not_found'));
        }

        return back()->with('success', __('messages.cart_product_removed'));
    }

    private function removeCartProduct(int $userId, $productId): string
    {
        return DB::transaction(function () use ($userId, $productId) {
            $cartItems = Cart::where('user_id', $userId)
                ->with('product')
                ->lockForUpdate()
                ->get();

            $target = $cartItems->first(
                fn (Cart $item) => (int) $item->product_id === (int) $productId
            );
            if (!$target) {
                return 'not_found';
            }

            $availableItems = $cartItems->filter(
                fn (Cart $item) => $item->product && $item->product->isSellable()
            );

            if ($availableItems->contains('id', $target->id) && $availableItems->count() <= 1) {
                return 'last_item';
            }

            $target->delete();

            return 'removed';
        });
    }

    public function abandon(Request $request)
    {
        $validated = $request->validate([
            'abandon_reason' => ['required', 'in:payment,shipping_price,later,help,no_answer'],
            'abandon_message' => ['nullable', 'string', 'max:1500'],
        ]);
        $user = auth()->user();
        $items = Cart::available()->with('product')->where('user_id', $user->id)->get();

        if ($items->isEmpty()) {
            return back()->with('error', 'Seu carrinho já está vazio.');
        }

        $currency = $this->getCurrency();
        $rate = (float) ($currency->value ?? 1);
        $abandonedCart = null;

        DB::transaction(function () use ($items, $user, $currency, $rate, $validated, &$abandonedCart) {
            $abandonedCart = AbandonedCart::create([
                'user_id' => $user->id,
                'total' => $items->sum(fn ($item) => (float) $item->product->price * $item->quantity),
                'items_count' => $items->sum('quantity'),
                'currency_sign' => $currency->sign ?? 'US$',
                'currency_value' => $rate,
                'status' => 'abandoned',
                'recovery_token' => Str::random(64),
                'abandoned_at' => now(),
                'feedback_reason' => $validated['abandon_reason'],
                'feedback_message' => $validated['abandon_message'] ?? null,
                'feedback_at' => now(),
            ]);

            foreach ($items as $item) {
                $abandonedCart->items()->create([
                    'product_id' => $item->product_id,
                    'product_name' => $item->product->external_name ?? $item->product->name ?? 'Produto',
                    'sku' => $item->product->sku,
                    'image' => $item->product->photo,
                    'unit_price' => $item->product->price,
                    'quantity' => $item->quantity,
                ]);
            }

            Cart::where('user_id', $user->id)->delete();
        });

        $reasonLabels = [
            'payment' => 'Não conseguiu concluir o pagamento',
            'shipping_price' => 'Preço ou frete não ficou adequado',
            'later' => 'Vai comprar em outro momento',
            'help' => 'Ficou com dúvida e precisa de ajuda',
            'no_answer' => 'Preferiu não informar o motivo',
        ];
        app(BusinessEventService::class)->record(
            'cart',
            'Cliente abandonou o carrinho',
            $reasonLabels[$validated['abandon_reason']] ?? 'Motivo não informado',
            'info',
            $user->id,
            null,
            'Carrinho #' . $abandonedCart->id,
        );

        dispatch(function () use ($abandonedCart) {
            try {
                $abandonedCart->loadMissing('user');
                Mail::to($abandonedCart->user->email)->send(new AbandonedCartHelpMail($abandonedCart));
                $abandonedCart->update(['help_email_sent_at' => now()]);
            } catch (\Throwable $e) {
                Log::error('Falha ao enviar ajuda de carrinho abandonado', ['cart_id' => $abandonedCart->id, 'message' => $e->getMessage()]);
                app(BusinessEventService::class)->record('email', 'E-mail do carrinho não foi enviado', 'A equipe pode entrar em contato manualmente com o cliente.', 'warning', $abandonedCart->user_id, null, 'Carrinho #' . $abandonedCart->id);
            }
        })->afterResponse();

        return redirect()->route('user.abandoned-carts.show', $abandonedCart)->with('success', 'Seu carrinho foi salvo no histórico e removido da sacola.');
    }
}
