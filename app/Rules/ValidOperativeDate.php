<?php

namespace App\Rules;

use App\Support\OperatingSchedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidOperativeDate implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail('La fecha proporcionada no es válida.');

            return;
        }

        $error = OperatingSchedule::validateDate((string) $value);
        if ($error !== null) {
            $fail($error);
        }
    }
}
