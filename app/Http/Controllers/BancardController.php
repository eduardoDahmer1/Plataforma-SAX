<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\PaymentMethod;

class BancardController extends Controller
{
    private $baseUrl;
    private $apiUrl;
    private $publicKey;
    private $privateKey;

    public function __construct()
    {
        // Define base URL de acordo com o modo
        $this->baseUrl = (env('BANCARD_MODE', 'sandbox') === 'sandbox')
            ? 'https://vpos.infonet.com.py:8888'
            : 'https://vpos.infonet.com.py';

        $this->apiUrl = "{$this->baseUrl}/vpos/api/0.3";

        // Pega as credenciais do banco
        $bancard = PaymentMethod::where('type', 'gateway')
                    ->where('name', 'Bancard')
                    ->where('active', 1)
                    ->first();

        if ($bancard && $bancard->credentials) {
            $creds = json_decode($bancard->credentials, true);
            $this->publicKey  = $creds['public_key'] ?? '';
            $this->privateKey = $creds['private_key'] ?? '';
        }
    }

    public function checkoutPage(Order $order)
    {
        try {
            Log::info('Bancard - Iniciando checkout', [
                'order_id' => $order->id ?? null,
                'user_id'  => auth()->id()
            ]);

            $user = auth()->user();
            if ($order->user_id !== $user->id) {
                abort(403, 'Acesso negado ao pedido.');
            }

            $shopProcessId = $order->id . '-' . time();

            // País do pedido e moeda
            $country  = $order->country === 'BR' ? 'BR' : 'PY';
            $currency = $country === 'BR' ? 'BRL' : 'PYG';

            // Telefone internacional correto
            $phone = preg_replace('/\D/', '', $order->phone);
            if ($country === 'PY' && substr($phone, 0, 3) !== '595') {
                $phone = '595' . $phone;
            } elseif ($country === 'BR' && substr($phone, 0, 2) !== '55') {
                $phone = '55' . $phone;
            }

            // Endereço e cidade válidos
            $address = $order->street ?: ($order->address ?? 'Desconhecido');
            $city    = $order->city ?: 'Desconhecido';

            // Valor em inteiro
            $amount = intval(round($order->total));
            if ($currency === 'BRL') $amount *= 100;

            $payload = [
                "shop_process_id" => $shopProcessId,
                "amount" => $amount,
                "currency" => $currency,
                "description" => "Compra na loja SAX",
                "return_url" => route('bancard.callback'),
                "first_name" => $order->name,
                "last_name" => $order->surname ?: $order->name,
                "email" => $order->email,
                "phone" => $phone,
                "address" => $address,
                "city" => $city,
                "country" => $country,
                "items" => $order->items->map(function($item) use ($currency) {
                    $price = intval(round($item->price));
                    if ($currency === 'BRL') $price *= 100;
                    return [
                        "name" => $item->name,
                        "quantity" => intval($item->quantity),
                        "price" => max(1000, $price),
                    ];
                })->toArray()
            ];

            Log::info('Bancard - Requisição enviada', [
                'order_id' => $order->id,
                'shop_process_id' => $shopProcessId,
                'payload' => $payload,
                'credentials' => [
                    'public_key_set' => !empty($this->publicKey),
                    'private_key_set' => !empty($this->privateKey),
                ]
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode($this->publicKey . ':' . $this->privateKey),
                'Accept' => 'application/json',
            ])->timeout(10)
              ->post("{$this->apiUrl}/single_buy", $payload);

            $data = $response->json();

            Log::info('Bancard - Resposta recebida', [
                'status' => $response->status(),
                'body' => $data,
            ]);

            if (!isset($data['process_id'])) {
                Log::error('Bancard - process_id não gerado', $data);
                return redirect()->route('checkout.error')
                    ->with('error', 'Erro ao iniciar pagamento Bancard');
            }

            $order->shop_process_id = $data['process_id'];
            $order->save();

            return view('layout.bancard', [
                'process_id' => $data['process_id'],
                'order'      => $order
            ]);

        } catch (\Throwable $e) {
            Log::error('Erro checkout Bancard', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'order_id' => $order->id,
            ]);
            return redirect()->route('checkout.error')
                ->with('error', 'Erro ao processar pagamento: ' . $e->getMessage());
        }
    }

    public function bancardCallback(Request $request)
    {
        $data = $request->all();
        Log::info('Retorno Bancard:', $data);

        if (!isset($data['shop_process_id'])) {
            return redirect()->route('checkout.error')
                ->with('error', 'shop_process_id ausente');
        }

        $order = Order::where('shop_process_id', $data['shop_process_id'])->first();
        if (!$order) {
            Log::error('Pedido Bancard não encontrado', $data);
            return redirect()->route('checkout.error')
                ->with('error', 'Pedido não encontrado');
        }

        $order->status = (isset($data['status']) && $data['status'] === 'success') ? 'paid' : 'failed';
        $order->save();

        $route = ($order->status === 'paid') ? 'checkout.success' : 'checkout.error';

        return redirect()->route($route)
                 ->with($order->status === 'paid' ? 'success' : 'error',
                        $order->status === 'paid' ? 'Pagamento realizado com sucesso!' : 'Pagamento falhou.');
    }
}
