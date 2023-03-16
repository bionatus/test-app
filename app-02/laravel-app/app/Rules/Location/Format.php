<?php

namespace App\Rules\Location;

use App\Types\Location;
use Illuminate\Contracts\Validation\Rule;

class Format implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        return Location::isValidStringFormat($value);
    }

    public function message()
    {
        return 'The location must follow the "latitude,longitude" format.';
    }
}
