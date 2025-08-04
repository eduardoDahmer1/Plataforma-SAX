<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    public function step1()
    {
        $cart = session('cart', []);
        return view('checkout.step1', compact('cart'));
    }

    public function storeStep1(Request $request)
    {
        return redirect()->route('checkout.step2');
    }

    public function step2()
    {
        return view('checkout.step2');
    }

    public function storeStep2(Request $request)
    {
        $data = $request->validate([
            'name' => 'required',
            'document' => 'required',
            'email' => 'required|email',
            'phone' => 'required',
        ]);

        session(['checkout.personal' => $data]);
        return redirect()->route('checkout.step3');
    }

    public function step3()
    {
        return view('checkout.step3');
    }

    public function storeStep3(Request $request)
    {
        $data = $request->validate([
            'shipping_method' => 'required',
            'address' => 'nullable|string'
        ]);

        session(['checkout.shipping' => $data]);
        return redirect()->route('checkout.step4');
    }

    public function step4()
    {
        $personal = session('checkout.personal');
        $shipping = session('checkout.shipping');
        $cart = session('cart', []);

        return view('checkout.step4', compact('personal', 'shipping', 'cart'));
    }

    public function finish(Request $request)
    {
        $payment = $request->validate([
            'payment_method' => 'required|in:bancard,deposito'
        ]);

        $cart = session('cart');
        $personal = session('checkout.personal');
        $shipping = session('checkout.shipping');

        if (!$cart || !$personal || !$shipping) {
            return redirect()->route('checkout.step1')->with('error', 'Sessão expirada.');
        }

        if (!auth()->check() || !in_array(auth()->user()->user_type, [1, 2])) {
            return redirect()->route('checkout.step1')->with('error', 'Sem permissão para finalizar.');
        }

        $total = collect($cart)->reduce(function ($carry, $item, $productId) {
            $product = Product::find($productId);
            return $carry + ($product->price * $item['quantity']);
        }, 0);

        if ($shipping['shipping_method'] === 'delivery') {
            $total += 10.00; // ajuste conforme moeda
        }

        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $total,
            'payment_method' => $payment['payment_method'],
            'status' => 'pendente'
        ]);

        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $product->price
            ]);
        }

        session()->forget(['cart', 'checkout.personal', 'checkout.shipping']);

        return match ($payment['payment_method']) {
            'bancard' => redirect()->route('payment.bancard', $order->id),
            'deposito' => redirect()->route('payment.deposito', $order->id),
            default => redirect()->route('home')->with('success', 'Pedido realizado!')
        };
    }
}
