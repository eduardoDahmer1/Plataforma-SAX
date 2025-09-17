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
        $this->baseUrl = (env('BANCARD_MODE', 'sandbox') === 'sandbox')
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
            $this->publicKey = $creds['public_key'] ?? '';
            $this->privateKey = $creds['private_key'] ?? '';
        }
    }

    public function checkoutPage(Order $order)
    {
        try {
            $user = auth()->user();
            if ($order->user_id !== $user->id) {
                abort(403, 'Acesso negado ao pedido.');
            }

            $shopProcessId = $order->id . '-' . time();

            // Normaliza telefone e país
            $phone = preg_replace('/\D/', '', $order->phone); // só números
            if (strlen($phone) < 8) $phone = '595' . $phone; // adiciona código PY se necessário
            $country = ($phone[0] === '5') ? 'PY' : 'BR';

            // Normaliza cidade e endereço
            $city = $order->city ?: 'Desconhecido';
            $address = $order->street ?: 'Desconhecido';

            // Normaliza valor mínimo em PYG
            $amount = max(1000, intval(round($order->total)));

            $payload = [
                "shop_process_id" => $shopProcessId,
                "amount" => $amount,
                "currency" => "PYG",
                "description" => "Compra na loja SAX",
                "return_url" => route('bancard.callback'),
                "first_name" => $order->name,
                "last_name" => $order->surname ?: $order->name,
                "email" => $order->email,
                "phone" => $phone,
                "address" => $address,
                "city" => $city,
                "country" => $country,
                "items" => $order->items->map(function($item) {
                    return [
                        "name" => $item->name,
                        "quantity" => intval($item->quantity),
                        "price" => max(1000, intval(round($item->price))),
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
                return back()->with('error', 'Erro ao iniciar pagamento Bancard');
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
            return back()->with('error', 'Erro ao processar pagamento Bancard: ' . $e->getMessage());
        }
    }

    public function bancardCallback(Request $request)
    {
        $data = $request->all();
        Log::info('Retorno Bancard:', $data);

        if (!isset($data['shop_process_id'])) {
            return response()->json(['error' => 'shop_process_id ausente'], 400);
        }

        $order = Order::where('shop_process_id', $data['shop_process_id'])->first();
        if (!$order) {
            Log::error('Pedido Bancard não encontrado', $data);
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $order->status = (isset($data['status']) && $data['status'] === 'success') ? 'paid' : 'failed';
        $order->save();

        return response()->json(['message' => 'Callback recebido com sucesso']);
    }
}
