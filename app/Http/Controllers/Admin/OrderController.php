<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Cart;
use Illuminate\Support\Facades\Session;


class OrderController extends Controller
{
    public function index()
    {
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
    
        // Deleta itens do pedido
        foreach ($order->items as $item) {
            $item->delete();
        }
    
        // Deleta o pedido
        $order->delete();
    
        // Limpa carrinho da sessão do usuário — só vai funcionar se for o mesmo usuário logado!
        // Se for no admin e você não é o mesmo usuário, isso não afeta.
        Session::forget('cart');
    
        return redirect()->route('admin.orders.index')->with('success', 'Pedido excluído com sucesso.');
    }
}
