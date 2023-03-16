<?php

namespace Tests\Unit\Rules;

use App\Rules\ProhibitedAttribute;
use Tests\TestCase;

class ProhibitedAttributeTest extends TestCase
{
    /** @test */
    public function it_returns_false_if_value_is_a_string()
    {
        $rule = new ProhibitedAttribute();

        $this->assertFalse($rule->passes('', 'any value'));
    }

    /** @test */
    public function it_returns_false_if_value_is_an_empty_string()
    {
        $rule = new ProhibitedAttribute();

        $this->assertFalse($rule->passes('', ''));
    }

    /** @test */
    public function it_returns_false_if_value_is_a_number()
    {
        $rule = new ProhibitedAttribute();

        $this->assertFalse($rule->passes('', 12345));
    }

    /** @test */
    public function it_returns_false_if_value_is_an_array()
    {
        $rule = new ProhibitedAttribute();

        $this->assertFalse($rule->passes('', ['any value']));
    }

    /** @test */
    public function it_returns_false_if_value_is_an_empty_array()
    {
        $rule = new ProhibitedAttribute();

        $this->assertFalse($rule->passes('', []));
    }

    /** @test */
    public function it_returns_false_if_value_is_null()
    {
        $rule = new ProhibitedAttribute();

        $this->assertFalse($rule->passes('', null));
    }
}
