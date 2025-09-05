<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\Notification;

class BancardController extends Controller
{
    private $baseUrl;
    private $apiUrl;

    public function __construct()
    {
        $this->baseUrl = (env('BANCARD_MODE', 'sandbox') === 'sandbox')
            ? 'https://vpos.infonet.com.py:8888'
            : 'https://vpos.infonet.com.py';

        $this->apiUrl = "{$this->baseUrl}/vpos/api/0.3";
    }

    /**
     * Exibe o checkout Bancard
     */
    public function checkout(Request $request)
    {
        // pega pedido existente
        $order = Order::find($request->input('order_id'));
        if (!$order) {
            return back()->with('error', 'Pedido não encontrado');
        }
    
        // garante payment_method
        $order->payment_method = 'bancard';
        $order->save();
    
        $amount = intval($order->total); // PYG sem decimais
        $shopProcessId = uniqid();
    
        $payload = [
            "shop_process_id" => $shopProcessId,
            "amount" => $amount,
            "currency" => "PYG",
            "description" => "Compra na loja SAX",
            "return_url" => route('bancard.callback'),
        ];
    
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('BANCARD_PUBLIC_KEY') . ':' . env('BANCARD_PRIVATE_KEY')),
            'Accept' => 'application/json',
        ])->post("{$this->apiUrl}/single_buy", $payload);
    
        $data = $response->json();
    
        if (!isset($data['process_id'])) {
            Log::error('Erro ao gerar process_id Bancard', $data);
            return back()->with('error', 'Erro ao iniciar pagamento Bancard');
        }
    
        // salva process_id
        $order->shop_process_id = $data['process_id'];
        $order->save();
    
        // retorna view
        return view('layout.bancard', [
            'process_id' => $data['process_id'],
            'order'      => $order
        ]);
    }    

    public function checkoutPage(Order $order)
    {
        $amount = intval($order->total); // PYG sem decimais
        $shopProcessId = uniqid();
    
        $payload = [
            "shop_process_id" => $shopProcessId,
            "amount" => $amount,
            "currency" => "PYG",
            "description" => "Compra na loja SAX",
            "return_url" => route('bancard.callback'),
        ];
    
        $response = Http::withHeaders([
            'Authorization' => 'Basic ' . base64_encode(env('BANCARD_PUBLIC_KEY') . ':' . env('BANCARD_PRIVATE_KEY')),
            'Accept' => 'application/json',
        ])->post("{$this->apiUrl}/single_buy", $payload);
    
        $data = $response->json();
    
        if (!isset($data['process_id'])) {
            Log::error('Erro ao gerar process_id Bancard', $data);
            return back()->with('error', 'Erro ao iniciar pagamento Bancard');
        }
    
        $order->shop_process_id = $data['process_id'];
        $order->save();
    
        return view('layout.bancard', [
            'process_id' => $data['process_id'],
            'order'      => $order
        ]);
    }
      
    /**
     * Callback que o Bancard chama após pagamento
     */
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

        if (isset($data['status']) && $data['status'] === 'success') {
            $order->status = 'paid';
            $order->save();

            Notification::create(['order_id' => $order->id]);
        } else {
            $order->status = 'failed';
            $order->save();
        }

        return response()->json(['message' => 'Callback recebido com sucesso']);
    }

    /**
     * Finaliza pagamento (opcional)
     */
    public function bancardFinish(Request $request)
    {
        $bancardResponse = $request->all();
        Log::debug('bancard_callback_finish', $bancardResponse);

        if (isset($bancardResponse['operation']) && $bancardResponse['operation']['response_code'] == "00") {
            $order = Order::where('shop_process_id', $bancardResponse['operation']['shop_process_id'])->first();

            if ($order && $order->status !== "paid") {
                $order->status = 'paid';
                $order->save();

                $notification = new Notification();
                $notification->order_id = $order->id;
                $notification->save();
            }

            return response()->json(["status" => 200, "message" => __("Success")]);
        }

        return response()->json(["status" => 404, "message" => __("Not Found")], 404);
    }

    /**
     * Rollback do pagamento
     */
    public function bancardRollback($shop_process_id)
    {
        $data = [
            "public_key" => env('BANCARD_PUBLIC_KEY'),
            "operation" => [
                "token" => md5(env('BANCARD_PRIVATE_KEY') . $shop_process_id . "rollback" . "0.00"),
                "shop_process_id" => $shop_process_id
            ]
        ];

        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post("{$this->apiUrl}/single_buy/rollback", $data);

        $bancardResponse = $response->json();

        if (isset($bancardResponse['status']) && $bancardResponse['status'] === "success") {
            $order = Order::where('shop_process_id', $shop_process_id)->first();
            if ($order) {
                $order->status = 'pending';
                $order->save();

                $notification = new Notification();
                $notification->order_id = $order->id;
                $notification->save();
            }

            return response()->json(["status" => 200, "message" => "Payment rollbacked successfully."]);
        } else {
            Log::debug('bancard_rollback_response', [$bancardResponse]);
            return response()->json(["status" => 404, "message" => "Not Found"], 404);
        }
    }
}
