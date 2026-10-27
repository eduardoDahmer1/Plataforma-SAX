<?php

namespace App\Services;

use Illuminate\Support\Facades\Cache;
use InvalidArgumentException;

final class BrazilLocationService
{
    private const STATES = [
        ['id' => 12, 'code' => 'AC', 'name' => 'Acre'],
        ['id' => 27, 'code' => 'AL', 'name' => 'Alagoas'],
        ['id' => 16, 'code' => 'AP', 'name' => 'Amapá'],
        ['id' => 13, 'code' => 'AM', 'name' => 'Amazonas'],
        ['id' => 29, 'code' => 'BA', 'name' => 'Bahia'],
        ['id' => 23, 'code' => 'CE', 'name' => 'Ceará'],
        ['id' => 53, 'code' => 'DF', 'name' => 'Distrito Federal'],
        ['id' => 32, 'code' => 'ES', 'name' => 'Espírito Santo'],
        ['id' => 52, 'code' => 'GO', 'name' => 'Goiás'],
        ['id' => 21, 'code' => 'MA', 'name' => 'Maranhão'],
        ['id' => 51, 'code' => 'MT', 'name' => 'Mato Grosso'],
        ['id' => 50, 'code' => 'MS', 'name' => 'Mato Grosso do Sul'],
        ['id' => 31, 'code' => 'MG', 'name' => 'Minas Gerais'],
        ['id' => 15, 'code' => 'PA', 'name' => 'Pará'],
        ['id' => 25, 'code' => 'PB', 'name' => 'Paraíba'],
        ['id' => 41, 'code' => 'PR', 'name' => 'Paraná'],
        ['id' => 26, 'code' => 'PE', 'name' => 'Pernambuco'],
        ['id' => 22, 'code' => 'PI', 'name' => 'Piauí'],
        ['id' => 33, 'code' => 'RJ', 'name' => 'Rio de Janeiro'],
        ['id' => 24, 'code' => 'RN', 'name' => 'Rio Grande do Norte'],
        ['id' => 43, 'code' => 'RS', 'name' => 'Rio Grande do Sul'],
        ['id' => 11, 'code' => 'RO', 'name' => 'Rondônia'],
        ['id' => 14, 'code' => 'RR', 'name' => 'Roraima'],
        ['id' => 42, 'code' => 'SC', 'name' => 'Santa Catarina'],
        ['id' => 35, 'code' => 'SP', 'name' => 'São Paulo'],
        ['id' => 28, 'code' => 'SE', 'name' => 'Sergipe'],
        ['id' => 17, 'code' => 'TO', 'name' => 'Tocantins'],
    ];

    private const GEONAMES_ADMIN_CODES = [
        12 => '01', 27 => '02', 16 => '03', 13 => '04', 29 => '05', 23 => '06', 53 => '07',
        32 => '08', 52 => '29', 21 => '13', 51 => '14', 50 => '11', 31 => '15', 15 => '16',
        25 => '17', 41 => '18', 26 => '30', 22 => '20', 33 => '21', 24 => '22', 43 => '23',
        11 => '24', 14 => '25', 42 => '26', 35 => '27', 28 => '28', 17 => '31',
    ];

    public function __construct(private GeoNamesService $geoNames)
    {
    }

    public function states(): array
    {
        return self::STATES;
    }

    public function cities(int $stateId): array
    {
        $adminCode = self::GEONAMES_ADMIN_CODES[$stateId] ?? null;
        if ($adminCode === null) {
            throw new InvalidArgumentException('O estado brasileiro informado é inválido.');
        }

        return Cache::remember("geonames:brazil-cities:{$stateId}:v2", now()->addDays(30), function () use ($adminCode): array {
            return collect($this->geoNames->secondLevelSubdivisions('BR', $adminCode, 'pt'))
                ->map(fn (array $city): array => [
                    'id' => (int) ($city['geoname_id'] ?? 0),
                    'name' => trim((string) ($city['name'] ?? '')),
                ])
                ->filter(fn (array $city): bool => $city['id'] > 0 && $city['name'] !== '')
                ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
                ->values()
                ->all();
        });
    }
}
