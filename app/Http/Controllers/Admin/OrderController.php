<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;

class OrderController extends Controller
{
    public function index()
    {
        // Busca os pedidos com info do usuário relacionado
        $orders = Order::with('user')->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.orders.index', compact('orders'));
    }

    public function show($id)
    {
        $order = Order::with('user', 'items')->findOrFail($id);
        return view('admin.orders.show', compact('order'));
    }

    public function destroy($id)
    {
        $order = Order::with('items')->findOrFail($id);

        // Exclui os itens do pedido
        foreach ($order->items as $item) {
            $item->delete();
        }

        // Exclui o pedido
        $order->delete();

        return redirect()->route('admin.orders.index')->with('success', 'Pedido excluído com sucesso.');
    }

}
