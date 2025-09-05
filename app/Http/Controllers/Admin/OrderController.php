<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
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

    // Atualiza status do pedido
    public function update(Request $request, $id)
    {
        $order = Order::findOrFail($id);

        $request->validate([
            'status' => 'required|in:pending,processing,completed,canceled',
        ]);

        $order->status = $request->status;
        $order->save();

        return redirect()->back()->with('success', 'Status do pedido atualizado!');
    }

    // Mostra detalhes do pedido
    public function show($id)
    {
        $order = Order::with(['user', 'items'])->findOrFail($id);
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

        Session::forget('cart');

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
            $filePath = $file->store('deposit_receipts', 'public');

            $order->deposit_receipt = $filePath;
            $order->save();
        }

        // Redireciona para a página do pedido no painel do usuário
        return redirect()->route('user.orders.show', $order->id)
            ->with('success', 'Comprovante enviado com sucesso!');
    }

    // Método para criar um pedido (exemplo de checkout preenchendo todos os campos)
    public function store(Request $request)
    {
        $request->validate([
            'payment_method' => 'required|string',
            'status' => 'required|string',
            'name' => 'required|string',
            'document' => 'nullable|string',
            'email' => 'required|email',
            'phone' => 'nullable|string',
            'address' => 'nullable|string',
            'city' => 'nullable|string',
            'state' => 'nullable|string',
            'country' => 'nullable|string',
            'cep' => 'nullable|string',
            'street' => 'nullable|string',
            'number' => 'nullable|string',
            'observations' => 'nullable|string',
            'shipping' => 'nullable|integer',
            'store' => 'nullable|integer',
        ]);

        $order = Order::create([
            'user_id' => auth()->id(),
            'total' => $request->total ?? 0,
            'payment_method' => $request->payment_method,
            'status' => $request->status,
            'name' => $request->name,
            'document' => $request->document,
            'email' => $request->email,
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'state' => $request->state,
            'country' => $request->country,
            'cep' => $request->cep,
            'street' => $request->street,
            'number' => $request->number,
            'observations' => $request->observations,
            'shipping' => $request->shipping,
            'store' => $request->store,
        ]);

        // Aqui você pode criar os itens do pedido, se tiver no request
        if ($request->filled('items') && is_array($request->items)) {
            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_id' => $item['product_id'],
                    'name' => $item['name'] ?? null,
                    'external_name' => $item['external_name'] ?? null,
                    'slug' => $item['slug'] ?? null,
                    'sku' => $item['sku'] ?? null,
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
        }

        Session::forget('cart');

        return redirect()->route('user.dashboard')->with('success', 'Pedido criado com sucesso!');
    }
}
