<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\PaymentMethod;
use App\Models\Order;
use App\Models\Cart;
use App\Models\OrderItem;
use Illuminate\Support\Facades\DB;
use App\Services\CuponService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Mail;
use App\Mail\OrderStatusMail;
use App\Models\Policy;
use App\Models\Product;
use App\Services\BusinessEventService;

class CheckoutController extends Controller
{
    public function __construct(private CuponService $cupons, private BusinessEventService $events)
    {
    }

    // Página inicial do checkout
    public function index(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::available()->with('product')->where('user_id', $user->id)->get();
        $paymentMethods = PaymentMethod::where('active', 1)->get();
        $policies = Policy::where('is_active', true)->orderBy('id')->get();

        $cart->transform(function ($item) {
            if ($item->product) {
                $item->product->formatted_price = currency_format($item->product->price);
                $item->product->formatted_previous_price = $item->product->previous_price ? currency_format($item->product->previous_price) : null;
            }
            return $item;
        });

        // O cupom da sessão é revalidado contra o carrinho atual a cada carregamento.
        $resumo = $this->cupons->resumoDoCarrinho($user, $cart->filter(fn ($i) => $i->product)->values());

        return view('checkout.index', compact('paymentMethods', 'cart', 'resumo', 'policies'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'document' => 'required|string|max:50',
            'email' => 'required|email',
            'phone' => 'required|string',
            'shipping' => 'required|in:1,2,3',
            'payment_method' => 'required|in:deposito,bancard_v2,whatsapp',
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'street' => 'required_if:shipping,2',
            'number' => 'required_if:shipping,2',
            'district' => 'required_if:shipping,2',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'required_if:shipping,2',
            'cep' => 'required_if:shipping,2',
            'frete_valor' => 'nullable|numeric', // Validamos o campo que enviamos via JS
            'store' => 'required_if:shipping,3',
            'observations' => 'nullable|string',
            'accept_terms' => 'accepted',
        ]);

        $cart = Cart::available()->with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) {
            return redirect()->route('checkout.index')->with('error', 'Carrinho vazio');
        }

        $frete = (float) $request->input('frete_valor', 0);
        $paymentMethod = $request->input('payment_method');

        // O cupom vem da sessão e é recalculado aqui no servidor: o valor enviado pelo
        // navegador nunca é usado para definir o desconto.
        $itensValidos = $cart->filter(fn ($item) => $item->product)->values();
        $resumo = $this->cupons->resumoDoCarrinho($user, $itensValidos);

        $subtotal = $resumo['subtotal'];
        $desconto = $resumo['desconto'];
        $cupon    = $resumo['cupon'];

        if ($resumo['aviso']) {
            return back()->with('error', $resumo['aviso'])->withInput();
        }

        $total = max(0, ($subtotal - $desconto) + $frete);

