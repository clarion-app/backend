<?php

namespace ClarionApp\Backend\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ComposerPackageName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^[a-z0-9]([_.\-]?[a-z0-9]+)*\/[a-z0-9]([_.\-]?[a-z0-9]+)*$/', $value)) {
            $fail('The :attribute must be a valid composer package name (vendor/package).');
        }
    }
}
