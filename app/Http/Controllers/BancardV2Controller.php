<?php

namespace App\Http\Controllers;

use App\Models\Cart;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Services\BancardV2Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\View\View;

class BancardV2Controller extends Controller
{
    private const SUCCESS_STATUSES = ['success', 'approved', 'paid', 'payment_success'];
    private const FAILURE_STATUSES = ['cancelled', 'failed', 'rejected', 'payment_fail', 'payment_failed'];
    private const CURRENCY = 'PYG';
    private BancardV2Service $bancard;

    public function __construct()
    {
        $gateway = PaymentMethod::query()
            ->where('type', 'gateway')
            ->whereRaw('LOWER(name) = ?', ['bancard v2'])
            ->first();
        $this->bancard = BancardV2Service::fromPaymentMethod($gateway);
    }

    public function checkoutPage(Order $order): View|RedirectResponse
    {
        $user = auth()->user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Acesso negado ao pedido.');
        }

        if (!$this->bancard->isConfigured()) {
            return redirect()->route('checkout.index')->with('error', 'Credenciais Bancard V2 não configuradas.');
        }

        try {
            $amount = $this->convertOrderTotalToPyg($order);
            $currency = self::CURRENCY;
            $shopProcessId = $this->bancard->generateShopProcessId($order);
            $token = $this->bancard->buildSingleBuyToken($shopProcessId, $amount, $currency);

            $order->shop_process_id = $shopProcessId;
            $order->save();

            $payload = $this->bancard->buildSingleBuyPayload(
                $order,
                $shopProcessId,
                $amount,
                $currency,
                $token,
                route('bancard.v2.return', ['shop_process_id' => $shopProcessId]),
                route('checkout.bancard.v2.cancel', ['order' => $order->id]),
                $this->bancard->buildOrderDescription($order)
            );

            $singleBuy = $this->bancard->postSingleBuy($payload);
            $data = $singleBuy['data'];

            if ($singleBuy['ok'] && isset($data['process_id'])) {
                Log::info('Bancard V2 checkout created', [
                    'order_id' => $order->id,
                    'shop_process_id' => $shopProcessId,
                    'process_id' => $data['process_id'],
                ]);

                $pygSymbol = $this->resolvePygSymbol();

                return view('payment.bancard-v2', [
                    'order' => $order,
                    'processId' => $data['process_id'],
                    'checkoutJsUrl' => $this->bancard->getCheckoutJsUrl(),
                    'totalInPyg' => $amount,
                    'pygSymbol' => $pygSymbol,
                ]);
            }

            Log::error('Bancard V2 API error', [
                'order_id' => $order->id,
                'status' => $singleBuy['status'],
                'response' => $data,
            ]);

            return redirect()->route('checkout.index')->with('error', $this->bancard->extractApiErrorMessage($data));
        } catch (\Throwable $e) {
            Log::error('Bancard V2 error', [
                'order_id' => $order->id,
                'message' => $e->getMessage(),
            ]);

            return redirect()->route('checkout.index')->with('error', 'Erro ao iniciar pagamento Bancard V2.');
        }
    }

    /**
     * Converte o valor total do pedido para PYG usando a cotação cadastrada.
     */
    private function convertOrderTotalToPyg(Order $order): string
    {
        $valorBase = $order->total;
        $valorMoeda = $order->currency_value ?? 1;
        $moedaBase = $order->currency_sign ?? 'USD';

        // Buscar taxa do PYG
        $pyg = \App\Models\Currency::where('sign', 'GS$')->orWhere('name', 'PYG')->first();
        $taxaPyg = $pyg->value ?? 1;

        // Converter para PYG
        $valorEmPyg = $valorBase * ($taxaPyg / $valorMoeda);
        return number_format($valorEmPyg, 2, '.', '');
    }

    public function cancelCheckout(Order $order): RedirectResponse
    {
        $user = auth()->user();
        if (!$user || $order->user_id !== $user->id) {
            abort(403, 'Acesso negado ao pedido.');
        }

        return redirect()->route('user.orders.show', $order->id)
            ->with('warning', 'Pagamento não efetuado.');
    }

    public function callback(Request $request): JsonResponse
    {
        $payload = $request->all();
        $shopProcessId = $this->bancard->extractShopProcessId($payload);

        Log::info('Bancard V2 callback received', [
            'shop_process_id' => $shopProcessId,
            'status' => data_get($payload, 'status') ?? data_get($payload, 'operation.status'),
            'response_code' => data_get($payload, 'operation.response_code') ?? data_get($payload, 'response_code'),
        ]);

        if (!$shopProcessId) {
            Log::warning('Bancard V2 callback sem shop_process_id', $payload);
            return response()->json([
                'status' => 'error',
                'description' => 'shop_process_id ausente',
            ]);
        }

        if (!$this->bancard->hasValidCallbackToken($payload)) {
            Log::warning('Bancard V2 callback token inválido', [
                'shop_process_id' => $shopProcessId,
                'received_token' => data_get($payload, 'operation.token'),
            ]);

            return response()->json([
                'status' => 'error',
                'description' => 'token inválido',
            ]);
        }

        $order = Order::where('shop_process_id', $shopProcessId)->first();
        if (!$order) {
            Log::warning('Bancard V2 callback com pedido não encontrado', [
                'shop_process_id' => $shopProcessId,
            ]);

            return response()->json([
                'status' => 'error',
                'description' => 'pedido não encontrado',
            ]);
        }

        $this->syncOrderStatus($order, $payload);

        return response()->json(['status' => 'success']);
    }

    public function returnPage(Request $request): RedirectResponse
    {
        $shopProcessId = $request->query('shop_process_id');
        $status = strtolower((string) $request->query('status', ''));


        Log::info('Bancard V2 return', [
            'shop_process_id' => $shopProcessId,
            'status' => $status,
        ]);

        $order = $shopProcessId
            ? Order::where('shop_process_id', $shopProcessId)->first()
            : null;

        if (!$order) {
            return redirect()->route('home')
                ->with('info', 'Retorno Bancard V2 recebido. Aguardando confirmação do pagamento.');
        }

        // Nunca aprovar apenas pelo status da return_url.
        // Se já estiver pago, assumimos confirmação prévia por callback/rotina segura.
        if ($order->status === 'paid') {
            session()->forget('applied_cupon');
            return $this->redirectAfterSuccess($order);
        }

        $confirmation = $this->bancard->fetchSingleBuyConfirmation((string) $order->shop_process_id);
        if ($this->bancard->isApprovedConfirmation($confirmation)) {
            Log::info('Bancard V2 return reconciled by confirmation', [
                'order_id' => $order->id,
                'shop_process_id' => $order->shop_process_id,
                'response_code' => data_get($confirmation, 'response_code'),
                'response' => data_get($confirmation, 'response'),
            ]);

            $this->markOrderAsPaid($order);
            session()->forget('applied_cupon');
            $this->storeSuccessDisplayPayload((string) $order->shop_process_id, $confirmation, $order);
            return $this->redirectAfterSuccess($order);
        }

        if (in_array($status, self::FAILURE_STATUSES, true)) {
            if ($order->status !== 'paid') {
                $order->status = 'failed';
                $order->save();
            }

            $this->storeErrorDisplayPayload((string) $order->shop_process_id, $confirmation, $order, $status);
            return $this->redirectAfterFailure($order);
        }

        if (auth()->check() && (int) auth()->id() === (int) $order->user_id) {
            return redirect()->route('user.orders.show', $order->id)
                ->with('info', 'Pagamento Bancard V2 em análise/aguardando confirmação.');
        }

        return redirect()->route('home')
            ->with('info', 'Pagamento Bancard V2 em análise/aguardando confirmação.');
    }

    public function errorPage(Request $request): View|RedirectResponse
    {
        $shopProcessId = (string) $request->query('shop_process_id', '');
        $status = strtolower((string) $request->query('status', ''));

        if ($shopProcessId === '') {
            return redirect()->route('home')->with('info', 'Dados da transação não encontrados.');
        }

        $order = Order::where('shop_process_id', $shopProcessId)->first();
        $displayPayload = session()->pull('bancard_v2_error_payload');
        $pygSymbol = $this->resolvePygSymbol();

        if (is_array($displayPayload) && (string) data_get($displayPayload, 'shopProcessId') === $shopProcessId) {
            return view('payment.bancard-v2-error', $displayPayload + ['pygSymbol' => $pygSymbol]);
        }

        $confirmation = $this->bancard->fetchSingleBuyConfirmation($shopProcessId);
        $displayPayload = $this->bancard->buildErrorDisplayPayload($shopProcessId, $confirmation, $order, $status);

        return view('payment.bancard-v2-error', $displayPayload + ['pygSymbol' => $pygSymbol]);
    }

    public function successPage(Request $request): View|RedirectResponse
    {
        $shopProcessId = (string) $request->query('shop_process_id', '');

        if ($shopProcessId === '') {
            return redirect()->route('home')->with('info', 'Dados da transação não encontrados.');
        }

        $order = Order::where('shop_process_id', $shopProcessId)->first();
        $displayPayload = session()->pull('bancard_v2_success_payload');
        $pygSymbol = $this->resolvePygSymbol();

        if (is_array($displayPayload) && (string) data_get($displayPayload, 'shopProcessId') === $shopProcessId) {
            return view('payment.bancard-v2-success', $displayPayload + ['pygSymbol' => $pygSymbol]);
        }

        $confirmation = $this->bancard->fetchSingleBuyConfirmation($shopProcessId);
        $displayPayload = $this->bancard->buildSuccessDisplayPayload($shopProcessId, $confirmation, $order);

        return view('payment.bancard-v2-success', [
            'transactionDateTime' => $displayPayload['transactionDateTime'],
            'shopProcessId' => $displayPayload['shopProcessId'],
            'amount' => $displayPayload['amount'],
            'responseDescription' => $displayPayload['responseDescription'],
            'pygSymbol' => $pygSymbol,
        ]);
    }

    private function redirectAfterSuccess(Order $order): RedirectResponse
    {
        if (auth()->check() && (int) auth()->id() === (int) $order->user_id) {
            return redirect()->route('bancard.v2.success', [
                'shop_process_id' => (string) $order->shop_process_id,
            ])->with('success', 'Pagamento Bancard V2 confirmado!');
        }

        return redirect()->route('home')->with('success', 'Pagamento Bancard V2 confirmado!');
    }

    private function redirectAfterFailure(Order $order): RedirectResponse
    {
        if (auth()->check() && (int) auth()->id() === (int) $order->user_id) {
            return redirect()->route('bancard.v2.error', [
                'shop_process_id' => (string) $order->shop_process_id,
                'status' => 'payment_failed',
            ])->with('error', 'Pagamento Bancard V2 não aprovado.');
        }

        return redirect()->route('home')->with('error', 'Pagamento Bancard V2 não aprovado.');
    }

    private function storeSuccessDisplayPayload(string $shopProcessId, ?array $confirmation, ?Order $order): void
    {
        session()->flash('bancard_v2_success_payload', $this->bancard->buildSuccessDisplayPayload($shopProcessId, $confirmation, $order));
    }

    private function storeErrorDisplayPayload(string $shopProcessId, ?array $confirmation, ?Order $order, string $status): void
    {
        session()->flash('bancard_v2_error_payload', $this->bancard->buildErrorDisplayPayload($shopProcessId, $confirmation, $order, $status));
    }

    private function syncOrderStatus(Order $order, array $payload): void
    {
        $responseCode = (string) (data_get($payload, 'operation.response_code') ?? data_get($payload, 'response_code') ?? '');
        $status = strtolower((string) (data_get($payload, 'status') ?? data_get($payload, 'operation.status') ?? ''));

        if ($responseCode !== '') {
            if ($responseCode === '00') {
                $this->markOrderAsPaid($order);
                return;
            }

            if ($order->status !== 'paid') {
                $order->status = 'failed';
                $order->save();
            }

            return;
        }

        if (in_array($status, self::SUCCESS_STATUSES, true)) {
            $this->markOrderAsPaid($order);
            return;
        }

        if (in_array($status, self::FAILURE_STATUSES, true)) {
            $order->status = 'failed';
            $order->save();
        }
    }

    private function markOrderAsPaid(Order $order): void
    {
        if ($order->status !== 'paid') {
            $order->status = 'paid';
            $order->save();

            $this->reduceOrderStock($order);
        }

        Cart::where('user_id', $order->user_id)->delete();
    }

    private function reduceOrderStock(Order $order): void
    {
        $order->loadMissing('items.product');

        foreach ($order->items as $item) {
            $product = $item->product;

            if (!$product) {
                continue;
            }

            $quantity = max(0, (int) $item->quantity);
            $availableStock = max(0, (int) $product->stock);
            $decrementBy = min($quantity, $availableStock);

            if ($decrementBy > 0) {
                $product->decrement('stock', $decrementBy);
            }
        }
    }

    private function resolvePygSymbol(): string
    {
        $pygCurrency = \App\Models\Currency::where('name', 'PYG')->first();

        return $pygCurrency?->sign ?? 'G$';
    }


    private function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

}
