<?php

namespace Tests\Unit\Rules;

use App\Rules\InValidCountries;
use Config;
use Tests\TestCase;

class InValidCountriesTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_country_is_not_a_string()
    {
        $rule = new InValidCountries();

        $this->assertFalse($rule->passes('', []));
    }

    /** @test */
    public function it_returns_false_if_country_is_empty()
    {
        $rule = new InValidCountries();

        $this->assertFalse($rule->passes('', ''));
    }

    /** @test */
    public function it_returns_false_if_country_is_no_in_the_valid_country_config()
    {
        Config::set('communications.allowed_countries', ['valid']);

        $rule = new InValidCountries();

        $this->assertFalse($rule->passes('', 'invalid'));
    }

    /** @test */
    public function it_returns_true_on_valid_country()
    {
        Config::set('communications.allowed_countries', ['valid']);

        $rule = new InValidCountries();

        $this->assertTrue($rule->passes('', 'valid'));
    }
}
