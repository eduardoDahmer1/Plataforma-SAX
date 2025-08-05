<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CheckoutController extends Controller
{
    // Adiciona produto ao carrinho e redireciona pro passo 1
    public function add(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::findOrFail($productId);
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'title' => $product->title,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        return redirect()->route('checkout.step1');
    }

    public function addAndCheckout(Request $request)
    {
        $productId = $request->input('product_id');
        $quantity = $request->input('quantity', 1);

        $product = Product::findOrFail($productId);
        $cart = session()->get('cart', []);

        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            $cart[$productId] = [
                'title' => $product->title,
                'price' => $product->price,
                'quantity' => $quantity,
            ];
        }

        session()->put('cart', $cart);

        // Vai direto para o checkout passo 1
        return redirect()->route('checkout.step1');
    }

    // Passo 1 - Visualiza carrinho
    public function step1()
    {
        $cart = session('cart', []);
        return view('checkout.step1', compact('cart'));
    }

    public function storeStep1(Request $request)
    {
        return redirect()->route('checkout.step2');
    }

    // Passo 2 - Dados pessoais
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

        return response()->json(['success' => true]);
    }

    // Passo 3 - Entrega
    public function step3()
    {
        return view('checkout.step3');
    }

    public function storeStep3(Request $request)
    {
        $data = $request->validate([
            'shipping_method' => 'required',
            'address' => 'nullable|string',
        ]);

        session(['checkout.shipping' => $data]);

        return response()->json(['success' => true]);
    }

    // Passo 4 - Revisão
    public function step4()
    {
        $cart = session('cart', []);
        $personal = session('checkout.personal');
        $shipping = session('checkout.shipping');

        return view('checkout.step4', compact('cart', 'personal', 'shipping'));
    }

    public function finish(Request $request)
    {
        $payment = $request->validate([
            'payment_method' => 'required|in:bancard,deposito',
        ]);
    
        $cart = session('cart');
        $personal = session('checkout.personal');
        $shipping = session('checkout.shipping');
    
        // Verifique se o carrinho e os dados pessoais e de entrega estão definidos
        if (!$cart || !$personal || !$shipping) {
            return redirect()->route('checkout.step1')->with('error', 'Sessão expirada.');
        }
    
        // Se o usuário não estiver autenticado ou não for permitido finalizar o pedido
        if (!auth()->check() || !in_array(auth()->user()->user_type, [1, 2])) {
            return redirect()->route('checkout.step1')->with('error', 'Sem permissão para finalizar.');
        }
    
        // Calcular o total
        $total = collect($cart)->reduce(function ($carry, $item) {
            return $carry + ($item['price'] * $item['quantity']);
        }, 0);
    
        // Adiciona taxa de entrega se necessário
        if ($shipping['shipping_method'] === 'delivery') {
            $total += 10.00;
        }
    
        // Cria o pedido
        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $total,
            'payment_method' => $payment['payment_method'],
            'status' => 'pendente',
        ]);
    
        // Cria os itens do pedido
        foreach ($cart as $productId => $item) {
            OrderItem::create([
                'order_id' => $order->id,
                'product_id' => $productId,
                'quantity' => $item['quantity'],
                'price' => $item['price'],
            ]);
        }
    
        // Limpa os dados da sessão
        session()->forget(['cart', 'checkout.personal', 'checkout.shipping']);
    
        // Redireciona para o método de pagamento ou sucesso
        return match ($payment['payment_method']) {
            'bancard' => redirect()->route('payment.bancard', $order->id),
            'deposito' => redirect()->route('payment.deposito', $order->id),
            default => redirect()->route('home')->with('success', 'Pedido realizado com sucesso!')
        };
    }
}
