<?php

namespace Tests\Unit\Rules\Agent;

use App\Models\Agent;
use App\Rules\Agent\Exists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Tests\TestCase;

class ExistsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_message()
    {
        $rule = new Exists();

        $this->assertSame(Lang::get('validation.exists'), $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_no_agent()
    {
        $rule = new Exists();

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_fails_if_there_value_does_not_has_correct_format()
    {
        $rule = new Exists();

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_passes()
    {
        $agent = Agent::factory()->create();
        $rule  = new Exists();

        $this->assertTrue($rule->passes('attribute', 'client:' . $agent->getKey()));
    }
}
