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
        // Define o ambiente baseado no .env
        $isSandbox = env('BANCARD_MODE', 'sandbox') === 'sandbox';
        $this->baseUrl = $isSandbox
            ? 'https://vpos.infonet.com.py:8888'
            : 'https://vpos.infonet.com.py';

        $this->apiUrl = "{$this->baseUrl}/vpos/api/0.3";

        // Busca credenciais no banco
        $bancard = PaymentMethod::where('type', 'gateway')
            ->where('name', 'Bancard')
            ->where('active', 1)
            ->first();

        if ($bancard && $bancard->credentials) {
            $creds = json_decode($bancard->credentials, true);
            // Uso de trim() para evitar espaços invisíveis
            $this->publicKey  = trim($creds['public_key'] ?? '');
            $this->privateKey = trim($creds['private_key'] ?? '');
        }
    }

    public function checkoutPage(Order $order)
    {
        try {
            $user = auth()->user();
            if ($order->user_id !== $user->id) abort(403);

            // Identificador único da transação para o Bancard
            $shopProcessId = (string) $order->id . '_' . time();

            // Configuração de Moeda e Valor
            // Importante: Verifique se sua conta Bancard é PYG ou USD.
            $isPyg = true; 
            $currency = $isPyg ? 'PYG' : 'USD';
            $factor = $isPyg ? 7500 : 1; 
            $amount = (int) round($order->total * $factor);

            /**
             * GERAÇÃO DO TOKEN (Ponto Crítico)
             * A ordem deve ser estritamente: private_key + shop_process_id + amount + currency
             * Usamos variáveis simples para garantir que o $ na chave privada não seja interpretado.
             */
            $stringToHash = $this->privateKey . $shopProcessId . $amount . $currency;
            $token = md5($stringToHash);

            $payload = [
                "public_key" => $this->publicKey,
                "operation" => [
                    "token" => $token,
                    "shop_process_id" => $shopProcessId,
                    "amount" => (string) $amount,
                    "currency" => $currency,
                    "description" => "Pedido #{$order->id} - SAX",
                    "return_url" => route('bancard.callback'),
                    "cancel_url" => route('checkout.index'),
                ],
                "customer" => [
                    "first_name" => substr($order->name, 0, 30),
                    "last_name" => substr($order->surname ?: $order->name, 0, 30),
                    "email" => $order->email,
                    "phone" => substr(preg_replace('/\D/', '', $order->phone), 0, 15),
                    "address" => "Av San Blas", 
                    "city" => "Ciudad del Este",
                    "country" => "PY"
                ]
            ];

            // Log para conferência manual se o token bater com o que você gerar no MD5 online
            Log::info("Bancard Request:", ['payload' => $payload]);

            $response = Http::timeout(20)
                ->withHeaders(['Content-Type' => 'application/json'])
                ->post("{$this->apiUrl}/single_buy", $payload);

            $data = $response->json();

            if ($response->successful() && isset($data['process_id'])) {
                // O [PROCESS_ID] retornado pela API é o que será usado no Iframe
                $order->update(['shop_process_id' => $data['process_id']]);
                
                return view('layout.bancard', [
                    'process_id' => $data['process_id'], 
                    'order' => $order,
                    'isSandbox' => env('BANCARD_MODE', 'sandbox') === 'sandbox'
                ]);
            }

            Log::error('Bancard API Error:', ['response' => $data]);
            $msgErro = $data['messages'][0]['dsc'] ?? 'Erro ao comunicar com Bancard';
            return redirect()->route('checkout.index')->with('error', 'Bancard: ' . $msgErro);

        } catch (\Exception $e) {
            Log::error('Bancard Exception:', ['msg' => $e->getMessage()]);
            return redirect()->route('checkout.index')->with('error', 'Erro interno no processamento.');
        }
    }

    public function bancardCallback(Request $request)
    {
        // O Bancard envia o status após a interação no iframe
        $data = $request->all();
        $processId = $data['process_id'] ?? null;

        if (!$processId) return redirect()->route('checkout.index');

        $order = Order::where('shop_process_id', $processId)->first();
        if (!$order) return redirect()->route('home');

        // Status 'success' ou resposta 'S' indicam sucesso no vPOS
        if (($data['status'] ?? '') === 'success' || ($data['response'] ?? '') === 'S') {
            $order->update(['status' => 'paid']);
            Cart::where('user_id', $order->user_id)->delete();
            return redirect()->route('checkout.success');
        }

        return redirect()->route('checkout.index')->with('error', 'O pagamento não foi autorizado.');
    }
}