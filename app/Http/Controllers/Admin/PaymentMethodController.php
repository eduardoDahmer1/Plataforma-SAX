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

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,gateway',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
        ]);
    
        // Preenche as credenciais no formato adequado
        $data['credentials'] = [
            'public_key' => $request->input('public_key'),
            'private_key' => $request->input('private_key'),
        ];
    
        // Se não existir public_key e private_key, deixa o campo vazio
        if (!$data['credentials']['public_key'] && !$data['credentials']['private_key']) {
            $data['credentials'] = null;
        }
    
        // Cria o método de pagamento
        PaymentMethod::create($data);
    
        return redirect()->route('admin.payments.index')->with('success', 'Método de pagamento criado com sucesso!');
    }

    public function edit($id)
    {
        $method = PaymentMethod::findOrFail($id);
        return view('admin.payments.edit', compact('method'));
    }

    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,gateway',
            'details' => 'nullable|string',
            'active' => 'boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
        ]);

        $data['active'] = $request->has('active');

        $data['credentials'] = [
            'public_key' => $request->input('public_key'),
            'private_key' => $request->input('private_key'),
        ];

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
        $method = PaymentMethod::findOrFail($id);
        $method->active = $request->input('active') ? 1 : 0;
        $method->save();

        return response()->json(['message' => 'Status atualizado com sucesso']);
    }
}
