<?php

namespace App\Support;

final class CustomerDocument
{
    public const CPF = 'cpf';
    public const RG_BR = 'rg_br';
    public const CI_PY = 'ci_py';
    public const RUC_PY = 'ruc_py';

    public static function types(): array
    {
        return [self::CPF, self::RG_BR, self::CI_PY, self::RUC_PY];
    }

    public static function inferType(?string $type, mixed $value, ?string $countryCode = null): string
    {
        if (in_array($type, self::types(), true)) {
            return $type;
        }

        $raw = strtoupper(trim((string) $value));
        $digits = preg_replace('/\D+/', '', $raw);

        if (strlen($digits) === 11) {
            return self::CPF;
        }
        if (str_contains($raw, '-') && (string) $countryCode !== '55') {
            return self::RUC_PY;
        }

        return (string) $countryCode === '55' ? self::RG_BR : self::CI_PY;
    }

    public static function format(mixed $value, string $type): string
    {
        return match ($type) {
            self::CPF => self::formatCpf($value),
            self::RG_BR => self::formatRg($value),
            self::CI_PY => self::formatCi($value),
            self::RUC_PY => self::formatRuc($value),
            default => trim((string) $value),
        };
    }

    public static function isValid(mixed $value, string $type): bool
    {
        return match ($type) {
            self::CPF => self::isValidCpf($value),
            self::RG_BR => self::isValidRg($value),
            self::CI_PY => self::isValidCi($value),
            self::RUC_PY => self::isValidRuc($value),
            default => false,
        };
    }

    public static function validationMessage(string $type): string
    {
        return match ($type) {
            self::CPF => __('messages.document_invalid_cpf'),
            self::RG_BR => __('messages.document_invalid_rg_br'),
            self::CI_PY => __('messages.document_invalid_ci_py'),
            self::RUC_PY => __('messages.document_invalid_ruc_py'),
            default => __('messages.document_invalid_generic'),
        };
    }

    public static function isValidCpf(mixed $value): bool
    {
        $cpf = preg_replace('/\D+/', '', (string) $value);
        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            return false;
        }

        for ($position = 9; $position <= 10; $position++) {
            $sum = 0;
            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }
            $digit = (10 * $sum) % 11;
            $digit = $digit === 10 ? 0 : $digit;
            if ((int) $cpf[$position] !== $digit) {
                return false;
            }
        }

        return true;
    }

    public static function isValidRuc(mixed $value): bool
    {
        $raw = preg_replace('/[.\s]/', '', trim((string) $value));
        if (!preg_match('/^(\d{3,8})-(\d)$/', $raw, $matches)) {
            return false;
        }

        return self::rucCheckDigit($matches[1]) === (int) $matches[2];
    }

    public static function rucCheckDigit(string $base): int
    {
        $weight = 2;
        $total = 0;
        for ($index = strlen($base) - 1; $index >= 0; $index--) {
            if ($weight > 11) $weight = 2;
            $total += (int) $base[$index] * $weight;
            $weight++;
        }
        $remainder = $total % 11;

        return $remainder > 1 ? 11 - $remainder : 0;
    }

    private static function isValidRg(mixed $value): bool
    {
        $clean = preg_replace('/[^0-9X]/i', '', strtoupper((string) $value));
        return (bool) preg_match('/^\d{4,9}[0-9X]$/', $clean)
            && !preg_match('/^(\d)\1+$/', $clean);
    }

    private static function isValidCi(mixed $value): bool
    {
        $digits = preg_replace('/\D+/', '', (string) $value);
        return (bool) preg_match('/^\d{5,10}$/', $digits)
            && !preg_match('/^(\d)\1+$/', $digits);
    }

    private static function formatCpf(mixed $value): string
    {
        $digits = substr(preg_replace('/\D+/', '', (string) $value), 0, 11);
        $parts = [];
        if (strlen($digits) > 0) $parts[] = substr($digits, 0, 3);
        if (strlen($digits) > 3) $parts[] = substr($digits, 3, 3);
        if (strlen($digits) > 6) $parts[] = substr($digits, 6, 3);
        $formatted = implode('.', $parts);
        if (strlen($digits) > 9) $formatted .= '-'.substr($digits, 9, 2);

        return $formatted;
    }

    private static function formatRg(mixed $value): string
    {
        $clean = substr(preg_replace('/[^0-9X]/i', '', strtoupper((string) $value)), 0, 10);
        if (strlen($clean) <= 4) return $clean;
        $check = substr($clean, -1);
        $body = substr($clean, 0, -1);
        $groups = [];
        while (strlen($body) > 3) {
            array_unshift($groups, substr($body, -3));
            $body = substr($body, 0, -3);
        }
        if ($body !== '') array_unshift($groups, $body);

        return implode('.', $groups).'-'.$check;
    }

    private static function formatCi(mixed $value): string
    {
        $digits = substr(preg_replace('/\D+/', '', (string) $value), 0, 10);
        $groups = [];
        while (strlen($digits) > 3) {
            array_unshift($groups, substr($digits, -3));
            $digits = substr($digits, 0, -3);
        }
        if ($digits !== '') array_unshift($groups, $digits);

        return implode('.', $groups);
    }

    private static function formatRuc(mixed $value): string
    {
        $digits = substr(preg_replace('/\D+/', '', (string) $value), 0, 9);
        if (strlen($digits) <= 3) return $digits;

        return substr($digits, 0, -1).'-'.substr($digits, -1);
    }
}
