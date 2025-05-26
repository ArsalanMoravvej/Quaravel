<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ChoicesCountLessThanOptionsCount implements ValidationRule
{
    public function __construct(private array $options) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if ($value > count($this->options)) {
            $fail('The :attribute field must not be greater than the number of options.');
        }
    }
}
