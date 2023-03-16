<?php

namespace Tests\Unit\Types;

use App\Types\CountryDataType;
use Config;
use MenaraSolutions\Geographer\Country;
use Tests\TestCase;

class CountryDataTypeTest extends TestCase
{
    /** @test */
    public function it_returns_a_list_of_unique_valid_country_phone_codes()
    {
        Config::set('communications.allowed_countries', $allowedCountries = ['US', 'CA', 'MX', 'AU', 'AR']);
        $validPhoneCodes = [];
        foreach ($allowedCountries as $countryCode) {
            $validPhoneCodes[] = Country::build($countryCode)->phonePrefix;
        }
        $uniqueValidPhoneCodes = array_unique($validPhoneCodes);

        $this->assertEqualsCanonicalizing($uniqueValidPhoneCodes, CountryDataType::getPhoneCodes()->toArray());
    }

    /** @test */
    public function it_returns_a_list_of_allowed_countries()
    {
        Config::set('communications.allowed_countries', $allowedCountries = ['US', 'CA', 'MX', 'AU', 'AR']);

        $this->assertSame($allowedCountries, CountryDataType::getAllowedCountries());
    }
}
