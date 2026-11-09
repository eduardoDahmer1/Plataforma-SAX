<?php

namespace App\Services;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use RuntimeException;

class OpenAIResponseClient
{
    /** A bounded recovery budget shared by catalog and attribute generation. */
    public function create(string $apiKey, array $body): array
    {
        $started = microtime(true);
        // Leave time for validation and persistence before the existing 150s worker timeout.
        $budget = max(5, min(125, (int) config('services.openai.total_timeout', 120)));
        $attempts = max(1, min(3, (int) config('services.openai.max_attempts', 3)));
        $tokenCeiling = max((int) $body['max_output_tokens'], (int) config('services.openai.retry_max_output_tokens', 30000));
        $lastError = new RuntimeException('No fue posible completar la consulta a la IA.');

        for ($attempt = 1; $attempt <= $attempts; $attempt++) {
            $remaining = $budget - (microtime(true) - $started);
            if ($remaining < 3) break;
            $retry = false;
            $delay = 0.3;
            $metadata = ['attempt' => $attempt, 'model' => $body['model'], 'max_output_tokens' => $body['max_output_tokens']];
            try {
                $response = Http::withToken($apiKey)->acceptJson()->asJson()
                    ->connectTimeout(min(10, $remaining))
                    ->timeout(min(max(1, (int) config('services.openai.timeout', 90)), $remaining))
                    ->post(rtrim((string) config('services.openai.base_url'), '/').'/responses', $body);
                $metadata['request_id'] = $response->header('x-request-id');
                $metadata['http_status'] = $response->status();
                $payload = $response->json();
                if (! $response->successful()) {
                    $code = data_get($payload, 'error.code');
                    $retry = in_array($response->status(), [408, 409, 429, 500, 502, 503, 504], true)
                        && ! in_array($code, ['insufficient_quota', 'billing_hard_limit_reached'], true);
                    $retryAfter = $response->header('retry-after');
                    if (is_numeric($retryAfter)) $delay = max($delay, (float) $retryAfter);
                    $lastError = new RuntimeException(match ($response->status()) {
                        401, 403 => 'La clave de OpenAI no es válida o no tiene permisos.',
                        429 => $retry ? 'La IA está recibiendo demasiadas solicitudes. No se pudo completar la generación.' : 'La cuenta de OpenAI no tiene cuota disponible.',
                        default => 'OpenAI no pudo generar la propuesta (HTTP '.$response->status().').',
                    }, previous: $response->toException());
                    $metadata['reason'] = $code ?: 'http_error';
                } elseif (! is_array($payload)) {
                    $retry = true;
                    $metadata['reason'] = 'invalid_response';
                    $lastError = new RuntimeException('OpenAI devolvió una respuesta inválida.');
                } elseif (($payload['status'] ?? null) === 'incomplete') {
                    $reason = data_get($payload, 'incomplete_details.reason');
                    $metadata += ['reason' => $reason, 'response_id' => $payload['id'] ?? null,
                        'output_tokens' => data_get($payload, 'usage.output_tokens'),
                        'reasoning_tokens' => data_get($payload, 'usage.output_tokens_details.reasoning_tokens')];
                    $retry = $reason === 'max_output_tokens' && $body['max_output_tokens'] < $tokenCeiling;
                    if ($retry) $body['max_output_tokens'] = min($tokenCeiling, max(16000, $body['max_output_tokens'] * 2));
                    $lastError = new RuntimeException(match ($reason) {
                        'max_output_tokens' => 'La IA alcanzó el límite de generación antes de completar la propuesta.',
                        'content_filter' => 'La IA no pudo completar la propuesta por un filtro de contenido.',
                        default => 'La IA devolvió una respuesta incompleta.',
                    });
                } elseif (($payload['status'] ?? null) === 'failed') {
                    $metadata['reason'] = data_get($payload, 'error.code');
                    $retry = in_array($metadata['reason'], ['server_error', 'rate_limit_exceeded'], true);
                    $lastError = new RuntimeException('OpenAI no pudo completar la generación.');
                } else {
                    Log::info('Consulta IA completada.', $metadata + ['response_id' => $payload['id'] ?? null,
                        'elapsed_ms' => (int) ((microtime(true) - $started) * 1000), 'output_tokens' => data_get($payload, 'usage.output_tokens')]);
                    return $payload;
                }
            } catch (ConnectionException $exception) {
                $retry = true;
                $metadata['reason'] = 'connection_or_timeout';
                $lastError = new RuntimeException('La consulta a la IA superó el tiempo de espera o no pudo conectarse.', previous: $exception);
            }
            $retry = $retry && $attempt < $attempts && $budget - (microtime(true) - $started) > $delay + 3;
            Log::warning('Consulta IA no completada.', $metadata + ['retrying' => $retry, 'elapsed_ms' => (int) ((microtime(true) - $started) * 1000)]);
            if (! $retry) break;
            if (! app()->runningUnitTests()) usleep((int) ($delay * 1000000));
        }

        throw $lastError;
    }
}
