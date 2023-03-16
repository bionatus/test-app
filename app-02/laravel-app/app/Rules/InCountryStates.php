<?php

namespace App\Rules;

use Exception;
use Illuminate\Contracts\Validation\Rule;
use Lang;
use MenaraSolutions\Geographer\Country;

class InCountryStates implements Rule
{
    private ?string $countryIso = null;

    public function __construct($value)
    {
        if (is_string($value)) {
            $this->countryIso = $value;
        }
    }

    public function passes($attribute, $value): bool
    {
        if (!$this->countryIso) {
            return false;
        }

        try {
            $country = Country::build($this->countryIso);

            return in_array($value, $country->getStates()->pluck('isoCode'));
        } catch (Exception $exception) {
            return false;
        }
    }

    public function message(): string
    {
        return Lang::get('validation.in');
    }
}
