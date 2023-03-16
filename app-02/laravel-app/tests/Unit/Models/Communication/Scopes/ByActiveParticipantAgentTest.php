<?php

namespace Tests\Unit\Models\Communication\Scopes;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Communication;
use App\Models\Communication\Scopes\ByActiveParticipantAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByActiveParticipantAgentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_participant_agent()
    {
        $agent = Agent::factory()->create();

        $agentCall = AgentCall::factory()->completed()->usingAgent($agent)->create();
        AgentCall::factory()->usingAgent($agent)->usingCall($agentCall->call)->create();
        AgentCall::factory()->usingAgent($agent)->completed()->count(2)->create();

        $communications = Communication::scoped(new ByActiveParticipantAgent($agent))->get();

        $this->assertCount(3, $communications);
    }
}
