<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Models\Order;
use App\Models\Cart;

class PagoParController extends Controller
{
    private $apiUrl;
    private $publicKey;
    private $privateKey;

    public function __construct()
    {
        $this->publicKey  = config('services.pagopar.public_key');
        $this->privateKey = config('services.pagopar.private_key');
        $this->apiUrl     = 'https://api.pagopar.com/api/comercios/2.0/iniciar-transaccion';
    }

    /**
     * Inicia a transação no PagoPAR (Configurada para Bancard via ID 9)
     */
    public function payment(Order $order)
    {
        // Limpeza de chaves para evitar espaços em branco vindos do .env
        $pubKey  = trim($this->publicKey);
        $privKey = trim($this->privateKey);

        if (!$pubKey || !$privKey) {
            Log::error('PagoPAR - Erro: Chaves não localizadas. Verifique o .env e config/services.php');
            return redirect()->route('checkout.index')->with('error', 'Erro de configuração no gateway.');
        }

        // 1. O PagoPAR exige que o valor seja inteiro para Moeda Guarani (PYG).
        // Se sua loja usa centavos/USD, garanta que o arredondamento seja idêntico ao do Token.
        $totalAmount = (int) round($order->total);

        // 2. Geração do Token (Ordem: Privada + ID Pedido + Montante)
        // Forçamos strings para garantir a concatenação correta antes do sha1
        $stringParaHash = $privKey . (string)$order->id . (string)$totalAmount;
        $token = sha1($stringParaHash);

        // Payload seguindo estritamente a documentação v2.0
        $payload = [
            "token"               => $token,
            "public_key"          => $pubKey,
            "monto_total"         => $totalAmount,
            "tipo_pedido"         => "VENTA-COMERCIO",
            "merchant_order_id"   => (string) $order->id,
            "summary_description" => "Compra Loja SAX #" . $order->id,
            "payment_method"      => 9, // Força redirecionamento para Bancard
            "buyer" => [
                "ruc"           => $order->document ?? '88888888', // RUC genérico se vazio
                "email"         => $order->email,
                "city"          => 1,
                "name"          => substr($order->name . ' ' . ($order->surname ?? ''), 0, 50),
                "phone"         => substr(preg_replace('/\D/', '', $order->phone), 0, 20),
                "address"       => $order->street ?? 'N/A',
                "document"      => $order->document ?? '88888888',
                "document_type" => "CI",
            ],
            "compras_items" => $order->items->map(function ($item) use ($pubKey) {
                return [
                    "ciudad"         => "1",
                    "nombre"         => substr($item->name, 0, 100),
                    "cantidad"       => (int) $item->quantity,
                    "categoria"      => "909",
                    "public_key"     => $pubKey,
                    "precio_total"   => (int) round($item->price * $item->quantity),
                    "id_produto"     => (string) $item->product_id,
                    "url_imagem"     => "",
                    "descripcion"    => substr($item->name, 0, 100),
                ];
            })->toArray()
        ];

        try {
            // Log do payload para conferência (Cuidado: removemos dados sensíveis em produção se necessário)
            Log::info("PagoPAR - Enviando Pedido #{$order->id}", ['string_hash' => $stringParaHash, 'token' => $token]);

            $response = Http::post($this->apiUrl, $payload);
            $json = $response->json();

            if ($response->successful() && isset($json['respuesta']) && $json['respuesta'] === true) {
                // O hash retornado ('dados') identifica a sessão de pagamento
                $hashTransacao = $json['resultado'][0]['dados'];

                $order->update([
                    'shop_process_id' => $hashTransacao,
                    'status'          => 'pending'
                ]);

                // Redireciona o cliente para a tela de pagamento (Bancard)
                return redirect()->away("https://www.pagopar.com/pagos/{$hashTransacao}");
            }

            // Se chegou aqui, a API retornou erro (ex: Token no coincide)
            Log::error('PagoPAR - Erro na API', [
                'status'   => $response->status(),
                'response' => $json,
                'payload'  => $payload
            ]);

            $msgErro = $json['resultado'] ?? 'Erro desconhecido na API.';
            return redirect()->route('checkout.index')->with('error', "PagoPAR: {$msgErro}");
        } catch (\Exception $e) {
            Log::error('PagoPAR - Falha crítica na requisição', ['msg' => $e->getMessage()]);
            return redirect()->route('checkout.index')->with('error', 'Erro interno ao conectar com o meio de pagamento.');
        }
    }

    /**
     * Webhook (Callback)
     */
    public function callback(Request $request)
    {
        $data = $request->all();

        if (!isset($data['resultado'][0])) {
            return response()->json(['error' => 'Invalid structure'], 400);
        }

        $res = $data['resultado'][0];
        $hashPedido = $res['hash_do_pedido'];
        $tokenEnviado = $res['token'];

        // Validação: sha1(chave_privada + hash_do_pedido)
        $tokenValidacao = sha1($this->privateKey . $hashPedido);

        if ($tokenEnviado !== $tokenValidacao) {
            Log::alert('PagoPAR - Token de callback inválido', ['received' => $tokenEnviado, 'expected' => $tokenValidacao]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = Order::where('shop_process_id', $hashPedido)->first();

        if ($order) {
            if ($res['pago'] === true) {
                $order->update(['status' => 'paid']);
                Log::info("PagoPAR - Pedido #{$order->id} PAGO.");
            } elseif ($res['cancelado'] === true) {
                $order->update(['status' => 'cancelled']);
            }
        }

        return response()->json($data['resultado']);
    }

    /**
     * Retorno do Cliente (Finish)
     */
    public function finish(Request $request)
    {
        $hash = $request->query('hash');
        $order = Order::where('shop_process_id', $hash)->first();

        if (!$order) {
            return redirect()->route('checkout.index')->with('error', 'Pedido não localizado.');
        }

        // Limpa o carrinho apenas no final bem-sucedido
        Cart::where('user_id', $order->user_id)->delete();
        session()->forget('applied_cupon');

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success')->with('success', 'Pagamento confirmado!');
        }

        return redirect()->route('user.orders.show', $order->id)
            ->with('info', 'Estamos aguardando a confirmação do pagamento.');
    }
}
