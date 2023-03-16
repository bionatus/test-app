<?php

namespace App\Rules;

use Config;
use Illuminate\Contracts\Validation\Rule;
use Lang;

class InValidCountries implements Rule
{
    public function passes($attribute, $value): bool
    {
        if (!is_string($value) || !$value) {
            return false;
        }

        return in_array($value, Config::get('communications.allowed_countries'));
    }

    public function message(): string
    {
        return Lang::get('validation.in');
    }
}
