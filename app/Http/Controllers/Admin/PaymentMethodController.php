<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Log;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Crypt;

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
        $this->normalizeGatewayName($request);

        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,gateway',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'sandbox' => 'nullable|boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'bank_details' => 'nullable|string', // Para detalhes da conta bancária
            'sandbox_email' => 'nullable|email',
            'sandbox_password' => 'nullable|string|max:500',
            'sandbox_merchant_id' => 'nullable|integer|min:1',
            'production_email' => 'nullable|email',
            'production_password' => 'nullable|string|max:500',
            'production_merchant_id' => 'nullable|integer|min:1',
            'sandbox_base_url' => 'nullable|url|max:500',
            'production_base_url' => 'nullable|url|max:500',
            'operation_code' => 'nullable|integer|min:1',
            'beneficiary' => 'nullable|string|max:255',
        ]);

        $data['active'] = $request->has('active');
        $isBancardV2 = $this->isBancardV2Gateway($data['name'], $data['type']);
        $isRendixPix = $this->isRendixPixGateway($data['name'], $data['type']);
        if ($isRendixPix) {
            $this->validateRendixConfiguration($request);
        }
    
        if ($data['type'] === 'bank') {
            // Para tipo bank, salva detalhes bancários
            $data['credentials'] = null;
            $data['settings'] = null;
            $data['bank_details'] = $request->input('bank_details'); // Salva os detalhes do banco
        } else {
            // Para tipo gateway, salva as credenciais
            if ($isRendixPix) {
                $data['credentials'] = $this->rendixCredentials($request);
                $data['settings'] = $this->rendixSettings($request);
            } else {
                $data['credentials'] = [
                    'public_key' => $request->input('public_key'),
                    'private_key' => $request->input('private_key'),
                ];
                $data['settings'] = $isBancardV2 ? ['sandbox' => $request->boolean('sandbox')] : null;
            }
            $data['bank_details'] = null; // Limpa os detalhes bancários
        }

        $this->removeVirtualFields($data);
    
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
        $method->is_rendix_pix = $this->isRendixPixGateway($method->name, $method->type);
        $method->sandbox_email = $credentials['sandbox_email'] ?? '';
        $method->sandbox_merchant_id = $credentials['sandbox_merchant_id'] ?? '';
        $method->production_email = $credentials['production_email'] ?? '';
        $method->production_merchant_id = $credentials['production_merchant_id'] ?? '';
        $method->has_sandbox_password = !empty($credentials['sandbox_password']);
        $method->has_production_password = !empty($credentials['production_password']);
        $method->sandbox_base_url = $settings['sandbox_base_url'] ?? \App\Services\RendixPixService::SANDBOX_URL;
        $method->production_base_url = $settings['production_base_url'] ?? '';
        $method->operation_code = $settings['operation_code'] ?? 1;
        $method->beneficiary = $settings['beneficiary'] ?? 'SAX Department Store';

        return view('admin.payments.edit', compact('method'));
    }

    // update
    public function update(Request $request, $id)
    {
        $method = PaymentMethod::findOrFail($id);

        $this->normalizeGatewayName($request);
    
        $data = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bank,gateway',
            'description' => 'nullable|string',
            'active' => 'boolean',
            'sandbox' => 'nullable|boolean',
            'public_key' => 'nullable|string',
            'private_key' => 'nullable|string',
            'bank_details' => 'nullable|string', // Para detalhes da conta bancária
            'sandbox_email' => 'nullable|email',
            'sandbox_password' => 'nullable|string|max:500',
            'sandbox_merchant_id' => 'nullable|integer|min:1',
            'production_email' => 'nullable|email',
            'production_password' => 'nullable|string|max:500',
            'production_merchant_id' => 'nullable|integer|min:1',
            'sandbox_base_url' => 'nullable|url|max:500',
            'production_base_url' => 'nullable|url|max:500',
            'operation_code' => 'nullable|integer|min:1',
            'beneficiary' => 'nullable|string|max:255',
        ]);
    
        $data['active'] = $request->has('active');
        $isBancardV2 = $this->isBancardV2Gateway($data['name'], $data['type']);
        $isRendixPix = $this->isRendixPixGateway($data['name'], $data['type']);
        if ($isRendixPix) {
            $this->validateRendixConfiguration(
                $request,
                $this->normalizeCredentials($method->credentials),
            );
        }
    
        if ($data['type'] === 'bank') {
            $data['credentials'] = null;
            $data['settings'] = null;
            $data['bank_details'] = $request->input('bank_details'); // Atualiza os detalhes bancários
        } else {
            if ($isRendixPix) {
                $data['credentials'] = $this->rendixCredentials(
                    $request,
                    $this->normalizeCredentials($method->credentials),
                );
                $data['settings'] = array_merge(
                    $this->normalizeSettings($method->settings),
                    $this->rendixSettings($request),
                );
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
            }
            $data['bank_details'] = null; // Limpa os detalhes bancários
        }

        $this->removeVirtualFields($data);
    
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

        $activate = $request->boolean('active');
        if (
            $activate
            && $this->isRendixPixGateway($payment->name, $payment->type)
            && !\App\Services\RendixPixService::fromPaymentMethod($payment)->isConfigured()
        ) {
            return response()->json([
                'message' => 'Configure as credenciais do ambiente Rendix selecionado antes de ativar o Pix.',
            ], 422);
        }

        $payment->active = $activate;

        $payment->save();

        return response()->json(['message' => 'Status atualizado com sucesso']);
    }

    private function normalizeGatewayName(Request $request): void
    {
        $name = trim((string) $request->input('name'));

        if ($name === '') {
            $choice = trim((string) $request->input('gateway_choice'));
            $name = $choice === '__custom__'
                ? trim((string) $request->input('custom_gateway_name'))
                : $choice;
        }

        if ($name !== '') {
            $request->merge(['name' => $name]);
        }
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

    private function isRendixPixGateway(string $name, string $type): bool
    {
        return $type === 'gateway'
            && in_array(mb_strtolower(trim($name)), ['rendix pix', 'pix rendix', 'pix'], true);
    }

    private function rendixCredentials(Request $request, array $existing = []): array
    {
        $credentials = [
            'sandbox_email' => trim((string) $request->input('sandbox_email')),
            'sandbox_merchant_id' => trim((string) $request->input('sandbox_merchant_id')),
            'production_email' => trim((string) $request->input('production_email')),
            'production_merchant_id' => trim((string) $request->input('production_merchant_id')),
        ];

        foreach (['sandbox', 'production'] as $environment) {
            $field = $environment . '_password';
            $password = (string) $request->input($field, '');
            $credentials[$field] = $password !== ''
                ? Crypt::encryptString($password)
                : ($existing[$field] ?? null);
        }

        return $credentials;
    }

    private function rendixSettings(Request $request): array
    {
        return [
            'sandbox' => $request->boolean('rendix_sandbox'),
            'sandbox_base_url' => rtrim(
                trim((string) $request->input('sandbox_base_url', \App\Services\RendixPixService::SANDBOX_URL)),
                '/'
            ),
            'production_base_url' => rtrim(trim((string) $request->input('production_base_url', '')), '/'),
            'operation_code' => max(1, $request->integer('operation_code', 1)),
            'beneficiary' => trim((string) $request->input('beneficiary', 'SAX Department Store')),
        ];
    }

    private function removeVirtualFields(array &$data): void
    {
        unset(
            $data['public_key'],
            $data['private_key'],
            $data['sandbox'],
            $data['rendix_sandbox'],
            $data['sandbox_email'],
            $data['sandbox_password'],
            $data['sandbox_merchant_id'],
            $data['production_email'],
            $data['production_password'],
            $data['production_merchant_id'],
            $data['sandbox_base_url'],
            $data['production_base_url'],
            $data['operation_code'],
            $data['beneficiary'],
        );
    }

    private function validateRendixConfiguration(Request $request, array $existing = []): void
    {
        if (!$request->has('active')) {
            return;
        }

        $prefix = $request->boolean('rendix_sandbox') ? 'sandbox' : 'production';
        $labels = $prefix === 'sandbox' ? 'Sandbox' : 'Produção';
        $errors = [];

        if (!filled($request->input($prefix . '_email'))) {
            $errors[$prefix . '_email'] = "Informe o e-mail Rendix de {$labels} antes de ativar o Pix.";
        }
        if (!filled($request->input($prefix . '_merchant_id'))) {
            $errors[$prefix . '_merchant_id'] = "Informe o MerchantId Rendix de {$labels} antes de ativar o Pix.";
        }
        if (!filled($request->input($prefix . '_password')) && empty($existing[$prefix . '_password'])) {
            $errors[$prefix . '_password'] = "Informe a senha Rendix de {$labels} antes de ativar o Pix.";
        }
        if ($prefix === 'production' && !filled($request->input('production_base_url'))) {
            $errors['production_base_url'] = 'Informe a URL base de produção fornecida pela Rendix.';
        }

        if ($errors !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages($errors);
        }
    }
}
