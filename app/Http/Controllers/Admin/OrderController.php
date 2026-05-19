<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderItem; // Importante para o método store
use App\Services\ReceiptService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB; // Faltava essa importação para as transactions
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class OrderController extends Controller
{
    public function __construct(private ReceiptService $receiptService) {}
    // Lista pedidos
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']);

        if ($request->filled('payment_method')) {
            $query->where('payment_method', $request->payment_method);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('user_name')) {
            $query->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->user_name}%");
            });
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate(20);
        $orders->appends($request->all());

        return view('admin.orders.index', compact('orders'));
    }

    // Atualiza status do pedido ou do pagamento
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $wasPaid = $order->isPaid();

        // Validação flexível: exige um OU outro
        $request->validate([
            'status' => 'nullable|in:pending,processing,shipped,completed,canceled',
            'payment_status' => 'nullable|in:pending,paid,failed,refunded',
        ]);

        // Se veio atualização de STATUS DO PEDIDO
        if ($request->has('status')) {
            
            // Se o Admin cancelar, devolvemos o estoque (apenas se não estava cancelado antes)
            if ($request->status === 'canceled' && $order->status !== 'canceled') {
                foreach ($order->items as $item) {
                    $product = $item->product;
                    if ($product) {
                        $product->increment('stock', $item->quantity);
                    }
                }
            }
            
            $order->status = $request->status;
            
            // Lógica automática: Se marcar pedido como concluído, presume-se pago (opcional)
            if ($request->status === 'completed') {
                $order->payment_status = 'paid';
            }
        }

        // Se veio atualização de STATUS DO PAGAMENTO
        if ($request->has('payment_status')) {
            $order->payment_status = $request->payment_status;
        }

        $order->save();

        if (!$wasPaid && $order->isPaid()) {
            try {
                $this->receiptService->issueForOrder($order);
            } catch (\Throwable $e) {
                Log::error('Error al emitir recibo para pedido de depósito', [
                    'order_id' => $order->id,
                    'message'  => $e->getMessage(),
                ]);
            }
        }

        return redirect()->back()->with('success', 'Pedido atualizedo com sucesso!');
    }

    // Mostra detalhes do pedido
    public function show($id)
    {
        $order = Order::with(['user', 'items.product', 'receipt'])->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    // Deleta pedido
    public function destroy($id)
    {
        $order = Order::with('items')->findOrFail($id);

        // Deleta comprovante, se existir
        if ($order->deposit_receipt && Storage::disk('public')->exists('deposits/' . $order->deposit_receipt)) {
            Storage::disk('public')->delete('deposits/' . $order->deposit_receipt);
        }

        // Deleta itens do pedido
        foreach ($order->items as $item) {
            $item->delete();
        }

        // Deleta pedido
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Pedido excluído com sucesso.');
    }

    // Submete comprovante de depósito (Admin)
    public function depositSubmit(Request $request, Order $order)
    {
        $request->validate([
            'deposit_receipt' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        if ($request->hasFile('deposit_receipt')) {
            $file = $request->file('deposit_receipt');
            $filename = time() . '_' . $file->getClientOriginalName();
            $file->storeAs('deposits', $filename, 'public');

            $order->deposit_receipt = $filename;
            $order->save();
        }

        return redirect()->back()->with('success', 'Comprovante enviado com sucesso!');
    }

    // Cria pedido (Manualmente pelo Admin ou via API)
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'status'         => 'required|string',
            'name'            => 'required|string',
            'document'       => 'nullable|string',
            'email'          => 'required|email',
            'phone'          => 'nullable|string',
            'address'        => 'nullable|string',
            'city'           => 'nullable|string',
            'state'          => 'nullable|string',
            'country'        => 'nullable|string',
            'cep'            => 'nullable|string',
            'street'         => 'nullable|string',
            'number'         => 'nullable|string',
            'district'       => 'nullable|string',
            'complement'     => 'nullable|string',
            'observations'   => 'nullable|string',
            'shipping'       => 'nullable|integer',
            'store'          => 'nullable|integer',
            'shipping_cost'  => 'nullable|numeric',
            'total'          => 'required|numeric',
        ]);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'user_id'         => auth()->id(),
                'order_number'    => strtoupper(Str::random(10)),
                'txnid'           => $request->txnid ?? null,
                'charge_id'       => $request->charge_id ?? null,
                'pay_id'          => $request->pay_id ?? null,
                'payment_status'  => $request->payment_status ?? 'pending',
                'total'           => $request->total,
                'discount'        => $request->discount ?? 0,
                'payment_method'  => $request->payment_method,
                'status'          => $request->status,
                'name'            => $request->name,
                'document'        => $request->document,
                'email'           => $request->email,
                'phone'           => $request->phone,
                'address'         => $request->address,
                'city'            => $request->city,
                'state'           => $request->state,
                'country'         => $request->country,
                'cep'             => $request->cep,
                'street'          => $request->street,
                'number'          => $request->number,
                'district'        => $request->district,
                'complement'      => $request->complement,
                'observations'    => $request->observations,
                'shipping'        => $request->shipping,
                'store'           => $request->store,
                'shipping_cost'   => $request->shipping_cost,
                'packing_cost'    => $request->packing_cost,
                'tax'             => $request->tax,
                'currency_sign'   => $request->currency_sign ?? 'R$',
                'currency_value'  => $request->currency_value ?? 1,
                'order_note'      => $request->order_note,
                'internal_note'   => $request->internal_note,
                
                'shipping_name'     => $request->shipping_name ?? $request->name,
                'shipping_email'    => $request->shipping_email ?? $request->email,
                'shipping_phone'    => $request->shipping_phone ?? $request->phone,
                'shipping_country'  => $request->shipping_country ?? $request->country,
                'shipping_state'    => $request->shipping_state ?? $request->state,
                'shipping_city'     => $request->shipping_city ?? $request->city,
                'shipping_zip'      => $request->shipping_zip ?? $request->cep,
                'shipping_address'  => $request->shipping_address ?? $request->street,
                'shipping_address_number' => $request->shipping_address_number ?? $request->number,
                'shipping_complement'     => $request->shipping_complement ?? $request->complement,
                'shipping_district'       => $request->shipping_district ?? $request->district,
                'shipping_document'       => $request->shipping_document ?? $request->document,
            ]);

            if ($request->filled('items') && is_array($request->items)) {
                foreach ($request->items as $item) {
                    OrderItem::create([
                        'order_id'      => $order->id,
                        'product_id'    => $item['product_id'] ?? null,
                        'name'          => $item['name'] ?? 'Produto',
                        'sku'           => $item['sku'] ?? null,
                        'quantity'      => $item['quantity'] ?? 1,
                        'price'         => $item['price'] ?? 0,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('admin.orders.index')->with('success', 'Pedido criado com sucesso!');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Erro ao criar pedido: ' . $e->getMessage())->withInput();
        }
    }
}