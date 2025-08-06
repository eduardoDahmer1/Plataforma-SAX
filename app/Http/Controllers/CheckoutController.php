<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Session;

class CheckoutController extends Controller
{
    public function index(Request $request)
    {
        // Recupera o passo atual da sessão ou começa no passo 1
        $step = $request->session()->get('checkout_step', 1);
    
        // Recupera os itens do carrinho da sessão
        $cart = session()->get('cart', []);
    
        // Busca os métodos de pagamento ativos
        $paymentMethods = PaymentMethod::where('active', 1)->get();
    
        return view('checkout.index', compact('step', 'paymentMethods', 'cart'));
    }

    public function store(Request $request)
    {
        // Recupera a etapa atual
        $step = $request->input('step');
        $cart = session()->get('cart', []);
    
        switch ($step) {
            case 1:
                // Salva os dados do produto (se necessário) e avança para o próximo passo
                $request->session()->put('checkout_step', 2);
                break;
    
            case 2:
                // Salva os dados pessoais
                $request->session()->put('checkout_step', 3);
                break;
    
            case 3:
                // Salva o endereço de envio
                $request->session()->put('checkout_step', 4);
                break;
    
            case 4:
                // Salva o método de pagamento e avança para a finalização
                $paymentMethod = $request->input('payment_method');
    
                if ($paymentMethod === 'bancard') {
                    // Salve ou processe os dados do pagamento via Bancard
                    $request->session()->put('payment_method', 'bancard');
                } elseif ($paymentMethod === 'deposito') {
                    // Salve ou processe os dados de depósito bancário
                    $bankId = $request->input('bank_id');
                    $request->session()->put('payment_method', 'deposito');
                    $request->session()->put('bank_id', $bankId);
                }
    
                // Criação do pedido com os itens do carrinho
                $order = Order::create([
                    'user_id' => auth()->id(),
                    'status' => 'pending', // Status do pedido
                    'total' => array_sum(array_map(function ($item) {
                        return $item['price'] * $item['quantity'];
                    }, $cart)), // Total do pedido
                    'payment_method' => $paymentMethod,
                ]);
    
                // Associando os itens ao pedido
                foreach ($cart as $item) {
                    $order->items()->create([
                        'product_id' => $item['product_id'],
                        'quantity' => $item['quantity'],
                        'price' => $item['price'],
                    ]);
                }
    
                $request->session()->put('checkout_step', 5);
                break;
    
            case 5:
                // Finaliza o checkout
                return redirect()->route('checkout.success')->with('success', 'Compra realizada com sucesso!');
                break;
        }
    
        return response()->json(['success' => true]);
    }
}
