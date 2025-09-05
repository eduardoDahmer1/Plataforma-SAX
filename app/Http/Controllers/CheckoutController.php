<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;

class CheckoutController extends Controller
{
    // Exibe página de checkout
    public function index(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::with('product')->where('user_id', $user->id)->get();
        $paymentMethods = PaymentMethod::where('active', 1)->get();

        $cart->transform(function ($item) {
            if ($item->product) {
                $item->product->formatted_price = currency_format($item->product->price);
                $item->product->formatted_previous_price = $item->product->previous_price 
                    ? currency_format($item->product->previous_price) 
                    : null;
            }
            return $item;
        });

        return view('checkout.index', compact('paymentMethods', 'cart'));
    }

    // Cria o pedido
    public function store(Request $request)
    {
        $user = auth()->user();
    
        $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:50',
            'email' => 'required|email',
            'phone' => 'required|string',
            'shipping' => 'required|in:1,2,3',
            'payment_method' => 'required|in:deposito,bancard,whatsapp',
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'street' => 'required_if:shipping,2',
            'number' => 'required_if:shipping,2',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'required_if:shipping,2',
            'cep' => 'required_if:shipping,2',
            'store' => 'required_if:shipping,3',
            'observations' => 'nullable|string',
        ]);
    
        $cart = Cart::with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) return back()->with('error', 'Carrinho vazio');
    
        $total = $cart->sum(fn($item) => $item->quantity * $item->product->price);
        $paymentMethod = $request->input('payment_method');
    
        DB::beginTransaction();
        try {
            // Determina observações corretas
            $observations = '';
            switch ($request->shipping) {
                case '1': // endereço cadastrado
                case '2': // endereço alternativo
                    $observations = $request->input('observations') ?? '';
                    break;
                case '3': // retirada na loja
                    $observations = $request->input('observations_store') ?? '';
                    break;
            }
    
            // Cria o pedido
            $orderData = [
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'payment_method' => $paymentMethod,
                'name' => $request->input('name'),
                'document' => $request->input('document'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'observations' => $observations,
                'shipping' => $request->input('shipping'),
            ];
    
            $order = Order::create($orderData);
    
            // Salva endereço ou loja
            switch ($request->shipping) {
                case '1': // Endereço cadastrado do usuário
                    $order->update([
                        'street' => $user->street,
                        'number' => $user->number,
                        'city' => $user->city,
                        'state' => $user->state,
                        'cep' => $user->cep,
                        'country' => $user->country ?? 'brasil',
                    ]);
                    break;
    
                case '2': // Endereço alternativo
                    $order->update([
                        'street' => $request->input('street'),
                        'number' => $request->input('number'),
                        'city' => $request->input('city') ?? '',
                        'state' => $request->input('state') ?? '',
                        'cep' => $request->input('cep') ?? '',
                        'country' => $request->input('country'),
                    ]);
                    break;
    
                case '3': // Retirada na loja
                    $order->update([
                        'store' => $request->input('store')
                    ]);
                    break;
            }
    
            // Adiciona itens do carrinho ao pedido
            foreach ($cart as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'name' => $cartItem->product->name,
                    'external_name' => $cartItem->product->external_name,
                    'slug' => $cartItem->product->slug,
                    'sku' => $cartItem->product->sku,
                ]);
            }
    
            // Salva comprovante de depósito, se houver
            if ($paymentMethod === 'deposito' && $request->hasFile('deposit_receipt')) {
                $file = $request->file('deposit_receipt');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->storeAs('deposits', $filename, 'public');
                $order->deposit_receipt = $filename;
                $order->save();
            }
    
            DB::commit();
    
            // Limpa carrinho
            Cart::where('user_id', $user->id)->delete();
    
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar pedido: '.$e->getMessage());
        }
    
        // Redireciona conforme método de pagamento
        switch ($paymentMethod) {
            case 'bancard':
                return redirect()->route('checkout.bancard', ['order' => $order->id]);
            case 'deposito':
                return redirect()->route('checkout.deposito', ['order' => $order->id]);
            case 'whatsapp':
                return $this->whatsappOld($user, $cart);
        }
    
        return redirect()->route('checkout.success')->with('success', 'Pedido criado com sucesso!');
    }    
      

    // WhatsApp (lógica antiga da mensagem, mas cria pedido)
    public function whatsapp(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::with('product')->where('user_id', $user->id)->get();

        if ($cart->isEmpty()) {
            return back()->with('error', 'Carrinho vazio');
        }

        $total = $cart->sum(fn($item) => $item->quantity * $item->product->price);

        // Cria pedido
        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'payment_method' => 'whatsapp',
                'name' => $user->name,
                'document' => $user->document ?? '',
                'email' => $user->email,
                'phone' => $user->phone_number,
            ]);

            // Adiciona itens do carrinho
            foreach ($cart as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'name' => $cartItem->product->name,
                    'external_name' => $cartItem->product->external_name,
                    'slug' => $cartItem->product->slug,
                    'sku' => $cartItem->product->sku,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar pedido: ' . $e->getMessage());
        }

        // Gera a mensagem WhatsApp
        $message = "Olá! Quero finalizar minha compra:\n\n";
        foreach ($cart as $cartItem) {
            $product = $cartItem->product;
            $productName = $product->external_name ?? $product->name ?? 'Produto não encontrado';
            $productSku = $product->sku ?? 'N/A';
            $productLink = route('produto.show', $product->id);

            $message .= "Produto: {$productName}\n";
            $message .= "SKU: {$productSku}\n";
            $message .= "Link: {$productLink}\n";
            $message .= "Preço: " . currency($cartItem->product->price) . "\n";
            $message .= "Qtd: {$cartItem->quantity}\n";
            $message .= "------------------------\n";
        }

        $message .= "Total da compra: " . currency($total) . "\n\n";
        $message .= "Cliente: {$user->name}\n";
        $message .= "Telefone: +{$user->phone_country}{$user->phone_number}\n";

        // Limpa carrinho
        Cart::where('user_id', $user->id)->delete();

        $message = urlencode($message);
        $whatsappNumber = '595984167575';

        return redirect("https://wa.me/{$whatsappNumber}?text={$message}");
    }

    // Página de depósito
    public function deposito(Order $order)
    {
        $user = auth()->user();
        if ($order->user_id !== $user->id) {
            abort(403, 'Acesso negado ao pedido.');
        }

        $bankAccounts = PaymentMethod::where('type', 'bank')->where('active', 1)->get();
        $orderItems = $order->items;

        return view('layout.deposito', compact('order', 'bankAccounts', 'orderItems'));
    }

    // Submete comprovante de depósito
    public function submitDeposito(Request $request, Order $order)
    {
        $request->validate([
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('deposit_receipt')) {
            $file = $request->file('deposit_receipt');
            $filePath = $file->store('deposit_receipts', 'public');

            $order->deposit_receipt = $filePath;
            $order->save();
        }

        // Redireciona para a página do pedido no painel do usuário
        return redirect()->route('user.orders.show', $order->id)
                        ->with('success', 'Comprovante enviado com sucesso!');
    }

}
