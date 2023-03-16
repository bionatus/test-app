<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Lang;

class MoneyFormat implements Rule
{
    public function passes($attribute, $value): bool
    {
        return preg_match('/^\d+(\.\d{1,2})?$/', $value);
    }

    public function message(): string
    {
        return Lang::get('validation.regex');
    }
}
