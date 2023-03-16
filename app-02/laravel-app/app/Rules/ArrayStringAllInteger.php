<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;
use Illuminate\Support\Collection;

class ArrayStringAllInteger implements Rule
{
    public function passes($attribute, $value)
    {
        $values = Collection::make(explode(',', $value));

        return $values->every(function ($value) {
            if (!is_numeric($value)){
                return false;
            }
            return is_integer($value * 1);
        });
    }

    public function message()
    {
        return 'The values in :attribute must be integers.';
    }
}
