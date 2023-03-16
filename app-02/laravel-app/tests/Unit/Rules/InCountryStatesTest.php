<?php

namespace Tests\Unit\Rules;

use App\Rules\InCountryStates;
use App\Types\CountryDataType;
use Tests\TestCase;

class InCountryStatesTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_null_country_is_provided()
    {
        $rule = new InCountryStates(null);

        $this->assertFalse($rule->passes('', CountryDataType::UNITED_STATES . '-AR'));
    }

    /** @test */
    public function it_returns_false_if_invalid_country_is_provided()
    {
        $rule = new InCountryStates('invalid');

        $this->assertFalse($rule->passes('', CountryDataType::UNITED_STATES . '-AR'));
    }

    /** @test */
    public function it_returns_false_if_sate_does_not_exist_in_the_provided_country()
    {
        $rule = new InCountryStates(CountryDataType::UNITED_STATES);

        $this->assertFalse($rule->passes('', 'invalid'));
    }

    /** @test */
    public function it_returns_true_on_valid_state_for_provided_country()
    {
        $rule = new InCountryStates(CountryDataType::UNITED_STATES);

        $this->assertTrue($rule->passes('', CountryDataType::UNITED_STATES . '-AR'));
    }
}
