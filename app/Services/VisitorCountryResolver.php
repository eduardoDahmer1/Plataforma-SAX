<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class VisitorCountryResolver
{
    private const COUNTRY_NAMES = [
        'AR' => 'Argentina',
        'BR' => 'Brasil',
        'PY' => 'Paraguai',
        'US' => 'Estados Unidos',
    ];

    public function resolve(Request $request): array
    {
        if ($country = $this->fromRegisteredUser($request)) {
            return $country + ['source' => 'profile'];
        }

        foreach (['CF-IPCountry', 'CloudFront-Viewer-Country', 'X-Country-Code'] as $header) {
            if ($country = $this->fromCode($request->header($header))) {
                return $country + ['source' => 'header'];
            }
        }

        $ip = $request->ip();
        if (! $ip || filter_var($ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE) === false) {
            return [];
        }

        if (! config('services.ip_geolocation.enabled', true)) {
            return [];
        }

        $cacheKey = 'analytics.country.'.hash_hmac('sha256', $ip, config('app.key'));

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($ip): array {
            try {
                $url = str_replace('{ip}', rawurlencode($ip), config('services.ip_geolocation.url'));
                $response = Http::acceptJson()
                    ->connectTimeout(1)
                    ->timeout((int) config('services.ip_geolocation.timeout', 2))
                    ->get($url);

                if (! $response->successful() || $response->json('success') === false) {
                    return [];
                }

                $country = $this->fromCode($response->json('country_code'));
                if (! $country) {
                    return [];
                }

                $name = trim((string) $response->json('country'));
                if ($name !== '') {
                    $country['name'] = mb_substr($name, 0, 80);
                }

                return $country + ['source' => 'ip'];
            } catch (Throwable) {
                return [];
            }
        });
    }

    private function fromRegisteredUser(Request $request): ?array
    {
        $user = $request->user();
        if (! $user) {
            return null;
        }

        foreach ([$user->country, $user->location_country] as $value) {
            if ($country = $this->fromValue($value)) {
                return $country;
            }
        }

        return null;
    }

    private function fromValue(?string $value): ?array
    {
        $normalized = mb_strtoupper(trim((string) $value));
        $aliases = [
            'ARGENTINA' => 'AR',
            'BRASIL' => 'BR',
            'BRAZIL' => 'BR',
            'PARAGUAI' => 'PY',
            'PARAGUAY' => 'PY',
            'ESTADOS UNIDOS' => 'US',
            'UNITED STATES' => 'US',
            'USA' => 'US',
            'EUA' => 'US',
        ];

        return $this->fromCode($aliases[$normalized] ?? $normalized);
    }

    private function fromCode(?string $value): ?array
    {
        $code = strtoupper(trim((string) $value));
        if (! preg_match('/^[A-Z]{2}$/', $code) || in_array($code, ['XX', 'T1'], true)) {
            return null;
        }

        return ['code' => $code, 'name' => self::COUNTRY_NAMES[$code] ?? $code];
    }
}
