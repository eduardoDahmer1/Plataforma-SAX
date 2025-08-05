<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;

class PaymentMethodController extends Controller
{
    public function index()
    {
        $methods = PaymentMethod::all();
        return view('admin.payments.index', compact('methods'));
    }

    public function create()
    {
        return view('admin.payments.create');
    }

    // store
    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,gateway',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'bank_details' => 'nullable|string', // Para detalhes da conta bancária
        ]);
    
        if ($data['type'] === 'bank') {
            // Para tipo bank, salva detalhes bancários
            $data['credentials'] = null;
            $data['bank_details'] = $request->input('bank_details'); // Salva os detalhes do banco
        } else {
            // Para tipo gateway, salva as credenciais
            $data['credentials'] = json_encode([
                'public_key' => $request->input('public_key'),
                'private_key' => $request->input('private_key'),
            ]);
            $data['bank_details'] = null; // Limpa os detalhes bancários
        }
    
        PaymentMethod::create($data);
    
        return redirect()->route('admin.payments.index')->with('success', 'Método de pagamento criado com sucesso!');
    }

    public function edit($id)
    {
        $method = PaymentMethod::findOrFail($id);

        // Como credentials já é array (por cast), pega direto
        $credentials = $method->credentials ?? [];

        $method->public_key = $credentials['public_key'] ?? '';
        $method->private_key = $credentials['private_key'] ?? '';

        return view('admin.payments.edit', compact('method'));
    }

    // update
    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);
    
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,gateway',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'bank_details' => 'nullable|string', // Para detalhes da conta bancária
        ]);
    
        $data['active'] = $request->has('active');
    
        if ($data['type'] === 'bank') {
            $data['credentials'] = null;
            $data['bank_details'] = $request->input('bank_details'); // Atualiza os detalhes bancários
        } else {
            $data['credentials'] = json_encode([
                'public_key' => $request->input('public_key'),
                'private_key' => $request->input('private_key'),
            ]);
            $data['bank_details'] = null; // Limpa os detalhes bancários
        }
    
        $method->update($data);
    
        return redirect()->route('admin.payments.index')->with('success', 'Método atualizado com sucesso');
    }

    public function destroy($id)
    {
        $method = PaymentMethod::findOrFail($id);
        $method->delete();

        return redirect()->route('admin.payments.index')->with('success', 'Método de pagamento excluído.');
    }

    public function toggleActive(Request $request, $id)
    {
        \Log::info('toggleActive chamado', ['id' => $id, 'active' => $request->input('active')]);

        $payment = PaymentMethod::findOrFail($id);

        $payment->active = $request->input('active') == 1 ? 1 : 2;

        $payment->save();

        return response()->json(['message' => 'Status atualizado com sucesso']);
    }
}
