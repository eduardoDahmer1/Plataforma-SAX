<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('dhl_settings')
            ->select(['id', 'excluded_country_codes'])
            ->orderBy('id')
            ->get()
            ->each(function (object $setting): void {
                $countries = $this->decodeCountries($setting->excluded_country_codes);
                $countries = collect($countries)
                    ->map(fn ($code): string => strtoupper(trim((string) $code)))
                    ->filter(fn (string $code): bool => $code !== '' && $code !== 'BR')
                    ->prepend('PY')
                    ->unique()
                    ->values()
                    ->all();

                DB::table('dhl_settings')->where('id', $setting->id)->update([
                    'excluded_country_codes' => json_encode($countries),
                ]);
            });
    }

    public function down(): void
    {
        DB::table('dhl_settings')
            ->select(['id', 'excluded_country_codes'])
            ->orderBy('id')
            ->get()
            ->each(function (object $setting): void {
                $countries = collect($this->decodeCountries($setting->excluded_country_codes))
                    ->map(fn ($code): string => strtoupper(trim((string) $code)))
                    ->filter()
                    ->push('BR')
                    ->unique()
                    ->values()
                    ->all();

                DB::table('dhl_settings')->where('id', $setting->id)->update([
                    'excluded_country_codes' => json_encode($countries),
                ]);
            });
    }

    private function decodeCountries(mixed $value): array
    {
        if (is_array($value)) {
            return $value;
        }

        $decoded = json_decode((string) $value, true);

        return is_array($decoded) ? $decoded : [];
    }
};
