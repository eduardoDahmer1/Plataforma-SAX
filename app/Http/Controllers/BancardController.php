<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\Order;
use App\Models\PaymentMethod;
use Exception;

class BancardController extends Controller
{
    private $apiUrl;
    private $publicKey;
    private $privateKey;

    public function __construct()
    {
        $mode = env('BANCARD_MODE', 'sandbox');
        $baseUrl = ($mode === 'sandbox') ? 'https://vpos.infonet.com.py:8888' : 'https://vpos.infonet.com.py';
        $this->apiUrl = "{$baseUrl}/vpos/api/0.3";

        // Busca credenciais dinâmicas do banco
        $bancard = PaymentMethod::where('name', 'Bancard')->first();
        if ($bancard) {
            $creds = is_array($bancard->credentials) ? $bancard->credentials : json_decode($bancard->credentials, true);
            $this->publicKey  = trim($creds['public_key'] ?? '');
            $this->privateKey = trim($creds['private_key'] ?? '');
        }
    }

    public function checkoutPage($id)
    {
        try {
            $order = Order::findOrFail($id); // Busca o pedido pelo ID passado na URL

            // Dados Bancard (Ajuste conforme suas credenciais)
            $amount = (string) (int) round($order->total);
            $currency = 'PYG';
            $shopProcessId = $order->id . '_' . time();

            // Geração do Token (Verifique se as chaves estão no seu .env)
            $token = md5(env('BANCARD_PRIVATE_KEY') . $shopProcessId . $amount . $currency);

            $payload = [
                "public_key" => env('BANCARD_PUBLIC_KEY'),
                "operation" => [
                    "token" => $token,
                    "shop_process_id" => $shopProcessId,
                    "amount" => $amount,
                    "currency" => $currency,
                    "description" => "Pedido #{$order->id}",
                    "return_url" => route('bancard.return'),
                    "cancel_url" => route('checkout.index'),
                ],
                "customer" => [
                    "first_name" => $order->name,
                    "last_name"  => $order->surname ?? 'SAX',
                    "email"      => $order->email,
                    "phone"      => preg_replace('/\D/', '', $order->phone),
                    "address"    => $order->address ?? 'N/A',
                    "city"       => $order->city ?? 'Ciudad del Este',
                    "country"    => "PY"
                ]
            ];

            $response = Http::post("https://vpos.infonet.com.py/vpos/api/0.3/single_buy", $payload);
            $data = $response->json();

            if ($response->successful() && isset($data['process_id'])) {
                return view('layout.bancard', [
                    'process_id' => $data['process_id'],
                    'order'      => $order // É essencial passar o $order aqui
                ]);
            }

            return redirect()->route('checkout.index')->with('error', 'Erro na API Bancard');
        } catch (\Exception $e) {
            \Log::error("Bancard Error: " . $e->getMessage());
            return redirect()->route('checkout.index')->with('error', 'Erro ao iniciar pagamento.');
        }
    }

    public function returnPage()
    {
        return view('checkout.success'); // Crie essa view simples de "Obrigado"
    }
}
