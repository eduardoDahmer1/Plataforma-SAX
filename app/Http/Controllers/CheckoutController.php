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
use App\Models\UserAddress;
use App\Models\Currency;
use App\Services\BusinessEventService;
use App\Services\AdminNotificationService;
use App\Rules\BrazilianCpf;
use App\Services\RendixPixService;
use Illuminate\Validation\ValidationException;
use App\Rules\CustomerDocumentRule;
use App\Support\CustomerDocument;
use App\Support\CountrySupport;
use Illuminate\Validation\Rule;
use App\Services\StoreControlService;

class CheckoutController extends Controller
{
    public function __construct(
        private CuponService $cupons,
        private BusinessEventService $events,
        private AdminNotificationService $adminNotifications,
        private StoreControlService $storeControls
    )
    {
    }

    // Página inicial do checkout
    public function index(Request $request)
    {
        $user = auth()->user();
        $this->ensureLegacyAddress($user);
        $addresses = $user->addresses()->get();
        $cart = Cart::available()->with('product')->where('user_id', $user->id)->get();
        $paymentMethods = PaymentMethod::where('active', 1)->get()
            ->filter(fn (PaymentMethod $method): bool => $this->paymentModelIsManuallyEnabled($method))
            ->values();
        $policies = Policy::where('is_active', true)->orderBy('id')->get();
        $pygCurrency = Currency::where('name', 'PYG')->orWhere('sign', 'GS$')->first();

        $cart->transform(function ($item) {
            if ($item->product) {
                $item->product->formatted_price = currency_format($item->product->price);
                $item->product->formatted_previous_price = $item->product->previous_price ? currency_format($item->product->previous_price) : null;
            }
            return $item;
        });

        // O cupom da sessão é revalidado contra o carrinho atual a cada carregamento.
        $resumo = $this->cupons->resumoDoCarrinho($user, $cart->filter(fn ($i) => $i->product)->values());

        return view('checkout.index', compact('paymentMethods', 'cart', 'resumo', 'policies', 'addresses', 'pygCurrency'));
    }

