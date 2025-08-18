<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Product;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        // Recupera o passo atual da sessão ou começa no passo 1
        $step = $request->session()->get('checkout_step', 1);

        // Recupera os itens do carrinho da sessão
        $cart = session()->get('cart', []);

        // Recarrega os dados completos do produto para exibição
        foreach ($cart as $productId => $item) {
            $product = Product::find($productId);
            if ($product) {
                $cart[$productId]['name'] = $product->name;
                $cart[$productId]['external_name'] = $product->external_name;
                $cart[$productId]['slug'] = $product->slug;
                $cart[$productId]['sku'] = $product->sku;
                $cart[$productId]['stock'] = $product->stock;
            }
        }

        // Busca os métodos de pagamento ativos
        $paymentMethods = PaymentMethod::where('active', 1)->get();

        return view('checkout.index', compact('step', 'paymentMethods', 'cart'));
    }

    public function store(Request $request)
    {
        $step = $request->input('step');
        $cart = session()->get('cart', []);

        switch ($step) {
            case 1:
                $request->session()->put('checkout_step', 2);
                break;

            case 2:
                $request->session()->put('checkout_step', 3);
                break;

            case 3:
                $request->session()->put('checkout_step', 4);
                break;

            case 4:
                $paymentMethod = $request->input('payment_method');

                if ($paymentMethod === 'bancard') {
                    $request->session()->put('payment_method', 'bancard');
                } elseif ($paymentMethod === 'deposito') {
                    $bankId = $request->input('bank_id');
                    $request->session()->put('payment_method', 'deposito');
                    $request->session()->put('bank_id', $bankId);
                }

                // Criação do pedido
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'status' => 'pending',
                    'total' => array_sum(array_map(fn($item) => $item['price'] * $item['quantity'], $cart)),
                    'payment_method' => $paymentMethod,
                ]);

                // Associando os itens ao pedido com campos extras
                foreach ($cart as $item) {
                    $product = Product::find($item['product_id']);
                    if ($product) {
                        $order->items()->create([
                            'product_id'    => $item['product_id'],
                            'quantity'      => $item['quantity'],
                            'price'         => $item['price'],
                            'name'          => $product->name,
                            'external_name' => $product->external_name,
                            'slug'          => $product->slug,
                            'sku'           => $product->sku,
                        ]);
                    }
                }

                $request->session()->put('checkout_step', 5);
                break;

            case 5:
                return redirect()->route('checkout.success')->with('success', 'Compra realizada com sucesso!');
        }

        return response()->json(['success' => true]);
    }

    public function whatsapp(Request $request)
    {
        $cart = session('cart', []);
        
        if (empty($cart)) {
            return back()->with('error', 'Seu carrinho está vazio!');
        }

        $user = auth()->user();
        $message = "Olá! Quero finalizar minha compra:\n\n";

        foreach($cart as $item) {
            $product = Product::find($item['product_id']);
            if ($product) {
                $message .= "Produto: {$product->name} ({$product->external_name})\n";
                $message .= "SKU: {$product->sku}\n";
                $message .= "Preço: R$ ".number_format($item['price'], 2, ',', '.')."\n";
                $message .= "Qtd: {$item['quantity']}\n";
                $message .= "------------------------\n";
            }
        }

        $total = array_reduce($cart, fn($sum, $item) => $sum + ($item['price'] * $item['quantity']), 0);
        $message .= "Total da compra: R$ ".number_format($total, 2, ',', '.')."\n\n";
        $message .= "Cliente: {$user->name}\n";
        $message .= "Email: {$user->email}\n";

        $message = urlencode($message);
        $whatsappNumber = '595984167575';

        return redirect("https://wa.me/{$whatsappNumber}?text={$message}");
    }
}
