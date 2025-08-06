<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Product;
use App\Models\Order;
use App\Models\OrderItem;

class CartController extends Controller
{
    public function add(Request $request)
    {
        // Pegando os dados do produto e a quantidade do request
        $productId = $request->input('product_id');
        $quantity = (int) $request->input('quantity', 1);
    
        // Encontrar o produto no banco de dados
        $product = Product::findOrFail($productId);
    
        // Recupera o carrinho atual da sessão
        $cart = session()->get('cart', []);
    
        // Definindo o nome do produto, priorizando o "name" e usando "external_name" caso não tenha "name"
        $productName = $product->name ?? $product->external_name ?? 'Produto';
    
        // Se o produto já existe no carrinho, apenas aumenta a quantidade
        if (isset($cart[$productId])) {
            $cart[$productId]['quantity'] += $quantity;
        } else {
            // Se o produto não existir, cria um novo item no carrinho
            $cart[$productId] = [
                'product_id' => $productId,
                'title' => $productName, // Usando o nome do produto (ou external_name)
                'slug' => $product->slug,   // Slug (se necessário para SEO ou links)
                'price' => $product->price, // Preço do produto
                'quantity' => $quantity,    // Quantidade do produto
            ];
        }
    
        // Se o usuário estiver autenticado
        if (auth()->check()) {
            // Cria ou encontra o pedido existente
            $order = Order::firstOrCreate(
                [
                    'user_id' => auth()->id(),
                    'status' => 'cart', // Status do pedido como "carrinho"
                ],
                [
                    'total' => 0,
                    'payment_method' => 'none', // Defina o método de pagamento inicial
                ]
            );
    
            // Atualiza ou cria o item no pedido
            $order->items()->updateOrCreate(
                [
                    'order_id' => $order->id,
                    'product_id' => $product->id
                ],
                [
                    'quantity' => $cart[$productId]['quantity'],
                    'price' => $product->price
                ]
            );
        }
    
        // Atualiza a sessão com o carrinho
        session()->put('cart', $cart);
    
        // Retorna para a página anterior com sucesso
        return back()->with('success', 'Produto adicionado ao carrinho!');
    }    

    // Mostrar o carrinho
    public function view()
    {
        $cart = session()->get('cart', []);
        return view('cart.view', compact('cart'));
    }

    // Atualizar quantidade do item no carrinho
    public function update(Request $request, $productId)
    {
        $quantity = (int) $request->input('quantity', 1);
    
        $cart = session()->get('cart', []);
    
        if (isset($cart[$productId])) {
            if ($quantity > 0) {
                $cart[$productId]['quantity'] = $quantity;
            } else {
                unset($cart[$productId]);
            }
            
            // Atualiza a sessão com o carrinho
            session()->put('cart', $cart);
    
            // Se o usuário estiver autenticado, atualiza o pedido no banco de dados
            if (auth()->check()) {
                $order = Order::where('user_id', auth()->id())
                              ->where('status', 'cart')
                              ->first();
    
                if ($order) {
                    // Atualiza a quantidade e o preço do item
                    $orderItem = $order->items()->where('product_id', $productId)->first();
                    if ($orderItem) {
                        $orderItem->update([
                            'quantity' => $cart[$productId]['quantity'],
                            'price' => $cart[$productId]['price'], // Pode ser necessário recalcular o preço aqui
                        ]);
                    }
    
                    // Recalcula o total do pedido
                    $total = $order->items->sum(function($item) {
                        return $item->price * $item->quantity;
                    });
                    
                    // Atualiza o total no pedido
                    $order->update(['total' => $total]);
                }
            }
    
            return back()->with('success', 'Carrinho atualizado!');
        }
    
        return back()->with('error', 'Produto não encontrado no carrinho.');
    }

    // Remover item do carrinho
    public function remove($productId)
    {
        $cart = session()->get('cart', []);
        if (isset($cart[$productId])) {
            unset($cart[$productId]);
            session()->put('cart', $cart);
    
            if (auth()->check()) {
                $order = Order::where('user_id', auth()->id())
                    ->where('status', 'cart')
                    ->first();
    
                if ($order) {
                    // Remove o item do banco
                    $order->items()->where('product_id', $productId)->delete();
    
                    // Se não tiver mais itens, remove o pedido inteiro
                    if ($order->items()->count() === 0) {
                        $order->delete();
                    }
                }
            }
    
            return back()->with('success', 'Produto removido do carrinho!');
        }
    
        return back()->with('error', 'Produto não encontrado no carrinho.');
    }
}
