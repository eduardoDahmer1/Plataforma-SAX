<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class OrderController extends Controller
{
    public function index(Request $request)
    {
        $query = Order::with(['user', 'items']); // sem paymentMethod nem store

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

    public function show($id)
    {
        $order = Order::with(['user', 'items'])->findOrFail($id); // sem paymentMethod nem store
        return view('admin.orders.show', compact('order'));
    }

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
    
}
