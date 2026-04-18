<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use App\Models\UserCupon;
use App\Models\Cupon;
use Illuminate\Support\Facades\Hash;
use App\Models\PaymentMethod;

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
        /** @var \App\Models\User $user */
        $user = Auth::user();

        // 1. Validação (Aumentei o max de country para 100 conforme sua nova migration)
        $request->validate([
            'name'          => 'required|string|max:255',
            'email'         => 'required|email|max:255|unique:users,email,' . $user->id,
            'country'       => 'nullable|string|max:100',
            'address'       => 'nullable|string|max:255',
            'number'        => 'nullable|string|max:20',
            'district'      => 'nullable|string|max:255',
            'complement'    => 'nullable|string|max:255',
            'city'          => 'nullable|string|max:255',
            'state'         => 'nullable|string|max:255',
            'document'      => 'nullable|string|max:255',
            'postal_code'   => 'nullable|string|max:20',
            'cep'           => 'nullable|string|max:20',
        ]);

        // 2. Coleta os dados básicos
        $data = $request->only([
            'name', 'email', 'country', 'address', 'number', 
            'district', 'complement', 'city', 'state', 'document', 'additional_info'
        ]);

        // 3. Sincroniza o CEP / Postal Code
        if ($request->filled('postal_code')) {
            $data['cep'] = $request->postal_code;
        } elseif ($request->filled('cep')) {
            $data['cep'] = $request->cep;
        }

        // 4. Ajuste do Telefone (Remove o + se existir)
        if ($request->filled('phone_country')) {
            $data['phone_country'] = str_replace('+', '', $request->phone_country);
        }
        if ($request->filled('phone_number')) {
            $data['phone_number'] = $request->phone_number;
        }

        // 5. Ajuste de Compatibilidade para Integrações (Dica de Ouro do print)
        // Isso garante que campos de entrega também sejam preenchidos
        $data['shipping_address'] = $request->address;
        $data['shipping_address_number'] = $request->number;
        $data['shipping_complement'] = $request->complement;
        $data['shipping_district'] = $request->district;
        $data['shipping_city'] = $request->city;
        $data['shipping_state'] = $request->state;
        $data['shipping_postal_code'] = $data['cep'] ?? null;

        // 6. Ajuste do 'already_registered' (booleano)
        $data['already_registered'] = ($request->already_registered == '1' || $request->already_registered == 'si') ? 1 : 0;

        // 7. Tentativa de Salvamento com Log de Erro
        try {
            $user->update($data);
            return back()->with('success', 'Perfil atualizado com sucesso!');
        } catch (\Exception $e) {
            // Retorna o erro exato do SQL se a migration ainda não tiver sido aplicada ou falhar
            return back()->withErrors(['msg' => 'Erro no banco: ' . $e->getMessage()])->withInput();
        }
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

        // Busca o pedido com os itens
        $order = Order::with('items')
                    ->where('id', $id)
                    ->where('user_id', $user->id)
                    ->firstOrFail();

        // BUSCA AS CONTAS BANCÁRIAS
        // Removi o 'status' para evitar o erro de coluna não encontrada
        $bankAccounts = PaymentMethod::whereNotNull('bank_details')
                                    ->get();

        // PASSA AS DUAS VARIÁVEIS PARA A VIEW
        return view('users.order', compact('order', 'bankAccounts'));
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
