<?php

namespace App\Rules;

use App\Support\OperatingSchedule;
use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidLoanSchedule implements ValidationRule
{
    public function __construct(private readonly bool $requireFuture = true)
    {
    }

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!is_string($value) && !is_numeric($value)) {
            $fail('La fecha y hora proporcionada no es válida.');

            return;
        }

        $error = OperatingSchedule::validateDateTime((string) $value, $this->requireFuture);
        if ($error !== null) {
            $fail($error);
        }
    }
}
