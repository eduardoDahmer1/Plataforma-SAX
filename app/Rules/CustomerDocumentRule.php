<?php

namespace App\Rules;

use App\Support\CustomerDocument;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class CustomerDocumentRule implements ValidationRule
{
    public function __construct(private readonly string $type)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!CustomerDocument::isValid($value, $this->type)) {
            $fail(CustomerDocument::validationMessage($this->type));
        }
    }
}
