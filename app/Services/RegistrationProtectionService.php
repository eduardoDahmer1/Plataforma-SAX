<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class RegistrationProtectionService
{
    private const SESSION_KEY = 'registration_protection.tokens';

    public function issue(Request $request): string
    {
        $now = now()->timestamp;
        $tokens = collect($request->session()->get(self::SESSION_KEY, []))
            ->filter(fn ($issuedAt) => is_numeric($issuedAt) && (int) $issuedAt >= $now - 7200)
            ->take(-4)
            ->all();
        $token = Str::random(48);
        $tokens[hash('sha256', $token)] = $now;
        $request->session()->put(self::SESSION_KEY, $tokens);

        return $token;
    }

    public function validationError(Request $request): ?string
    {
        if (filled($request->input('company_website'))) {
            $this->logRejection($request, 'honeypot');

            return 'Não foi possível validar o cadastro. Atualize a página e tente novamente.';
        }

        $token = trim((string) $request->input('_registration_token'));
        $issuedAt = $request->session()->get(self::SESSION_KEY.'.'.hash('sha256', $token));
        $elapsed = is_numeric($issuedAt) ? now()->timestamp - (int) $issuedAt : null;
        $minimumSeconds = max(0, (int) config('services.turnstile.minimum_fill_seconds', 2));

        if ($token === '' || $elapsed === null || $elapsed < $minimumSeconds || $elapsed > 7200) {
            $this->logRejection($request, 'invalid-form-token');

            return 'O formulário expirou ou foi enviado rápido demais. Atualize a página e tente novamente.';
        }

        if (! config('services.turnstile.enabled')) {
            return null;
        }

        $turnstileToken = trim((string) $request->input('cf-turnstile-response'));
        $secret = trim((string) config('services.turnstile.secret_key'));
        if ($turnstileToken === '' || $secret === '') {
            $this->logRejection($request, 'missing-turnstile-token');

            return 'Conclua a verificação de segurança antes de criar a conta.';
        }

        try {
            $result = Http::asForm()
                ->connectTimeout(3)
                ->timeout(6)
                ->post('https://challenges.cloudflare.com/turnstile/v0/siteverify', [
                    'secret' => $secret,
                    'response' => $turnstileToken,
                    'remoteip' => $this->visitorIp($request),
                ])
                ->json();
        } catch (\Throwable $exception) {
            report($exception);
            $this->logRejection($request, 'turnstile-unavailable');

            return 'A verificação de segurança está temporariamente indisponível. Tente novamente em instantes.';
        }

        if (! data_get($result, 'success') || data_get($result, 'action') !== 'register') {
            $this->logRejection($request, 'turnstile-rejected');

            return 'A verificação de segurança não foi aceita. Tente novamente.';
        }

        return null;
    }

    public function consume(Request $request): void
    {
        $token = trim((string) $request->input('_registration_token'));
        if ($token !== '') {
            $request->session()->forget(self::SESSION_KEY.'.'.hash('sha256', $token));
        }
    }

    public function suspiciousNameReason(string $name): ?string
    {
        $normalized = Str::lower(Str::ascii($name));
        $spamTerms = [
            'http://', 'https://', 'www.', '.com/', '.net/', '.org/',
            'promo code', 'bonus code', 'welcome bonus', 'claim a', 'reward',
            'balance notice', 'account alert', 'casino', 'crypto', 'depositing a cent',
        ];

        if (Str::contains($normalized, $spamTerms)) {
            return 'O nome informado contém link ou texto promocional.';
        }
        if (mb_strlen($name) > 100 || preg_match('/[\r\n\t]/u', $name)) {
            return 'Informe apenas seu nome completo, sem mensagens adicionais.';
        }
        if (preg_match('/(?:\*\s*){3,}|[$€£]\s*\d|\b\d{4,}\b/u', $name)) {
            return 'Informe apenas seu nome completo.';
        }

        return null;
    }

    private function visitorIp(Request $request): string
    {
        $cloudflareIp = trim((string) $request->header('CF-Connecting-IP'));

        return filter_var($cloudflareIp, FILTER_VALIDATE_IP) ? $cloudflareIp : (string) $request->ip();
    }

    private function logRejection(Request $request, string $reason): void
    {
        Log::notice('Cadastro bloqueado pela proteção antiabuso.', [
            'reason' => $reason,
            'ip_hash' => hash('sha256', $this->visitorIp($request)),
        ]);
    }
}
