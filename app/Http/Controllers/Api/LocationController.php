<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\GeoNamesService;
use App\Support\CountryCallingCodes;
use App\Support\CountrySupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Throwable;
use App\Services\StoreControlService;

class LocationController extends Controller
{
    public function countries(Request $request, GeoNamesService $geoNames, StoreControlService $controls): JsonResponse
    {
        $locale = substr((string) $request->input('lang', app()->getLocale()), 0, 2);
        if (! $controls->enabled('geonames')) {
            $countries = collect(CountrySupport::countries($locale))
                ->whereIn('iso2', ['BR', 'PY'])
                ->map(fn (array $country): array => $country + ['calling_code' => CountryCallingCodes::for($country['iso2'])])
                ->values();

            return response()->json(['success' => true, 'geonames_available' => false, 'data' => $countries]);
        }
        $remoteResult = Cache::remember(
            'geonames:countries:resolved:'.$locale,
            now()->addMinutes(15),
            function () use ($geoNames, $locale): array {
                try {
                    return [
                        'available' => true,
                        'countries' => collect($geoNames->countries($locale))->keyBy('iso2')->all(),
                    ];
                } catch (Throwable $exception) {
                    Log::warning('GeoNames indisponível; usando catálogo local de países.', [
                        'message' => $exception->getMessage(),
                    ]);

                    return ['available' => false, 'countries' => []];
                }
            }
        );
        $remote = $remoteResult['countries'];

        $countries = collect(CountrySupport::countries($locale))
            ->map(function (array $country) use ($remote): array {
                $geoNamesCountry = $remote[$country['iso2']] ?? [];

                $remoteCallingCode = trim((string) ($geoNamesCountry['calling_code'] ?? ''));

                return $country + [
                    'calling_code' => $remoteCallingCode !== ''
                        ? $remoteCallingCode
                        : CountryCallingCodes::for($country['iso2']),
                ];
            })
            ->values();

        return response()->json([
            'success' => true,
            'geonames_available' => $remoteResult['available'],
            'data' => $countries,
        ]);
    }

    public function subdivisions(Request $request, GeoNamesService $geoNames, StoreControlService $controls): JsonResponse
    {
        if (! $controls->enabled('geonames')) return $this->disabledResponse();
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
        ]);

        return $this->geoNamesResponse(fn (): array => $geoNames->subdivisions(
            $validated['country'],
            app()->getLocale()
        ));
    }

    public function cities(Request $request, GeoNamesService $geoNames, StoreControlService $controls): JsonResponse
    {
        if (! $controls->enabled('geonames')) return $this->disabledResponse();
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'admin_code' => ['required', 'string', 'max:20'],
        ]);

        return $this->geoNamesResponse(fn (): array => $geoNames->cities(
            $validated['country'],
            $validated['admin_code'],
            app()->getLocale()
        ));
    }

    private function geoNamesResponse(callable $callback): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $callback()]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível carregar as localidades agora. Digite os dados manualmente ou tente novamente.',
            ], 502);
        }
    }

    private function disabledResponse(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'code' => 'geonames_disabled',
            'message' => 'As localidades internacionais ainda não estão habilitadas.',
        ], 503);
    }
}
