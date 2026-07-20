<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Order;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;
use App\Models\PaymentMethod;
use App\Mail\PasswordChangedMail;

class UserController extends Controller
{
    // Painel do usuário
    public function dashboard()
    {
        $user = Auth::user();

        // 1. Busca os pedidos do usuário
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        // 2. BUSCA SEGURA DO HISTÓRICO (Compatível com MySQL antigo)

        // Primeiro: Pegamos apenas os IDs dos produtos que ele viu
        $productIds = \DB::table('product_views_history')->where('user_id', $user->id)->orderBy('updated_at', 'DESC')->limit(12)->pluck('product_id')->toArray();

        $userHistory = collect(); // Inicializa vazio

        if (!empty($productIds)) {
            // Segundo: Buscamos os produtos reais usando esses IDs
            $userHistory = \App\Models\Product::whereIn('products.id', $productIds)
                ->where('status', 1)
                ->where('is_outlet', false)
                ->with('brand')
                // Join para garantir a ordem cronológica exata do histórico
                ->join('product_views_history', 'products.id', '=', 'product_views_history.product_id')
                ->where('product_views_history.user_id', $user->id)
                ->orderBy('product_views_history.updated_at', 'DESC')
                ->select('products.*')
                ->get()
                ->unique('id'); // Garante que não repita se houver lixo no banco
        }

        return view('users.dashboard', compact('user', 'orders', 'userHistory'));
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
            'name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $user->id,
            'country' => 'nullable|string|max:100',
            'address' => 'nullable|string|max:255',
            'number' => 'nullable|string|max:20',
            'district' => 'nullable|string|max:255',
            'complement' => 'nullable|string|max:255',
            'city' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:255',
            'document' => 'nullable|string|max:255',
            'postal_code' => 'nullable|string|max:20',
            'cep' => 'nullable|string|max:20',
        ]);

        // 2. Coleta os dados básicos
        $data = $request->only(['name', 'email', 'country', 'address', 'number', 'district', 'complement', 'city', 'state', 'document', 'additional_info']);

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
        $data['already_registered'] = $request->already_registered == '1' || $request->already_registered == 'si' ? 1 : 0;

        // 7. Tentativa de Salvamento com Log de Erro
        try {
            $user->update($data);
            return back()->with('success', 'Perfil atualizado com sucesso!');
        } catch (\Exception $e) {
            // Retorna o erro exato do SQL se a migration ainda não tiver sido aplicada ou falhar
            return back()
                ->withErrors(['msg' => 'Erro no banco: ' . $e->getMessage()])
                ->withInput();
        }
    }

    public function editPassword()
    {
        return view('users.password');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => [
                'required',
                'confirmed',
                'different:current_password',
                'max:72',
                Password::min(8)->letters()->numbers(),
            ],
        ], [
            'current_password.required' => 'Informe sua senha atual.',
            'current_password.current_password' => 'A senha atual informada está incorreta.',
            'password.required' => 'Informe a nova senha.',
            'password.confirmed' => 'A confirmação da nova senha não confere.',
            'password.different' => 'A nova senha deve ser diferente da senha atual.',
            'password.min' => 'A nova senha deve ter pelo menos 8 caracteres.',
            'password.max' => 'A nova senha deve ter no máximo 72 caracteres.',
            'password.letters' => 'A nova senha deve conter pelo menos uma letra.',
            'password.numbers' => 'A nova senha deve conter pelo menos um número.',
        ]);

        /** @var \App\Models\User $user */
        $user = $request->user();
        $user->password = Hash::make($validated['password']);
        $user->setRememberToken(Str::random(60));
        $user->save();

        $request->session()->regenerate();

        try {
            Mail::to($user->email)->send(new PasswordChangedMail($user));
        } catch (\Throwable $exception) {
            Log::error('Não foi possível enviar o aviso de alteração de senha.', [
                'user_id' => $user->id,
                'exception' => $exception,
            ]);

            return redirect()
                ->route('user.password.edit')
                ->with('success', 'Senha alterada com sucesso!')
                ->with('warning', 'A senha foi atualizada, mas não conseguimos enviar o e-mail de confirmação agora.');
        }

        return redirect()
            ->route('user.password.edit')
            ->with('success', 'Senha alterada com sucesso! Enviamos uma confirmação para o seu e-mail.');
    }

    public function orders()
    {
        $user = Auth::user();
        $orders = Order::where('user_id', $user->id)->orderBy('created_at', 'desc')->get();

        return view('users.orders', compact('orders'));
    }

    public function showOrder($id)
    {
        $user = Auth::user();

        // Busca o pedido com os itens
        $order = Order::with(['items', 'receipt', 'cupon'])->where('id', $id)->where('user_id', $user->id)->firstOrFail();

        // BUSCA AS CONTAS BANCÁRIAS
        // Removi o 'status' para evitar o erro de coluna não encontrada
        $bankAccounts = PaymentMethod::whereNotNull('bank_details')->get();

        // PASSA AS DUAS VARIÁVEIS PARA A VIEW
        return view('users.order', compact('order', 'bankAccounts'));
    }

    // Os cupons são tratados por CuponUserController + CuponService (rotas user.cupons.*).

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
