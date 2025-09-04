<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Product;
use App\Models\Cart;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\BancardController;

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
                $item->product->formatted_previous_price = $item->product->previous_price ? currency_format($item->product->previous_price) : null;
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
            'country' => 'required_if:shipping,2',
            'cep' => 'required_if:shipping,2',
            'house_code' => 'required_if:country,paraguai',
        ]);

        $cart = Cart::with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) {
            return back()->with('error', 'Carrinho vazio');
        }

        $paymentMethod = $request->input('payment_method');
        $total = $cart->sum(fn($item) => $item->quantity * $item->product->price);

        DB::beginTransaction();
        try {
            // Cria pedido
            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'payment_method' => $paymentMethod,
                'name' => $request->input('name'),
                'document' => $request->input('document'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
            ]);

            // Salva endereço ou loja
            if ($request->shipping == '1') {
                $order->update([
                    'street' => $user->street,
                    'number' => $user->number,
                    'city' => $user->city,
                    'state' => $user->state,
                    'country' => 'brasil',
                ]);
            } elseif ($request->shipping == '2') {
                $order->update([
                    'street' => $request->input('street'),
                    'number' => $request->input('number'),
                    'city' => $request->input('city') ?? '',
                    'state' => $request->input('state') ?? '',
                    'country' => $request->input('country'),
                ]);
            } else { // Retirada na loja
                $order->store_id = $request->input('store');
                $order->save();
            }

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

            // Salva comprovante de depósito
            if ($paymentMethod === 'deposito' && $request->hasFile('deposit_receipt')) {
                $file = $request->file('deposit_receipt');
                $filename = time().'_'.$file->getClientOriginalName();
                $file->storeAs('deposits', $filename, 'public');
                $order->deposit_receipt = $filename;
                $order->save();
            }

            // Limpa carrinho
            Cart::where('user_id', $user->id)->delete();

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao processar pedido: '.$e->getMessage());
        }

        // Redireciona conforme método de pagamento
        if ($paymentMethod === 'bancard') {
            $bancard = new BancardController();
            return $bancard->checkout(new Request(['order_id' => $order->id]));
        }

        if ($paymentMethod === 'deposito') {
            return redirect()->route('checkout.deposito', ['order' => $order->id]);
        }

        if ($paymentMethod === 'whatsapp') {
            return $this->whatsapp($order);
        }

        return redirect()->route('checkout.success')->with('success', 'Pedido criado com sucesso!');
    }

    // WhatsApp direto do carrinho
    public function whatsapp(Order $order)
    {
        $user = auth()->user();
        $cartItems = $order->items()->with('product')->get();

        $total = $cartItems->sum(fn($item) => $item->quantity * $item->product->price);

        $message = "Olá! Quero finalizar minha compra:\n\n";
        foreach ($cartItems as $cartItem) {
            $product = $cartItem->product;
            $productName = $product->external_name ?? 'Produto não encontrado';
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

        $message = urlencode($message);
        $whatsappNumber = '595984167575';

        return redirect("https://wa.me/{$whatsappNumber}?text={$message}");
    }
}
