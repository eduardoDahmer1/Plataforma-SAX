<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentMethod;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Carbon;

class BancardV2Service
{
    /**
     * Gera um shop_process_id único para o pedido.
     */
    public function generateShopProcessId(Order $order): string
    {
        $orderPart = str_pad((string) ($order->id % 1000), 3, '0', STR_PAD_LEFT);

        for ($attempt = 0; $attempt < 5; $attempt++) {
            // 10 (unix time) + 3 (order trace) + 2 (random) = 15 dígitos, sempre começa não zero.
            $timePart = (string) time();
            $randomPart = str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
            $shopProcessId = $timePart . $orderPart . $randomPart;

            if (!Order::where('shop_process_id', $shopProcessId)->exists()) {
                return $shopProcessId;
            }

            usleep(50000); // Aguarda 50ms antes de tentar novamente
        }

        // Fallback: força um novo timestamp
        return (string) (time() + 1) . $orderPart . str_pad((string) random_int(0, 99), 2, '0', STR_PAD_LEFT);
    }
    private const CURRENCY = 'PYG';
    private const API_TIMEOUT_SECONDS = 20;

    private string $baseUrl;
    private string $apiUrl;
    private string $publicKey;
    private string $privateKey;

    public function __construct(string $baseUrl, string $publicKey, string $privateKey)
    {
        $this->baseUrl = $baseUrl;
        $this->apiUrl = "{$baseUrl}/vpos/api/0.3";
        $this->publicKey = trim($publicKey);
        $this->privateKey = trim($privateKey);
    }

    public static function fromPaymentMethod(?PaymentMethod $gateway): self
    {
        $credentials = self::normalizeArray($gateway?->credentials);
        $settings = self::normalizeArray($gateway?->settings);

        $isSandbox = (bool) ($settings['sandbox'] ?? true);
        $baseUrl = $isSandbox
            ? 'https://vpos.infonet.com.py:8888'
            : 'https://vpos.infonet.com.py';

        return new self(
            $baseUrl,
            (string) ($credentials['public_key'] ?? ''),
            (string) ($credentials['private_key'] ?? '')
        );
    }

    public function isConfigured(): bool
    {
        return $this->publicKey !== '' && $this->privateKey !== '';
    }

    public function getCheckoutJsUrl(): string
    {
        return "{$this->baseUrl}/checkout/javascript/dist/bancard-checkout-4.0.0.js";
    }

    public function buildSingleBuyToken(string $shopProcessId, string $amount, string $currency = self::CURRENCY): string
    {
        return md5($this->privateKey . $shopProcessId . $amount . $currency);
    }

    public function buildGetConfirmationToken(string $shopProcessId): string
    {
        return md5($this->privateKey . $shopProcessId . 'get_confirmation');
    }

    public function buildConfirmToken(string $shopProcessId, string $amount, string $currency = self::CURRENCY): string
    {
        return md5($this->privateKey . $shopProcessId . 'confirm' . $amount . $currency);
    }

    public function buildSingleBuyPayload(
        Order $order,
        string $shopProcessId,
        string $amount,
        string $currency,
        string $token,
        string $returnUrl,
        string $cancelUrl,
        string $description
    ): array {
        return [
            'public_key' => $this->publicKey,
            'operation' => [
                'token' => $token,
                'shop_process_id' => $shopProcessId,
                'amount' => $amount,
                'currency' => $currency,
                'description' => $description,
                'return_url' => $returnUrl,
                'cancel_url' => $cancelUrl,
            ],
        ];
    }

    public function buildOrderDescription(Order $order): string
    {
        $description = 'PEDIDO-' . $order->id;

        return substr($description, 0, 20);
    }

    public function postSingleBuy(array $payload): array
    {
        $response = Http::timeout(self::API_TIMEOUT_SECONDS)->post("{$this->apiUrl}/single_buy", $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json() ?: [],
        ];
    }

    public function extractApiErrorMessage(array $data): string
    {
        $message = data_get($data, 'messages.0.dsc');

        return $message
            ? 'Erro na API Bancard V2: ' . $message
            : 'Erro na API Bancard V2.';
    }

    public function buildGetConfirmationPayload(string $shopProcessId): array
    {
        return [
            'public_key' => $this->publicKey,
            'operation' => [
                'token' => $this->buildGetConfirmationToken($shopProcessId),
                'shop_process_id' => $shopProcessId,
            ],
        ];
    }

    public function postGetConfirmation(string $shopProcessId): array
    {
        $payload = $this->buildGetConfirmationPayload($shopProcessId);
        $response = Http::timeout(self::API_TIMEOUT_SECONDS)->post("{$this->apiUrl}/single_buy/confirmations", $payload);

        return [
            'ok' => $response->successful(),
            'status' => $response->status(),
            'data' => $response->json() ?: [],
        ];
    }

    public function fetchSingleBuyConfirmation(string $shopProcessId): ?array
    {
        if ($shopProcessId === '' || !$this->isConfigured()) {
            return null;
        }

        try {
            $queryToken = $this->buildGetConfirmationToken($shopProcessId);
            $confirmationRequest = $this->postGetConfirmation($shopProcessId);
            $data = $confirmationRequest['data'];

            Log::info('Bancard V2 get confirmation', [
                'shop_process_id' => $shopProcessId,
                'token' => $queryToken,
                'response_code' => data_get($data, 'confirmation.response_code'),
            ]);

            if (!$confirmationRequest['ok'] || strtolower((string) data_get($data, 'status', '')) !== 'success') {
                return null;
            }

            $confirmation = data_get($data, 'confirmation');

            return is_array($confirmation) ? $confirmation : null;
        } catch (\Throwable $e) {
            Log::warning('Bancard V2 get confirmation failed', [
                'shop_process_id' => $shopProcessId,
                'message' => $e->getMessage(),
            ]);

            return null;
        }
    }

