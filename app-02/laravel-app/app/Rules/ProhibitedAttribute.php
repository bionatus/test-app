<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ProhibitedAttribute implements Rule
{
    public function passes($attribute, $value): bool
    {
        return false;
    }

    public function message(): string
    {
        return ':attribute is not allowed.';
    }
}
