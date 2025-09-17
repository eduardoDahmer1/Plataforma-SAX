<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\UserCupon;
use App\Models\Cupon;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    // Painel do usuário
    public function dashboard()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('users.dashboard', compact('user', 'orders'));
    }

    public function edit()
    {
        $user = Auth::user();
        return view('users.profile', compact('user'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
    
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
            'document'         => 'nullable|string|max:255',
        ]);
    
        $request->merge(['phone_country' => str_replace('+', '', $request->phone_country)]);
    
        $user->update($request->only([
            'name', 'email', 'phone_country', 'phone_number',
            'address', 'cep', 'city', 'state', 'additional_info', 'document'
        ]));

        return back()->with('success', 'Perfil atualizado com sucesso!');
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)
                        ->orderBy('created_at', 'desc')
                        ->get();

        return view('users.orders', compact('orders')); 
    }

    public function showOrder($id)
    {
        $user = Auth::user();
        $order = Order::with('items')
                      ->where('id', $id)
                      ->where('user_id', $user->id)
                      ->firstOrFail();

        return view('users.order', compact('order')); 
    }

    // Aplicar cupom no checkout
    public function applyCupon(Request $request)
    {
        $request->validate(['cupon' => 'required|string']);
        $user = Auth::user();
    
        $cupon = Cupon::where('codigo', $request->cupon)
                      ->where('data_inicio', '<=', now())
                      ->where('data_final', '>=', now())
                      ->first();
    
        if (!$cupon) {
            return back()->withErrors(['cupon' => 'Cupom inválido ou expirado.']);
        }
    
        $cartTotal = session()->get('cart_total', 0);
    
        if ($cupon->valor_minimo && $cartTotal < $cupon->valor_minimo) {
            return back()->withErrors(['cupon' => "O pedido deve ser mínimo de {$cupon->valor_minimo} para este cupom."]);
        }
    
        if ($cupon->valor_maximo && $cartTotal > $cupon->valor_maximo) {
            return back()->withErrors(['cupon' => "O pedido deve ser máximo de {$cupon->valor_maximo} para este cupom."]);
        }
    
        // Calcula desconto
        $discount = $cupon->tipo === 'percentual'
            ? ($cartTotal * $cupon->montante) / 100
            : $cupon->montante;
    
        // Salva na sessão (mantém compatibilidade)
        session()->put('cupon', [
            'id' => $cupon->id,
            'codigo' => $cupon->codigo,
            'discount' => $discount,
        ]);
    
        // Salva também no banco para histórico
        UserCupon::updateOrCreate(
            ['user_id' => $user->id, 'cupon_id' => $cupon->id],
            ['desconto' => round($discount, 2)]
        );
    
        return back()->with('success', "Cupom '{$cupon->codigo}' aplicado! Desconto: {$discount}");
    }

    // Remover cupom
    public function removeCupon()
    {
        session()->forget('cupon');
        return back()->with('success', 'Cupom removido com sucesso!');
    }

    public function cupons()
    {
        $user = Auth::user();
    
        $cupons = UserCupon::with('cupon')
                    ->where('user_id', $user->id)
                    ->get();
    
        return view('users.cupon', compact('cupons'));
    }

    public function checkoutSuccess(Request $request)
    {
        session()->forget('cart');

        $whatsappNumber = '595984167575';
        return redirect()->away("https://wa.me/{$whatsappNumber}?text=Pedido finalizado");
    }

    public function destroy(Request $request)
    {
        $request->validate(['password' => ['required']]);
        $user = Auth::user();

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
