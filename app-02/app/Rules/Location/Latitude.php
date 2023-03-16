<?php

namespace App\Rules\Location;

use App\Types\Location;
use Illuminate\Contracts\Validation\Rule;

class Latitude implements Rule
{
    public function passes($attribute, $value)
    {
        if (!is_string($value)) {
            return false;
        }

        return Location::isValidLatitude($value);
    }

    public function message()
    {
        return 'The latitude component must be between -90 and 90.';
    }
}
