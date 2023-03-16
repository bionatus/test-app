<?php

namespace Tests\Unit\Models\Agent\Scopes;

use App\Models\Agent;
use App\Models\AgentCall;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotEngagedInCallTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_returns_agents_that_are_not_busy_on_a_call()
    {
        AgentCall::factory()->inProgress()->create();
        AgentCall::factory()->invalid()->create();
        AgentCall::factory()->completed()->create();
        Agent::factory()->create();

        $agents = Agent::scoped(new Agent\Scopes\NotEngagedInCall())->get();

        $this->assertCount(3, $agents);
    }
}
