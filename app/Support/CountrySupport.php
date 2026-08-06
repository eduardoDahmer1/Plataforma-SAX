<?php

namespace App\Support;

use ResourceBundle;

final class CountrySupport
{
    public const BRAZIL = 'brasil';
    public const PARAGUAY = 'paraguai';

    /** ISO 3166-1 alpha-2: evita exibir agrupamentos internos do ICU como UE/ONU. */
    private const ISO2_CODES = 'AD AE AF AG AI AL AM AO AQ AR AS AT AU AW AX AZ BA BB BD BE BF BG BH BI BJ BL BM BN BO BQ BR BS BT BV BW BY BZ CA CC CD CF CG CH CI CK CL CM CN CO CR CU CV CW CX CY CZ DE DJ DK DM DO DZ EC EE EG EH ER ES ET FI FJ FK FM FO FR GA GB GD GE GF GG GH GI GL GM GN GP GQ GR GS GT GU GW GY HK HM HN HR HT HU ID IE IL IM IN IO IQ IR IS IT JE JM JO JP KE KG KH KI KM KN KP KR KW KY KZ LA LB LC LI LK LR LS LT LU LV LY MA MC MD ME MF MG MH MK ML MM MN MO MP MQ MR MS MT MU MV MW MX MY MZ NA NC NE NF NG NI NL NO NP NR NU NZ OM PA PE PF PG PH PK PL PM PN PR PS PT PW PY QA RE RO RS RU RW SA SB SC SD SE SG SH SI SJ SK SL SM SN SO SR SS ST SV SX SY SZ TC TD TF TG TH TJ TK TL TM TN TO TR TT TV TW TZ UA UG UM US UY UZ VA VC VE VG VI VN VU WF WS YE YT ZA ZM ZW';

    public static function normalizeForStorage(mixed $country): string
    {
        $value = trim((string) $country);
        $lower = mb_strtolower($value);

        if (in_array($lower, ['br', 'bra', 'brasil', 'brazil'], true)) {
            return self::BRAZIL;
        }

        if (in_array($lower, ['py', 'pry', 'paraguai', 'paraguay'], true)) {
            return self::PARAGUAY;
        }

        $iso2 = strtoupper($value);

        return self::isSupportedIso2($iso2) ? $iso2 : '';
    }

    public static function iso2(mixed $country): string
    {
        return match (self::normalizeForStorage($country)) {
            self::BRAZIL => 'BR',
            self::PARAGUAY => 'PY',
            default => strtoupper(trim((string) $country)),
        };
    }

    public static function isBrazil(mixed $country): bool
    {
        return self::normalizeForStorage($country) === self::BRAZIL;
    }

    public static function isParaguay(mixed $country): bool
    {
        return self::normalizeForStorage($country) === self::PARAGUAY;
    }

    public static function usesDhl(mixed $country): bool
    {
        $normalized = self::normalizeForStorage($country);

        return $normalized !== ''
            && ! in_array($normalized, [self::BRAZIL, self::PARAGUAY], true);
    }

    public static function shippingProvider(mixed $country): string
    {
        if (self::isBrazil($country)) {
            return 'manual_br';
        }

        if (self::isParaguay($country)) {
            return 'local_py';
        }

        return self::usesDhl($country) ? 'dhl' : 'unknown';
    }

    public static function isSupported(mixed $country): bool
    {
        return self::normalizeForStorage($country) !== '';
    }

    public static function isSupportedIso2(string $iso2): bool
    {
        return in_array($iso2, self::iso2Codes(), true)
            && array_key_exists($iso2, self::localizedNames('pt'));
    }

    /**
     * Lista mundial renderizada localmente. O GeoNames complementa os dados
     * dinâmicos (divisões e cidades), sem tornar BR/PY dependentes da API.
     */
    public static function countries(?string $locale = null): array
    {
        $locale ??= app()->getLocale();
        $names = self::localizedNames($locale);
        $countries = [];

        foreach ($names as $iso2 => $name) {
            $countries[] = [
                'iso2' => $iso2,
                'value' => match ($iso2) {
                    'BR' => self::BRAZIL,
                    'PY' => self::PARAGUAY,
                    default => $iso2,
                },
                'name' => $name,
                'shipping_provider' => match ($iso2) {
                    'BR' => 'manual_br',
                    'PY' => 'local_py',
                    default => 'dhl',
                },
            ];
        }

        usort($countries, static function (array $a, array $b): int {
            $priority = ['BR' => 0, 'PY' => 1];
            $aPriority = $priority[$a['iso2']] ?? 2;
            $bPriority = $priority[$b['iso2']] ?? 2;

            return $aPriority <=> $bPriority ?: strnatcasecmp($a['name'], $b['name']);
        });

        return $countries;
    }

    public static function name(mixed $country, ?string $locale = null): string
    {
        $iso2 = self::iso2($country);

        return self::localizedNames($locale ?? app()->getLocale())[$iso2]
            ?? strtoupper(trim((string) $country));
    }

    public static function localizedNames(string $locale): array
    {
        $locale = match (substr($locale, 0, 2)) {
            'es' => 'es',
            'en' => 'en',
            default => 'pt_BR',
        };

        $bundle = ResourceBundle::create($locale, 'ICUDATA-region');
        $regions = $bundle?->get('Countries');
        $names = [];

        if ($regions instanceof ResourceBundle) {
            foreach ($regions as $code => $name) {
                if (in_array((string) $code, self::iso2Codes(), true)) {
                    $names[(string) $code] = (string) $name;
                }
            }
        }

        if ($names === []) {
            $names = [
                'BR' => 'Brasil',
                'PY' => 'Paraguai',
            ];
        }

        return $names;
    }

    private static function iso2Codes(): array
    {
        static $codes;

        return $codes ??= explode(' ', self::ISO2_CODES);
    }
}
