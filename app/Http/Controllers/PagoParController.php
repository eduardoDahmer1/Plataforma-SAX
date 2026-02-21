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
        // Puxa as credenciais de config/services.php ou .env
        $this->publicKey  = config('services.pagopar.public_key');
        $this->privateKey = config('services.pagopar.private_key');
        
        // URL da API v2.0 conforme documentação
        $this->apiUrl = 'https://api.pagopar.com/api/comercios/2.0/iniciar-transaccion';
    }

    /**
     * Inicia a transação no PagoPAR (inclui Bancard via ID 9)
     */
    public function payment(Order $order)
    {
        if (!$this->publicKey || !$this->privateKey) {
            Log::error('PagoPAR - Erro: Chaves não configuradas no ambiente.');
            return redirect()->route('checkout.index')->with('error', 'Erro de configuração no gateway.');
        }

        // Valor total formatado para float (importante para o hash)
        $totalAmount = floatval($order->total);
        
        // Geração do Token de segurança (Privada + ID Pedido + Montante)
        $token = sha1($this->privateKey . $order->id . strval($totalAmount));

        $payload = [
            "token"               => $token,
            "public_key"          => $this->publicKey,
            "monto_total"         => $totalAmount,
            "tipo_pedido"         => "VENTA-COMERCIO",
            "merchant_order_id"   => (string) $order->id,
            "summary_description" => "Compra Loja SAX #" . $order->id,
            "payment_method"      => 9, // <--- AQUI: 9 força o redirecionamento Bancard via PagoPAR
            "buyer" => [
                "ruc"           => $order->document ?? '', 
                "email"         => $order->email,
                "city"          => 1, 
                "name"          => $order->name . ' ' . ($order->surname ?? ''),
                "phone"         => $order->phone,
                "address"       => $order->street ?? 'N/A',
                "document"      => $order->document ?? '',
                "document_type" => "CI", 
            ],
            "compras_items" => $order->items->map(function ($item) {
                return [
                    "cidade"        => "1",
                    "nome"          => $item->name,
                    "quantidade"    => intval($item->quantity),
                    "categoria"     => "909", 
                    "chave_pública" => $this->publicKey,
                    "preço_total"   => intval($item->price * $item->quantity),
                    "id_produto"    => (string) $item->product_id,
                    "url_imagem"    => "",
                    "descrição"     => $item->name,
                ];
            })->toArray()
        ];

        try {
            $response = Http::post($this->apiUrl, $payload);
            $json = $response->json();

            if ($response->successful() && ($json['resposta'] ?? false)) {
                // O hash retornado identifica a transação no PagoPAR
                $hash = $json['resultado'][0]['dados'];

                // Atualizamos o pedido com o hash (shop_process_id)
                $order->update([
                    'shop_process_id' => $hash,
                    'status'          => 'pending'
                ]);

                // Redireciona o cliente para a URL de pagamento do PagoPAR
                return redirect()->away("https://www.pagopar.com/pagos/{$hash}");
            }

            Log::error('PagoPAR - Resposta de erro da API', ['response' => $json]);
            return redirect()->route('checkout.index')->with('error', 'Não foi possível iniciar o pagamento.');

        } catch (\Exception $e) {
            Log::error('PagoPAR - Exceção no pagamento', ['error' => $e->getMessage()]);
            return redirect()->route('checkout.index')->with('error', 'Erro interno ao processar pagamento.');
        }
    }

    /**
     * Webhook (Callback) - PagoPAR chama este método para avisar que pagou
     */
    public function callback(Request $request)
    {
        $data = $request->all();
        Log::info('PagoPAR - Webhook recebido', $data);

        if (!isset($data['resultado'][0])) {
            return response()->json(['error' => 'Invalid structure'], 400);
        }

        $res = $data['resultado'][0];
        $hashPedido = $res['hash_do_pedido'];
        $tokenEnviado = $res['token'];

        // Validação de segurança: sha1(chave_privada + hash_do_pedido)
        $tokenValidacao = sha1($this->privateKey . $hashPedido);

        if ($tokenEnviado !== $tokenValidacao) {
            Log::alert('PagoPAR - Token de callback inválido (Fraude?)', ['hash' => $hashPedido]);
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $order = Order::where('shop_process_id', $hashPedido)->first();

        if ($order) {
            if ($res['pago'] === true) {
                $order->update(['status' => 'paid']);
                Log::info("PagoPAR - Pedido #{$order->id} marcado como PAGO.");
            } elseif ($res['cancelado'] === true) {
                $order->update(['status' => 'cancelled']);
            }
        }

        return response()->json($data['resultado']);
    }

    /**
     * Finish - Para onde o cliente volta após fechar a janela do PagoPAR
     */
    public function finish(Request $request)
    {
        $hash = $request->query('hash');
        $order = Order::where('shop_process_id', $hash)->first();

        if (!$order) {
            return redirect()->route('checkout.index')->with('error', 'Pedido não localizado.');
        }

        // Se chegou aqui, o processo terminou. Limpamos o carrinho do usuário.
        Cart::where('user_id', $order->user_id)->delete();
        session()->forget('applied_cupon');

        if ($order->status === 'paid') {
            return redirect()->route('checkout.success')->with('success', 'Pagamento processado com sucesso!');
        }

        // Se ainda estiver pendente, pode ser que o webhook demore alguns segundos
        return redirect()->route('user.orders.show', $order->id)
            ->with('info', 'Seu pagamento está sendo processado. Verifique o status em instantes.');
    }
}