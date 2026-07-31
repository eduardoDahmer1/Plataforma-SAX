<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class BrazilianCpf implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $cpf = preg_replace('/\D+/', '', (string) $value);

        if (strlen($cpf) !== 11 || preg_match('/^(\d)\1{10}$/', $cpf)) {
            $fail('Informe um CPF brasileiro válido para pagar com Pix.');
            return;
        }

        for ($position = 9; $position <= 10; $position++) {
            $sum = 0;

            for ($index = 0; $index < $position; $index++) {
                $sum += (int) $cpf[$index] * (($position + 1) - $index);
            }

            $digit = (10 * $sum) % 11;
            $digit = $digit === 10 ? 0 : $digit;

            if ((int) $cpf[$position] !== $digit) {
                $fail('Informe um CPF brasileiro válido para pagar com Pix.');
                return;
            }
        }
    }
}
