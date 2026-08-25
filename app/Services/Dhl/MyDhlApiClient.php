<?php

namespace App\Services\Dhl;

use App\Exceptions\DhlApiException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Throwable;

final class MyDhlApiClient
{
    private string $baseUrl;

    private string $apiKey;

    private string $apiSecret;

    private string $accountNumber;

    private int $timeout;

    private int $retryTimes;

    private array $settings;

    public function __construct(?DhlSettingsService $settingsService = null)
    {
        $this->settings = $settingsService?->resolved() ?? (array) config('services.dhl', []);
        $this->baseUrl = rtrim((string) ($this->settings['base_url'] ?? ''), '/');
        $this->apiKey = trim((string) ($this->settings['api_key'] ?? ''));
        $this->apiSecret = trim((string) ($this->settings['api_secret'] ?? ''));
        $this->accountNumber = trim((string) ($this->settings['account_number'] ?? ''));
        $this->timeout = max(1, (int) ($this->settings['timeout'] ?? 20));
        $this->retryTimes = max(1, (int) ($this->settings['retry_times'] ?? 2));
    }

    public function isConfigured(): bool
    {
        return (bool) ($this->settings['enabled'] ?? false)
            && $this->baseUrl !== ''
            && $this->environmentMatchesBaseUrl()
            && $this->apiKey !== ''
            && $this->apiSecret !== ''
            && $this->accountNumber !== '';
    }

    public function missingConfiguration(): array
    {
        $missing = [];

        if (! (bool) ($this->settings['enabled'] ?? false)) {
            $missing[] = 'DHL_ENABLED';
        }
        if ($this->baseUrl === '') {
            $missing[] = 'DHL_BASE_URL';
        } elseif (! $this->environmentMatchesBaseUrl()) {
            $missing[] = 'DHL_BASE_URL/DHL_ENVIRONMENT';
        }
        if ($this->apiKey === '') {
            $missing[] = 'DHL_API_KEY';
        }
        if ($this->apiSecret === '') {
            $missing[] = 'DHL_API_SECRET';
        }
        if ($this->accountNumber === '') {
            $missing[] = 'DHL_ACCOUNT_NUMBER';
        }

        return $missing;
    }

    public function accountNumber(): string
    {
        return $this->accountNumber;
    }

    public function configurationSummary(): array
    {
        return [
            'enabled' => (bool) ($this->settings['enabled'] ?? false),
            'environment' => (string) ($this->settings['environment'] ?? 'sandbox'),
            'base_url' => $this->baseUrl,
            'account' => $this->mask($this->accountNumber),
            'credentials_present' => $this->apiKey !== '' && $this->apiSecret !== '',
            'missing' => $this->missingConfiguration(),
        ];
    }

    /**
     * Consulta de tarifas para remessas com uma ou mais peças.
     */
    public function rates(array $payload): array
    {
        return $this->request('POST', '/rates', $payload, true);
    }

    /**
     * Consulta leve de produtos DHL disponíveis para uma remessa de uma peça.
     */
    public function products(array $query): array
    {
        return $this->request('GET', '/products', array_merge($query, [
            'accountNumber' => $this->accountNumber,
        ]), true);
    }

    /**
     * Valida disponibilidade de coleta/entrega em um endereço.
     */
    public function validateAddress(array $query): array
    {
        return $this->request('GET', '/address-validate', $query, true);
    }

