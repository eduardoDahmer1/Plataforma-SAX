<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
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
            'sandbox' => 'nullable|boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'bank_details' => 'nullable|string', // Para detalhes da conta bancária
        ]);

        $data['active'] = $request->has('active');
        $isBancardV2 = $this->isBancardV2Gateway($data['name'], $data['type']);
    
        if ($data['type'] === 'bank') {
            // Para tipo bank, salva detalhes bancários
            $data['credentials'] = null;
            $data['settings'] = null;
            $data['bank_details'] = $request->input('bank_details'); // Salva os detalhes do banco
        } else {
            // Para tipo gateway, salva as credenciais
            $data['credentials'] = [
                'public_key' => $request->input('public_key'),
                'private_key' => $request->input('private_key'),
            ];
            $data['settings'] = $isBancardV2 ? ['sandbox' => $request->boolean('sandbox')] : null;
            $data['bank_details'] = null; // Limpa os detalhes bancários
        }

        unset($data['public_key'], $data['private_key'], $data['sandbox']);
    
        PaymentMethod::create($data);
    
        return redirect()->route('admin.payments.index')->with('success', 'Método de pagamento criado com sucesso!');
    }

    public function edit($id)
    {
        $method = PaymentMethod::findOrFail($id);

        $credentials = $this->normalizeCredentials($method->credentials);
        $settings = $this->normalizeSettings($method->settings);

        $method->public_key = $credentials['public_key'] ?? '';
        $method->private_key = $credentials['private_key'] ?? '';
        $method->credentials = $credentials;
        $method->settings = $settings;
        $method->sandbox = (bool) ($settings['sandbox'] ?? true);
        $method->show_sandbox_control = $this->isBancardV2Gateway($method->name, $method->type);

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
            'sandbox' => 'nullable|boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'bank_details' => 'nullable|string', // Para detalhes da conta bancária
        ]);
    
        $data['active'] = $request->has('active');
        $isBancardV2 = $this->isBancardV2Gateway($data['name'], $data['type']);
    
        if ($data['type'] === 'bank') {
            $data['credentials'] = null;
            $data['settings'] = null;
            $data['bank_details'] = $request->input('bank_details'); // Atualiza os detalhes bancários
        } else {
            $data['credentials'] = [
                'public_key' => $request->input('public_key'),
                'private_key' => $request->input('private_key'),
            ];
            $data['settings'] = $isBancardV2
                ? array_merge(
                    $this->normalizeSettings($method->settings),
                    ['sandbox' => $request->boolean('sandbox')]
                )
                : $method->settings;
            $data['bank_details'] = null; // Limpa os detalhes bancários
        }

        unset($data['public_key'], $data['private_key'], $data['sandbox']);
    
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
        Log::info('toggleActive chamado', ['id' => $id, 'active' => $request->input('active')]);

        $payment = PaymentMethod::findOrFail($id);

        $payment->active = $request->boolean('active');

        $payment->save();

        return response()->json(['message' => 'Status atualizado com sucesso']);
    }

    private function normalizeCredentials(mixed $credentials): array
    {
        if (is_array($credentials)) {
            return $credentials;
        }

        if (!is_string($credentials) || $credentials === '') {
            return [];
        }

        $decoded = json_decode($credentials, true);

        if (is_array($decoded)) {
            return $decoded;
        }

        if (!is_string($decoded) || $decoded === '') {
            return [];
        }

        $decodedTwice = json_decode($decoded, true);

        return is_array($decodedTwice) ? $decodedTwice : [];
    }

    private function normalizeSettings(mixed $settings): array
    {
        if (is_array($settings)) {
            return $settings;
        }

        if (!is_string($settings) || $settings === '') {
            return [];
        }

        $decoded = json_decode($settings, true);

        return is_array($decoded) ? $decoded : [];
    }

    private function isBancardV2Gateway(string $name, string $type): bool
    {
        return $type === 'gateway' && mb_strtolower(trim($name)) === 'bancard v2';
    }
}
