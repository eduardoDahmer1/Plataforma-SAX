<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Order;

class PagoParController extends Controller
{
    private $apiUrl;
    private $publicKey;
    private $privateKey;

    public function __construct()
    {
        // Pega direto de config/services.php
        $this->publicKey  = config('services.pagopar.public_key');
        $this->privateKey = config('services.pagopar.private_key');

        if (!$this->publicKey || !$this->privateKey) {
            Log::error('PagoPAR - Credenciais não configuradas');
        }

        // URL da API PagoPAR
        $this->apiUrl = 'https://hub.pagopar.com/api/v1';
    }

    /**
     * Inicia o pagamento no Pagopar
     */
    public function payment(Order $order)
    {
        if (!$this->publicKey || !$this->privateKey) {
            abort(500, 'Credenciais PagoPAR não configuradas.');
        }

        $processId = $order->id . '-' . time();

        $items = $order->items->map(function ($item) {
            return [
                'name'     => $item->name,
                'quantity' => intval($item->quantity),
                'price'    => max(100, intval(round($item->price))),
            ];
        })->toArray();

        $payload = [
            "token"        => sha1($this->privateKey . $order->id . intval($order->total)),
            "public_key"   => $this->publicKey,
            "operation"    => [
                "shop_process_id" => $processId,
                "amount"          => intval(round($order->total)),
                "currency"        => "BRL",
                "description"     => "Compra na loja SAX",
                "return_url"      => route('pagopar.finish'),
                "cancel_url"      => route('pagopar.finish'),
                "items"           => $items,
                "customer"        => [
                    "name"    => $order->name,
                    "email"   => $order->email,
                    "phone"   => $order->phone,
                    "address" => $order->street ?? 'Desconhecido',
                    "city"    => $order->city ?? 'Desconhecido',
                    "state"   => $order->state ?? '',
                    "country" => 'BR',
                    "zip"     => $order->cep ?? ''
                ]
            ]
        ];

        Log::info('PagoPAR - Payload enviado', $payload);

        $response = Http::post("{$this->apiUrl}/charge/create", $payload);

        if ($response->failed()) {
            Log::error('PagoPAR - Falha na comunicação', [
                'response' => $response->body()
            ]);
            return redirect()->route('checkout.error')
                ->with('error', 'Erro ao iniciar pagamento com Pagopar');
        }

        $json = $response->json();

        if (!isset($json['status']) || $json['status'] !== 'success') {
            Log::error('PagoPAR - Erro na resposta', $json);
            return redirect()->route('checkout.error')
                ->with('error', 'Pagamento não pôde ser iniciado');
        }

        // Salva os dados no pedido
        $order->shop_process_id = $processId;
        $order->charge_id = $json['response']['charge_id'] ?? null;
        $order->status = 'pending';
        $order->save();

        Log::info('PagoPAR - Pedido criado com sucesso', [
            'order_id'   => $order->id,
            'process_id' => $processId,
            'charge_id'  => $order->charge_id
        ]);

        return redirect($json['response']['payment_url']);
    }

    /**
     * Callback do Pagopar (notificação de status)
     */
    public function callback(Request $request)
    {
        $data = $request->all();
        Log::info('PagoPAR - Callback recebido', $data);

        if (!isset($data['shop_process_id'])) {
            return response()->json(['error' => 'shop_process_id ausente'], 400);
        }

        $order = Order::where('shop_process_id', $data['shop_process_id'])->first();
        if (!$order) {
            Log::error('PagoPAR - Pedido não encontrado no callback', $data);
            return response()->json(['error' => 'Pedido não encontrado'], 404);
        }

        $status = $data['status'] ?? '';
        if ($status === 'success') {
            $order->status = 'paid';
        } elseif ($status === 'failed') {
            $order->status = 'failed';
        } else {
            $order->status = 'pending';
        }

        $order->save();

        return response()->json(['message' => 'OK']);
    }

    /**
     * Retorno final do cliente após pagamento
     */
    public function finish(Request $request)
    {
        $status = $request->get('status', '');
        $processId = $request->get('shop_process_id', '');

        $order = Order::where('shop_process_id', $processId)->first();

        if (!$order) {
            return redirect()->route('checkout.error')
                ->with('error', 'Pedido não encontrado após retorno');
        }

        if ($status === 'success') {
            return redirect()->route('checkout.success')
                ->with('success', 'Pagamento realizado com sucesso!');
        }

        return redirect()->route('checkout.error')
            ->with('error', 'Pagamento não concluído');
    }
}
