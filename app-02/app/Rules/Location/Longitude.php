<?php

namespace App\Rules\Location;

use App\Types\Location;
use Illuminate\Contracts\Validation\Rule;

class Longitude implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        return Location::isValidLongitude($value);
    }

    public function message()
    {
        return 'The longitude component must be between -180 and 180.';
    }
}
