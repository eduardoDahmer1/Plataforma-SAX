<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use RuntimeException;

class GeoNamesService
{
    private string $baseUrl;
    private string $username;

    public function __construct()
    {
        $this->baseUrl = rtrim((string) config('services.geonames.base_url', 'https://secure.geonames.org'), '/');
        $this->username = trim((string) config('services.geonames.username'));
    }

    public function isConfigured(): bool
    {
        return $this->username !== '';
    }

    public function countries(string $locale = 'pt'): array
    {
        return Cache::remember('geonames:countries:'.substr($locale, 0, 2), now()->addDays(7), function () use ($locale): array {
            $data = $this->request('/countryInfoJSON', [
                'lang' => substr($locale, 0, 2),
            ]);

            return collect($data['geonames'] ?? [])
                ->map(function (array $country): array {
                    $iso2 = strtoupper((string) ($country['countryCode'] ?? ''));
                    $phone = trim(explode(',', (string) ($country['phone'] ?? ''))[0]);

                    return [
                        'iso2' => $iso2,
                        'name' => (string) ($country['countryName'] ?? $iso2),
                        'calling_code' => preg_replace('/\D+/', '', $phone),
                    ];
                })
                ->filter(fn (array $country): bool => preg_match('/^[A-Z]{2}$/', $country['iso2']) === 1)
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        });
    }

    public function subdivisions(string $countryCode, string $locale = 'pt'): array
    {
        $countryCode = $this->countryCode($countryCode);

        return Cache::remember("geonames:subdivisions:{$countryCode}:".substr($locale, 0, 2), now()->addDays(30), function () use ($countryCode, $locale): array {
            $data = $this->request('/searchJSON', [
                'country' => $countryCode,
                'featureCode' => 'ADM1',
                'maxRows' => 1000,
                'style' => 'FULL',
                'lang' => substr($locale, 0, 2),
            ]);

            return collect($data['geonames'] ?? [])
                ->map(fn (array $place): array => [
                    'geoname_id' => $place['geonameId'] ?? null,
                    'code' => (string) ($place['adminCode1'] ?? ''),
                    'name' => (string) ($place['name'] ?? $place['toponymName'] ?? ''),
                ])
                ->filter(fn (array $place): bool => $place['code'] !== '' && $place['name'] !== '')
                ->unique('code')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        });
    }

    public function cities(string $countryCode, string $adminCode1, string $locale = 'pt'): array
    {
        $countryCode = $this->countryCode($countryCode);
        $adminCode1 = trim($adminCode1);

        if ($adminCode1 === '' || mb_strlen($adminCode1) > 20) {
            throw new RuntimeException('A subdivisão informada é inválida.');
        }

        $cacheKey = 'geonames:cities:'.$countryCode.':'.md5($adminCode1).':'.substr($locale, 0, 2);

        return Cache::remember($cacheKey, now()->addDays(14), function () use ($countryCode, $adminCode1, $locale): array {
            $data = $this->request('/searchJSON', [
                'country' => $countryCode,
                'adminCode1' => $adminCode1,
                'featureClass' => 'P',
                'maxRows' => 1000,
                'orderby' => 'population',
                'style' => 'FULL',
                'lang' => substr($locale, 0, 2),
            ]);

            return collect($data['geonames'] ?? [])
                ->map(fn (array $place): array => [
                    'geoname_id' => $place['geonameId'] ?? null,
                    'name' => (string) ($place['name'] ?? $place['toponymName'] ?? ''),
                    'region' => (string) ($place['adminName1'] ?? ''),
                    'latitude' => isset($place['lat']) ? (float) $place['lat'] : null,
                    'longitude' => isset($place['lng']) ? (float) $place['lng'] : null,
                ])
                ->filter(fn (array $place): bool => $place['name'] !== '')
                ->unique(fn (array $place): string => mb_strtolower($place['name']))
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        });
    }

    private function request(string $path, array $query): array
    {
        if (! $this->isConfigured()) {
            throw new RuntimeException('O usuário do GeoNames não foi configurado.');
        }

        $response = Http::acceptJson()
            ->timeout((int) config('services.geonames.timeout', 12))
            ->retry(2, 300)
            ->get($this->baseUrl.$path, $query + ['username' => $this->username]);

        $data = $response->json();

        if ($response->failed() || isset($data['status'])) {
            throw new RuntimeException((string) data_get($data, 'status.message', 'Não foi possível consultar o GeoNames.'));
        }

        return is_array($data) ? $data : [];
    }

    private function countryCode(string $countryCode): string
    {
        $countryCode = strtoupper(trim($countryCode));

        if (preg_match('/^[A-Z]{2}$/', $countryCode) !== 1) {
            throw new RuntimeException('O país deve estar no formato ISO de duas letras.');
        }

        return $countryCode;
    }
}
