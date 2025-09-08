<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;

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
    public function checkoutPage(Order $order)
    {
        try {
            $user = auth()->user();
            if ($order->user_id !== $user->id) {
                abort(403, 'Acesso negado ao pedido.');
            }

            $amount = intval(round($order->total));
            if ($amount < 1000) {
                return back()->with('error', 'O valor mínimo para pagamento Bancard é 1000 PYG');
            }

            $shopProcessId = $order->id . '-' . time();

            $payload = [
                "shop_process_id" => $shopProcessId,
                "amount" => $amount,
                "currency" => "PYG",
                "description" => "Compra na loja SAX",
                "return_url" => route('bancard.callback'),
                "first_name" => $order->name,
                "email" => $order->email,
            ];

            $response = Http::withHeaders([
                'Authorization' => 'Basic ' . base64_encode(env('BANCARD_PUBLIC_KEY') . ':' . env('BANCARD_PRIVATE_KEY')),
                'Accept' => 'application/json',
            ])->timeout(10)
              ->throw()
              ->post("{$this->apiUrl}/single_buy", $payload);

            $data = $response->json();

            Log::info('Bancard request', $payload);
            Log::info('Bancard response', $data);

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
        } catch (\Throwable $e) {
            Log::error('Erro checkout Bancard', ['message' => $e->getMessage()]);
            return back()->with('error', 'Erro ao processar pagamento Bancard: ' . $e->getMessage());
        }
    }

    /**
     * Callback do Bancard após pagamento
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

        $order->status = (isset($data['status']) && $data['status'] === 'success') ? 'paid' : 'failed';
        $order->save();

        return response()->json(['message' => 'Callback recebido com sucesso']);
    }

    /**
     * Finaliza pagamento (opcional)
     */
    public function bancardFinish(Request $request)
    {
        $resp = $request->all();
        Log::debug('bancard_callback_finish', $resp);

        if (isset($resp['operation']) && $resp['operation']['response_code'] == "00") {
            $order = Order::where('shop_process_id', $resp['operation']['shop_process_id'])->first();

            if ($order && $order->status !== "paid") {
                $order->status = 'paid';
                $order->save();
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

        try {
            $response = Http::withHeaders(['Content-Type' => 'application/json'])
                            ->timeout(10)
                            ->throw()
                            ->post("{$this->apiUrl}/single_buy/rollback", $data);

            $resp = $response->json();
            Log::info('bancard_rollback_response', $resp);

            if (isset($resp['status']) && $resp['status'] === "success") {
                $order = Order::where('shop_process_id', $shop_process_id)->first();
                if ($order) {
                    $order->status = 'pending';
                    $order->save();
                }
                return response()->json(["status" => 200, "message" => "Payment rollbacked successfully."]);
            }

            return response()->json(["status" => 404, "message" => "Not Found"], 404);

        } catch (\Throwable $e) {
            Log::error('Erro rollback Bancard', ['message' => $e->getMessage()]);
            return response()->json(["status" => 500, "message" => "Erro no rollback"]);
        }
    }
}
