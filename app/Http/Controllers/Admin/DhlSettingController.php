<?php

namespace App\Http\Controllers\Admin;

use App\Exceptions\DhlApiException;
use App\Http\Controllers\Controller;
use App\Models\DhlPackage;
use App\Services\Dhl\DhlRateService;
use App\Services\Dhl\DhlSettingsService;
use App\Services\Dhl\MyDhlApiClient;
use App\Services\GeoNamesService;
use App\Support\CountrySupport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;
use InvalidArgumentException;
use Throwable;

class DhlSettingController extends Controller
{
    public function edit(DhlSettingsService $settings): View
    {
        $this->ensureMasterAdmin();

        $setting = $settings->current();
        $packages = DhlPackage::query()->orderBy('sort_order')->get();

        return view('admin.dhl.edit', [
            'setting' => $setting,
            'credentials' => [
                'api_key' => filled($setting->api_key),
                'api_secret' => filled($setting->api_secret),
                'account_number' => filled($setting->account_number),
                'account_masked' => $this->mask((string) $setting->account_number),
            ],
            'packages' => $packages,
            'destinationCountries' => CountrySupport::countries(app()->getLocale()),
        ]);
    }

    public function subdivisions(Request $request, GeoNamesService $geoNames): JsonResponse
    {
        $this->ensureMasterAdmin();
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
        ]);
        $countryCode = strtoupper((string) $validated['country']);

        abort_unless(CountrySupport::isSupportedIso2($countryCode), 422, 'País inválido.');

        return $this->geoNamesResponse(fn (): array => $geoNames->subdivisions(
            $countryCode,
            'en'
        ));
    }

    public function countries(GeoNamesService $geoNames): JsonResponse
    {
        $this->ensureMasterAdmin();

        return $this->geoNamesResponse(fn (): array => $geoNames->countries('en'));
    }

    public function cities(Request $request, GeoNamesService $geoNames): JsonResponse
    {
        $this->ensureMasterAdmin();
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'admin_code' => ['nullable', 'string', 'max:20'],
        ]);
        $countryCode = strtoupper((string) $validated['country']);

        abort_unless(CountrySupport::isSupportedIso2($countryCode), 422, 'País inválido.');

        return $this->geoNamesResponse(function () use ($geoNames, $countryCode, $validated): array {
            $adminCode = trim((string) ($validated['admin_code'] ?? ''));

            return $adminCode !== ''
                ? $geoNames->cities($countryCode, $adminCode, 'en')
                : $geoNames->citiesByCountry($countryCode, 'en');
        });
    }

    public function postalCodes(Request $request, GeoNamesService $geoNames): JsonResponse
    {
        $this->ensureMasterAdmin();
        $validated = $request->validate([
            'country' => ['required', 'string', 'size:2'],
            'city' => ['required', 'string', 'max:120'],
            'admin_code' => ['nullable', 'string', 'max:20'],
        ]);
        $countryCode = strtoupper((string) $validated['country']);

        abort_unless(CountrySupport::isSupportedIso2($countryCode), 422, 'País inválido.');

        return $this->geoNamesResponse(fn (): array => $geoNames->postalCodes(
            $countryCode,
            $validated['city'],
            $validated['admin_code'] ?? null,
        ));
    }

    public function update(Request $request, DhlSettingsService $settings): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $setting = $settings->current();
        $data = $this->validated($request);

        $data['enabled'] = $request->boolean('enabled');
        $data['rate_markup_enabled'] = $request->boolean('rate_markup_enabled');
        $data['fallback_measurements_enabled'] = $request->boolean('fallback_measurements_enabled');
        $data['free_shipping_enabled'] = $request->boolean('free_shipping_enabled');
        $data['origin_country_code'] = strtoupper((string) $data['origin_country_code']);
        $data['test_destination_country_code'] = strtoupper((string) $data['test_destination_country_code']);
        $data['currency'] = strtoupper((string) $data['currency']);
        $data['excluded_country_codes'] = collect(preg_split('/\s*,\s*/', (string) ($data['excluded_country_codes'] ?? ''), -1, PREG_SPLIT_NO_EMPTY))
            ->map(fn (string $countryCode): string => strtoupper(trim($countryCode)))
            ->unique()
            ->values()
            ->all();

        $selectedTestPackage = collect($data['packages'])
            ->first(fn (array $package): bool => (int) $package['id'] === (int) $data['test_package_id']);
        if (! $selectedTestPackage) {
            throw ValidationException::withMessages([
                'test_package_id' => 'Selecione uma embalagem DHL válida para o teste.',
            ]);
        }
        if ((float) $data['test_weight_kg'] > (float) $selectedTestPackage['max_gross_weight_kg']) {
            throw ValidationException::withMessages([
                'test_weight_kg' => 'O peso do teste ultrapassa o limite da embalagem selecionada.',
            ]);
        }
        $data['test_length_cm'] = $selectedTestPackage['length_cm'];
        $data['test_width_cm'] = $selectedTestPackage['width_cm'];
        $data['test_height_cm'] = $selectedTestPackage['height_cm'];

        if (! collect($data['packages'])->contains(fn (array $package): bool => (bool) ($package['active'] ?? false))) {
            throw ValidationException::withMessages([
                'packages' => 'Mantenha ao menos uma embalagem DHL ativa.',
            ]);
        }

        foreach ($data['packages'] as $index => $package) {
            if ((float) $package['max_gross_weight_kg'] <= (float) $package['tare_weight_kg']) {
                throw ValidationException::withMessages([
                    "packages.{$index}.max_gross_weight_kg" => 'O peso bruto máximo precisa ser maior que o peso da caixa vazia.',
                ]);
            }
        }

        foreach (['api_key', 'api_secret', 'account_number'] as $credential) {
            if (! filled($data[$credential] ?? null)) {
                unset($data[$credential]);
            } else {
                $data[$credential] = trim((string) $data[$credential]);
            }
        }

        if ($data['enabled']) {
            $missing = collect(['api_key', 'api_secret', 'account_number'])
                ->filter(fn (string $field): bool => blank($data[$field] ?? $setting->{$field}))
                ->values();

            if ($missing->isNotEmpty()) {
                throw ValidationException::withMessages([
                    'enabled' => 'Para ativar a DHL, preencha chave da API, segredo e número da conta.',
                ]);
            }
        }

        $packages = $data['packages'];
        unset($data['packages']);

        DB::transaction(function () use ($setting, $data, $packages): void {
            $setting->fill($data + ['updated_by' => auth()->id()])->save();

            foreach ($packages as $packageData) {
                $package = DhlPackage::query()->findOrFail($packageData['id']);
                $packageData['active'] = (bool) ($packageData['active'] ?? false);
                unset($packageData['id']);
                $package->update($packageData);
            }
        });
        $settings->clearCache();

        return back()->with('success', 'Configurações da DHL atualizadas com segurança.');
    }

    public function test(Request $request, DhlSettingsService $settings): RedirectResponse
    {
        $this->ensureMasterAdmin();
        $setting = $settings->current();
        $settings->clearCache();

        $testData = $request->validate([
            'test_package_id' => ['required', 'integer', 'exists:dhl_packages,id'],
            'test_weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'test_declared_value' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'rate_markup_enabled' => ['nullable', 'boolean'],
            'rate_markup_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'test_destination_country_code' => ['required', 'string', 'size:2', 'not_in:PY,py'],
            'test_destination_province_code' => ['nullable', 'string', 'max:20'],
            'test_destination_province_name' => ['nullable', 'string', 'max:120'],
            'test_destination_postal_code' => ['required', 'string', 'max:30'],
            'test_destination_city' => ['required', 'string', 'max:120'],
        ]);

        if ($setting->environment !== 'sandbox') {
            return back()->with('error', 'O teste pelo painel é permitido somente no ambiente sandbox.');
        }

        $testPackage = DhlPackage::query()->findOrFail($testData['test_package_id']);
        if ((float) $testData['test_weight_kg'] > (float) $testPackage->max_gross_weight_kg) {
            throw ValidationException::withMessages([
                'test_weight_kg' => 'O peso total ultrapassa o limite de '.number_format($testPackage->max_gross_weight_kg, 3, ',', '.').' kg da embalagem selecionada.',
            ]);
        }

        try {
            $client = new MyDhlApiClient($settings);
            $rates = new DhlRateService($client, $settings);
            $response = $rates->quote([
                'country_code' => strtoupper((string) $testData['test_destination_country_code']),
                'postal_code' => (string) $testData['test_destination_postal_code'],
                'city_name' => (string) $testData['test_destination_city'],
                'province_code' => (string) ($testData['test_destination_province_code'] ?? ''),
            ], [[
                'weight' => (float) $testData['test_weight_kg'],
                'length' => (float) $testPackage->length_cm,
                'width' => (float) $testPackage->width_cm,
                'height' => (float) $testPackage->height_cm,
                'type_code' => (string) $testPackage->dhl_package_code,
            ]], (float) $testData['test_declared_value']);

            $markupEnabled = $request->boolean('rate_markup_enabled');
            $markupPercent = $markupEnabled ? max(0, (float) $testData['rate_markup_percent']) : 0.0;
            $products = collect((array) ($response['products'] ?? []))
                ->take(10)
                ->map(function (array $product) use ($markupPercent): array {
                    $providerPrice = data_get($product, 'totalPrice.0.price');
                    $providerPrice = is_numeric($providerPrice) ? round((float) $providerPrice, 2) : null;
                    $markupValue = $providerPrice !== null ? round($providerPrice * ($markupPercent / 100), 2) : null;

                    return [
                        'code' => (string) ($product['productCode'] ?? '?'),
                        'name' => (string) ($product['productName'] ?? 'Produto DHL'),
                        'provider_price' => $providerPrice,
                        'markup_value' => $markupValue,
                        'total_price' => $providerPrice !== null ? round($providerPrice + $markupValue, 2) : null,
                        'currency' => data_get($product, 'totalPrice.0.priceCurrency'),
                        'delivery' => data_get($product, 'deliveryCapabilities.estimatedDeliveryDateAndTime'),
                    ];
                })->values()->all();

            return back()->with('dhl_test_result', [
                'success' => true,
                'message' => 'A DHL respondeu com sucesso no sandbox.',
                'markup_enabled' => $markupEnabled,
                'markup_percent' => $markupPercent,
                'products' => $products,
            ]);
        } catch (DhlApiException $exception) {
            return back()->with('dhl_test_result', [
                'success' => false,
                'message' => $exception->getMessage(),
                'status' => $exception->httpStatus(),
                'request_id' => $exception->requestId(),
            ]);
        } catch (InvalidArgumentException $exception) {
            return back()->with('dhl_test_result', [
                'success' => false,
                'message' => $exception->getMessage(),
            ]);
        }
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'enabled' => ['nullable', 'boolean'],
            'environment' => ['required', Rule::in(['sandbox', 'production'])],
            'api_key' => ['nullable', 'string', 'max:500'],
            'api_secret' => ['nullable', 'string', 'max:500'],
            'account_number' => ['nullable', 'string', 'max:80'],
            'currency' => ['required', 'string', 'size:3'],
            'unit_of_measurement' => ['required', Rule::in(['metric'])],
            'duties_taxes_payer' => ['required', Rule::in(['receiver', 'shipper'])],
            'incoterm' => ['required', Rule::in(['DAP', 'DDP'])],
            'excluded_country_codes' => ['nullable', 'string', 'max:255', 'regex:/^\s*[A-Za-z]{2}(\s*,\s*[A-Za-z]{2})*\s*$/'],
            'volumetric_divisor' => ['required', 'numeric', 'min:1000', 'max:10000'],
            'rate_markup_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'rate_markup_enabled' => ['nullable', 'boolean'],
            'fallback_measurements_enabled' => ['nullable', 'boolean'],
            'free_shipping_enabled' => ['nullable', 'boolean'],
            'free_shipping_min_items' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'free_shipping_min_subtotal' => ['nullable', 'numeric', 'min:0', 'max:9999999999'],
            'free_shipping_max_billable_weight_kg' => ['nullable', 'numeric', 'gt:0', 'max:1000'],
            'packages' => ['required', 'array', 'size:8'],
            'packages.*.id' => ['required', 'integer', 'exists:dhl_packages,id'],
            'packages.*.active' => ['nullable', 'boolean'],
            'packages.*.name' => ['required', 'string', 'max:120'],
            'packages.*.dhl_package_code' => ['nullable', 'string', 'max:20'],
            'packages.*.length_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'packages.*.width_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'packages.*.height_cm' => ['required', 'numeric', 'gt:0', 'max:300'],
            'packages.*.tare_weight_kg' => ['required', 'numeric', 'min:0', 'max:100'],
            'packages.*.max_gross_weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'packages.*.max_items' => ['required', 'integer', 'min:1', 'max:1000'],
            'packages.*.fill_ratio_percent' => ['required', 'numeric', 'min:10', 'max:100'],
            'packages.*.product_examples' => ['nullable', 'string', 'max:1000'],
            'origin_country_code' => ['required', 'string', 'size:2'],
            'origin_postal_code' => ['required', 'string', 'max:30'],
            'origin_city_name' => ['required', 'string', 'max:120'],
            'origin_province_code' => ['nullable', 'string', 'max:20'],
            'origin_province_name' => ['nullable', 'string', 'max:120'],
            'origin_district_name' => ['nullable', 'string', 'max:120'],
            'origin_address_line_1' => ['required', 'string', 'max:255'],
            'origin_address_line_2' => ['nullable', 'string', 'max:255'],
            'origin_company_name' => ['required', 'string', 'max:255'],
            'origin_trading_name' => ['nullable', 'string', 'max:255'],
            'origin_tax_id' => ['required', 'string', 'max:40'],
            'origin_contact_name' => ['required', 'string', 'max:120'],
            'origin_phone' => ['required', 'string', 'max:40'],
            'origin_email' => ['required', 'email', 'max:255'],
            'test_package_id' => ['required', 'integer', 'exists:dhl_packages,id'],
            'test_weight_kg' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'test_length_cm' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'test_width_cm' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'test_height_cm' => ['required', 'numeric', 'gt:0', 'max:1000'],
            'test_declared_value' => ['required', 'numeric', 'gt:0', 'max:9999999999'],
            'test_destination_country_code' => ['required', 'string', 'size:2', 'not_in:PY,py'],
            'test_destination_province_code' => ['nullable', 'string', 'max:20'],
            'test_destination_province_name' => ['nullable', 'string', 'max:120'],
            'test_destination_postal_code' => ['required', 'string', 'max:30'],
            'test_destination_city' => ['required', 'string', 'max:120'],
        ]);
    }

    private function geoNamesResponse(callable $callback): JsonResponse
    {
        try {
            return response()->json(['success' => true, 'data' => $callback()]);
        } catch (Throwable $exception) {
            report($exception);

            return response()->json([
                'success' => false,
                'message' => 'Não foi possível consultar o GeoNames agora. Tente novamente em alguns instantes.',
            ], 502);
        }
    }

    private function ensureMasterAdmin(): void
    {
        abort_unless(auth()->user()?->isMasterAdmin(), 403);
    }

    private function mask(string $value): string
    {
        return $value === '' ? '' : str_repeat('*', max(0, strlen($value) - 4)).substr($value, -4);
    }
}