    public function store(Request $request)
    {
        $user = auth()->user();

        if ($request->input('shipping') === '2') {
            $request->merge([
                'country' => CountrySupport::normalizeForStorage($request->input('country')),
            ]);
        }
        $request->merge([
            'phone_country' => preg_replace('/\D+/', '', (string) $request->input('phone_country', $user->phone_country)),
        ]);

        $documentType = CustomerDocument::inferType(
            $request->input('document_type'),
            $request->input('document'),
            $request->input('phone_country', $user->phone_country)
        );
        $request->merge([
            'document_type' => $documentType,
            'document' => CustomerDocument::format($request->input('document'), $documentType),
        ]);

        $documentRules = ['required', 'string', 'max:30', new CustomerDocumentRule($documentType)];
        if ($request->input('payment_method') === RendixPixService::PROVIDER) {
            $documentRules[] = new BrazilianCpf();
        }

        $request->validate([
            'name' => 'required|string|max:255',
            'document' => $documentRules,
            'document_type' => ['required', 'string', 'in:'.implode(',', CustomerDocument::types())],
            'email' => 'required|email',
            'phone' => 'required|string',
            'phone_country' => ['required', 'string', 'regex:/^\d{1,6}$/'],
            'shipping' => 'required|in:1,2,3',
            'shipping_address_id' => 'required_if:shipping,1|nullable|integer',
            'payment_method' => 'required|in:deposito,bancard_v2,rendix_pix,whatsapp',
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf',
            'street' => 'required_if:shipping,2',
            'number' => 'required_if:shipping,2',
            'district' => [Rule::requiredIf(fn (): bool => $request->input('shipping') === '2' && ! CountrySupport::usesDhl($request->input('country'))), 'nullable', 'string', 'max:160'],
            'city' => 'required_if:shipping,2|nullable|string',
            'state' => 'required_if:shipping,2|nullable|string',
            'country' => ['required_if:shipping,2', 'nullable', function (string $attribute, mixed $value, \Closure $fail): void {
                if ($value !== null && $value !== '' && ! CountrySupport::isSupported($value)) {
                    $fail('Selecione um país válido.');
                }
            }],
            'cep' => [Rule::requiredIf(fn (): bool => $request->input('shipping') === '2' && ! CountrySupport::isParaguay($request->input('country'))), 'nullable', 'string', 'max:30'],
            'address_label' => 'required_if:shipping,2|nullable|string|max:80',
            'make_default_address' => 'nullable|boolean',
            'frete_valor' => 'nullable|numeric', // Validamos o campo que enviamos via JS
            'store' => 'required_if:shipping,3',
            'observations' => 'nullable|string',
            'accept_terms' => 'accepted',
            'accept_pix_terms' => $request->input('payment_method') === RendixPixService::PROVIDER
                ? 'accepted'
                : 'nullable',
        ]);

        $paymentMethod = (string) $request->input('payment_method');
        if (! $this->storeControls->paymentEnabled($paymentMethod)) {
            throw ValidationException::withMessages([
                'payment_method' => __('messages.store_payment_disabled_message'),
            ]);
        }

        if ($request->input('payment_method') === RendixPixService::PROVIDER) {
            if ($documentType !== CustomerDocument::CPF) {
                throw ValidationException::withMessages([
                    'document' => __('messages.checkout_pix_requires_cpf'),
                ]);
            }

            $rendixGateway = RendixPixService::gateway();
            if (!$rendixGateway?->active || !RendixPixService::fromPaymentMethod($rendixGateway)->isConfigured()) {
                throw ValidationException::withMessages([
                    'payment_method' => __('messages.checkout_pix_unavailable'),
                ]);
            }
        }

        $cart = Cart::available()->with('product')->where('user_id', $user->id)->get();
        if ($cart->isEmpty()) {
            return redirect()->route('checkout.index')->with('error', 'Carrinho vazio');
        }

        $selectedAddress = null;
        if ($request->input('shipping') === '1') {
            $selectedAddress = $user->addresses()
                ->whereKey($request->integer('shipping_address_id'))
                ->first();

            if (! $selectedAddress) {
                return back()->withErrors(['shipping_address_id' => 'Selecione um endereço válido para entrega.'])->withInput();
            }
        }

        $destinationCountry = match ((string) $request->input('shipping')) {
            '1' => $selectedAddress?->country,
            '2' => $request->input('country'),
            '3' => CountrySupport::PARAGUAY,
            default => null,
        };

        if (CountrySupport::usesDhl($destinationCountry) && ! $this->storeControls->enabled('geonames')) {
            throw ValidationException::withMessages([
                'country' => 'As entregas internacionais ainda não estão habilitadas. Selecione Brasil ou Paraguai.',
            ]);
        }

        if (CountrySupport::usesDhl($destinationCountry)) {
            throw ValidationException::withMessages([
                'country' => 'Este endereço internacional será atendido pela DHL. A cotação ainda precisa ser habilitada com as credenciais DHL antes de concluir a compra.',
            ]);
        }

        // O frete é sempre decidido no servidor. O valor oculto do navegador não
        // pode transformar uma entrega internacional ou paraguaia em frete grátis.
        $destinationCity = (string) ($selectedAddress?->city ?: $request->input('city'));
        $frete = CountrySupport::isParaguay($destinationCountry) && $request->input('shipping') !== '3'
            ? $this->calcularFrete($destinationCity)
            : 0.0;

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
                'document_type' => $documentType,
                'email' => $request->input('email'),
                'phone_country' => (string) $request->input('phone_country'),
                'phone' => $request->input('phone'),
                'observations' => $observations,
                'shipping' => $request->input('shipping'),
                'order_number' => strtoupper(Str::random(10)),
                'currency_sign' => session('currency_sign', 'US$'),
                'currency_value' => (float) session('currency_value', 1),
                'locale' => app()->getLocale(),
                'terms_accepted_at' => now(),
                'terms_version' => hash('sha256', Policy::where('is_active', true)->orderBy('id')->get(['id', 'updated_at'])->toJson()),
                'rendix_terms_accepted_at' => $paymentMethod === RendixPixService::PROVIDER ? now() : null,
            ]);

            switch ($request->shipping) {
                case '1':
                    $address = $selectedAddress;
                    $order->update([
                        'street' => $address->street,
                        'number' => $address->number,
                        'district' => $address->district,
                        'complement' => $address->complement,
                        'city' => $address->city,
                        'state' => $address->state,
                        'cep' => $address->postal_code,
                        'country' => CountrySupport::iso2($address->country),
                    ]);
                    break;
                case '2':
                    $makeDefault = $request->boolean('make_default_address') || ! $user->addresses()->exists();

                    if ($makeDefault) {
                        $user->addresses()->update(['is_default' => false]);
                    }

                    $address = $user->addresses()->create([
                        'label' => $request->input('address_label', 'Meu endereço'),
                        'country' => CountrySupport::normalizeForStorage($request->input('country')),
                        'postal_code' => $request->input('cep'),
                        'state' => $request->input('state'),
                        'city' => $request->input('city'),
                        'street' => $request->input('street'),
                        'number' => $request->input('number'),
                        'district' => $request->input('district'),
                        'complement' => $request->input('complement'),
                        'is_default' => $makeDefault,
                    ]);

                    if ($makeDefault) {
                        $this->syncLegacyAddress($user, $address);
                    }

                    $order->update([
                        'street' => $address->street, 'number' => $address->number,
                        'district' => $address->district, 'complement' => $address->complement,
                        'city' => $address->city ?? '', 'state' => $address->state ?? '',
                        'cep' => $address->postal_code ?? '', 'country' => CountrySupport::iso2($address->country),
                    ]);
                    break;
                case '3':
                    $order->update(['store' => $request->input('store')]);
                    break;
            }

            // Registra antes do primeiro e-mail o valor que será enviado ao Bancard.
            // Assim, a mensagem de pedido criado informa a moeda e a conversão reais.
            if ($paymentMethod === 'bancard_v2') {
                $pygCurrency = Currency::where('name', 'PYG')->orWhere('sign', 'GS$')->first();
                $pygRate = (float) ($pygCurrency?->value ?: 1);

                $order->update([
                    'payment_currency' => 'PYG',
                    'payment_amount' => round((float) $order->total * $pygRate, 2),
                    'payment_exchange_rate' => $pygRate,
                ]);
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
                $filePath = $request->file('deposit_receipt')->store('deposits', 'public');
                $order->update(['deposit_receipt' => $filePath]);
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
            } elseif (in_array($paymentMethod, ['bancard_v2', RendixPixService::PROVIDER], true)) {
                $this->enviarEmailPedido($order, $this->checkoutEmailMessage($order, 'gateway_aguardando'));
            }

            // 8. Limpeza do Carrinho (Para todos os métodos, incluindo gateways)
            Cart::where('user_id', $user->id)->delete();
            $this->cupons->remover();

            // 8. Redirecionamentos Finais
            if ($paymentMethod === 'bancard_v2') {
                return redirect()->route('checkout.bancard.v2', ['order' => $order->id]);
            }

            if ($paymentMethod === RendixPixService::PROVIDER) {
                return redirect()->route('checkout.rendix.pix', ['order' => $order->id]);
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
            $this->adminNotifications->notifyAdmins(
                'checkout_error',
                'Erro no checkout',
                'Um cliente não conseguiu concluir o pedido. Verifique os eventos do painel.',
                '/admin',
                ['user_id' => $user?->id],
            );
            return back()->with('error', 'Erro ao processar pedido.')->withInput();
        }
    }

    private function paymentModelIsManuallyEnabled(PaymentMethod $method): bool
    {
        $name = mb_strtolower(trim((string) $method->name));

        if (($method->type ?? null) === 'gateway' && $name === 'bancard v2') {
            return $this->storeControls->enabled('bancard');
        }

        if (($method->type ?? null) === 'gateway' && in_array($name, ['rendix pix', 'pix rendix', 'pix'], true)) {
            return $this->storeControls->enabled('pix');
        }

        return true;
    }

    private function ensureLegacyAddress($user): void
    {
        if ($user->addresses()->exists() || ! filled($user->address) || ! filled($user->number) || ! filled($user->district)) {
            return;
        }

        $user->addresses()->create([
            'label' => 'Endereço principal',
            'country' => CountrySupport::normalizeForStorage($user->country) ?: CountrySupport::BRAZIL,
            'postal_code' => $user->cep,
            'state' => $user->state,
            'city' => $user->city,
            'street' => $user->address,
            'number' => $user->number,
            'district' => $user->district,
            'complement' => $user->complement,
            'is_default' => true,
        ]);
    }

    private function syncLegacyAddress($user, UserAddress $address): void
    {
        $user->update([
            'country' => $address->country,
            'cep' => $address->postal_code,
            'state' => $address->state,
            'city' => $address->city,
            'address' => $address->street,
            'number' => $address->number,
            'district' => $address->district,
            'complement' => $address->complement,
        ]);
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
        $pais = CountrySupport::normalizeForStorage($request->input('country'));

        // Subtotal e desconto em valor base (USD); o cupom já entra no total do frete.
        $resumo = $this->cupons->resumoDoCarrinho(auth()->user());
        $totalComDesconto = $resumo['total'];

        if (CountrySupport::usesDhl($pais)) {
            return response()->json([
                'error' => 'dhl_not_configured',
                'message' => 'A cotação internacional será feita pela DHL e ainda não está habilitada.',
            ], 409);
        }

        if (! CountrySupport::isParaguay($pais) || empty(trim($cidade))) {
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
