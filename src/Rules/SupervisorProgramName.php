<?php

namespace ClarionApp\Backend\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SupervisorProgramName implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) || !preg_match('/^[a-zA-Z0-9][a-zA-Z0-9_-]{0,63}$/', $value)) {
            $fail('The :attribute must be a valid program name.');
        }
    }
}
