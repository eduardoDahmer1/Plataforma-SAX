<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Mostra o painel do usuário
    public function dashboard()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('users.dashboard', compact('user', 'orders'));
    }

    // Mostra o formulário de edição do perfil
    public function edit()
    {
        $user = Auth::user();
        return view('users.profile', compact('user'));
    }

    // Atualiza o perfil do usuário
    public function update(Request $request)
    {
        $user = Auth::user();
    
        // Validação
        $request->validate([
            'name'             => 'required|string|max:255',
            'email'            => 'required|email|max:255|unique:users,email,' . $user->id,
            'phone_country'    => 'nullable|string|max:5',
            'phone_number'     => 'nullable|string|max:20',
            'address'          => 'nullable|string|max:255',
            'cep'              => 'nullable|string|max:20',
            'city'             => 'nullable|string|max:255',
            'state'            => 'nullable|string|max:255',
            'additional_info'  => 'nullable|string|max:255',
            'document'  => 'nullable|string|max:255',
        ]);
    
        // Remove o "+" do código do país se quiser
        $request->merge([
            'phone_country' => str_replace('+', '', $request->phone_country),
        ]);
    
        // Atualiza o usuário
        $user->update([
            'name'              => $request->name,
            'email'             => $request->email,
            'phone_country'     => $request->phone_country,
            'phone_number'      => $request->phone_number,
            'already_registered'=> $request->already_registered ?? $user->already_registered,
            'address'           => $request->address,
            'cep'               => $request->cep,
            'city'              => $request->city,
            'state'             => $request->state,
            'additional_info'   => $request->additional_info,
            'document'=> $request->document, 
        ]);
    
        return back()->with('success', 'Perfil atualizado com sucesso!');
    }
      
    // Lista todos os pedidos do usuário
    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('users.order', compact('orders')); 
    }

    // Mostra um pedido específico
    public function showOrder($id)
    {
        $user = Auth::user();
        $order = Order::with('items') // carrega itens
                      ->where('id', $id)
                      ->where('user_id', $user->id)
                      ->firstOrFail();

        return view('users.order', compact('order')); 
    }

    // Redireciona após checkout (sucesso)
    public function checkoutSuccess(Request $request)
    {
        session()->forget('cart');

        // Apenas exemplo de redirecionamento para WhatsApp
        $whatsappNumber = '595984167575';
        return redirect()->away("https://wa.me/{$whatsappNumber}?text=Pedido finalizado");
    }

    public function destroy(Request $request)
    {
        $request->validate([
            'password' => ['required'],
        ]);

        $user = Auth::user();

        // Confirma senha
        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Senha incorreta']);
        }

        Auth::logout();

        $user->delete();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/')->with('status', 'Conta deletada com sucesso.');
    }
}
