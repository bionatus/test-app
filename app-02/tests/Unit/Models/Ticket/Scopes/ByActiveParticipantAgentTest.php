<?php

namespace Tests\Unit\Models\Ticket\Scopes;

use App\Models\Agent;
use App\Models\AgentCall;
use App\Models\Ticket;
use App\Models\Ticket\Scopes\ByActiveParticipantAgent;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ByActiveParticipantAgentTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_filters_by_participant_agent()
    {
        $agent = Agent::factory()->create();

        $agentCall = AgentCall::factory()->usingAgent($agent)->create();
        $session   = $agentCall->call->communication->session;
        AgentCall::factory()->usingAgent($agent)->usingCall($agentCall->call)->create();
        $session->ticket()->associate(Ticket::factory()->create());
        $session->save();

        $anotherAgentCall = AgentCall::factory()->completed()->usingAgent($agent)->create();
        $anotherSession   = $anotherAgentCall->call->communication->session;
        $anotherSession->ticket()->associate(Ticket::factory()->create());
        $anotherSession->save();

        $tickets = Ticket::scoped(new ByActiveParticipantAgent($agent))->get();

        $this->assertCount(1, $tickets);
    }
}
