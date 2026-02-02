<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Cart;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Models\Cupon;

class CheckoutController extends Controller
{
    // Página inicial do checkout
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

    public function store(Request $request)
    {
        $user = auth()->user();

        // 1. Validações (Bancard adicionado às opções permitidas)
        $request->validate([
            'name'            => 'required|string|max:255',
            'document'        => 'required|string|max:50',
            'email'           => 'required|email',
            'phone'           => 'required|string',
            'shipping'        => 'required|in:1,2,3',
            'payment_method'  => 'required|in:deposito,bancard,whatsapp', // Bancard validado aqui
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'street'          => 'required_if:shipping,2',
            'number'          => 'required_if:shipping,2',
            'city'            => 'nullable|string',
            'state'           => 'nullable|string',
            'country'         => 'required_if:shipping,2',
            'cep'             => 'required_if:shipping,2',
            'store'           => 'required_if:shipping,3',
            'observations'    => 'nullable|string',
            'cupon'           => 'nullable|string',
        ]);

        $cart = Cart::with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) return back()->with('error', 'Carrinho vazio');

        $total         = $cart->sum(fn($item) => $item->quantity * $item->product->price);
        $paymentMethod = $request->input('payment_method');
        $cupon         = null;
        $desconto      = 0;

        // 2. Lógica de Cupom (Mantida apenas para depósito conforme seu código)
        if ($request->filled('cupon') && $paymentMethod === 'deposito') {
            $cupon = Cupon::where('codigo', $request->input('cupon'))
                ->where('data_inicio', '<=', now())
                ->where('data_final', '>=', now())
                ->first();

            if ($cupon) {
                if (($cupon->valor_minimo && $total < $cupon->valor_minimo) ||
                    ($cupon->valor_maximo && $total > $cupon->valor_maximo)
                ) {
                    return back()->with('error', 'Este cupom não se aplica ao valor do seu pedido.');
                }

                $desconto = $cupon->tipo === 'percentual'
                    ? $total * ($cupon->montante / 100)
                    : $cupon->montante;

                $total -= $desconto;
                $total = max(0, $total);

                session(['applied_cupon' => $cupon]);
            } else {
                return back()->with('error', 'Cupom inválido ou expirado.');
            }
        }

