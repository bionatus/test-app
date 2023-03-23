<?php

namespace App\Types;

use Config;
use Illuminate\Support\Collection;
use MenaraSolutions\Geographer\Earth;

class CountryDataType
{
    const UNITED_STATES = 'US';

    public static function getPhoneCodes(): Collection
    {
        $geo          = new Earth();
        $rawCountries = $geo->getCountries();

        return Collection::make($rawCountries)->filter(function($country) {
            return in_array($country->code, self::getAllowedCountries());
        })->values()->pluck('phonePrefix')->unique();
    }

    public static function getAllowedCountries(): array
    {
        return Config::get('communications.allowed_countries');
    }
}
