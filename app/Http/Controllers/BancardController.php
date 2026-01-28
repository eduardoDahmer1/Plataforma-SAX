<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Cart;
use App\Models\PaymentMethod;

class BancardController extends Controller
{
    private $baseUrl;
    private $apiUrl;
    private $publicKey;
    private $privateKey;

    public function __construct()
    {
        $this->baseUrl = (env('BANCARD_MODE', 'sandbox') === 'sandbox')
            ? 'https://vpos.infonet.com.py:8888'
            : 'https://vpos.infonet.com.py';

        $this->apiUrl = "{$this->baseUrl}/vpos/api/0.3";

        $bancard = PaymentMethod::where('type', 'gateway')
            ->where('name', 'Bancard')
            ->where('active', 1)
            ->first();

        if ($bancard && $bancard->credentials) {
            $creds = json_decode($bancard->credentials, true);
            $this->publicKey  = trim($creds['public_key'] ?? '');
            // Armazenamos a chave bruta para evitar interpretação de caracteres especiais
            $this->privateKey = trim($creds['private_key'] ?? '');
        }
    }

    public function checkoutPage(Order $order)
    {
        try {
            $user = auth()->user();
            if ($order->user_id !== $user->id) abort(403);

            $shopProcessId = (string) $order->id . time();

            /**
             * AJUSTE DE CONVERSÃO:
             * Seu carrinho está em USD (3.65), mas o Bancard em produção no Paraguai 
             * costuma exigir PYG. Vamos converter para garantir que o 'amount' não seja muito baixo.
             * Se sua conta for USD, remova a multiplicação por 7500.
             */
            $isPyg = true; // Altere para false se sua conta Bancard for especificamente USD
            $currency = $isPyg ? 'PYG' : 'USD';
            $factor = $isPyg ? 7500 : 1; // Cotação aproximada do Guaraní
            
            $amount = (int) round($order->total * $factor);

            // GERAÇÃO DO TOKEN BLINDADA
            // Usamos concatenação com aspas simples para garantir que o PHP não processe o '$' da chave
            $token = md5($this->privateKey . $shopProcessId . $amount . $currency);

            $payload = [
                "public_key" => $this->publicKey,
                "operation" => [
                    "token" => $token,
                    "shop_process_id" => $shopProcessId,
                    "amount" => (string) $amount,
                    "currency" => $currency,
                    "description" => "SAX Pedido #{$order->id}",
                    "return_url" => route('bancard.callback'),
                    "cancel_url" => route('checkout.index'),
                ],
                "customer" => [
                    "first_name" => substr($order->name, 0, 30),
                    "last_name" => substr($order->surname ?: $order->name, 0, 30),
                    "email" => $order->email,
                    "phone" => preg_replace('/\D/', '', $order->phone),
                    "address" => "Av San Blas", 
                    "city" => "Ciudad del Este",
                    "country" => "PY"
                ]
            ];

            Log::info("Bancard - Tentativa Final {$currency}", [
                'amount' => $amount,
                'token' => $token
            ]);

            $response = Http::timeout(20)->post("{$this->apiUrl}/single_buy", $payload);
            $data = $response->json();

            if ($response->successful() && isset($data['process_id'])) {
                $order->update(['shop_process_id' => $data['process_id']]);
                return view('layout.bancard', ['process_id' => $data['process_id'], 'order' => $order]);
            }

            Log::error('Bancard - Erro Producao', ['data' => $data]);
            return redirect()->route('checkout.index')->with('error', 'Erro Bancard: ' . ($data['messages'][0]['dsc'] ?? 'Verifique as credenciais'));

        } catch (\Exception $e) {
            Log::error('Bancard - Exception', ['msg' => $e->getMessage()]);
            return redirect()->route('checkout.index')->with('error', 'Erro interno.');
        }
    }

    public function bancardCallback(Request $request)
    {
        $data = $request->all();
        $processId = $data['process_id'] ?? null;
        if (!$processId) return redirect()->route('checkout.index');

        $order = Order::where('shop_process_id', $processId)->first();
        if (!$order) return redirect()->route('home');

        if (($data['status'] ?? '') === 'success' || ($data['response'] ?? '') === 'S') {
            $order->update(['status' => 'paid']);
            Cart::where('user_id', $order->user_id)->delete();
            return redirect()->route('checkout.success');
        }

        return redirect()->route('checkout.index')->with('error', 'Pagamento não concluído.');
    }
}