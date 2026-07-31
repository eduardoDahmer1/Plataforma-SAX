<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\PaymentTransaction;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class RendixPixService
{
    public const PROVIDER = 'rendix_pix';
    public const SANDBOX_URL = 'https://apisandbox.agillitas.com.br';

    private const TIMEOUT_SECONDS = 20;

    public function __construct(
        private string $baseUrl,
        private string $email,
        private string $password,
        private string $merchantId,
        private int $operationCode = 1,
        private string $beneficiary = 'SAX Department Store',
        private string $environment = 'sandbox',
    ) {
        $this->baseUrl = rtrim(trim($this->baseUrl), '/');
    }

    public static function gateway(): ?PaymentMethod
    {
        return PaymentMethod::query()
            ->where('type', 'gateway')
            ->where(function ($query) {
                $query->whereRaw('LOWER(name) = ?', ['rendix pix'])
                    ->orWhereRaw('LOWER(name) = ?', ['pix rendix'])
                    ->orWhereRaw('LOWER(name) = ?', ['pix']);
            })
            ->first();
    }

    public static function fromPaymentMethod(?PaymentMethod $gateway, ?string $environment = null): self
    {
        $credentials = self::normalizeArray($gateway?->credentials);
        $settings = self::normalizeArray($gateway?->settings);
        $sandbox = $environment !== null
            ? $environment === 'sandbox'
            : (bool) ($settings['sandbox'] ?? true);
        $prefix = $sandbox ? 'sandbox' : 'production';

        $password = (string) ($credentials[$prefix . '_password'] ?? '');
        if ($password !== '') {
            try {
                $password = Crypt::decryptString($password);
            } catch (\Throwable) {
                // Compatibilidade com configurações inseridas antes da criptografia.
            }
        }

        $baseUrl = $sandbox
            ? (string) ($settings['sandbox_base_url'] ?? self::SANDBOX_URL)
            : (string) ($settings['production_base_url'] ?? '');

        return new self(
            $baseUrl,
            trim((string) ($credentials[$prefix . '_email'] ?? '')),
            $password,
            trim((string) ($credentials[$prefix . '_merchant_id'] ?? '')),
            max(1, (int) ($settings['operation_code'] ?? 1)),
            trim((string) ($settings['beneficiary'] ?? 'SAX Department Store')),
            $sandbox ? 'sandbox' : 'production',
        );
    }

    public function isConfigured(): bool
    {
        return $this->baseUrl !== ''
            && $this->email !== ''
            && $this->password !== ''
            && $this->merchantId !== '';
    }

    public function environment(): string
    {
        return $this->environment;
    }

    public function createSale(Order $order, PaymentTransaction $transaction, string $webhookUrl): array
    {
        $payload = [
            'merchantId' => is_numeric($this->merchantId) ? (int) $this->merchantId : $this->merchantId,
            'purchase' => (float) number_format((float) $order->total, 2, '.', ''),
            'cpf' => preg_replace('/\D+/', '', (string) $order->document),
            'cnpj' => '',
            'customerAcceptedTerms' => (bool) $order->rendix_terms_accepted_at,
            'controlNumber' => $transaction->control_number,
            'phone' => $this->normalizeBrazilianPhone((string) $order->phone),
            'email' => (string) $order->email,
            'isExternal' => true,
            'urlWebhook' => $webhookUrl,
            'currencyCode' => 'USD',
            'operationCode' => $this->operationCode,
            'beneficiary' => $this->beneficiary,
        ];

        return $this->authorizedRequest('post', '/efx/v2/external/sell', $payload);
    }

    public function getSale(string $saleId): array
    {
        return $this->authorizedRequest('get', '/efx/v1/external/sell/' . rawurlencode($saleId));
    }

    public function getTermsDocument(): array
    {
        return $this->authorizedRequest('get', '/efx/gerenciador/venda/v1/vendas/termo');
    }

    public function extractSaleData(array $response): array
    {
        $data = data_get($response, 'data');

        if (!is_array($data)) {
            return [];
        }

        $data['saleId'] = $data['saleId']
            ?? $data['saleID']
            ?? $data['SaleId']
            ?? null;
        $data['pixCopyPaste'] = $data['pixCopyPaste']
            ?? $data['pixCopyPast']
            ?? $data['PixCopyPast']
            ?? $data['pixCopiaCola']
            ?? $data['PixCopiaCola']
            ?? null;
        $data['qrCodeBase64'] = $data['qrCodeBase64']
            ?? $data['QrCodeBase64']
            ?? null;
        $data['qrCodeExpiration'] = $data['qrCodeExpiration']
            ?? $data['QrCodeExpiration']
            ?? $data['expiracao']
            ?? $data['Expiracao']
            ?? null;

        return $data;
    }

    public function providerStatus(array $saleData): string
    {
        return trim((string) data_get($saleData, 'status', ''));
    }

    public function localStatus(string $providerStatus): string
    {
        return match ($providerStatus) {
            '1' => 'created',
            '2' => 'pending',
            '3' => 'paid',
            '6' => 'expired',
            '8', '9', '11' => 'failed',
            '12' => 'refunded',
            default => 'pending',
        };
    }

    public function statusDescription(string $providerStatus): string
    {
        return match ($providerStatus) {
            '1' => __('messages.pix_status_created'),
            '2' => __('messages.pix_status_pending'),
            '3' => __('messages.pix_status_paid'),
            '6' => __('messages.pix_status_expired'),
            '8' => __('messages.pix_status_cpf_mismatch'),
            '9' => __('messages.pix_status_cpf_limit'),
            '11' => __('messages.pix_status_processing_error'),
            '12' => __('messages.pix_status_refunded'),
            default => __('messages.pix_status_waiting_confirmation'),
        };
    }

    public function extractWebhookSaleId(array $payload): ?string
    {
        foreach (['saleId', 'saleID', 'SaleId', 'id', 'data.saleId', 'data.saleID', 'data.SaleId', 'data.id', 'sale.id', 'transaction.saleId'] as $path) {
            $value = data_get($payload, $path);
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    public function extractWebhookControlNumber(array $payload): ?string
    {
        foreach (['controlNumber', 'data.controlNumber', 'sale.controlNumber', 'transaction.controlNumber'] as $path) {
            $value = data_get($payload, $path);
            if ($value !== null && trim((string) $value) !== '') {
                return trim((string) $value);
            }
        }

        return null;
    }

    private function authorizedRequest(string $method, string $path, array $payload = []): array
    {
        $token = $this->accessToken();
        $request = Http::acceptJson()
            ->asJson()
            ->withToken($token)
            ->timeout(self::TIMEOUT_SECONDS);

        $response = $method === 'get'
            ? $request->get($this->baseUrl . $path, $payload)
            : $request->{$method}($this->baseUrl . $path, $payload);

        if ($response->status() === 401) {
            Cache::forget($this->tokenCacheKey());
            $request = Http::acceptJson()
                ->asJson()
                ->withToken($this->accessToken())
                ->timeout(self::TIMEOUT_SECONDS);
            $response = $method === 'get'
                ? $request->get($this->baseUrl . $path, $payload)
                : $request->{$method}($this->baseUrl . $path, $payload);
        }

        return [
            'ok' => $response->successful() && (bool) data_get($response->json(), 'success', true),
            'status' => $response->status(),
            'data' => $response->json() ?: [],
        ];
    }

    private function accessToken(): string
    {
        if (!$this->isConfigured()) {
            throw new \RuntimeException(__('messages.rendix_credentials_not_configured'));
        }

        $cacheKey = $this->tokenCacheKey();
        $cached = Cache::get($cacheKey);
        if (is_string($cached) && $cached !== '') {
            return $cached;
        }

        $response = Http::acceptJson()
            ->asJson()
            ->timeout(self::TIMEOUT_SECONDS)
            ->post($this->baseUrl . '/efx/v2/external/login', [
                'email' => $this->email,
                'password' => $this->password,
            ]);

        $body = $response->json() ?: [];
        $token = trim((string) data_get($body, 'data.token', ''));

        if (!$response->successful() || !(bool) data_get($body, 'success', false) || $token === '') {
            Log::warning('Rendix Pix authentication failed', [
                'environment' => $this->environment,
                'http_status' => $response->status(),
                'message' => data_get($body, 'message'),
            ]);
            throw new \RuntimeException(__('messages.rendix_authentication_failed'));
        }

        $ttlMilliseconds = max(120000, (int) data_get($body, 'data.expirationInMilliSeconds', 3600000));
        Cache::put($cacheKey, $token, now()->addMilliseconds($ttlMilliseconds - 60000));

        return $token;
    }

    private function tokenCacheKey(): string
    {
        return 'rendix_pix_token:' . hash('sha256', $this->environment . '|' . $this->baseUrl . '|' . $this->email);
    }

    private function normalizeBrazilianPhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);
        if (str_starts_with($digits, '55')) {
            return '+' . $digits;
        }

        return '+55' . ltrim($digits, '0');
    }

    private static function normalizeArray(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        if (!is_string($value) || $value === '') {
            return [];
        }

        $decoded = json_decode($value, true);

        return is_array($decoded) ? $decoded : [];
    }
}