        DB::beginTransaction();
        try {
            // Separação Nome / Sobrenome
            $nameParts = explode(' ', trim($request->input('name')));
            $firstName = array_shift($nameParts);
            $lastName  = implode(' ', $nameParts) ?: $firstName;

            // Observações baseadas no frete
            $observations = match ($request->shipping) {
                '1', '2' => $request->input('observations') ?? '',
                '3'      => $request->input('observations_store') ?? '',
                default  => ''
            };

            // 3. Criar pedido (Status 'pending' inicial)
            $order = Order::create([
                'user_id'       => $user->id,
                'status'        => 'pending',
                'total'         => $total,
                'payment_method' => $paymentMethod,
                'cupon_id'      => $cupon->id ?? null,
                'discount'      => $desconto,
                'name'          => $firstName,
                'surname'       => $lastName,
                'document'      => $request->input('document'),
                'email'         => $request->input('email'),
                'phone'         => $request->input('phone'),
                'observations'  => $observations,
                'shipping'      => $request->input('shipping'),
            ]);

            // 4. Lógica de Endereço/Loja (Mantida intacta)
            switch ($request->shipping) {
                case '1':
                    $order->update([
                        'street'  => $user->street,
                        'number'  => $user->number,
                        'city'    => $user->city,
                        'state'   => $user->state,
                        'cep'     => $user->cep,
                        'country' => $user->country == 'paraguai' ? 'PY' : 'BR',
                    ]);
                    break;
                case '2':
                    $order->update([
                        'street'  => $request->input('street'),
                        'number'  => $request->input('number'),
                        'city'    => $request->input('city') ?? '',
                        'state'   => $request->input('state') ?? '',
                        'cep'     => $request->input('cep') ?? '',
                        'country' => $request->input('country') == 'paraguai' ? 'PY' : 'BR',
                    ]);
                    break;
                case '3':
                    $order->update(['store' => $request->input('store')]);
                    break;
            }

            // 5. Salvar itens do pedido
            foreach ($cart as $cartItem) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $cartItem->product_id,
                    'quantity'      => $cartItem->quantity,
                    'price'         => $cartItem->product->price,
                    'name'          => $cartItem->product->name ?? $cartItem->product->external_name ?? 'Produto',
                    'external_name' => $cartItem->product->external_name,
                    'slug'          => $cartItem->product->slug,
                    'sku'           => $cartItem->product->sku,
                ]);
            }

            // Upload de comprovante se for depósito imediato
            if ($paymentMethod === 'deposito' && $request->hasFile('deposit_receipt')) {
                $file = $request->file('deposit_receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('deposits', $filename, 'public');
                $order->update(['deposit_receipt' => $filename]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error("Erro no checkout: " . $e->getMessage());
            return back()->with('error', 'Erro ao processar pedido: ' . $e->getMessage());
        }

        // 6. Finalização do Carrinho
        // No Bancard, só deletamos o carrinho APÓS a confirmação de sucesso no callback.
        if ($paymentMethod !== 'bancard') {
            Cart::where('user_id', $user->id)->delete();
            session()->forget('applied_cupon');
        }

        // 7. Redirecionamento Final
        return match ($paymentMethod) {
            // Redireciona para o controller do Bancard que você já tem pronto
            'bancard'  => redirect()->action([BancardController::class, 'checkoutPage'], ['order' => $order->id]),

            'deposito' => redirect()->route('checkout.deposito', ['order' => $order->id])
                ->with('success', 'Pedido criado com sucesso!'),

            'whatsapp' => $this->whatsapp($request), // Certifique-se que este método existe no controller

            default    => redirect()->route('checkout.success')
                ->with('success', 'Pedido criado com sucesso!'),
        };
    }

    // Decide gateway (Apenas Bancard conforme solicitado)
    protected function redirectGateway(Order $order)
    {
        // Encaminha sempre para o Bancard se o método de pagamento for bancard
        return app(\App\Http\Controllers\BancardController::class)->checkoutPage($order);
    }

    // WhatsApp
    public function whatsapp(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) return back()->with('error', 'Carrinho vazio');

        $total = $cart->sum(fn($item) => $item->quantity * $item->product->price);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'       => $user->id,
                'status'        => 'pending',
                'total'         => $total,
                'payment_method' => 'whatsapp',
                'name'          => $user->name,
                'document'      => $user->document ?? '',
                'email'         => $user->email,
                'phone'         => $user->phone_number,
            ]);

            foreach ($cart as $cartItem) {
                OrderItem::create([
                    'order_id'      => $order->id,
                    'product_id'    => $cartItem->product_id,
                    'quantity'      => $cartItem->quantity,
                    'price'         => $cartItem->product->price,
                    'name'          => $cartItem->product->name,
                    'external_name' => $cartItem->product->external_name,
                    'slug'          => $cartItem->product->slug,
                    'sku'           => $cartItem->product->sku,
                ]);
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar pedido: ' . $e->getMessage());
        }

        $message = "Olá! Quero finalizar minha compra:\n\n";
        foreach ($cart as $cartItem) {
            $p = $cartItem->product;
            $message .= "Produto: " . ($p->external_name ?? $p->name ?? 'Produto não encontrado') . "\n";
            $message .= "SKU: " . ($p->sku ?? 'N/A') . "\n";
            $message .= "Link: " . route('produto.show', $p->id) . "\n";
            $message .= "Preço: " . currency($cartItem->product->price) . "\n";
            $message .= "Qtd: {$cartItem->quantity}\n------------------------\n";
        }
        $message .= "Total: " . currency($total) . "\nCliente: {$user->name}\nTelefone: +{$user->phone_country}{$user->phone_number}\n";

        Cart::where('user_id', $user->id)->delete();

        return redirect("https://wa.me/595984167575?text=" . urlencode($message));
    }

    // Página de depósito
    public function deposito(Order $order)
    {
        $user = auth()->user();
        if ($order->user_id !== $user->id) abort(403, 'Acesso negado ao pedido.');

        $bankAccounts = PaymentMethod::where('type', 'bank')->where('active', 1)->get();
        $orderItems   = $order->items;

        return view('layout.deposito', compact('order', 'bankAccounts', 'orderItems'));
    }

    // Upload do comprovante
    public function submitDeposito(Request $request, Order $order)
    {
        $request->validate([
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('deposit_receipt')) {
            $filePath = $request->file('deposit_receipt')->store('deposit_receipts', 'public');
            $order->deposit_receipt = $filePath;
            $order->save();
        }

        return redirect()->route('user.orders.show', $order->id)
            ->with('success', 'Comprovante enviado com sucesso!');
    }
}
