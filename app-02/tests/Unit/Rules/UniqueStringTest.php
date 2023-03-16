<?php

namespace Tests\Unit\Rules;

use App\Rules\UniqueString;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UniqueStringTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_false_if_provided_value_is_object_or_array()
    {
        $rule = new UniqueString();

        $this->assertFalse($rule->passes('', ['value']));
        $this->assertFalse($rule->passes('', json_decode('{"value":"1"}')));
    }

    /** @test */
    public function it_returns_true_if_value_has_never_been_used()
    {
        $rule = new UniqueString();

        $this->assertTrue($rule->passes('', 'value'));
    }

    /** @test */
    public function it_returns_false_if_value_has_already_been_used()
    {
        $rule = new UniqueString();

        $this->assertTrue($rule->passes('', 'value'));
        $this->assertFalse($rule->passes('', 'value'));
    }

    /** @test */
    public function it_returns_false_for_same_trimmed_strings()
    {
        $rule = new UniqueString();

        $this->assertTrue($rule->passes('', 'value '));
        $this->assertFalse($rule->passes('', ' value'));
    }

    /** @test */
    public function it_returns_false_for_same_case_insensitive_strings()
    {
        $rule = new UniqueString();

        $this->assertTrue($rule->passes('', 'Value'));
        $this->assertFalse($rule->passes('', 'vaLue'));
    }

    /** @test */
    public function it_returns_a_message()
    {
        $rule = new UniqueString();

        $this->assertSame('The :attribute has already been taken.', $rule->message());
    }
}
