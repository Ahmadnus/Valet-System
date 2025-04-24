<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class SyrianPhoneNumber implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
          if (!preg_match('/^\+9639\d{8}$/', $value)) {
            $fail("The {$attribute} must be a valid Syrian mobile number starting with +9639 and followed by 8 digits.");
        }
    }
}