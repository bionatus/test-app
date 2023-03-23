<?php

namespace Tests\Unit\Rules\AgentCall;

use App\Models\AgentCall;
use App\Models\Call;
use App\Rules\AgentCall\Exists;
use App\Rules\Call\Exists as CallExists;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Lang;
use Mockery;
use Tests\TestCase;

class ExistsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_a_message()
    {
        $rule = new Exists(new CallExists('provider'));

        $this->assertSame(Lang::get('validation.exists'), $rule->message());
    }

    /** @test */
    public function it_fails_if_there_is_no_call()
    {
        $mock = Mockery::mock(CallExists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn(new Call());
        $rule = new Exists($mock);

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_fails_if_there_is_no_agent_call()
    {
        $call = Call::factory()->create();
        $mock = Mockery::mock(CallExists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn($call);
        $rule = new Exists($mock);

        $this->assertFalse($rule->passes('attribute', 'invalid'));
    }

    /** @test */
    public function it_returns_an_stub_agent_call_if_fails()
    {
        $rule = new Exists(new CallExists('provider'));

        $this->assertFalse($rule->agentCall()->exists);
    }

    /** @test */
    public function it_passes()
    {
        $agentCall = AgentCall::factory()->create();
        $mock      = Mockery::mock(CallExists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn($agentCall->call);
        $rule = new Exists($mock);

        $this->assertTrue($rule->passes('attribute', 'client:' . $agentCall->agent_id));
    }

    /** @test */
    public function it_returns_a_real_agent_call_if_passes()
    {
        $agentCall = AgentCall::factory()->create();
        $mock      = Mockery::mock(CallExists::class);
        $mock->shouldReceive('call')->withNoArgs()->once()->andReturn($agentCall->call);
        $rule = new Exists($mock);

        $rule->passes('attribute', 'client:' . $agentCall->agent_id);
        $this->assertSame($agentCall->getKey(), $rule->agentCall()->getKey());
    }
}