        DB::beginTransaction();
        try {
            $cartProductIds = $cart->pluck('product_id')->unique()->values();
            $sellableProductIds = Product::sellable()
                ->whereIn('id', $cartProductIds)
                ->lockForUpdate()
                ->pluck('id');

            if ($sellableProductIds->count() !== $cartProductIds->count()) {
                DB::rollBack();

                return redirect()->route('cart.view')->with(
                    'error',
                    'Um ou mais produtos foram enviados para outlet ou deixaram de estar disponíveis. Revise o carrinho antes de continuar.'
                );
            }

            $nameParts = explode(' ', trim($request->input('name')));
            $firstName = array_shift($nameParts);
            $lastName = implode(' ', $nameParts) ?: $firstName;

            $observations = match ($request->shipping) {
                '1', '2' => $request->input('observations') ?? '',
                '3' => 'Retirada na Loja ID: ' . $request->input('store'),
                default => '',
            };

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'shipping_cost' => $frete,
                'payment_method' => $paymentMethod,
                'cupon_id' => $cupon->id ?? null,
                'discount' => $desconto,
                'name' => $firstName,
                'surname' => $lastName,
                'document' => $request->input('document'),
                'email' => $request->input('email'),
                'phone' => $request->input('phone'),
                'observations' => $observations,
                'shipping' => $request->input('shipping'),
                'order_number' => strtoupper(Str::random(10)),
                'currency_sign' => session('currency_sign', 'US$'),
                'currency_value' => (float) session('currency_value', 1),
                'locale' => app()->getLocale(),
                'terms_accepted_at' => now(),
                'terms_version' => hash('sha256', Policy::where('is_active', true)->orderBy('id')->get(['id', 'updated_at'])->toJson()),
            ]);

            switch ($request->shipping) {
                case '1':
                    // 1. Atualiza os dados do pedido com o endereço do usuário
                    $order->update([
                        'street' => $user->street, 
                        'number' => $user->number, 
                        'district' => $user->district,
                        'complement' => $user->complement, 
                        'city' => $user->city, 
                        'state' => $user->state,
                        'cep' => $user->cep, 
                        'country' => $user->country == 'paraguai' ? 'PY' : 'BR',
                    ]);

                    if ($user->country === 'paraguai' && !empty($user->city)) {
                        $valorFrete = $this->calcularFrete($user->city);
                        
                        $order->update(['shipping_cost' => $valorFrete]);
                        
                        $novoTotal = $order->total + $valorFrete;
                        $order->update(['total' => $novoTotal]);
                    }
                    break;
                case '2':
                    $order->update([
                        'street' => $request->input('street'), 'number' => $request->input('number'),
                        'district' => $request->input('district'), 'complement' => $request->input('complement'),
                        'city' => $request->input('city') ?? '', 'state' => $request->input('state') ?? '',
                        'cep' => $request->input('cep') ?? '', 'country' => $request->input('country') == 'paraguai' ? 'PY' : 'BR',
                    ]);
                    break;
                case '3':
                    $order->update(['store' => $request->input('store')]);
                    break;
            }

            // 5. Salvar itens do pedido
            foreach ($cart as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'name' => $cartItem->product->name ?? ($cartItem->product->external_name ?? 'Produto'),
                    'external_name' => $cartItem->product->external_name,
                    'slug' => $cartItem->product->slug,
                    'sku' => $cartItem->product->sku,
                ]);
            }

            // 6. Consumo do cupom: o contador de usos é incrementado de forma atômica.
            // Se o último uso foi tomado por outro cliente nesse meio-tempo, o pedido
            // inteiro é revertido em vez de fechar com um desconto indevido.
            if ($cupon && !$this->cupons->registrarUso($cupon, $user, $order, $desconto)) {
                DB::rollBack();

                return back()->with('error', __('messages.cupon_esgotado'))->withInput();
            }

            // 7. Comprovante de Depósito
            if ($paymentMethod === 'deposito' && $request->hasFile('deposit_receipt')) {
                $file = $request->file('deposit_receipt');
                $filename = time() . '_' . $file->getClientOriginalName();
                $file->storeAs('deposits', $filename, 'public');
                $order->update(['deposit_receipt' => $filename]);
            }
            DB::commit();
            /**
             * DISPARO DE E-MAILS DE ACORDO COM O MÉTODO
             */
            if ($paymentMethod === 'deposito') {
                $msg = $request->hasFile('deposit_receipt')
                    ? $this->checkoutEmailMessage($order, 'deposito_recebido')
                    : $this->checkoutEmailMessage($order, 'deposito_reservado');

                $this->enviarEmailPedido($order, $msg);
            } elseif ($paymentMethod === 'bancard_v2') {
                $this->enviarEmailPedido($order, $this->checkoutEmailMessage($order, 'gateway_aguardando'));
            }

            // 8. Limpeza do Carrinho (Para todos os métodos, incluindo gateways)
            Cart::where('user_id', $user->id)->delete();
            $this->cupons->remover();

            // 8. Redirecionamentos Finais
            if ($paymentMethod === 'bancard_v2') {
                return redirect()->route('checkout.bancard.v2', ['order' => $order->id]);
            }

            if ($paymentMethod === 'deposito') {
                return redirect()
                    ->route('checkout.deposito', ['order' => $order->id])
                    ->with('success', 'Pedido criado com sucesso!');
            }

            if ($paymentMethod === 'whatsapp') {
                return $this->whatsapp($request);
            }

            return redirect()->route('checkout.success')->with('success', 'Pedido concluído!');
        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Erro crítico no checkout: ' . $e->getMessage());
            $this->events->record('checkout', 'Cliente não conseguiu concluir o pedido', 'O checkout encontrou um erro antes de criar o pedido. A equipe pode oferecer ajuda.', 'error', $user?->id);
            return back()->with('error', 'Erro ao processar pedido.')->withInput();
        }
    }

    private function calcularFrete($cidade)
    {
        $cidades10Dolares = [
    'asuncion', 'san lorenzo', 'luque', 
    'fernando de la mora', 'ciudad del este', 'presidente franco', 
    'hernandarias',   'pedro juan caballero'
    ];

        $cidadeNormalizada = strtolower(
            preg_replace(
                ['/(á|à|ã|â|ä)/', '/(é|è|ê|ë)/', '/(í|ì|î|ï)/', '/(ó|ò|õ|ô|ö)/', '/(ú|ù|û|ü)/', '/ñ/'],
                ['a', 'e', 'i', 'o', 'u', 'n'],
                trim($cidade)
            )
        );

        return in_array($cidadeNormalizada, $cidades10Dolares) ? 10.00 : 15.00;
    }
    
    public function ajaxCalcularFrete(Request $request)
    {
        if (!$request->has(['city', 'country'])) {
            return response()->json(['error' => 'Dados insuficientes'], 400);
        }

        $cidade = $request->input('city');
        $pais = $request->input('country');

        // Subtotal e desconto em valor base (USD); o cupom já entra no total do frete.
        $resumo = $this->cupons->resumoDoCarrinho(auth()->user());
        $totalComDesconto = $resumo['total'];

        if ($pais !== 'paraguai' || empty(trim($cidade))) {
            return response()->json([
                'frete'              => 0,
                'frete_formatado'    => currency_format(0),
                'desconto'           => $resumo['desconto'],
                'desconto_formatado' => currency_format($resumo['desconto']),
                'total_formatado'    => currency_format($totalComDesconto),
            ]);
        }

        $frete = $this->calcularFrete($cidade);

        return response()->json([
            'frete'              => $frete,
            'frete_formatado'    => currency_format($frete),
            'desconto'           => $resumo['desconto'],
            'desconto_formatado' => currency_format($resumo['desconto']),
            'total_formatado'    => currency_format($totalComDesconto + $frete),
        ]);
    }

    public function whatsapp(Request $request)
    {
        $user = auth()->user();
        $cart = Cart::available()->with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) {
            return back()->with('error', 'Carrinho vazio');
        }

        $itensValidos = $cart->filter(fn ($item) => $item->product)->values();
        $resumo = $this->cupons->resumoDoCarrinho($user, $itensValidos);

        $subtotal = $resumo['subtotal'];
        $desconto = $resumo['desconto'];
        $cupon    = $resumo['cupon'];
        $total    = $resumo['total'];

        DB::beginTransaction();
        try {
            $cartProductIds = $cart->pluck('product_id')->unique()->values();
            $sellableProductIds = Product::sellable()
                ->whereIn('id', $cartProductIds)
                ->lockForUpdate()
                ->pluck('id');

            if ($sellableProductIds->count() !== $cartProductIds->count()) {
                DB::rollBack();

                return redirect()->route('cart.view')->with(
                    'error',
                    'Um ou mais produtos foram enviados para outlet ou deixaram de estar disponíveis. Revise o carrinho antes de continuar.'
                );
            }

            $order = Order::create([
                'user_id' => $user->id,
                'status' => 'pending',
                'total' => $total,
                'payment_method' => 'whatsapp',
                'cupon_id' => $cupon->id ?? null,
                'discount' => $desconto,
                'name' => $user->name,
                'document' => $user->document ?? '',
                'email' => $user->email,
                'phone' => $user->phone_number,
            ]);

            foreach ($cart as $cartItem) {
                OrderItem::create([
                    'order_id' => $order->id,
                    'product_id' => $cartItem->product_id,
                    'quantity' => $cartItem->quantity,
                    'price' => $cartItem->product->price,
                    'name' => $cartItem->product->name,
                ]);
            }

            if ($cupon && !$this->cupons->registrarUso($cupon, $user, $order, $desconto)) {
                DB::rollBack();

                return back()->with('error', __('messages.cupon_esgotado'));
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar pedido: ' . $e->getMessage());
        }

        $message = "Olá! Quero finalizar minha compra:\n\n";
        foreach ($cart as $cartItem) {
            $p = $cartItem->product;
            $message .= 'Produto: ' . ($p->external_name ?? ($p->name ?? 'Produto não encontrado')) . "\n";
            $message .= 'SKU: ' . ($p->sku ?? 'N/A') . "\n";
            $message .= 'Link: ' . route('produto.show', $p->id) . "\n";
            $message .= 'Preço: ' . currency($cartItem->product->price) . "\n";
            $message .= "Qtd: {$cartItem->quantity}\n------------------------\n";
        }

        $message .= 'Subtotal: ' . currency($subtotal) . "\n";

        if ($cupon) {
            $message .= 'Cupom: ' . $cupon->codigo . ' (-' . currency($desconto) . ")\n";
        }

        $message .= 'Total: ' . currency($total) . "\nCliente: {$user->name}\nTelefone: +{$user->phone_country}{$user->phone_number}\n";

        Cart::where('user_id', $user->id)->delete();
        $this->cupons->remover();

        return redirect('https://wa.me/595984167575?text=' . urlencode($message));
    }

    public function deposito(Order $order)
    {
        $user = auth()->user();
        if ($order->user_id !== $user->id) {
            abort(403, 'Acesso negado ao pedido.');
        }

        $bankAccounts = PaymentMethod::where('type', 'bank')->where('active', 1)->get();
        $order->load('cupon');
        $orderItems = $order->items()->with('product:id,photo,external_name')->get();

        return view('layout.deposito', compact('order', 'bankAccounts', 'orderItems'));
    }

    /**
     * UPLOAD DO COMPROVANTE (Quando o cara envia depois, pela página de detalhes)
     */
    public function submitDeposito(Request $request, Order $order)
    {
        if ($order->user_id !== auth()->id()) {
            abort(403, 'Acesso negado ao pedido.');
        }

        $request->validate([
            'deposit_receipt' => 'required|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('deposit_receipt')) {
            $filePath = $request->file('deposit_receipt')->store('deposits', 'public');
            $order->deposit_receipt = $filePath;
            $order->save();

            $this->enviarEmailPedido($order, $this->checkoutEmailMessage($order, 'comprovante_enviado'));

            return redirect()->route('user.orders.show', $order->id)
                ->with('success', __('messages.deposito_comprovante_recebido'));
        }

        return back()->with('info', __('messages.deposito_em_verificacao'));
    }

    /**
     * Envia o e-mail do pedido depois que a resposta já foi entregue ao navegador.
     *
     * O envio é por SMTP externo e a fila está em modo síncrono: mandar o e-mail no meio
     * do request deixava o cliente esperando o handshake do servidor de e-mail (vários
     * segundos) só para ver a página carregar. Uma falha no envio também não pode
     * derrubar um pedido que já foi salvo — por isso o try/catch.
     */
    private function enviarEmailPedido(Order $order, string $mensagem): void
    {
        dispatch(function () use ($order, $mensagem) {
            try {
                Mail::to($order->email)->send(new OrderStatusMail($order, $mensagem));
            } catch (\Throwable $e) {
                Log::error('Falha ao enviar e-mail do pedido', [
                    'order_id' => $order->id,
                    'error' => $e->getMessage(),
                ]);
            }
        })->afterResponse();
    }

    private function emailLocaleByOrder(Order $order): string
    {
        // Idioma escolhido pelo cliente na compra. Pedidos antigos não têm
        // essa coluna, então caímos no mapeamento pela moeda.
        if (in_array($order->locale, \App\Http\Middleware\SetLocale::LOCALES, true)) {
            return $order->locale;
        }

        $sign = strtoupper(trim((string) ($order->currency_sign ?? '')));

        if ($sign === 'R$') {
            return 'pt_BR';
        }

        if ($sign === 'G$') {
            return 'es';
        }

        return 'en';
    }

    private function checkoutEmailMessage(Order $order, string $messageType): string
    {
        $locale = $this->emailLocaleByOrder($order);

        return match ($locale) {
            'es' => match ($messageType) {
                'deposito_recebido' => 'Recibimos tu comprobante de deposito. Nuestro equipo financiero lo validara pronto para liberar tu pedido.',
                'deposito_reservado' => 'Tu pedido fue reservado. Para finalizar, realiza el deposito en las cuentas indicadas y envia el comprobante desde tu panel.',
                'gateway_aguardando' => 'Tu pedido fue generado con exito. Estamos esperando la confirmacion de pago del sistema para continuar con el envio.',
                'comprovante_enviado' => 'Tu comprobante fue enviado con exito. Nuestro equipo ya fue notificado y estamos revisando el pago para liberar tu pedido lo antes posible.',
                default => 'Actualizamos tu pedido. Puedes revisar los detalles desde tu panel.',
            },
            'en' => match ($messageType) {
                'deposito_recebido' => 'We received your deposit receipt. Our finance team will validate it shortly to release your order.',
                'deposito_reservado' => 'Your order has been reserved. To complete it, make the deposit to the indicated accounts and upload the receipt from your panel.',
                'gateway_aguardando' => 'Your order was created successfully. We are waiting for payment confirmation from the gateway to proceed with shipping.',
                'comprovante_enviado' => 'Your receipt was sent successfully. Our team has already been notified and we are reviewing the payment to release your order as soon as possible.',
                default => 'Your order was updated. You can check the details from your panel.',
            },
            default => match ($messageType) {
                'deposito_recebido' => 'Recebemos o seu comprovante de deposito. Nossa equipe financeira ira valida-lo em breve para liberar seu pedido.',
                'deposito_reservado' => 'Seu pedido foi reservado. Para concluir, realize o deposito nas contas indicadas e envie o comprovante pelo nosso painel.',
                'gateway_aguardando' => 'Seu pedido foi gerado com sucesso. Estamos aguardando a confirmacao de pagamento do sistema para dar continuidade ao envio.',
                'comprovante_enviado' => 'Seu comprovante foi enviado com sucesso. Nossa equipe ja foi notificada e estamos analisando o pagamento para liberar seu pedido o mais rapido possivel.',
                default => 'Seu pedido foi atualizado. Voce pode revisar os detalhes no seu painel.',
            },
        };
    }
}