    public function isApprovedConfirmation(?array $confirmation): bool
    {
        if (!$confirmation) {
            return false;
        }

        $responseCode = (string) data_get($confirmation, 'response_code', '');
        $response = strtoupper((string) data_get($confirmation, 'response', ''));

        // response_code is the source of truth when present.
        if ($responseCode !== '') {
            return $responseCode === '00';
        }

        return $response === 'S';
    }

    public function buildSuccessDisplayPayload(string $shopProcessId, ?array $confirmation, ?Order $order): array
    {
        return [
            'transactionDateTime' => $this->resolveTransactionDateTime($confirmation),
            'shopProcessId' => $shopProcessId,
            'amount' => $this->resolveTransactionAmount($confirmation, $order),
            'responseDescription' => $this->resolveResponseDescription($confirmation),
        ];
    }

    public function buildErrorDisplayPayload(string $shopProcessId, ?array $confirmation, ?Order $order, string $status): array
    {
        return [
            'transactionDateTime' => $this->resolveTransactionDateTime($confirmation),
            'shopProcessId' => $shopProcessId,
            'amount' => $this->resolveTransactionAmount($confirmation, $order),
            'responseDescription' => $this->resolveErrorResponseDescription($confirmation, $status),
        ];
    }

    public function hasValidCallbackToken(array $payload): bool
    {
        $shopProcessId = $this->extractShopProcessId($payload);
        $amount = $this->normalizeAmountForToken(data_get($payload, 'operation.amount'));
        $currency = (string) (data_get($payload, 'operation.currency') ?? self::CURRENCY);
        $receivedToken = (string) (data_get($payload, 'operation.token') ?? '');

        if (!$shopProcessId || $amount === null || $receivedToken === '') {
            return false;
        }

        $expectedToken = $this->buildConfirmToken((string) $shopProcessId, $amount, $currency);

        return hash_equals($expectedToken, $receivedToken);
    }

    public function extractShopProcessId(array $payload): ?string
    {
        return data_get($payload, 'operation.shop_process_id')
            ?? data_get($payload, 'shop_process_id')
            ?? data_get($payload, 'shop_process_id_alias');
    }

    private function resolveTransactionDateTime(?array $confirmation): string
    {
        $dateCandidates = [
            data_get($confirmation, 'date'),
            data_get($confirmation, 'datetime'),
            data_get($confirmation, 'transaction_date'),
            data_get($confirmation, 'created_at'),
            data_get($confirmation, 'operation_datetime'),
        ];

        foreach ($dateCandidates as $candidate) {
            if (!is_string($candidate) || trim($candidate) === '') {
                continue;
            }

            try {
                return Carbon::parse($candidate)->format('d/m/Y H:i:s');
            } catch (\Throwable) {
                continue;
            }
        }

        return now()->format('d/m/Y H:i:s');
    }

    private function resolveTransactionAmount(?array $confirmation, ?Order $order): string
    {
        $amount = data_get($confirmation, 'amount');

        if ($amount === null && $order) {
            $amount = $order->total;
        }

        return $this->formatAmount($amount ?? 0);
    }

    private function resolveResponseDescription(?array $confirmation): string
    {
        $description = data_get($confirmation, 'response_description')
            ?? data_get($confirmation, 'description')
            ?? data_get($confirmation, 'message')
            ?? '';

        $text = trim((string) $description);

        return $text !== '' ? $text : 'Transação recebida e em processamento.';
    }

    private function resolveErrorResponseDescription(?array $confirmation, string $status): string
    {
        $responseCode = trim((string) data_get($confirmation, 'response_code', ''));

        $mappedDescription = match ($responseCode) {
            '51' => 'No aprobada - insuficiencia de fondos.',
            '33' => 'Tarjeta vencida.',
            '55' => 'Clave inválida.',
            '05' => 'Tarjeta inhabilitada.',
            '12' => 'Transacción inválida.',
            '15' => 'Emisor inexistente o tarjeta no habilitada.',
            '94' => 'Transacción duplicada.',
            '17' => 'Operación cancelada por el cliente.',
            default => '',
        };

        if ($mappedDescription !== '') {
            return $mappedDescription;
        }

        $fromGateway = trim((string) (data_get($confirmation, 'response_description')
            ?? data_get($confirmation, 'description')
            ?? data_get($confirmation, 'message')
            ?? ''));

        if ($fromGateway !== '') {
            return $fromGateway;
        }

        if ($status !== '') {
            return 'Pagamento não aprovado (' . $status . ').';
        }

        return 'Pagamento não aprovado.';
    }

    private function formatAmount(mixed $amount): string
    {
        return number_format((float) $amount, 2, '.', '');
    }

    private function normalizeAmountForToken(mixed $amount): ?string
    {
        if ($amount === null) {
            return null;
        }

        if (is_int($amount) || is_float($amount)) {
            return number_format((float) $amount, 2, '.', '');
        }

        $value = trim((string) $amount);
        if ($value === '') {
            return null;
        }

        $normalized = str_replace(',', '.', $value);
        if (!is_numeric($normalized)) {
            return null;
        }

        return number_format((float) $normalized, 2, '.', '');
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

        if (is_array($decoded)) {
            return $decoded;
        }

        if (!is_string($decoded) || $decoded === '') {
            return [];
        }

        $decodedTwice = json_decode($decoded, true);

        return is_array($decodedTwice) ? $decodedTwice : [];
    }
}