    private function request(string $method, string $path, array $data, bool $retryable): array
    {
        $this->assertConfigured();

        $requestId = (string) Str::uuid();
        $startedAt = microtime(true);

        try {
            $pending = $this->pendingRequest($requestId);

            if ($retryable && $this->retryTimes > 1) {
                $pending = $pending->retry(
                    $this->retryTimes,
                    300,
                    static fn (Throwable $exception, PendingRequest $request): bool => $exception instanceof ConnectionException
                        || ($exception->getCode() >= 500 && $exception->getCode() <= 599),
                    false
                );
            }

            $response = strtoupper($method) === 'GET'
                ? $pending->get($this->baseUrl.$path, $data)
                : $pending->send(strtoupper($method), $this->baseUrl.$path, ['json' => $data]);
        } catch (ConnectionException $exception) {
            $this->logRequest('warning', $path, null, $requestId, $startedAt);

            throw new DhlApiException(
                'Não foi possível conectar ao ambiente da DHL.',
                null,
                $requestId,
                ['reason' => 'connection_failed']
            );
        }

        $this->logRequest($response->successful() ? 'info' : 'warning', $path, $response->status(), $requestId, $startedAt);

        try {
            $decoded = $response->json();
        } catch (Throwable) {
            $decoded = null;
        }
        $data = is_array($decoded) ? $decoded : [];

        if ($response->failed()) {
            throw $this->apiException($response, $data, $requestId);
        }

        if (! is_array($decoded)) {
            throw new DhlApiException(
                'A DHL devolveu uma resposta inesperada. Tente novamente em alguns instantes.',
                $response->status(),
                $requestId,
                ['reason' => 'invalid_response_format']
            );
        }

        return $data;
    }

    private function pendingRequest(string $requestId): PendingRequest
    {
        return Http::acceptJson()
            ->asJson()
            ->withBasicAuth($this->apiKey, $this->apiSecret)
            ->withHeaders([
                'Message-Reference' => $requestId,
                'Message-Reference-Date' => now('UTC')->format('D, d M Y H:i:s').' GMT',
                'User-Agent' => 'SAX-Department-Store-DHL/1.0',
            ])
            ->timeout($this->timeout);
    }

    private function assertConfigured(): void
    {
        if ($this->isConfigured()) {
            return;
        }

        throw new DhlApiException(
            'A integração DHL ainda não está completamente configurada.',
            null,
            null,
            ['missing' => $this->missingConfiguration()]
        );
    }

    private function apiException(Response $response, array $data, string $requestId): DhlApiException
    {
        $fallback = match (true) {
            $response->status() === 429 => 'A DHL recebeu muitas solicitações. Aguarde alguns segundos e tente novamente.',
            $response->status() >= 500 => 'O ambiente da DHL está temporariamente indisponível. Tente novamente em alguns instantes.',
            default => 'A DHL rejeitou a solicitação.',
        };
        $message = (string) ($data['detail'] ?? $data['title'] ?? data_get($data, 'additionalDetails.0') ?? $fallback);
        $message = $this->friendlyApiMessage($message);

        return new DhlApiException(
            mb_substr($message, 0, 1000),
            $response->status(),
            $requestId,
            [
                'title' => $data['title'] ?? null,
                'instance' => $data['instance'] ?? null,
                'additional_details' => $data['additionalDetails'] ?? [],
            ]
        );
    }

    private function friendlyApiMessage(string $message): string
    {
        if (preg_match('/Invalid Postcode Format\.\s*Valid formats:\s*([A-Z]{2})\s*-\s*([^\r\n]+)/i', $message, $matches) !== 1) {
            return $message;
        }

        $countryCode = strtoupper($matches[1]);
        $format = trim($matches[2]);

        if (preg_match('/^9+\((\d+)\)$/', $format, $digits) === 1) {
            return sprintf(
                'Código postal inválido para %s. A DHL exige exatamente %d dígitos.',
                $countryCode,
                (int) $digits[1]
            );
        }

        return sprintf(
            'Código postal inválido para %s. Formato aceito pela DHL: %s.',
            $countryCode,
            $format
        );
    }

    private function logRequest(string $level, string $path, ?int $status, string $requestId, float $startedAt): void
    {
        Log::log($level, 'DHL MyDHL API request', [
            'environment' => (string) ($this->settings['environment'] ?? 'sandbox'),
            'path' => $path,
            'status' => $status,
            'request_id' => $requestId,
            'duration_ms' => (int) round((microtime(true) - $startedAt) * 1000),
        ]);
    }

    private function mask(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return str_repeat('*', max(0, strlen($value) - 4)).substr($value, -4);
    }

    private function environmentMatchesBaseUrl(): bool
    {
        $environment = strtolower(trim((string) ($this->settings['environment'] ?? 'sandbox')));

        return match ($environment) {
            'sandbox' => $this->baseUrl === 'https://express.api.dhl.com/mydhlapi/test',
            'production' => $this->baseUrl === 'https://express.api.dhl.com/mydhlapi',
            default => false,
        };
    }
}
